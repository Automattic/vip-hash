<?php

namespace automattic\vip\hash;


class DataModel {

	public function markHash( $hash, $username, $value ) {
		return false;
	}

	public function hashFile( $file ) {
		$code = php_strip_whitespace( $file );
		if ( empty( $code ) ) {
			return false;
		}
		$hash = sha1( $code );
		return $hash;
	}

	public function getHashStatusByUser( $hash, $username ) {
		return false;
	}

	public function getHashStatusAllUsers( $hash ) {
		return array();
	}
} 