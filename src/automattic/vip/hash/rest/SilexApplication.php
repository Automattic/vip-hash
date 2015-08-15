<?php

namespace automattic\vip\hash\rest;

use automattic\vip\hash\DataModel;
use automattic\vip\hash\HashRecord;

class SilexApplication {
	public function run() {
		$app = new \Silex\Application();

		$this->register_endpoints( $app );

		$app->view( function ( array $controllerResult ) use ( $app ) {
			return $app->json( $controllerResult );
		});

		$app->run();
	}

	public function register_endpoints( Silex\Application $app ) {
		/**
		 * remote/hash <- send
		 * remote/hash/<hash> <- read
		 * remote/hash/seen/since/<time> <- read
		 */
		$app->get ( '/', function() {
			return 'VIP Hash Database';
		});

		$app->get( '/hash/seen/since/{timestamp}', function( $timestamp ) use( $app ) {
			$model = new DataModel();
			return $model->getHashesAfter( $timestamp );
		});

		$app->get( '/hash/{hash}', function( $hash ) use( $app ) {
			$data = new DataModel();
			try {
				return $model->getHashStatusAllUsers( $hash );
			} catch( \Exception $e ) {
				return array( 'error' => $e->getMessage() );
			}
		});


	}
}
