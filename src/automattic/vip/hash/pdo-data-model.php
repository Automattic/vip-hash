<?php

namespace automattic\vip\hash;

use PDO;

/**
 * Implements the DataModel interface with PDO sqlite
 */
class Pdo_Data_Model extends NullDataModel {

	/**
	 * @var \PDO
	 */
	private $pdo = null;

	private $dbdir = '';

	private $config;

	public function __construct( $dbdir = '' ) {
		$this->dbdir = $dbdir;
		$this->init();
	}

	public function init() {
		if ( ! $this->pdo ) {
			$this->pdo = new PDO( 'sqlite:' . $this->getDBDir() . 'db.sqlite' );
		}
		$helper = new \automattic\vip\hash\pdo\DB_Helper( $this->pdo );
		$helper->create_tables();

		$path = $this->getDBDir().'config.json';
		$this->config = new \automattic\vip\hash\config\JSONConfig( $path );
	}

	/**
	 * Attempts to upgrade the database table scheme
	 * @return bool did it succeed?
	 */
	public function copy_and_upgrade() {
		$helper = new \automattic\vip\hash\pdo\DB_Helper( $this->pdo );
		return $helper->copy_and_upgrade();
	}

	/**
	 * @return \PDO
	 */
	public function getPDO() : \PDO {
		return $this->pdo;
	}


	/**
	 * Save a hash record to the data store
	 *
	 * @param \automattic\vip\hash\HashRecord $record the hash to be saved
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function saveHash( HashRecord $record ) : bool {
		$pdo = $this->getPDO();

		$username = $record->getUsername();
		$hash = $record->getHash();
		$date = $record->getDate();
		$seen = time();
		$status = $record->getStatus();
		$notes = $record->getNote();
		$human_note = $record->getHumanNote();

		/*if ( empty( $username ) ) {
			throw new \Exception( 'Empty username' );
		}*/

		$identifier = $hash . '-' . $username . '-' . $date;

		$query = 'INSERT INTO wpcom_vip_hashes( id, identifier, user, hash, date, seen, status, notes, human_note )
								SELECT :id, :identifier, :username, :hash, :date, :seen, :status, :notes, :human_note
								WHERE NOT EXISTS ( select 1 from wpcom_vip_hashes
													WHERE identifier = :identifier)';
		$sth = $pdo->prepare( $query );
		if ( ! $sth ) {
			$error_info = print_r( $pdo->errorInfo(), true );
			throw new \Exception( 'Error creating insert statement ' . $error_info );
		}
		$result = $sth->execute( array(
			':id'         => null,
			':identifier' => $identifier,
			':username'   => $username,
			':hash'       => $hash,
			':date'       => $date,
			':seen'       => $seen,
			':status'     => $status,
			':notes'      => $notes,
			':human_note' => $human_note,
		) );

