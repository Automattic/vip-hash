<?php

namespace automattic\vip\hash;


interface HashQuery {
	//
	public function fetch( array $arguments ) : bool;
	public function totalPages() : int;
	public function hashes() : array;
	public function hashCount() : int;
}
