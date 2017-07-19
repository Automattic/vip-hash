<?php

namespace automattic\vip\hash;

class Remote {

	private $id = 0;
	private $name = '';
	private $uri = '';
	private $last_sent = 0;
	private $latest_seen = 0;

	/**
	 * This is an auth object, deserialized from a db store
	 *
	 * @var Requests_Auth_OAuth1
	 */
	private $oauth_details = null;
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
			$this->oauth_details = unserialize( $data['oauth_details'] );
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

		/**
	 * @param Remote $remote
	 *
	 * @return mixed
	 *
	 */
	public function fetchHashes() {
		/**
		 * Finish by retrieving the data from the remote end that we don't have
		 */
		$oauth = $this->getOauthDetails();
		$i_saw = $this->getLatestSeen();
		$oauth = $this->getOauthDetails();
		$options = [];
		if ( ! empty( $oauth ) ) {
			$options['auth'] = $oauth;
		}
		$response = \Requests::get( $this->getUri() . 'viphash/v1/hashes?since=' . $i_saw, array(), $options );
		if ( 200 !== $response->status_code ) {
			$response->throw_for_status();
			return false;
		}
		$new_items = json_encode( $response->body );
		return $new_items;
	}

	/**
	 * @param array           $data
	 * @param Remote          $remote
	 * @param OutputInterface $output
	 *
	 * @return bool
	 */
	public function sendHashChunk( array $data ) {
		$send_data = json_encode( $data );

		$oauth = $this->getOauthDetails();
		$options = [];
		if ( ! empty( $oauth ) ) {
			$options['auth'] = $oauth;
		}

		/**
		 * @var: $response \Requests_Response
		 */
		$response = \Requests::post( $this->getUri() . 'viphash/v1/hashes', array(), $send_data, $options );

		if ( 200 !== $response->status_code ) {
			/*echo 'Problem response code? '.$response->getStatusCode()."--\n";
			echo $response->getBody()->getContents().'|';*/
			return false;
		}
		$this->setLastSent( time() );
		return true;
	}
}
