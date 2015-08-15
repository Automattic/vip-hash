<?php

namespace automattic\vip\hash\rest;

use automattic\vip\hash\DataModel;
use automattic\vip\hash\HashRecord;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SilexApplication {

	private $dbdir='';
	private $app;

	public function __construct( $dbdir ) {
		$this->dbdir = $dbdir;
	}

	public function run() {
		$app = new \Silex\Application();
		$this->app = $app;
		$app['debug'] = true;
		$this->register_endpoints( $app );

		$app->view( function ( array $controllerResult ) use ( $app ) {
			return $app->json( $controllerResult );
		});

		$app->run();
	}

	public function register_endpoints( \Silex\Application $app ) {
		/**
		 * remote/hash <- send
		 * remote/hash/<hash> <- read
		 * remote/hash/seen/since/<time> <- read
		 */
		$app->get ( '/', function () {
			return 'VIP Hash Database';
		});
		$dbdir = $this->dbdir;

		$app->get( '/hash/seen/since/{timestamp}', array( $this, 'hash_seen_since' ) );
		$app->get( '/hash/{hash}', array( $this, 'get_hash' ) );
		$app->post( '/hash', array( $this, 'post_hash' ) );

	}

	public function hash_seen_since ( $timestamp ) {
		$model = new DataModel( $this->dbdir );
		return $model->getHashesAfter( $timestamp );
	}

	function get_hash( $hash ) {
		$model = new DataModel( $this->dbdir );
		try {
			return $model->getHashStatusAllUsers( $hash );
		} catch( \Exception $e ) {
			return array( 'error' => $e->getMessage() );
		}
	}

	public function post_hash ( Request $request ) {
		$json_data = $request->get('data');
		$model = new DataModel( $this->dbdir );
		$data = json_decode( $json_data, true );
		foreach ( $data as $record ) {
			try {
				$model->markHash(
					$record['hash'],
					$record['username'],
					$record['value'],
					$record['note'],
					$record['date']
				);
			} catch ( \Exception $e ) {
				return array( 'error' => $e->getMessage() );
			}
		}
	}
}
