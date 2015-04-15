<?php

namespace automattic\vip\hash;

use PDO;

class DataModel {

	/**
	 * @var \PDO
	 */
	private $pdo = null;

	public function __construct() {
		$this->init();
	}

	public function init() {
		if ( !$this->pdo ) {
			$this->pdo = new PDO( 'sqlite:' . $this->getDBDir() . 'db.sqlite' );
			$this->pdo->query( 'CREATE TABLE IF NOT EXISTS wpcom_vip_hashes (
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				identifier CHAR(50) NOT NULL,
				user CHAR(30) NOT NULL,
				hash CHAR(30) NOT NULL,
				date INT NOT NULL,
				seen INT NOT NULL,
				status INT NOT NULL,
				notes TEXT
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
		$folder = $_SERVER['HOME'].DIRECTORY_SEPARATOR.'.viphash'.DIRECTORY_SEPARATOR;
		if ( !is_writable( $folder ) ) {
			$folder = '';
		} else {
			if ( !file_exists( $folder ) ) {
				mkdir( $folder, 0777, true );
			}
		}
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
		$results = $this->pdo->query( "SELECT * FROM wpcom_vip_hashes WHERE date > $date ORDER BY date ASC LIMIT 1" );
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

	public function getHashesSeenAfter( $date ) {
		$date = intval( $date );
		$results = $this->pdo->query( "SELECT * FROM wpcom_vip_hashes WHERE seen > $date ORDER BY seen ASC LIMIT 1" );
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
} 