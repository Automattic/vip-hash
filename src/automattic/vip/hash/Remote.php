<?php

namespace automattic\vip\hash;


class Remote {

	private $data;

	/**
	 * @param $data
	 */
	function __construct( $data ) {
		$this->data = $data;
	}

	/**
	 * @return mixed
	 */
	public function getName() {
		return $this->data['name'];
	}

	/**
	 * @param $name
	 */
	public function setName( $name ) {
		$this->data['name'] = $name;
	}

	/**
	 * @param $last_seen
	 */
	public function setLastSeen( $last_seen ) {
		//
	}

	/**
	 * @param $last_sent
	 */
	public function setLastSent( $last_sent ) {
		//
	}

	/**
	 * @param DataModel $data
	 */
	public function save( DataModel $data ) {
		$pdo = $data->getPDO();

		// check if we need to save or update the value
		if ( isset( $this->data['id'] ) ) {
			// it's new
		} else {
			// it's old, update it
		}
	}
}