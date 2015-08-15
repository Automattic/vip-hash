<?php

namespace automattic\vip\hash;

use PDO;

class DataModel {

	/**
	 * @var \PDO
	 */
	private $pdo = null;

	private $dbdir = '';

	public function __construct() {
		$this->init();
	}

	public function init() {
		if ( !$this->pdo ) {
			$this->pdo = new PDO( 'sqlite:' . $this->getDBDir() . 'db.sqlite' );
			$this->pdo->query( 'CREATE TABLE IF NOT EXISTS wpcom_vip_hashes (
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				identifier CHAR(50) NOT NULL UNIQUE,
				user CHAR(30) NOT NULL,
				hash CHAR(30) NOT NULL,
				date INT NOT NULL,
				seen INT NOT NULL,
				status INT NOT NULL,
				notes TEXT
			)' );
			$this->pdo->query( 'CREATE TABLE IF NOT EXISTS wpcom_vip_hash_remotes (
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				name CHAR(50) NOT NULL UNIQUE,
				uri CHAR(30) NOT NULL,
				latest_seen INT NOT NULL,
				last_sent INT NOT NULL
			)' );
		}
	}

	/**
	 * @return \PDO
	 */
	public function getPDO() {
		return $this->pdo;
	}

	/**
	 * @param        $hash
	 * @param        $username
	 * @param bool   $value
	 *
	 * @param string $note
	 *
	 * @param string $date
	 *
	 * @throws \Exception
	 * @return bool
	 */
	public function markHash( $hash, $username, $value, $note = '', $date = '' ) {

		$record = new HashRecord( $this );
		$record->setHash( $hash );
		$record->setUsername( $username );
		$record->setStatus( $value );
		$record->setNote( $note );

		if ( !empty( $date ) ) {
			$record->setDate( $date );
		}

		return $record->save( $this );
	}

	/**
	 * @param $file
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function hashFile( $file ) {
		if ( !file_exists( $file ) ) {
			throw new \Exception( "File does not exist" );
		}
		if ( is_dir( $file ) ) {
			throw new \Exception( "You cannot hash a folder" );
		}
		if ( !is_file( $file ) ) {
			throw new \Exception( "Only files can be hashed" );
		}
		$code = php_strip_whitespace( $file );
		$hash = sha1( $code );
		return $hash;
	}


	/**
	 * @param $hash
	 * @param $username
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function getHashStatusByUser( $hash, $username ) {
		$results = $this->pdo->query( "SELECT * FROM wpcom_vip_hashes WHERE hash = '$hash' AND user = '$username'" );

		if ( !$results ) {
			$error_info = print_r( $this->pdo->errorInfo(), true );
			throw new \Exception( $error_info, $this->pdo->errorCode() );
		}

		$output_data = array();
		while ( $row = $results->fetch( PDO::FETCH_ASSOC ) ) {
			unset( $row['id'] );
			$output_data[] = $row;
		}
		return $output_data;
	}

	/**
	 * @param $hash
	 *
	 * @throws \Exception
	 * @return array
	 */
	public function getHashStatusAllUsers( $hash ) {
		$results = $this->pdo->query( "SELECT * FROM wpcom_vip_hashes WHERE hash = '$hash'" );

		if ( !$results ) {
			$error_info = print_r( $this->pdo->errorInfo(), true );
			throw new \Exception( $error_info  );
		}

		$output_data = array();
		while ( $row = $results->fetch( PDO::FETCH_ASSOC ) ) {
			unset( $row['id'] );
			$output_data[] = $row;
		}
		return $output_data;
	}

