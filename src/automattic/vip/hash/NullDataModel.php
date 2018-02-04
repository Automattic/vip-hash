<?php
/**
 * Created by PhpStorm.
 * User: tarendai
 * Date: 19/08/2016
 * Time: 02:59
 */

namespace automattic\vip\hash;


class NullDataModel implements DataModel {

	public function init() {
		//
	}

	/**
	 * Save a hash record to the data store
	 *
	 * @param  HashRecord $hash the hash to be saved
	 *
	 * @return bool successful?
	 */
	public function saveHash( HashRecord $hash ) : bool {
		return false;
	}

	/**
	 * @param $file
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function hashFile( $file ) {
		if ( ! file_exists( $file ) ) {
			throw new \Exception( 'File does not exist' );
		}
		if ( is_dir( $file ) ) {
			throw new \Exception( 'You cannot hash a folder "'.$file.'"' );
		}
		if ( ! is_file( $file ) ) {
			throw new \Exception( 'Only files can be hashed' );
		}
		$code = '';
		$file_parts = pathinfo( $file );
		// only run php strip whitespace if it's a PHP file, else it's faster to just do file_get_contents
		if ( in_array( $file_parts['extension'], [ 'php', 'php5', 'php3', 'php4', 'ph3', 'ph4' ] ) ) {
			$code = php_strip_whitespace( $file );
		} else {
			$code = file_get_contents( $file );
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
		return [];
	}

	/**
	 * @param $hash
	 *
	 * @throws \Exception
	 * @return array
	 */
	public function getHashStatusAllUsers( $hash ) {
		return [];
	}

	public function getNewestSeenHash() {
		return [];
	}

	public function getHashesAfter( $date ) {
		return [];
	}

	public function getHashesSeenAfter( $date ) {
		return [];
	}

	/**
	 * @param Remote $remote
	 *
	 * @return bool
	 * @internal param $name
	 * @internal param $uri
	 * @internal param int $latest_seen
	 * @internal param int $last_sent
	 *
	 */
	public function addRemote( Remote $remote ) : bool {
		return false;
	}

	/**
	 * @param Remote $remote
	 *
	 * @return bool
	 * @internal param $id
	 * @internal param $name
	 * @internal param $uri
	 * @internal param int $latest_seen
	 * @internal param int $last_sent
	 *
	 */
	public function updateRemote( Remote $remote ) : bool {
		return false;
	}

	public function removeRemote( Remote $remote ) : bool {
		return false;
	}

	/**
	 * @return Remote[]
	 * @throws \Exception
	 */
	public function getRemotes() {
		return [];
	}

	/**
	 * @param $name
	 *
	 * @throws \Exception
	 * @return bool|Remote
	 */
	public function getRemote( $name ) {
		return false;
	}

	/**
	 * @inherit
	 */
	public function config() : config\Config {
		return new config\NullConfig();
	}

	/**
	 * Returns a Hash Query object
	 * @return HashQuery a new query for fetching hashes
	 */
	public function newQuery() : HashQuery {
		return new NullHashQuery();
	}
}
