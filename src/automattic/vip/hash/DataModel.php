<?php

namespace automattic\vip\hash;

class DataModel {

	function __construct() {
		//
	}

	/**
	 * @param $hash
	 * @param $username
	 * @param bool $value
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function markHash( $hash, $username, $value ) {

		$record = new HashRecord();
		$record->setHash( $hash );
		$record->setUsername( $username );
		$record->setStatus( $value );

		$folder = $this->getDBDir();
		$record->save( $folder );
		return true;
	}

	/**
	 * @param $file
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function hashFile( $file ) {
		if ( !file_exists( $file ) ) {
			throw new \Exception( "File does not exist" );
		}
		$code = php_strip_whitespace( $file );
		if ( empty( $code ) ) {
			throw new \Exception( "Empty file contents cannot be hashed" );
		}
		$hash = sha1( $code );
		return $hash;
	}

	/**
	 * @param $hash
	 * @param $username
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function getHashStatusByUser( $hash, $username ) {
		$hash_folder = $this->getDBDir().$hash;
		if ( !file_exists( $hash_folder ) ) {
			throw new \Exception( "No entries exist for this hash" );
		}
		$user_folder = $hash_folder.'/'.$username.'/';
		if ( !file_exists( $user_folder ) ) {
			throw new \Exception( "No entries exist for this user and hash" );
		}
		$files = array_diff( scandir( $user_folder ), array( '..', '.' ) );
		if ( empty( $files ) ) {
			throw new \Exception( "Hash or User Not found" );
		}
		$output_data = array();
		foreach ( $files as $file ) {
			$record = new HashRecord();
			$record->loadFile( $user_folder.'/'.$file );
			$output_data[] = $record->getData();
		}
		return $output_data;
	}

	/**
	 * @param $hash
	 *
	 * @throws \Exception
	 * @return array
	 */
	public function getHashStatusAllUsers( $hash ) {
		$hash_folder = $this->getDBDir().$hash;
		if ( !file_exists( $hash_folder ) ) {
			throw new \Exception( "No entries exist for this hash" );
		}
		$folders = array_diff( scandir( $hash_folder ), array( '..', '.' ) );
		if ( empty( $folders ) ) {
			throw new \Exception( "Hash Not found" );
		}
		$output_data = array();
		foreach ( $folders as $folder ) {
			$user_folder = $hash_folder.'/'.$folder.'/';
			$files = array_diff( scandir( $user_folder ), array( '..', '.' ) );
			if ( empty( $files ) ) {
				continue;
			}
			foreach ( $files as $file ) {
				$record = new HashRecord();
				$record->loadFile( $user_folder.'/'.$file );
				$output_data[] = $record->getData();
			}

		}
		return $output_data;
	}

	/**
	 * @return string the folder containing hash records with a trailing slash
	 */
	public function getDBDir() {
		return $_SERVER['HOME'].'/.viphash/';
	}
} 