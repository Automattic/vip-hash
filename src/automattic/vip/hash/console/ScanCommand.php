<?php

namespace automattic\vip\hash\console;

use automattic\vip\hash\DataModel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ScanCommand
 * @package automattic\vip\hash\console
 */
class ScanCommand extends Command {

	private $allowed_file_types;

	protected function configure() {
		$this->allowed_file_types = array(
			'php',
			'php5',
			'js',
			'html',
			'htm'
		);
		$this->setName( 'scan' )
			->setDescription( 'take a folder and generate a json response detailing the files inside' )
			->addArgument(
				'folder',
				InputArgument::REQUIRED,
				'A file hash to find, or a file to be hashed. Assumes hash if the given value is not a locatable file'
			)->addArgument(
				'format',
				InputArgument::OPTIONAL,
				'The format to output, json by default, can additionally specify markdown'
			);
	}

	protected function execute( InputInterface $input, OutputInterface $output ) {
		$folder = $input->getArgument( 'folder' );
		$format = $input->getArgument( 'format' );
		if ( empty( $folder ) ) {
			throw new \Exception( 'Empty folder parameter' );
		}
		if ( empty( $format ) ) {
			$format = 'json';
		}
		if ( !in_array( $format, array( 'json', 'markdown' ) ) ) {
			throw new \Exception( 'Unknown format' );
		}
		$data = $this->ProcessFile( $folder );
		if ( $format == 'json' ) {
			$json = json_encode( $data, JSON_PRETTY_PRINT );
			$output->writeln( $json );
		} else if ( $format == 'markdown' ) {
			$markdown = '';
			$markdown .= $this->displayMarkdown( $data );

			$output->writeln( $markdown );
		}
	}

	private function displayMarkdown( array $node ) {
		$md = '';
		if ( !empty( $node['folder'] ) ) {
			if ( !empty( $node['contents'] ) ) {
				foreach ( $node['contents'] as $subnode ) {
					$md .= $this->displayMarkdown( $subnode );
				}
			}
		} else if ( !empty( $node['file'] ) ) {
			$notes = '';
			if ( !empty( $node['hashes'] ) ) {
				foreach ( $node['hashes'] as $hash ) {
					if ( $hash['status'] == 'false' ) {
						$notes .= PHP_EOL.'Notes';
						$notes .= PHP_EOL.PHP_EOL.'```'.PHP_EOL.$hash['notes'].PHP_EOL.'```'.PHP_EOL;
					}
				}
			}
			if ( !empty( $notes ) ) {
				$md .= '## '.$node['file'].PHP_EOL.$notes;
			}
		} else {
			$md .= '? unknown entry in data structure'.PHP_EOL;
		}
		if ( !empty( $md ) ) {
			$md .= PHP_EOL;
		}
		return $md;
	}

	/**
	 * @param $file
	 *
	 * @return array
	 * @throws \Exception
	 */
	private function ProcessFile( $file ) {
		// don't process the vendor folder
		if ( substr( $file, -6) === 'vendor' ){
			return null;
		}

		// don't process the .git folder
		if ( substr( $file, -4) === '.git' ){
			return null;
		}

		// don't process the .svn folder
		if ( substr( $file, -4) === '.svn' ){
			return null;
		}
		// don't process the .svn folder
		if ( substr( $file, -5) === '.idea' ){
			return null;
		}

		$data = array();
		if ( is_dir( $file ) ) {
			$unfiltered_folders = scandir( $file );
			$folders = array_diff( $unfiltered_folders, array( '..', '.' ) );
			if ( empty( $folders ) ) {
				return null;
			}
			$contents = array();
			foreach ( $folders as $found_file ) {
				$result =  $this->ProcessFile( $file . DIRECTORY_SEPARATOR . $found_file );
				if ( !empty( $result ) && ( $result != null ) ) {
					if ( is_dir( $file . DIRECTORY_SEPARATOR . $found_file ) ) {
						$contents[] = $result;
					} else {
						$f = array(
							'file' => $file . DIRECTORY_SEPARATOR . $found_file,
							'hashes' => $result
						);
						$contents[] = $f;
					}
				}
			}
			$data = array(
				'folder'   => $file,
				'contents' => $contents
			);

		} else {
			// only process the file types we're interested in
			$info = pathinfo( $file );
			if ( isset( $info['extension'] ) ) {
				if ( !in_array( $info['extension'], $this->allowed_file_types ) ) {
					return null;
				}
			}
			$data_model = new DataModel();
			try {
				$hash = $data_model->hashFile( $file );
			} catch ( \Exception $e ) {
				$data = array(
					array(
						'hash' => 'empty',
						'status' => 'unknown',
						'file' => $file
					)
				);
				return $data;
			}
			try {
				$data = $data_model->getHashStatusAllUsers( $hash );
				if ( empty( $data ) ) {
					$data = array(
						array(
							'hash' => $hash,
							'status' => 'unknown',
							'file' => $file
						)
					);
				}
			} catch ( \Exception $e ) {
				$data = array(
					array(
						'hash' => $hash,
						'status' => 'unknown',
						'file' => $file
					)
				);
			}

		}
		return $data;
	}
}