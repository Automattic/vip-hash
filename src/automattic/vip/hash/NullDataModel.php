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
	public function saveHash( HashRecord $hash ) {
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
		$code = php_strip_whitespace( $file );
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
	public function addRemote( Remote $remote ) {
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
	public function updateRemote( Remote $remote ) {
		return false;
	}

	public function removeRemote( Remote $remote ) {
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
	public function getConfig() : config\Config {
		return new config\NullConfig();
	}
}
