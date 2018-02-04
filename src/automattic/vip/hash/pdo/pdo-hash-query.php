<?php

namespace automattic\vip\hash\pdo;

class PDOHashQuery implements \automattic\vip\hash\HashQuery {

	/**
	 * PDO
	 * @var \PDO
	 */
	private $pdo;

	private $hashes;
	private $arguments;
	private $pageCount;


	public function __construct( \PDO $pdo ) {
		$this->pdo = $pdo;
		$this->arguments = $this->hashes = [];
		$this->pageCount = 0;
	}

	/**
	 * @inherit
	 */
	public function fetch( array $arguments ) : bool {
		$this->arguments = $arguments;

		$parameters = [];
		$query = '';
		// figure out the WHERE clauses
		$where = [];

		// particular hash
		if ( !empty( $arguments['hash'] ) ) {
			$parameters[':hash'] = $arguments['hash'];
			$where[] = 'hash = :hash';
		}

		// particular user
		if ( !empty( $arguments['user'] ) ) {
			$parameters[':user'] = $arguments['user'];
			$where[] = 'user = :user';
		}
		// seen after
		

		if ( !empty( $where ) ) {
			$query .= 'WHERE '.implode( ' AND ', $where );
		}
 
		// Figure out the Page/Limit clauses
		$limits = '';
		$query .= $limits;

		$query = 'SELECT * FROM wpcom_vip_hashes '.$query;
		
		$sth = $this->pdo->prepare( $query );
		if ( ! $sth ) {
			$error_info = print_r( $this->pdo->errorInfo(), true );
			throw new \Exception( 'Error creating PDO Hash Query statement ' . $error_info );
		}
		$result = $sth->execute( $parameters );

		if ( ! $result ) {
			$error_info = print_r( $this->pdo->errorInfo(), true );
			$error_info_sth = print_r( $this->sth->errorInfo(), true );
			throw new \Exception(
				"Error executing PDO HashQuery statement\nPDO: #" . $this->pdo->errorCode() . ' ' . $error_info .
				"\n STH: #" . $sth->errorCode() . ' ' . $error_info_sth .
				"\n identifier:" . $identifier
			);
		}
		$this->hashes = $sth->fetchAll( \PDO::FETCH_ASSOC );
		unset( $sth );
		$this->hashes = array_map( function( $hash ) {
			unset( $hash['id']);
			return $hash;
		}, $this->hashes );

		return true;
	}

	/**
	 * @inherit
	 */
	public function totalPages() : int {
		return $this->pageCount;
	}

	/**
	 * @inherit
	 */
	public function hashes() : array {
		return $this->hashes;
	}

	/**
	 * @inherit
	 */
	public function hashCount() : int {
		return count( $this->hashes );
	}
}
