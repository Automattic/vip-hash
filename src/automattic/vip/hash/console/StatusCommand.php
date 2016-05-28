<?php

namespace automattic\vip\hash\console;

use automattic\vip\hash\DataModel;
use automattic\vip\hash\Pdo_Data_Model;
use Symfony\Component\Console\Command\Command;
use automattic\vip\hash\console\FileSystemCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ScanCommand
 * @package automattic\vip\hash\console
 */
class StatusCommand extends FileSystemCommand {

	protected function configure() {
		$this->setName( 'status' )
			->setDescription( 'take a folder and generates a status report of good bad and unknown file hashes' )
			->addArgument(
				'folder',
				InputArgument::OPTIONAL,
				'A file hash to find, or a file to be hashed. Assumes hash if the given value is not a locatable file'
			);
	}

	protected function execute( InputInterface $input, OutputInterface $output ) {
		$folder = $input->getArgument( 'folder' );
		if ( empty( $folder ) ) {
			$folder = '.';
		}
		$data_model = new Pdo_Data_Model();
		$data = $this->processNode( $folder, $data_model );
		$tree = new \cli\Tree;
		$tree->setData( $this->prettify_tree( $data ) );
		$tree->setRenderer( new \cli\tree\Markdown( 4 ) );
		$output->write( $tree->render() );

		$good = 1;
		$bad = 1;
		$unknown = 1;
		$total = $good + $bad + $unknown;
		$percentage = ( ( $good + $bad ) / $total ) * 100;
		$final = '<info>'.$good.' good</info>, <error>'.$bad.' bad</error>, <comment>'.$unknown.' unknown</comment>, '.number_format( $percentage, 2 ).'% seen';
		$output->writeln( '' );
		$output->writeln( $final );
	}

	function prettify_tree( array $data ) {
		if ( ! empty( $data['folder'] ) ) {
			$contents = array();
			foreach ( $data['contents'] as $item ) {
				if ( ! empty( $item['file'] ) ) {
					$status = '?';
					if ( ! empty( $item['hashes'] ) ) {
						$status = $this->hash_status( $item['hashes'] );
					}
					$key = explode( '/', $item['file'] );
					$str = end( $key ).' '. $status;//"\t( ".$item['file']. ' ) ';
					if ( '?' === $status ) {
						$str = '<comment>'.$str.'</>';
					}
					if ( '✓' === $status ) {
						$str = '<info>'.$str.'</>';
					}
					if ( 'x' == $status ) {
						$str = '<fg=red>'.$str.'</>';
					}
					if ( '~' == $status ) {
						$str = '<fg=magenta>'.$str.'</>';
					}
					$contents[] = $str;
					continue;
				}
				if ( ! empty( $item['folder'] ) ) {
					$key = $item['folder'];
					$key = explode( '/', $item['folder'] );
					$contents[ end( $key ).'/' ] = $this->prettify_tree( $item );
				}
			}
			return $contents;
		}
		return array();
	}

	/**
	 * Returns the hash status identifier
	 * @param  array  $hashes [description]
	 * @return [type]         [description]
	 */
	function hash_status( array $hashes ) {
		$status = '?';
		$status_set = false;
		if ( empty( $hashes ) ) {
			return $status;
		}
		foreach ( $hashes as $hash ) {
			if ( ! $status_set ) {
				if ( 'true' == $hash['status'] ) {
					$status = '✓';
				} else if ( 'false' == $hash['status'] ) {
					$status = 'x';
				}
				$status_set = true;
				continue;
			}
			if ( '✓' == $status  ) {
				if ( 'false' == $hash['status']  ) {
					$status = '~';
				}
			}
		}
		return $status;
	}

	function count_hash_status( array $hashes ) {
		$statuses = [];
		$status = '? ';
		$status_set = false;
		if ( empty( $hashes ) ) {
			$statuses[] = 'unknown';
		}
		if ( ! empty( $hashes ) ) {
			foreach ( $hashes as $hash ) {
				$statuses[] = $hash['status'];
				if ( ! $status_set ) {
					if ( 'true' == $hash['status'] ) {
						$status = '✓ ';
					} else if ( 'false' == $hash['status'] ) {
						$status = 'x ';
					}
					$status_set = true;
					continue;
				}
				if ( '✓ ' == $status  ) {
					if ( 'false' == $hash['status']  ) {
						$status = '~ ';
					}
				}
			}
		}
	}

	public function count_statuses( array $statuses ) {
		$result = array();
		$counts = array_count_values( $statuses );
		foreach ( $counts as $key => $count ) {
			$result[] = $count.'x '.$key;
		}
		return $result;
	}

	/**
	 * @param $file
	 *
	 * @return array
	 * @throws \Exception
	 */
	private function processNode( $file, DataModel $data_model ) {

		$data = array();
		if ( is_dir( $file ) ) {
			$data = $this->processFolder( $file, $data_model );
		} else {
			$data = $this->processFile( $file, $data_model );
		}
		return $data;
	}

	/**
	 * Processes a file node
	 * @param  [type] $file [description]
	 * @return array       the data representing this file with hash status and filename
	 */
	public function processFile( $file, DataModel $data_model ) {
		$data = array();
		// only process the file types we're interested in
		$info = pathinfo( $file );
		if ( isset( $info['extension'] ) ) {
			if ( ! in_array( $info['extension'], $this->allowed_file_types ) ) {
				return null;
			}
		}
		try {
			$hash = $data_model->hashFile( $file );
		} catch ( \Exception $e ) {
			$data = array(
				array(
					'hash' => 'empty',
					'status' => 'unknown',
					'file' => $file,
				),
			);
			return $data;
		}
		try {
			$data = $data_model->getHashStatusAllUsers( $hash );
		} catch ( \Exception $e ) {
			$data = array(
				array(
					'hash' => $hash,
					'status' => 'unknown',
					'file' => $file,
				),
			);
		}
		if ( empty( $data ) ) {
			$data = array(
				array(
					'hash' => $hash,
					'status' => 'unknown',
					'file' => $file,
				),
			);
		}
		return $data;
	}

	public function processFolder( $file, DataModel $data_model ) {

		$skip_folders = array(
			'.git',
			'.svn',
			'.idea',
		);

		foreach ( $skip_folders as $skip ) {
			if ( substr( $file, strlen( $skip ) * -1 ) === $skip ) {
				return null;
			}
		}

		$unfiltered_folders = scandir( $file );
		$folders = array_diff( $unfiltered_folders, array( '..', '.' ) );
		if ( empty( $folders ) ) {
			return null;
		}
		$contents = array();
		foreach ( $folders as $found_file ) {
			$result = $this->processNode( $file . DIRECTORY_SEPARATOR . $found_file, $data_model );
			if ( ! empty( $result ) && ( null != $result ) ) {
				$f = array(
					'file' => $file . DIRECTORY_SEPARATOR . $found_file,
					'hashes' => $result,
				);
				if ( is_dir( $file . DIRECTORY_SEPARATOR . $found_file ) ) {
					$f = $result;
				}
				$contents[] = $f;
			}
		}
		$data = array(
			'folder'   => $file,
			'contents' => $contents,
		);
		return $data;
	}
}
