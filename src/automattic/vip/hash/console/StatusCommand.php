<?php

namespace automattic\vip\hash\console;

use automattic\vip\hash\Pdo_Data_Model;
use cli\Tree;
use cli\tree\Markdown;
use SplFileInfo;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

/**
 * Class ScanCommand
 * @package automattic\vip\hash\console
 */
class StatusCommand extends FileSystemCommand {

	/**
	 * @var array
	 */
	private $status_markup = [
		'?' => '<comment>%s</comment>',
		'~' => '<mixed>%s</mixed>',
		'x' => '<problem>%s</problem>',
		'✓' => '<info>%s</info>',
	];

	/**
	 * @var array
	 */
	private $status_names = [
		'?' => 'Not seen',
		'~' => 'Mixed',
		'x' => 'Problematic',
		'✓' => 'Good',
	];

	/**
	 *
	 */
	protected function configure() {
		$this->setName( 'status' )
			->setDescription( 'take a folder and generates a status report of good problematic and unknown file hashes' )
			->addArgument(
				'folder',
				InputArgument::OPTIONAL,
				'A file hash to find, or a file to be hashed. Assumes hash if the given value is not a locatable file'
			);
	}

	/**
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 *
	 * @return int|null|void
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) {
		$folder = $input->getArgument( 'folder' );
		if ( empty( $folder ) || '.' === $folder ) {
			$folder = getcwd();
		} elseif ( '..' === $folder ) {
			$folder = dirname( getcwd() );
		}
		$mixed_style = new OutputFormatterStyle( 'magenta', null );
		$problem_style = new OutputFormatterStyle( 'red', null );
		$bold_style = new OutputFormatterStyle( 'white', null );
		$output->getFormatter()->setStyle( 'mixed', $mixed_style );
		$output->getFormatter()->setStyle( 'problem', $problem_style );
		$output->getFormatter()->setStyle( 'bold', $bold_style );

		$data_model = new Pdo_Data_Model();
		$fileInfo = new SplFileInfo( $folder );
		$data = $this->processNode( $fileInfo, $data_model );
		if ( empty( $data ) ) {
			$output->writeln( '<info>There are no reviewable files to check the status for. The tools scan/status commands look for these file extensions:</info>' );
			$output->writeln( '<info>'.implode( ', ', FileSystemCommand::$allowed_file_types ).'</info>' );
			return;
		}
		$this->display_tree( $output, $data );
		$this->display_totals( $output, $data );
		$this->save_status( $data );
	}

	/**
	 * @param OutputInterface $output
	 * @param array           $data
	 */
	function display_tree( OutputInterface $output, array $data ) {
		if ( empty( $data ) ) {
			$output->write( '<error>Nothing found</error>' );
			return;
		}
		$tree = new Tree;
		$tree->setData( $this->prettify_tree( $data ) );
		$tree->setRenderer( new Markdown( 4 ) );
		$output->write( $tree->render() );
	}

	/**
	 * @param OutputInterface $output
	 * @param array           $data
	 */
	function display_totals( OutputInterface $output, array $data ) {
		$statuses = $this->count_tree( $data );

		$total = 0;
		$parts = [];
		foreach ( $statuses as $status => $count ) {
			$part = $status.': '.$this->status_names[ $status ];
			$parts[] = sprintf( $this->status_markup[ $status ], $part );
			$total += $count;
		}

		if ( empty( $total ) ) {
			// nothing found
			$output->writeln( '<comment>No reviewable files found</comment>' );
			return;
		}
		$output->writeln( '' );
		$legend = implode( $parts, ', ' );
		$output->writeln( '<bold>Legend:</bold> '.$legend );
		$parts = [];
		foreach ( $statuses as $status => $count ) {
			if ( $count > 0 ) {
				$part = $status.': '.$count. ' ('.number_format( ( $count / $total ) * 100, 1 ).'%)';
				$part = sprintf( $this->status_markup[ $status ], $part );
				$parts[] = $part;
			}
		}

		$final = implode( $parts, ', ' );
		$output->writeln( '<bold>Results:</bold> '.$final );
		$output->writeln( '<bold>Seen:</bold> '.( $total - $statuses['?'] ).'/'.$total.' files ( '.number_format( ( $total - $statuses['?'] ) / $total * 100, 1 ).'% )' );
	}

	
	/**
	 * @param array           $data
	 */
	function save_status( array $data ) {
		$file   = 'status.json';
		$handle = fopen($file, 'w+');
		$output = new StreamOutput($handle);
		
		$statuses = $this->count_tree( $data );

		$total = 0;
		$parts = [];
		foreach ( $statuses as $status => $count ) {
			$part = $status.': '.$this->status_names[ $status ];
			$parts[] = sprintf( $this->status_markup[ $status ], $part );
			$total += $count;
		}
		if ( empty( $total ) ) {
			// nothing found
			$output->writeln( 'No reviewable files found' );
			return;
		}
		
		$parts = [];
		foreach ( $statuses as $status => $count ) {
			if ( $count > 0 ) {
				$parts[] = '"'.$status.'":'.$count;
			}
		}
		$final = "[{".implode($parts, ',')."}]";
		$output->write($final);
		fclose($handle);
	
	}

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	function count_tree( array $data ) {
		$statuses = [
			'~' => 0,
			'?' => 0,
			'✓' => 0,
			'x' => 0,
		];
		if ( ! empty( $data['folder'] ) ) {
			foreach ( $data['contents'] as $item ) {
				if ( ! empty( $item['file'] ) ) {
					$status = '?';
					if ( ! empty( $item['hashes'] ) ) {
						$status = $this->hash_status( $item['hashes'] );
					}
					$statuses[ $status ] += 1;
					continue;
				}
				if ( empty( $item['folder'] ) ) {
					continue;
				}
				$sub = $this->count_tree( $item );
				$statuses['~'] += $sub['~'];
				$statuses['?'] += $sub['?'];
				$statuses['✓'] += $sub['✓'];
				$statuses['x'] += $sub['x'];
			}
		}
		return $statuses;
	}

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	function prettify_tree( array $data ) {
		if ( ! empty( $data['folder'] ) ) {
			if ( empty( $data['contents'] ) ) {
				return array();
			}
			$contents = array();
			foreach ( $data['contents'] as $item ) {
				if ( ! empty( $item['file'] ) ) {
					$status = '?';
					if ( ! empty( $item['hashes'] ) ) {
						$status = $this->hash_status( $item['hashes'] );
					}
					$key = explode( '/', $item['file'] );
					$str = end( $key ).' '. $status;
					$str = sprintf( $this->status_markup[ $status ], $str );
					$contents[] = $str;
					continue;
				}
				if ( ! empty( $item['folder'] ) ) {
					$key = explode( '/', $item['folder'] );
					$contents[ end( $key ).'/' ] = $this->prettify_tree( $item );
				}
			}
			if ( empty( $contents ) ) {
				return array();
			}
			return $contents;
		}
		return array();
	}

	/**
	 * Returns the hash status identifier
	 *
	 * @param  array $hashes [description]
	 *
	 * @return string [type]         [description]
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
			if ( 'x' == $status  ) {
				if ( 'true' == $hash['status']  ) {
					$status = '~';
				}
			}
		}
		return $status;
	}

	/**
	 * @param array $hashes
	 */
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

	/**
	 * @param array $statuses
	 *
	 * @return array
	 */
	public function count_statuses( array $statuses ) {
		$result = array();
		$counts = array_count_values( $statuses );
		foreach ( $counts as $key => $count ) {
			$result[] = $count.'x '.$key;
		}
		return $result;
	}
}