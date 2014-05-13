<?php namespace Terbium\DbConfig;

use Terbium\DbConfig\Interfaces\DbProviderInterface;
use Terbium\DbConfig\Exceptions\SaveException;


class DbConfig
{

    /**
     * The database provider.
     *
     * @var \Terbium\DbConfig\Interfaces\DbProviderInterface
     */
    protected $dbProvider;

    /**
     * The current environment.
     *
     * @var string
     */
    protected $environment;


    /**
     * All of the configuration items from DB.
     *
     * @var array
     */
    protected $items = array();


    /**
     * @var
     */
    private $origConfig;


    /**
     * The after load callbacks for namespaces.
     *
     * @var array
     */
    protected $afterLoad = array();


    /**
     * @param $origConfig
     * @param $environment
     * @param DbProviderInterface $dbProvider
     */
    public function __construct(&$origConfig, $environment, DbProviderInterface $dbProvider)
    {

        $this->dbProvider = $dbProvider;

        $this->origConfig = $origConfig;

        $this->environment = $environment;
    }


    /**
     * Determine if the given configuration value exists.
     *
     * @param  string $key
     * @return bool
     */
    public function has($key, $fallback = true)
    {

        $default = microtime(true);

        return $this->get($key, $default, $fallback) !== $default;
    }


    /**
     * Get the specified configuration value.
     *
     * @param  string $key
     * @param  mixed $default
     * @param bool $fallback
     * @return mixed
     */
    public function get($key, $default = null, $fallback = true)
    {

        list($namespace, $group, $item) = $this->origConfig->parseKey($key);

        $collection = $this->getCollection($group, $namespace);

        $this->load($group, $namespace, $collection);

        $result =  array_get($this->items[$collection], $item, $default);


        // found one in DB
        if ($result !== $default) return $result;

        // not set in DB and needn't to fallback
        if (!$fallback) {
            return $default;
        }

        return $this->origConfig->get($key, $default);
    }

    /**
     * Get the collection identifier.
     *
     * @param  string $group
     * @param  string $namespace
     * @return string
     */
    protected function getCollection($group, $namespace = null)
    {

        $namespace = $namespace ? : '*';

        return $namespace . '::' . $group;
    }


    /**
     * Load the configuration group for the key from files and merge with data from DB.
     *
     * @param  string $group
     * @param  string $namespace
     * @param  string $collection
     *
     * @return void
     */
    protected function load($group, $namespace, $collection)
    {

        $env = $this->environment;

        // If we've already loaded this collection, we will just bail out since we do
        // not want to load it again. Once items are loaded a first time they will
        // stay kept in memory within this class and not loaded from disk again.
        if (isset($this->items[$collection])) {
            return;
        }

        //load items from DB
        $items = $this->dbProvider->load($collection, $env);

        // If we've already loaded this collection, we will just bail out since we do
        // not want to load it again. Once items are loaded a first time they will
        // stay kept in memory within this class and not loaded from disk again.


        $this->afterLoad = $this->origConfig->getAfterLoadCallbacks();


        if (isset($this->afterLoad[$namespace])) {
            $items = $this->callAfterLoad($namespace, $group, $items);
        }

        $this->items[$collection] = $items;
    }

    /**
     * Call the after load callback for a namespace.
     *
     * @param  string $namespace
     * @param  string $group
     * @param  array $items
     * @return array
     */
    protected function callAfterLoad($namespace, $group, $items)
    {

        $callback = $this->afterLoad[$namespace];

        return call_user_func($callback, $this->origConfig, $group, $items);
    }


    /**
     * Save item into database and set to current config
     *
     * @param string $key
     * @param mixed $value
     * @param string $environment
     *
     * @return void
     *
     * @throws Exceptions\SaveException
     */
    public function store($key, $value, $environment = null)
    {

        // Default to the current environment.
        if (is_null($environment)) {
            $environment = $this->environment;
        }

        list($namespace, $group, $item) = $this->origConfig->parseKey($key);

        if (is_null($item)) {
            throw new SaveException('The key should contain a group');
        }

        $collection = $this->getCollection($group, $namespace);

        $dbkey = $collection . '.' . $item;

        // save key => value into DB
        $this->dbProvider->store($dbkey, $value, $environment);

        //set value to config
        $this->origConfig->set($key, $value);


    }

    /**
     * Remove item from the database
     *
     * @param string $key
     * @param string $environment
     *
     * @return void
     *
     * @throws Exceptions\SaveException
     */
    public function forget($key, $environment = null)
    {

        // Default to the current environment.
        if (is_null($environment)) {
            $environment = $this->environment;
        }

        list($namespace, $group, $item) = $this->origConfig->parseKey($key);

        if (is_null($item)) {
            throw new SaveException('The key should contain a group');
        }

        $collection = $this->getCollection($group, $namespace);

        $dbkey = $collection . '.' . $item;

        // remove item from DB
        $this->dbProvider->forget($dbkey, $environment);

        // remove item from original config
        $this->origConfig->offsetUnset($key);

    }

    /**
     * clear all current items (they will be reloaded on next usage)
     */
    public function clear()
    {

        $this->items = array();
    }

    /**
     * Clear the table with settings
     */
    public function clearDb()
    {

        $this->dbProvider->clear();

    }

    /**
     * Return query builder with list of settings from database
     *
     * @param string $wildcard
     * @param string $environment
     *
     * @return \Illuminate\Database\Query\Builder
     */

    public function listDb($wildcard = null, $environment = null)
    {

        // Default to the current environment.
        if (is_null($environment)) {
            $environment = $this->environment;
        }

        return $this->dbProvider->listDb($wildcard, $environment);
    }


    /**
     *
     * Call original Config::{method}
     *
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {

        return call_user_func_array(array($this->origConfig, $name), $arguments);

    }


}
