<?php

namespace automattic\vip\hash\console;

use automattic\vip\hash\DataModel;
use DirectoryIterator;
use SplFileInfo;
use Symfony\Component\Console\Command\Command;

abstract class FileSystemCommand extends Command {

	public static $allowed_file_types = array(
		'php',
		'php5',
		'js',
		'html',
		'htm',
		'twig',
		'po',
		'pot',
		'jss',
		'jsx',
		'mustache',
		'handlebars',
		'diff',
		'patch',
	);

	public static $skip_folders = array(
		'.git',
		'.svn',
		'.idea',
	);

	/**
	 * @param           $file
	 *
	 * @param DataModel $data_model
	 *
	 * @return array
	 */
	public function processNode( SplFileInfo $file, DataModel $data_model ) {
		if ( $file->isDir() ) {
			$data = $this->processFolder( $file, $data_model );
		} else {
			$data = $this->processFile( $file, $data_model );
		}
		return $data;
	}

	/**
	 * @param           $file
	 * @param DataModel $data_model
	 *
	 * @return array|null
	 */
	public function processFolder( SplFileInfo $file, DataModel $data_model ) {
		if ( in_array( $file->getBasename(), self::$skip_folders) ) {
			return null;
		}
		/*foreach ( self::$skip_folders as $skip ) {
			if ( strpos( $file->getBasename(), strlen( $skip ) * -1 ) === $skip ) {
				return null;
			}
		}*/

		$contents = array();
		/** @var SplFileInfo  $file_info */
		$it = new DirectoryIterator( $file->getRealPath() );
		foreach ( $it as $fileInfo ) {
			if ( $fileInfo->isDot() ) {
				continue;
			}
			$result = $this->processNode( $fileInfo, $data_model );

			if ( ! empty( $result ) && ( null != $result ) ) {
				$contents[] = $result;
			}
			$fileInfo = null;
		}
		$it = null;
		if ( empty( $contents ) ) {
			return null;
		}
		$data = array(
			'folder'   => $file->getRealPath(),
			'contents' => $contents,
		);
		return $data;
	}

	/**
	 * Processes a file node
	 *
	 * @param           $file
	 * @param DataModel $data_model
	 *
	 * @return array the data representing this file with hash status and filename
	 * @internal param $ [type] $file [description]
	 */
	public function processFile( SplFileInfo $file, DataModel $data_model ) {
		// only process the file types we're interested in
		if ( !in_array( $file->getExtension(), self::$allowed_file_types ) ) {
			return null;
		}
		try {
			$hash = $data_model->hashFile( $file->getRealPath() );
		} catch ( \Exception $e ) {
			$data = array(
				array(
					'hash' => 'empty',
					'status' => 'unknown',
					'file' => $file,
				),
			);
			return array(
				'file' => $file->getRealPath(),
				'hashes' => $data,
			);
		}
		try {
			$data = $data_model->getHashStatusAllUsers( $hash );
		} catch ( \Exception $e ) {
			$data = array(
				array(
					'hash' => $hash,
					'status' => 'unknown',
					'file' => $file->getRealPath(),
				),
			);
		}
		if ( empty( $data ) ) {
			$data = array(
				array(
					'hash' => $hash,
					'status' => 'unknown',
					'file' => $file->getRealPath(),
				),
			);
		}
		return array(
			'file' => $file->getRealPath(),
			'hashes' => $data,
		);
	}
}

