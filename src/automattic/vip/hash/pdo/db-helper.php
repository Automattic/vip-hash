<?php

namespace automattic\vip\hash\pdo;

use PDO;

class DB_Helper {

	private $pdo;
	public function __construct( \PDO $pdo ) {
		$this->pdo = $pdo;
		$this->pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
	}

	public function create_tables( $prefix = 'wpcom_', $conditional = true ) {
		$not_exists = '';
		if ( true === $conditional ) {
			$not_exists = 'IF NOT EXISTS ';
		}
		$this->pdo->query( 'CREATE TABLE '.$not_exists.$prefix.'vip_hashes (
			id INTEGER PRIMARY KEY AUTOINCREMENT,
			identifier CHAR(100) NOT NULL UNIQUE,
			user CHAR(50) NOT NULL,
			hash CHAR(100) NOT NULL,
			date INT NOT NULL,
			seen INT NOT NULL,
			status CHAR(30) NOT NULL,
			notes TEXT,
			human_note TEXT
		)' );

		$this->pdo->query( 'CREATE TABLE '.$not_exists.$prefix.'vip_hash_remotes (
			id INTEGER PRIMARY KEY AUTOINCREMENT,
			name CHAR(60) NOT NULL UNIQUE,
			uri CHAR(255) NOT NULL,
			latest_seen INT NOT NULL,
			last_sent INT NOT NULL,
			oauth_details TEXT
		)' );
	}

	/**
	 * Copies the tables and uses the latest schema to do so
	 *
	 * @return bool did PDO commit succeed?
	 */
	public function copy_and_upgrade() {
		// start a transaction
		$this->pdo->beginTransaction();

		// create copies
		$this->create_tables( 'wpcom_temp_', false );

		//copy data over to temporary tables
		$this->copy_hash_table( 'wpcom_vip_hashes', 'wpcom_temp_vip_hashes' );
		$this->copy_remotes_table( 'wpcom_vip_hash_remotes', 'wpcom_temp_vip_hash_remotes' );
		//

		// drop original tables
		$this->drop_table( 'wpcom_vip_hashes' );
		$this->drop_table( 'wpcom_vip_hash_remotes' );

		// rename copies to original
		$this->rename_table( 'wpcom_temp_vip_hashes', 'wpcom_vip_hashes' );

		$this->rename_table( 'wpcom_temp_vip_hash_remotes', 'wpcom_vip_hash_remotes' );
		//$this->drop_table( 'wpcom_temp_vip_hash_remotes' );

		// end transaction
		return $this->pdo->commit();
	}

	public function drop_table( $table_name ) {
		// DROP TABLE X
		$st = $this->pdo->prepare( 'DROP TABLE '.$table_name );
		if ( ! $st ) {
			$error_info = print_r( $this->pdo->errorInfo(), true );
			throw new \Exception( $error_info );
		}
		$st->execute();
	}

	public function copy_hash_table( $source, $target ) {
		// INSERT INTO new_X SELECT ... FROM X
		// get rid of duplicate identifiers
		$sql = "delete from $source where rowid not in
		 (
		 select  min(rowid)
		 from    $source
		 group by
				 identifier
		 )";
		print_r( $sql."\n" );
		$st = $this->pdo->prepare( $sql );
		if ( ! $st ) {
			$error_info = print_r( $this->pdo->errorInfo(), true );
			throw new \Exception( $error_info );
		}
		$st->execute();

		$columns = implode( ', ', [
			'id',
			'identifier',
			'user',
			'hash',
			'date',
			'seen',
			'status',
			'notes',
			'""',
		] );
		$sql = 'INSERT INTO '.$target.' SELECT '.$columns.' FROM '.$source ;
		print_r( $sql."\n" );
		$st = $this->pdo->prepare( $sql );
		if ( ! $st ) {
			$error_info = print_r( $this->pdo->errorInfo(), true );
			throw new \Exception( $error_info );
		}
		$st->execute();
	}

	public function copy_remotes_table( $source, $target ) {
		$columns = implode( ', ', [
			'id',
			'name',
			'uri',
			'latest_seen',
			'last_sent',
			'""',
		] );
		$sql = 'INSERT INTO '.$target.' SELECT '.$columns.' FROM '.$source ;
		print_r( $sql."\n" );
		$st = $this->pdo->prepare( $sql );
		if ( ! $st ) {
			$error_info = print_r( $this->pdo->errorInfo(), true );
			throw new \Exception( $error_info );
		}
		$st->execute();
	}

	public function rename_table( $old, $new ) {
		// ALTER TABLE new_X RENAME TO X
		$st = $this->pdo->prepare( 'ALTER TABLE '.$old.' RENAME TO '.$new );
		if ( ! $st ) {
			$error_info = print_r( $this->pdo->errorInfo(), true );
			throw new \Exception( $error_info );
		}
		$st->execute();
	}
}
