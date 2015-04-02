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

	protected function configure() {
		$this->setName( 'get' )
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
		$data = new DataModel();
		$hash = $file;
		if ( file_exists( $file ) ) {
			$hash = $data->hashFile( $file );
		}
		if ( $username = $input->getArgument('username') ) {
			$result = $data->getHashStatusByUser( $hash, $username );
			$json = json_encode( $result, JSON_PRETTY_PRINT );
			$output->writeln( $json );
		} else {
			$result = $data->getHashStatusAllUsers( $hash );
			if ( empty( $result ) ) {
				throw new \Exception('No Hashes found' );
			}
			$json = json_encode( $result, JSON_PRETTY_PRINT );
			$output->writeln( $json );
		}
	}

	/**
	 * @param $file
	 *
	 * @return array
	 * @throws \Exception
	 */
	private function ProcessFile( $file ) {
		$data = array();
		if ( is_dir( $file ) ) {
			$folders = array_diff( scandir( $file ), array( '..', '.' ) );
			if ( empty( $folders ) ) {
				return $data;
			}
			foreach ( $folders as $found_file ) {
				$data[] = $this->ProcessFile( $file.DIRECTORY_SEPARATOR.$found_file  );
			}

		} else {
			$data_model = new DataModel();
			$hash = $data_model->hashFile( $file );

			$data = $data_model->getHashStatusAllUsers( $hash );
		}
		return $data;
	}
}