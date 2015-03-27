<?php

namespace automattic\vip\hash\console;

use automattic\vip\hash\DataModel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetCommand extends Command {
	protected function configure() {
		$this->setName( 'get' )
			->setDescription( 'take a file and/or username, and retrieve all records for it' )
			->addArgument(
				'file',
				InputArgument::REQUIRED,
				'A file hash to find, or a file to be hashed. Assumes hash if the given value is not a locatable file'
			)->addArgument(
				'username',
				InputArgument::OPTIONAL,
				'A wordpress.com username'
			);
	}

	protected function execute( InputInterface $input, OutputInterface $output ) {

		$file = $input->getArgument( 'file' );
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
}