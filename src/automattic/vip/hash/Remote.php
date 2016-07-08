<?php

namespace automattic\vip\hash;

class Remote {

	private $id = 0;
	private $name = '';
	private $uri = '';
	private $last_sent = 0;
	private $latest_seen = 0;
	private $remote_lastest_seen = 0;

	/**
	 * @param $data
	 */
	function __construct( $data = array() ) {
		if ( ! empty( $data ) ) {
			$this->id = $data['id'];
			$this->name = $data['name'];
			$this->uri = $data['uri'];
			$this->last_sent = $data['last_sent'];
			$this->latest_seen = $data['latest_seen'];
			if ( ! empty( $data['remote_latest_seen'] ) ) {
				$this->remote_lastest_seen = $data['remote_latest_seen'];
			}
		}
	}

	/**
	 * @return mixed
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param $name
	 */
	public function setName( $name ) {
		$this->name = $name;
	}

	/**
	 * @return mixed
	 */
	public function getUri() {
		return $this->uri;
	}

	/**
	 * @param $uri
	 */
	public function setUri( $uri ) {
		$this->uri = $uri;
	}

	public function getLastSent() {
		return $this->last_sent;
	}

	public function setLatestSeen( $latest_seen ) {
		$this->latest_seen = $latest_seen;
	}

	public function getLatestSeen() {
		return $this->latest_seen;
	}

	public function setRemoteLatestSeen( $remote_latest_seen ) {
		$this->remote_atest_seen = $remote_latest_seen;
	}

	public function getRemoteLatestSeen() {
		return $this->remote_latest_seen;
	}

	/**
	 * @param $last_seen
	 */
	public function setLastSeen( $last_seen ) {
		$this->last_seen = $last_seen;
	}

	/**
	 * @param $last_sent
	 */
	public function setLastSent( $last_sent ) {
		$this->last_sent = $last_sent;
	}

	/**
	 * @param DataModel $data
	 */
	public function save( DataModel $data_model ) {
		// check if we need to save or update the value
		if ( empty( $this->id ) ) {
			// it's new
			return $data_model->addRemote( $this->name, $this->uri, $this->latest_seen, $this->last_seen );
		}
		return $data_model->updateRemote( $this->id, $this->name, $this->uri, $this->latest_seen, $this->last_seen );
	}
}
