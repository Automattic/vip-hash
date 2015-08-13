<?php

namespace automattic\vip\hash\rest;

class SilexApplication {
	public function run() {
		$app = new Silex\Application();

		$this->register_endpoints( $app );

		$app->run();
	}

	public function register_endpoints( Silex\Application $app ) {
		//
	}
}
