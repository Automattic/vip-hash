<?php

namespace automattic\vip\hash;


/*$this->pdo->query( 'CREATE TABLE IF NOT EXISTS wpcom_vip_hash_remotes (
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	name CHAR(50) NOT NULL UNIQUE,
	uri CHAR(30) NOT NULL,
	latest_seen INT NOT NULL,
	last_sent INT NOT NULL
)' );*/
class Remote {

	private $data;


	private $name='';
	private $uri='';
	private $last_sent=0;
	private $latest_seen=0;

	/**
	 * @param $data
	 */
	function __construct( $data = array() ) {
		if ( !empty( $data ) ) {
			$this->name = $data['name'];
			$this->uri = $data['uri'];
			$this->last_sent = $data['last_sent'];
			$this->latest_seen = $data['latest_seen'];
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
	public function save( DataModel $model ) {
		$pdo = $model->getPDO();

		// check if we need to save or update the value
		if ( isset( $this->data['id'] ) ) {
			// it's new

			$query = "INSERT INTO wpcom_vip_hash_remotes VALUES
			( :id, :name, :uri, :latest_seen, :last_sent )";
			$sth   = $pdo->prepare( $query );
			if ( $sth ) {
				$result = $sth->execute( array(
					':id'          => null,
					':name'        => $this->name,
					':uri'         => $this->uri,
					':latest_seen' => $this->latest_seen,
					':last_sent'   => $this->last_sent
				) );

				if ( !$result ) {
					$error_info = print_r( $pdo->errorInfo(), true );
					throw new \Exception( $error_info );
				}
				return true;
			}

			return false;
		} else {
			// it's old, update it
			// //UPDATE Cars SET Name='Skoda Octavia' WHERE Id=3;
			$query = "UPDATE wpcom_vip_hash_remotes SET
			 name= :name, uri = :uri, latest_seen = :latest_seen, last_sent = :last_sent WHERE id = :id";
			$sth   = $pdo->prepare( $query );
			if ( $sth ) {
				$result = $sth->execute( array(
					':id'          => $this->data['id'],
					':name'        => $this->name,
					':uri'         => $this->uri,
					':latest_seen' => $this->latest_seen,
					':last_sent'   => $this->last_sent
				) );

				if ( !$result ) {
					$error_info = print_r( $pdo->errorInfo(), true );
					throw new \Exception( $error_info );
				}
				return true;
			}

			return false;
		}
	}
}