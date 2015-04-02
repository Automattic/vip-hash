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
			);
	}

	protected function execute( InputInterface $input, OutputInterface $output ) {
		$folder = $input->getArgument( 'folder' );
		if ( empty( $folder ) ) {
			throw new \Exception( 'Empty folder parameter' );
		}
		$data = $this->ProcessFile( $folder );
		$json = json_encode( $data, JSON_PRETTY_PRINT );
		$output->writeln( $json );
		return;
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
			$folders = array_diff( scandir( $file ), array( '..', '.' ) );
			if ( empty( $folders ) ) {
				return $data;
			}
			foreach ( $folders as $found_file ) {
				$result =  $this->ProcessFile( $file . DIRECTORY_SEPARATOR . $found_file );
				if ( !empty( $result ) ) {
					$data[] = $result;
				}
			}
			$data = array(
				'folder' => $file,
				'contents' => $data
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
					'hash' => 'empty',
					'status' => 'unknown',
					'file' => $file
				);
				return $data;
			}
			try {
				$data = $data_model->getHashStatusAllUsers( $hash );
			} catch ( \Exception $e ) {
				$data = array(
					'hash' => $hash,
					'status' => 'unknown',
					'file' => $file
				);
			}

		}
		return $data;
	}
}