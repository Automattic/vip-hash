<?php

namespace automattic\vip\hash;

/**
 * An interface for fetching hashes from a data store
 */
interface HashQuery {
	/**
	 * Fetches hashes given arguments
	 * 
	 * @param  array  $arguments A dictionary array containing parameters for what hashes to apc_fetch
	 * @return  bool              True if the hashes were fetched false for anything else, may throw exceptions
	 */
	public function fetch( array $arguments ) : bool;

	/**
	 * The number of pages if pagination is enabled
	 * @return int 0 if an error occurred, 1 if pagination is off, more if pagination is enabled
	 */
	public function totalPages() : int;

	/**
	 * An array of the found hashes
	 * 
	 * @return array An array of the hashes found when `fetch` was called, will be empty if that hasn't happened yet
	 */
	public function hashes() : array;

	/**
	 * How many hashes were fetched
	 * 
	 * @return int Number of hashes fetched
	 */
	public function hashCount() : int;
}
