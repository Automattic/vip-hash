<?php

namespace automattic\vip\hash;


interface DataModel {


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

	public function getNewestSeenHash();

	public function getHashesAfter( $date );

	public function getHashesSeenAfter( $date );

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
	public function addRemote( Remote $remote );

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
	public function updateRemote( Remote $remote );

	/**
	 * @return Remote[]
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
