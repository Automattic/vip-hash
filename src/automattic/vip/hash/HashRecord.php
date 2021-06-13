<?php

namespace automattic\vip\hash;

/**
 * Class HashRecord
 * @package automattic\vip\hash
 */
class HashRecord {

	/**
	 * @var array
	 */
	private $data;

	/**
	 * HashRecord constructor.
	 */
	function __construct( $file = '' ) {

		$hash = '';
		if ( ! empty( $file ) ) {
			$hash = $this->hashFile( $file );
		}
		$this->data = array(
			'date' => time(),
			'username' => '',
			'status' => false,
			'hash' => $hash,
			'notes' => '',
			'human_note' => '',
		);
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
	 * @return string
	 */
	public function getHumanNote() {
		return $this->data['human_note'];
	}

	/**
	 * @param string $note
	 */
	public function setHumanNote( $note ) {
		$this->data['human_note'] = $note;
	}

	/**
	 * The date this record was made
	 */
	function getDate() {
		return $this->data['date'];
	}

	/**
	 * @param $date
	 */
	function setDate( $date ) {
		if ( empty( $date ) ) {
			$date = time();
		}
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
	 * @return STRING
	 */
	function getStatus() {
		return $this->data['status'];
	}

	/**
	 * @param string $status
	 */
	function setStatus( $status ) {
		$this->data['status'] = $status;
	}

	/**
	 * @param $username
	 */
	function setUsername( $username) {
		$this->data['username'] = $username;
	}

	/**
	 * @return mixed
	 */
	function getUsername() {
		return $this->data['username'];
	}

	/**
	 * @return array
	 */
	public function getData() {
		return $this->data;
	}

	/**
	 * @param $data
	 */
	public function setData( $data ) {
		$this->data = $data;
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
			throw new \Exception( 'You cannot hash a folder "' . $file . '"' );
		}
		if ( ! is_file( $file ) ) {
			throw new \Exception( 'Only files can be hashed' );
		}
		$code = php_strip_whitespace( $file );
		$hash = sha1( $code );
		return $hash;
	}
}