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
		$data = new Pdo_Data_Model();
		$record = new HashRecord();
		$record = $this->fill_hash( $record, $input, $data );
		$data->saveHash( $record );
	}

	/**
	 * Fill in a HashRecord from an InputInterface with the relevant args
	 *
	 * @param  HashRecord     $record [description]
	 * @param  InputInterface $input  [description]
	 * @param  DataModel      $data   [description]
	 * @return [type]                 [description]
	 */
	private function fill_hash( HashRecord $record, InputInterface $input , DataModel $data ) {
		$file = $input->getArgument( 'hash' );
		if ( empty( $file ) ) {
			throw new \Exception( 'Empty hash/file parameter' );
		}
		$hash = $file;
		if ( file_exists( $file ) ) {
			$hash = $data->hashFile( $file );
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

		$note = $this->get_potential_file_arg( $input, 'note' );
		$human_note = $this->get_potential_file_arg( $input, 'human_note' );

		$record->setHash( $hash );
		$record->setUsername( $username );
		$record->setStatus( $status );
		$record->setNote( $note );
		$record->setHumanNote( $human_note );

		return $record;
	}

	private function get_potential_file_arg( InputInterface $input, $field ) {
		$val = $input->getArgument( $field );
		if ( file_exists( $val ) && is_readable( $val ) ) {
			$val = file_get_contents( $val );
		}
		return $val;
	}
}
