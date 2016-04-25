<?php

namespace automattic\vip\hash\console;

use automattic\vip\hash\DataModel;
use automattic\vip\hash\Pdo_Data_Model;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ScanCommand
 * @package automattic\vip\hash\console
 */
class StatusCommand extends Command {

	private $allowed_file_types;

	protected function configure() {
		$this->allowed_file_types = array(
			'php',
			'php5',
			'js',
			'html',
			'htm',
			'twig',
		);
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
		$tree = $this->displayTree( $data );
		$good = 0;
		$bad = 0;
		$unknown = 0;
		foreach ( $tree as $line ) {
			$final_line = $line;
			if ( strpos( $line, 'false' ) !== false ) {
				$final_line = '<error>'.$line.'</error>';
				$bad++;
			} else if ( false !== strpos( $line, 'true' ) ) {
				$final_line = '<info>'.$line.'</info>';
				$good++;
			} else if ( false !== strpos( $line, 'unknown' ) ) {
				$final_line = '<comment>'.$line.'</comment>';
				$unknown++;
			}
			$output->writeln( $final_line );
		}
		$total = $good + $bad + $unknown;
		$percentage = ( ( $good + $bad ) / $total ) * 100;
		$final = '<info>'.$good.' good</info>, <error>'.$bad.' bad</error>, <comment>'.$unknown.' unknown</comment>, '.number_format( $percentage, 2 ).'% seen';
		$output->writeln( $final );
	}

	/**
	 * Returns a string representing the indent for the tree string
	 * @param  integer $depth How deep into the tree is this node?
	 * @param  boolean $last  Is this the last node at this depth?
	 * @return string         An indent representing the node in the tree
	 */
	private function getTreeIndent( $depth, $last ) {
		$indent = '├───';
		if ( $depth > 0 ) {
			$indent = '|   ';
		} else if ( $last ) {
			$indent = '└───';
		}

		if ( $depth > 0 ) {
			$branch = '├';
			if ( $last ) {
				$branch = '└';
			}
			$indent .= str_repeat( '|   ', $depth -1 ).$branch.'───';
		}
		return $indent;
	}

	private function displayTree( array $node, $depth = -1, $last = false ) {
		$lines = [];
		$status = '  ';
		$md = '';
		$md .= $this->getTreeIndent( $depth, $last );

		if ( ! empty( $node['folder'] ) ) {
			$folderlines = array();
			if ( ! empty( $node['contents'] ) ) {
				$i = 1;
				foreach ( $node['contents'] as $subnode ) {
					$newlines = $this->displayTree( $subnode, $depth + 1, count( $node['contents'] ) == $i++ );
					$folderlines = array_merge( $folderlines, $newlines );
				}
			}
			if ( ! empty( $folderlines ) ) {
				$lines[] = $status.$md . '├ '.$node['folder'];
				$lines = array_merge( $lines, $folderlines );
			}
		} else if ( ! empty( $node['file'] ) ) {
			$statuses = [];
			$status = '? ';
			$status_set = false;
			if ( empty( $node['hashes'] ) ) {
				$statuses[] = 'unknown';
			}
			if ( ! empty( $node['hashes'] ) ) {
				foreach ( $node['hashes'] as $hash ) {
					$statuses[] = $hash['status'];
					if ( ! $status_set ) {
						if ( 'true' == $hash['status'] ) {
							$status = '✓ ';
						} else if ( 'false' == $hash['status'] ) {
							$status = 'x ';
						}
						$status_set = true;
					} else {
						if ( '✓ ' == $status  ) {
							if ( 'false' == $hash['status']  ) {
								$status = '~ ';
							}
						}
					}
				}
			}
			if ( ! empty( $statuses ) ) {
				$lines[] = $status.$md . ''. basename( $node['file'] ) .' - '.implode( ', ', $this->count_statuses( $statuses ) );
			}
		} else {
			$lines[] = '? unknown entry in data structure'.PHP_EOL;
		}
		return $lines;
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
