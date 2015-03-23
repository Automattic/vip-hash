<?php
/**
 * Created by PhpStorm.
 * User: tomnowell
 * Date: 23/03/15
 * Time: 13:48
 */

namespace automattic\vip\hash;


class HashRecord {

	private $date='';

	/**
	 * @var bool
	 */
	private $status = false;

	/**
	 * @var string
	 */
	private $username;

	function __construct() {
		$this->date = 'the time';
	}

	/**
	 * @param $file string a file path to load
	 */
	function load( $file ) {
		//
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
	 * @param $file
	 */
	function save( $file ) {
		//
	}
}