<?php

namespace automattic\vip\hash\console;

use automattic\vip\hash\DataModel;
use automattic\vip\hash\Pdo_Data_Model;
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
			->setDescription( 'take a hash or file and mark it\'s VIP worthiness as <info>true</info> or <error>false</error>, other values accepted' )
			->addArgument(
				'hash',
				InputArgument::REQUIRED,
				'A string hash to be marked, if a file is passed instead a hash will be generated for it'
			)->addArgument(
				'username',
				InputArgument::REQUIRED,
				'A WordPress.com username'
			)->addArgument(
				'status',
				InputArgument::REQUIRED,
				'The status to mark this hash with'
			)->addArgument(
				'note',
				InputArgument::OPTIONAL,
				'the offending item if marking as bad, or a file containing the note'
			)->addArgument(
				'human_note',
				InputArgument::OPTIONAL,
				'An explanation or information in a human readable format, or a file containing the note'
			);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) {
		$file = $input->getArgument( 'hash' );
		if ( empty( $file ) ) {
			throw new \Exception( 'Empty hash/file parameter' );
		}
		$username = $input->getArgument( 'username' );
		if ( empty( $username ) ) {
			throw new \Exception( 'Empty username parameter' );
		}
		$status = $input->getArgument( 'status' );
		if ( empty( $status ) ) {
			throw new \Exception( 'Empty status parameter' );
		}
		if ( ( 'true' != $status ) && ( 'false' != $status ) ) {
			throw new \Exception( 'Hash status must be true or false' );
		}

		$note = $input->getArgument( 'note' );
		if ( file_exists( $note ) && is_readable( $note ) ) {
			$note = file_get_contents( $note );
		}
		$human_note = $input->getArgument( 'human_note' );
		if ( file_exists( $human_note ) && is_readable( $human_note ) ) {
			$human_note = file_get_contents( $human_note );
		}
		$data = new Pdo_Data_Model();
		$hash = $file;
		if ( file_exists( $file ) ) {
			$hash = $data->hashFile( $file );
		}
		$data->markHash( $hash, $username, $status, $note, $human_note );
	}
}
