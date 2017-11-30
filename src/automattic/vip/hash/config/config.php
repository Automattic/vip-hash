<?php

namespace automattic\vip\hash\config;

interface Config {
	public function set( string $key, $value );
	public function get( string $key );
}
