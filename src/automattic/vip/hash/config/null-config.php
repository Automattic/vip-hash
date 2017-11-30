<?php

namespace automattic\vip\hash\config;

class NullConfig implements Config {

	private $file;
	private $data;

	public function set( string $key, $value ) {
		//
	}

	public function get( string $key ) {
		return null;
	}

	private function save() {
		//
	}
}
