<?php

namespace automattic\vip\hash;

class HashRecord {

	private $data;

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
	 * Does this hash already exist in the database?
	 *
	 * @return bool
	 */
	function exists() {
		// @TODO: implement check
		return false;
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
		return $this->data['humannote'];
	}

	/**
	 * @param string $note
	 */
	public function setHumanNote( $note ) {
		$this->data['humannote'] = $note;
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

	function setUsername( $username) {
		$this->data['username'] = $username;
	}

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