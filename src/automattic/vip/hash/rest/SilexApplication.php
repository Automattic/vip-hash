<?php

namespace automattic\vip\hash\rest;

use automattic\vip\hash\DataModel;
use automattic\vip\hash\Pdo_Data_Model;
use automattic\vip\hash\HashRecord;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SilexApplication {

	private $dbdir = '';
	private $app;

	/**
	 * The primary data model
	 * @var DataModel
	 */
	private $model;

	public function __construct( $dbdir ) {
		$this->dbdir = $dbdir;
	}

	public function run() {
		$app = new \Silex\Application();
		$this->app = $app;
		$app['debug'] = true;
		$this->register_endpoints( $app );

		$app->view( function( array $controller_result ) use ( $app ) {
			return $app->json( $controller_result );
		});

		$this->model = new Pdo_Data_Model( $this->dbdir );

		$app->run();
	}

	public function register_endpoints( \Silex\Application $app ) {
		/**
		 * remote/hash <- send
		 * remote/hash/<hash> <- read
		 * remote/hash/seen/since/<time> <- read
		 */
		$app->get( '/', function () {
			return 'VIP Hash Database';
		});

		$app->get( '/hash/seen/since/{timestamp}', array( $this, 'hash_seen_since' ) );
		$app->get( '/hash/{hash}', array( $this, 'get_hash' ) );
		$app->post( '/hash', array( $this, 'post_hash' ) );

	}

	public function hash_seen_since( $timestamp ) {
		return $this->model->getHashesSeenAfter( $timestamp );
	}

	function get_hash( $hash ) {
		try {
			return $this->model->getHashStatusAllUsers( $hash );
		} catch ( \Exception $e ) {
			return array( 'error' => $e->getMessage() );
		}
	}

	public function post_hash( Request $request ) {
		$json_data = $request->get( 'data' );
		$data = json_decode( $json_data, true );
		foreach ( $data as $record ) {
			try {
				$this->model->markHash(
					$record['hash'],
					$record['user'],
					$record['status'],
					$record['notes'],
					$record['date']
				);
			} catch ( \Exception $e ) {
				return array( 'error' => $e->getMessage() );
			}
		}
		return $request;
	}
}
