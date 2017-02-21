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
	function __construct() {
		$this->data = array(
			'date' => time(),
			'username' => '',
			'status' => false,
			'hash' => '',
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
}