		if ( ! $result ) {
			$error_info = print_r( $pdo->errorInfo(), true );
			$error_info_sth = print_r( $sth->errorInfo(), true );
			throw new \Exception(
				"Error executing insert statement\nPDO: #" . $pdo->errorCode() . ' ' . $error_info .
				"\n STH: #" . $sth->errorCode() . ' ' . $error_info_sth .
				"\n identifier:" . $identifier
			);
		}
		return true;
	}

	/**
	 * @return string the folder containing hash records with a trailing slash
	 */
	public function getDBDir() : string {

		if ( ! empty( $this->dbdir ) ) {
			return $this->dbdir;
		}
		$folders = $this->searchDBFolders();

		if ( empty( $folders ) ) {
			throw new \Exception( 'Could not locate a place to put the PDO/SQLite database' );
		}
		foreach ( $folders as $f ) {
			if ( ! is_writable( $f ) ) {
				continue;
			}
			if ( ( ! file_exists( $f ) ) && ( ! mkdir( $f, 0777, true ) ) ) {
				continue;
			}
			$this->dbdir = $f;
			return $f;
		}
		
		throw new \Exception( 'PDO/SQLite Database locations were found, but for whatever reason they were neither writable, or the folders could not be created' );
	}

	/**
	 * Search for various folders that could contain the database
	 * 
	 * @return array an array of strings representing folder paths
	 */
	protected function searchDBFolders() : array {
		$folders = [];
		if ( ! empty( $_SERVER['HOME'] ) ) {
			$folders[] = $_SERVER['HOME'] . DIRECTORY_SEPARATOR . '.viphash' . DIRECTORY_SEPARATOR;
		}
		if ( function_exists( 'posix_getpwuid' ) ) {
			$shell_user = posix_getpwuid( posix_getuid() );
			$shell_home = $shell_user['dir'];
			$folders[] = $shell_home . DIRECTORY_SEPARATOR . '.viphash' . DIRECTORY_SEPARATOR;
		}

		// Windows
		if ( ! empty( $_SERVER['HOMEDRIVE'] ) && ! empty( $_SERVER['HOMEPATH'] ) ) {
			$folders[] = $_SERVER['HOMEDRIVE'] . $_SERVER['HOMEPATH'] . DIRECTORY_SEPARATOR . '.viphash' . DIRECTORY_SEPARATOR;
		}
		$folders[] = '~' . DIRECTORY_SEPARATOR . '.viphash' . DIRECTORY_SEPARATOR;
		$folders[] = '.viphash' . DIRECTORY_SEPARATOR;
		return $folders;
	}

	/**
	 * @inherit
	 */
	public function getNewestSeenHash() {
		$results = $this->pdo->query( 'SELECT * FROM wpcom_vip_hashes ORDER BY seen DESC LIMIT 1' );
		if ( ! $results ) {
			$error_info = print_r( $this->pdo->errorInfo(), true );
			throw new \Exception( $error_info );
		}

		$output_data = array();
		while ( $row = $results->fetch( PDO::FETCH_ASSOC ) ) {
			unset( $row['id'] );
			return $row;
		}
		return $output_data;
	}


	/**
	 * @param Remote $remote
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function addRemote( Remote $remote ) : bool {

		$name = $remote->getName();
		$uri = $remote->getUri();
		$latest_seen = $remote->getLatestSeen();
		$last_sent = $remote->getLastSent();
		$oauth_details = $remote->getOauthDetails();

		$query = 'INSERT INTO wpcom_vip_hash_remotes ( name, uri, latest_seen, last_sent, oauth_details )
		VALUES
			(  :name, :uri, :latest_seen, :last_sent, :oauth_details )';//:id,
		$sth = $this->pdo->prepare( $query );
		if ( ! $sth ) {
			$error_info = print_r( $this->pdo->errorInfo(), true );
			throw new \Exception( 'PDO: prepared statement error :' . $error_info );
			//throw new \Exception( 'failed to prepare statement' );
		}
		$result = $sth->execute( array(
			/*':id'                  => '',*/
			':name'                => $name,
			':uri'                 => $uri,
			':latest_seen'         => $latest_seen,
			':last_sent'           => $last_sent,
			':oauth_details'       => serialize( $oauth_details ),
		) );

		if ( ! $result ) {
			$error_info = print_r( $sth->errorInfo(), true );
			throw new \Exception( 'PDO execution statement error: ' . $error_info );
		}
		return true;
	}

	public function updateRemote( Remote $remote ) : bool {
		$id = $remote->getId();
		$name = $remote->getName();
		$uri = $remote->getUri();
		$latest_seen = $remote->getLatestSeen();
		$last_sent = $remote->getLastSent();

		$oauth_details = $remote->getOauthDetails();

		// it's old, update it
		// //UPDATE Cars SET Name='Skoda Octavia' WHERE Id=3;
		$query = 'UPDATE wpcom_vip_hash_remotes SET
		 name= :name, uri = :uri, latest_seen = :latest_seen, last_sent = :last_sent,
		 oauth_details = :oauth_details WHERE id = :id';
		$sth   = $this->pdo->prepare( $query );
		if ( ! $sth ) {
			$error_info = print_r( $this->pdo->errorInfo(), true );
			throw new \Exception( $error_info );
		}
		$result = $sth->execute( array(
			':id'                  => $id,
			':name'                => $name,
			':uri'                 => $uri,
			':latest_seen'         => $latest_seen,
			':last_sent'           => $last_sent,
			':oauth_details'       => serialize( $oauth_details ),
		) );

		if ( ! $result ) {
			$error_info = print_r( $sth->errorInfo(), true );
			throw new \Exception( $error_info );
		}
		return true;
	}

	public function removeRemote( Remote $remote ) : bool {
		$name = $remote->getName();
		// it's old, update it
		// //UPDATE Cars SET Name='Skoda Octavia' WHERE Id=3;
		$query = 'DELETE FROM wpcom_vip_hash_remotes WHERE name= :name';
		$sth   = $this->pdo->prepare( $query );
		if ( ! $sth ) {
			$error_info = print_r( $this->pdo->errorInfo(), true );
			throw new \Exception( $error_info );
		}
		$result = $sth->execute( [
			':name' => $name
		] );

		if ( ! $result ) {
			$error_info = print_r( $sth->errorInfo(), true );
			throw new \Exception( $error_info );
		}
		return true;
	}

	/**
	 * @return array
	 * @throws \Exception
	 */
	public function getRemotes() {
		$results = $this->pdo->query( 'SELECT * FROM wpcom_vip_hash_remotes' );
		if ( ! $results ) {
			$error_info = print_r( $this->pdo->errorInfo(), true );
			throw new \Exception( $error_info );
		}

		$output_data = array();
		while ( $row = $results->fetch( PDO::FETCH_ASSOC ) ) {
			$output_data[] = new Remote( $row );
		}
		return $output_data;
	}

	/**
	 * @param $name
	 *
	 * @throws \Exception
	 * @return bool|Remote
	 */
	public function getRemote( $name ) {
		$results = $this->pdo->query( "SELECT * FROM wpcom_vip_hash_remotes WHERE name = '$name'" );
		if ( ! $results ) {
			$error_info = print_r( $this->pdo->errorInfo(), true );
			throw new \Exception( $error_info );
		}

		while ( $row = $results->fetch( PDO::FETCH_ASSOC ) ) {
			//unset( $row['id'] );
			return new Remote( $row );
		}
		return false;
	}

	/**
	 * @inherit
	 */
	public function config() : config\Config {
		return $this->config;
	}

	/**
	 * @inherit
	 */
	public function newQuery() : HashQuery {
		return new \automattic\vip\hash\pdo\PDOHashQuery( $this->pdo );
	}
}
