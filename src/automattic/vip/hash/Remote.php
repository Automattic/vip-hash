<?php

namespace automattic\vip\hash;

class Remote {

	private $id = 0;
	private $name = '';
	private $uri = '';
	private $last_sent = 0;
	private $latest_seen = 0;

	private $oauth_details = '';
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
			$this->oauth_details = $data['oauth_details'];
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

	/**
	 * @param $last_sent
	 */
	public function setLastSent( $last_sent ) {
		$this->last_sent = $last_sent;
	}

	/**
	 * @param DataModel $data_model
	 *
	 * @return bool
	 */
	public function save( DataModel $data_model ) {
		// check if we need to save or update the value
		if ( empty( $this->id ) ) {
			// it's new
			return $data_model->addRemote( $this->name, $this->uri, $this->latest_seen, $this->last_sent );
		}
		return $data_model->updateRemote( $this->id, $this->name, $this->uri, $this->latest_seen, $this->last_sent );
	}

	/**
	 * @return string
	 */
	public function getOauthDetails() {
		return $this->oauth_details;
	}

	/**
	 * @param string $oauth2_access_token
	 */
	public function setOauthDetails( $oauth_details ) {
		$this->oauth_details = $oauth_details;
	}

	/**
	 * @return int|mixed
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param int|mixed $id
	 */
	public function setId( $id ) {
		$this->id = $id;
	}
}
