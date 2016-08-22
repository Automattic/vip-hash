<?php

namespace automattic\vip\hash;

class Remote {

	private $id = 0;
	private $name = '';
	private $uri = '';
	private $last_sent = 0;
	private $latest_seen = 0;

	private $oauth2_access_token = '';
	private $oauth2_expires = 0;
	private $oauth2_refresh_token = '';

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
			$this->oauth2_access_token = $data['oauth2_access_token'];
			$this->oauth2_expires = $data['oauth2_expires'];
			$this->oauth2_refresh_token = $data['oauth2_refresh_token'];
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
	public function getOauth2AccessToken() {
		return $this->oauth2_access_token;
	}

	/**
	 * @param string $oauth2_access_token
	 */
	public function setOauth2AccessToken( $oauth2_access_token ) {
		$this->oauth2_access_token = $oauth2_access_token;
	}

	/**
	 * @return int
	 */
	public function getOauth2Expires() {
		return $this->oauth2_expires;
	}

	/**
	 * @param int $oauth2_expires
	 */
	public function setOauth2Expires( $oauth2_expires ) {
		$this->oauth2_expires = $oauth2_expires;
	}

	/**
	 * @return string
	 */
	public function getOauth2RefreshToken() {
		return $this->oauth2_refresh_token;
	}

	/**
	 * @param string $oauth2_refresh_token
	 */
	public function setOauth2RefreshToken( $oauth2_refresh_token ) {
		$this->oauth2_refresh_token = $oauth2_refresh_token;
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
