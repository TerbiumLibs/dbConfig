<?php namespace Terbium\DbConfig\Interfaces;

interface DbProviderInterface {

	/**
	 * Load the given configuration collection.
	 *
	 * @param  string  $environment
	 * @param  string  $collection
	 * @return array
	 */
	public function load($environment, $collection);

	/**
	 * Save item to the database or update the existing one
	 *
	 * @param string $key
	 * @param mixed $value
	 * @param string $environment
	 */
	public function store($key, $value, $environment = 'production');

	/**
	 * Remove item from the database
	 *
	 * @param string $key
	 * @param string $environment
	 */
	public function forget($key, $environment = 'production');

	/**
	 * Clear the table with settings
	 */
	public function clear();


}
