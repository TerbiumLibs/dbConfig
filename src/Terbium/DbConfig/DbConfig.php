<?php namespace Terbium\DbConfig;

use Illuminate\Config\Repository;
use Illuminate\Config\LoaderInterface;
use Terbium\DbConfig\Interfaces\DbProviderInterface;
use Terbium\DbConfig\Exceptions\SaveException;


class DbConfig extends Repository
{

    /**
     * The database provider.
     *
     * @var \Terbium\DbConfig\Interfaces\DbProviderInterface
     */
    protected $dbProvider;


    /**
     * @var
     */
    private $origConfig;


    /** Create a new configuration repository.
     * @param LoaderInterface $loader
     * @param string $environment
     * @param DbProviderInterface $dbProvider
     * @param $origConfig
     */
    public function __construct(LoaderInterface $loader, $environment, DbProviderInterface $dbProvider, &$origConfig)
    {

        parent::__construct($loader, $environment);

        $this->dbProvider = $dbProvider;

        $this->origConfig = $origConfig;
    }

    /**
     * load packadges list from orig configuration
     */
    private function updatePackadgesList(){

        if ($this->origConfig) {
            $this->packages = $this->origConfig->packages;
        }
    }


    /**
     * Get the specified configuration value.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function get($key, $default = null) {

        $this->updatePackadgesList();

        return parent::get($key, $default);
    }


    /**
     * Set a given configuration value.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function set($key, $value)
    {
        $this->updatePackadgesList();

        parent::set($key, $value);
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

        $this->updatePackadgesList();

        $env = $this->environment;

        // If we've already loaded this collection, we will just bail out since we do
        // not want to load it again. Once items are loaded a first time they will
        // stay kept in memory within this class and not loaded from disk again.
        if (isset($this->items[$collection])) {
            return;
        }

        $items = $this->loader->load($env, $group, $namespace);

        //load items from DB
        $items = array_replace_recursive($items, $this->dbProvider->load($collection, $env));

        // If we've already loaded this collection, we will just bail out since we do
        // not want to load it again. Once items are loaded a first time they will
        // stay kept in memory within this class and not loaded from disk again.
        if (isset($this->afterLoad[$namespace])) {
            $items = $this->callAfterLoad($namespace, $group, $items);
        }

        $this->items[$collection] = $items;
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
        $this->updatePackadgesList();

        // Default to the current environment.
        if (is_null($environment)) {
            $environment = $this->environment;
        }

        list($namespace, $group, $item) = $this->parseKey($key);

        if (is_null($item)) {
            throw new SaveException('The key should contain a group');
        }

        $collection = $this->getCollection($group, $namespace);

        $dbkey = $collection . '.' . $item;

        // save key => value into DB
        $this->dbProvider->store($dbkey, $value, $environment);

        //set value to current config
        $this->set($key, $value);


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
        $this->updatePackadgesList();

        // Default to the current environment.
        if (is_null($environment)) {
            $environment = $this->environment;
        }

        list($namespace, $group, $item) = $this->parseKey($key);

        if (is_null($item))
            throw new SaveException('The key should contain a group');

        $collection = $this->getCollection($group, $namespace);

        $dbkey = $collection . '.' . $item;

        // remove item from DB
        $this->dbProvider->forget($dbkey, $environment);

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


}
