<?php

namespace automattic\vip\hash\pdo;

class PDOHashQuery implements HashQuery {

	/**
	 * PDO
	 * @var \PDO
	 */
	private $pdo;
	public function __construct( \PDO $pdo ) {
		$this->pdo = $pdo;
	}
}
