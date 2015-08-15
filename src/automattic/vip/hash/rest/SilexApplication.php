<?php

namespace automattic\vip\hash\rest;

use automattic\vip\hash\DataModel;
use automattic\vip\hash\HashRecord;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SilexApplication {

	private $dbdir='';

	public function __construct( $dbdir ) {
		$this->dbdir;
	}

	public function run() {
		$app = new \Silex\Application();
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

		$app->get( '/hash/seen/since/{timestamp}', function ( $timestamp ) use ( $app, $dbdir ) {
			$model = new DataModel( $dbdir );
			return $model->getHashesAfter( $timestamp );
		});

		$app->get( '/hash/{hash}', function ( $hash ) use ( $app, $dbdir ) {
			$data = new DataModel( $dbdir );
			try {
				return $model->getHashStatusAllUsers( $hash );
			} catch( \Exception $e ) {
				return array( 'error' => $e->getMessage() );
			}
		});

		$app->post( '/hash', function ( Request $request ) use ( $dbdir ) {
			$data = $request->get('data');
			$model = new DataModel( $dbdir );
			foreach ( $data as $record ) {
				try {
					$model->markHash( $data['hash'], $data['username'], $data['value'], $data['note'], $data['date'] );
				} catch ( \Exception $e ) {
					return array( 'error' => $e->getMessage() );
				}
			}
			return "Success";
		});


	}
}
