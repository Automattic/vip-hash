<?php

namespace automattic\vip\hash;


class NullHashQuery implements HashQuery {
	public function fetch( array $arguments ) : bool {
		return false;
	}
	public function totalPages() : int {
		return 0;
	}
	public function hashes() : array {
		return [];
	}
	public function hashCount() : int {
		return 0;
	}
}
