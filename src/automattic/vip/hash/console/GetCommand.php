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
			->setDescription( 'take a file and mark it as <info>good</info> or <error>bad</error>' )
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
			$status = $data->getHashStatusByUser( $hash, $username );
			$output->writeln( $status );
		} else {
			$statuses = $data->getHashStatusAllUsers( $hash );
			$output->writeln('wip');
		}
	}
}