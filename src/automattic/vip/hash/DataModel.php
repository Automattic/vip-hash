<?php

namespace automattic\vip\hash;

use PDO;

interface DataModel {


	public function __construct( $dbdir = '' );

	public function init();

	/**
	 * @param        $hash
	 * @param        $username
	 * @param bool   $value
	 *
	 * @param string $note
	 *
	 * @param string $date
	 *
	 * @throws \Exception
	 * @return bool
	 */
	public function markHash( $hash, $username, $value, $note = '', $date = '' );

	/**
	 * @param $file
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function hashFile( $file );


	/**
	 * @param $hash
	 * @param $username
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function getHashStatusByUser( $hash, $username );

	/**
	 * @param $hash
	 *
	 * @throws \Exception
	 * @return array
	 */
	public function getHashStatusAllUsers( $hash );

	/**
	 * @return string the folder containing hash records with a trailing slash
	 */
	public function getDBDir();

	public function getNewestSeenHash();

	public function getHashesAfter( $date );

	public function getHashesSeenAfter( $date );


	public function addRemote( $name, $uri );

	/**
	 * @return array
	 * @throws \Exception
	 */
	public function getRemotes();

	/**
	 * @param $name
	 *
	 * @throws \Exception
	 * @return bool|Remote
	 */
	public function getRemote( $name );
}
