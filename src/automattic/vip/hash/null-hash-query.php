<?php

namespace automattic\vip\hash;


class NullHashQuery implements HashQuery {
	/**
	 * @inherit
	 */
	public function fetch( array $arguments ) : bool {
		return false;
	}

	/**
	 * @inherit
	 */
	public function totalPages() : int {
		return 0;
	}

	/**
	 * @inherit
	 */
	public function hashes() : array {
		return [];
	}
	
	/**
	 * @inherit
	 */
	public function hashCount() : int {
		return 0;
	}

	/**
	 * @inherit
	 */
	public function totalHashCount() : int {
		return 0;
	}
}
