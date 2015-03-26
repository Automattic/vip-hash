<?php

namespace automattic\vip\hash;

class DataModel {

	function __construct() {
		//
	}

	/**
	 * @param $hash
	 * @param $username
	 * @param $value
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function markHash( $hash, $username, $value ) {
		$file = $this->getDBDir().$hash.'-'.$username;
		if ( file_exists( $file ) ) {
			// it already exists! we don't edit/update records, we only add and retrieve them
			return false;
		}
		touch( $file );
		$result = file_put_contents( $file, $value );
		if ( !$result ) {
			throw new \Exception( "Failed to save hash", 1 );
		}
		return true;
	}

	/**
	 * @param $file
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function hashFile( $file ) {
		$code = php_strip_whitespace( $file );
		if ( empty( $code ) ) {
			throw new \Exception( "Empty file contents cannot be hashed", 2 );
		}
		$hash = sha1( $code );
		return $hash;
	}

	/**
	 * @param $hash
	 * @param $username
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function getHashStatusByUser( $hash, $username ) {
		$file = $this->getDBDir().$hash.'-'.$username;
		if ( file_exists( $file ) ) {
			return file_get_contents( $file );
		}
		throw new \Exception( "Hash not found", 3 );
	}

	/**
	 * @param $hash
	 *
	 * @throws \Exception
	 * @return array
	 */
	public function getHashStatusAllUsers( $hash ) {
		$files = scandir( $this->getDBDir() );

		if ( !$files ) {
			throw new \Exception( "No hashes found", 4 );
		}

		$results = array();
		foreach ( $files as $file ) {
			if ( substr( $file, 0, strlen( $hash ) ) == $hash ) {
				$results[] = $file;
			}
		}
		return $results;
	}

	/**
	 * @return string the folder containing hash records with a trailing slash
	 */
	public function getDBDir() {
		return $_SERVER['HOME'].'/.viphash/';
	}
} 