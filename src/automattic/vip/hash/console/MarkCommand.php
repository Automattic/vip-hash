<?php

namespace automattic\vip\hash\console;

use automattic\vip\hash\DataModel;
use automattic\vip\hash\HashRecord;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * {@inheritDoc}
 */
class MarkCommand extends Command {

	/**
	 * {@inheritDoc}
	 */
	protected function configure() {
		$this->setName( 'mark' )
			->setDescription( 'take a file and mark it\'s VIP worthiness as <info>true</info> or <error>false</error>' )
			->addArgument(
				'file',
				InputArgument::REQUIRED,
				'The file or hash to be marked'
			)->addArgument(
				'username',
				InputArgument::REQUIRED,
				'A wordpress.com username'
			)->addArgument(
				'status',
				InputArgument::REQUIRED,
				'"true" or "false"'
			)->addArgument(
				'note',
				InputArgument::OPTIONAL,
				'notes, perhaps a line number and code with some text'
			);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) {
		$file = $input->getArgument( 'file' );
		if ( empty( $file ) ) {
			throw new \Exception( 'Empty file parameter' );
		}
		$username = $input->getArgument( 'username' );
		if ( empty( $username ) ) {
			throw new \Exception( 'Empty username parameter' );
		}
		$status = $input->getArgument( 'status' );
		if ( empty( $status ) ) {
			throw new \Exception( 'Empty status parameter' );
		}
		if ( $status != 'true' && $status != 'false' ) {
			throw new \Exception( 'Hash status must be true or false' );
		}

		$note = $input->getArgument( 'note' );
		$data = new DataModel();
		$hash = $file;
		if ( file_exists( $file ) ) {
			$hash = $data->hashFile( $file );
		}
		$data->markHash( $hash, $username, $status, $note );
	}
}