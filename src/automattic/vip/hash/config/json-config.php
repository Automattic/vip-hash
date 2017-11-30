<?php

namespace automattic\vip\hash\config;

class JSONConfig implements Config {

	private $file;
	private $data;

	public function __construct( $json_file_path ) {
		$this->file = $json_file_path;
		if ( file_exists( $json_file_path ) && is_readable( $json_file_path ) ) {
			$contents = file_get_contents( $json_file_path );
			$this->data = json_decode( $contents );
			return;
		}
		$this->data = [];
		if ( is_writable( $json_file_path ) ) {
			touch( $json_file_path );
		}
	}

	public function set( string $key, $value ) {
		$this->data[ $key ] = $value;
		$this->save();
	}

	public function get( string $key ) {
		if ( empty( $this->data[ $key ] ) ) {
			return null; // todo throw exception
		}
		return $this->data[ $key ];
	}

	private function save() {
		$json = json_encode( $this->data );
		file_put_contents( $this->file );
	}
}
