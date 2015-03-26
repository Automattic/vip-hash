<?php
/**
 * Created by PhpStorm.
 * User: tomnowell
 * Date: 23/03/15
 * Time: 13:48
 */

namespace automattic\vip\hash;


class HashRecord {

	private $loaded_from_file = false;

	private $date='';

	/**
	 * @var bool
	 */
	private $status = false;

	/**
	 * @var string
	 */
	private $username;

	/**
	 * @var string
	 */
	private $hash;

	private $note = '';

	/**
	 * @return string
	 */
	public function getNote() {
		return $this->note;
	}

	/**
	 * @param string $note
	 */
	public function setNote( $note ) {
		$this->note = $note;
	}

	function __construct() {
		$this->date = 'the time';
	}

	/**
	 * @param $file string a file path to load
	 */
	function loadFile( $file ) {
		$this->loaded_from_file = true;
	}

	/**
	 * The date this record was made
	 */
	function getDate() {
		return $this->date;
	}

	function setDate( $date ) {
		$this->date = $date;
	}

	/**
	 * @return string
	 */
	public function getHash() {
		return $this->hash;
	}

	/**
	 * @param string $hash
	 */
	public function setHash( $hash ) {
		$this->hash = $hash;
	}



	/**
	 * @return bool
	 */
	function getStatus() {
		return $this->status;
	}

	/**
	 * @param bool $status
	 */
	function setStatus( $status=false ) {
		$this->status = $status;
	}

	function setUsername( $username) {
		$this->username = $username;
	}

	function getUsername() {
		return $this->username;
	}

	/**
	 * Saves this record
	 *
	 * @param $folder string the location of the hash database with a trailing slash
	 */
	function save( $folder ) {
		$file = $this->generateFileName();
		$full_path = $folder . $file;

		$data = array();
		if ( !empty( $this->note ) ) {
			$data['note'] = $this->getNote();
		}
		if ( !empty( $this->status ) ) {
			$data['status'] = $this->getStatus();
		}
		if ( !empty( $this->username ) ) {
			$data['username'] = $this->getUsername();
		}
		if ( !empty( $this->hash ) ) {
			$data['hash'] = $this->getHash();
		}
		if ( !empty( $this->date ) ) {
			$data['date'] = $this->getDate();
		}
		$contents = json_encode( $data );

		// save contents to file
		file_put_contents( $file, $contents );
	}

	/**
	 * @return string filename to be appended to the hash folder
	 */
	private function generateFileName() {
		$file = $this->getHash().'/';
		$file .= $this->getUsername().'/';
		$file .= $this->getDate();
		return $file;
	}
}