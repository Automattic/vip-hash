<?php

namespace automattic\vip\hash\console;

use automattic\vip\hash\DataModel;
use automattic\vip\hash\Pdo_Data_Model;
use automattic\vip\hash\HashRecord;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
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

		$hash = $input->getArgument( 'hash' );
		if ( empty( $hash ) ) {
			throw new \Exception( 'Empty hash/file/folder parameter' );
		}
		/**
		 * If this is a folder, we need to handle it differently
		 */
		if ( is_dir( $hash ) ) {
			$dir_iterator = new RecursiveDirectoryIterator($hash);
			$filter = new \RecursiveCallbackFilterIterator($dir_iterator, function ( SplFileInfo $current, $key, RecursiveDirectoryIterator $iterator ) {
				// Skip hidden files and directories.
				if ( $current->getFilename()[0] === '.') {
					return false;
				}
				if ( $current->isDir() ) {
					//return false;
					return !in_array( $current->getFilename(), FileSystemCommand::$skip_folders );
				}
				// only process the file types we're interested in
				if ( ! in_array( $current->getExtension(), FileSystemCommand::$allowed_file_types ) ) {
					return false;
				}
				return true;
			});

			$objects = new RecursiveIteratorIterator( $filter, RecursiveIteratorIterator::SELF_FIRST);
			/** @var SplFileInfo  $file_info */
			foreach( $objects as $name => $file_info ) {
				if ( $file_info->isDir() ) {
					continue;
				}
				$record = new HashRecord();
				$record = $this->fill_hash_from_input( $record, $input, $file_info->getRealPath(), $data );
				$data->saveHash( $record );
			}
			return;
		}
		$record = new HashRecord();
		$record = $this->fill_hash_from_input( $record, $input, $hash, $data );
		$data->saveHash( $record );
	}

	/**
	 * Fill in a HashRecord from an InputInterface with the relevant args
	 *
	 * @param  HashRecord     $record [description]
	 * @param  InputInterface $input  [description]
	 * @param  String         $hash
	 * @param  DataModel      $data   [description]
	 *
	 * @return HashRecord [type]                 [description]
	 * @throws \Exception
	 */
	private function fill_hash_from_input( HashRecord $record, InputInterface $input , $hash, DataModel $data ) {
		if ( file_exists( $hash ) ) {
			$hash = $data->hashFile( $hash );
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