	/**
	 * @return string the folder containing hash records with a trailing slash
	 */
	public function getDBDir() {

		if ( !empty( $this->dbdir ) ) {
			return $this->dbdir;
		}
		$folders = array();
		if ( !empty ( $_SERVER['HOME'] ) ) {
			$folders[] = $_SERVER['HOME'].DIRECTORY_SEPARATOR.'.viphash'.DIRECTORY_SEPARATOR;
		}
		if ( function_exists( 'posix_getpwuid' ) ) {
			$shell_user = posix_getpwuid( posix_getuid() );
			$shell_home = $shell_user['dir'];
			$folders[] = $shell_home.DIRECTORY_SEPARATOR.'.viphash'.DIRECTORY_SEPARATOR;
		}

		// Windows
		if ( !empty( $_SERVER['HOMEDRIVE'] ) && !empty( $_SERVER['HOMEPATH'] ) ) {
			$folders[] = $_SERVER['HOMEDRIVE']. $_SERVER['HOMEPATH'].DIRECTORY_SEPARATOR.'.viphash'.DIRECTORY_SEPARATOR;
		}
		$folder[] '.viphash'.DIRECTORY_SEPARATOR;

		$folder = '';
		foreach ( $folders as $f ) {
			if ( is_writable( $f ) ) {
				if ( !file_exists( $f ) ) {
					if ( ! mkdir( $f, 0777, true ) ) {
						continue;
					}
				}
				$folder = $f;
				break;
			}
		}
		$this->dbdir = $folder;
		return $folder;
	}

	public function getNewestSeenHash() {
		$results = $this->pdo->query( "SELECT * FROM wpcom_vip_hashes ORDER BY seen DESC LIMIT 1" );
		if ( !$results ) {
			$error_info = print_r( $this->pdo->errorInfo(), true );
			throw new \Exception( $error_info  );
		}

		$output_data = array();
		while ( $row = $results->fetch( PDO::FETCH_ASSOC ) ) {
			unset( $row['id'] );
			return $row;
		}
		return $output_data;
	}

	public function getHashesAfter( $date ) {
		$date = intval( $date );
		$results = $this->pdo->query( "SELECT * FROM wpcom_vip_hashes WHERE date > $date ORDER BY date ASC" );
		if ( !$results ) {
			$error_info = print_r( $this->pdo->errorInfo(), true );
			throw new \Exception( $error_info  );
		}

		$output_data = array();
		while ( $row = $results->fetch( PDO::FETCH_ASSOC ) ) {
			unset( $row['id'] );
			$output_data[] = $row;
		}
		return $output_data;
	}

	public function getHashesSeenAfter( $date ) {
		$date = intval( $date );
		$results = $this->pdo->query( "SELECT * FROM wpcom_vip_hashes WHERE seen > $date ORDER BY seen ASC" );
		if ( !$results ) {
			$error_info = print_r( $this->pdo->errorInfo(), true );
			throw new \Exception( $error_info  );
		}

		$output_data = array();
		while ( $row = $results->fetch( PDO::FETCH_ASSOC ) ) {
			unset( $row['id'] );
			$output_data[] = $row;
		}
		return $output_data;
	}


	public function addRemote( $name, $uri ) {
		$pdo = $this->getPDO();

		$query = "INSERT INTO wpcom_vip_hash_remotes VALUES
		( :id, :name, :uri, :latest_seen, :last_sent )";
		$sth   = $pdo->prepare( $query );
		if ( $sth ) {
			$result = $sth->execute( array(
				':id'          => null,
				':name'        => $name,
				':uri'         => $uri,
				':latest_seen' => 0,
				':last_sent'   => 0
			) );

			if ( !$result ) {
				$error_info = print_r( $pdo->errorInfo(), true );
				throw new \Exception( $error_info );
			}
			return true;
		}

		return false;
	}

	/**
	 * @return array
	 * @throws \Exception
	 */
	public function getRemotes() {
		$results = $this->pdo->query( "SELECT * FROM wpcom_vip_hash_remotes" );
		if ( !$results ) {
			$error_info = print_r( $this->pdo->errorInfo(), true );
			throw new \Exception( $error_info  );
		}

		$output_data = array();
		while ( $row = $results->fetch( PDO::FETCH_ASSOC ) ) {
			unset( $row['id'] );
			$output_data[] = $row;
		}
		return $output_data;
	}

	/**
	 * @param $name
	 *
	 * @throws \Exception
	 * @return bool|mixed
	 */
	public function getRemote( $name ) {
		$results = $this->pdo->query( "SELECT * FROM wpcom_vip_hash_remotes WHERE name = '$name'" );
		if ( !$results ) {
			$error_info = print_r( $this->pdo->errorInfo(), true );
			throw new \Exception( $error_info  );
		}

		while ( $row = $results->fetch( PDO::FETCH_ASSOC ) ) {
			unset( $row['id'] );
			return $row;
		}
		return false;
	}
} 