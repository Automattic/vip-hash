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

	private $data;

	function __construct() {
		$this->data = array(
			'date' => time(),
			'username' => '',
			'status' => false,
			'hash' => '',
			'notes' => ''
		);
	}

	/**
	 * Does this hash already exist in the database?
	 *
	 * @param $folder
	 *
	 * @return bool
	 */
	function exists( $folder ) {
		if ( $this->loaded_from_file ) {
			return true;
		}

		$file = $this->generateFileName();
		$full_path = $folder . $file;

		return file_exists( $full_path );
	}

	/**
	 * @param $file string a file path to load
	 *
	 * @throws \Exception
	 */
	function loadFile( $file ) {
		$this->loaded_from_file = true;

		if ( !file_exists( $file ) ) {
			throw new \Exception( "File does not exist, cannot load record", 8 );
		}
		$contents = file_get_contents( $file );
		$json_data = json_decode( $contents );

		foreach ( $this->data as $key => $value ) {
			$this->data[ $key ] = $json_data[ $key ];
		}
	}

	/**
	 * @return string
	 */
	public function getNote() {
		return $this->data['note'];
	}

	/**
	 * @param string $note
	 */
	public function setNote( $note ) {
		$this->data['note'] = $note;
	}

	/**
	 * The date this record was made
	 */
	function getDate() {
		return $this->data['date'];
	}

	function setDate( $date ) {
		$this->data['date'] = $date;
	}

	/**
	 * @return string
	 */
	public function getHash() {
		return $this->data['hash'];
	}

	/**
	 * @param string $hash
	 */
	public function setHash( $hash ) {
		$this->data['hash'] = $hash;
	}

	/**
	 * @return bool
	 */
	function getStatus() {
		return $this->data['status'];
	}

	/**
	 * @param bool $status
	 */
	function setStatus( $status ) {
		$this->data['status'] = $status;
	}

	function setUsername( $username) {
		$this->data['username'] = $username;
	}

	function getUsername() {
		return $this->data['username'];
	}

	/**
	 * Saves this record
	 *
	 * @param $folder string the location of the hash database with a trailing slash
	 *
	 * @throws \Exception
	 */
	function save( $folder ) {
		if ( $this->exists( $folder ) ) {
			throw new \Exception( "Cannot save record, it already exists!", 9 );
		}

		$file = $this->generateFileName();
		$file_folder = $this->generateFolderName();
		mkdir( $folder.$file_folder, 0777, true );

		$full_path = $folder . $file;

		$contents = json_encode( $this->data );

		// save contents to file
		touch( $full_path );
		file_put_contents( $full_path, $contents );
	}

	/**
	 * @return array
	 */
	public function getData() {
		return $this->data;
	}

	private function generateFolderName() {
		$file = $this->getHash().'/';
		$file .= $this->getUsername();
		return $file;
	}

	/**
	 * @return string filename to be appended to the hash folder
	 */
	private function generateFileName() {
		$file .= $this->generateFolderName().'/';
		$file .= $this->getDate();
		return $file;
	}
}