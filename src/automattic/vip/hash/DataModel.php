<?php

namespace automattic\vip\hash;


interface DataModel {


	public function __construct( $dbdir = '' );

	public function init();

	/**
	 * Save a hash record to the data store
	 *
	 * @param  HashRecord $hash the hash to be saved
	 * @return bool successful?
	 */
	public function saveHash( HashRecord $hash );

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

	/**
	 * @param     $name
	 * @param     $uri
	 * @param int $latest_seen
	 * @param int $last_sent
	 *
	 * @return bool
	 */
	public function addRemote( $name, $uri, $latest_seen = 0, $last_sent = 0  );

	/**
	 * @param     $id
	 * @param     $name
	 * @param     $uri
	 * @param int $latest_seen
	 * @param int $last_sent
	 *
	 * @return bool
	 */
	public function updateRemote( $id, $name, $uri, $latest_seen = 0, $last_sent = 0 );

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
