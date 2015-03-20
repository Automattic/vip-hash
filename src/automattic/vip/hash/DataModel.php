<?php

namespace automattic\vip\hash;


class DataModel {

	public function markHash( $hash, $username, $value ) {
		//
	}

	public function hashFile( $file ) {
		$code = php_strip_whitespace( $file );
		$hash = sha1( $code );
		return $hash;
	}

	public function getHashStatusByUser( $hash, $username ) {
		return 'wip';
	}

	public function getHashStatusAllUsers( $hash ) {
		return 'wip';
	}
} 