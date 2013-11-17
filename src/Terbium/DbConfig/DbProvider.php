<?php namespace Terbium\DbConfig;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\NamespacedItemResolver;
use Terbium\DbConfig\Exceptions\SaveException;

class DbProvider extends NamespacedItemResolver implements Interfaces\DbProviderInterface
{

    /**
     * The database table.
     */
    protected $table;


    /**
     * Create a new database configuration loader.
     * @param $table database table
     */
    public function __construct($table)
    {

        $this->table = $table;
    }

    /**
     * Load the given configuration collection.
     *
     * @param  string $environment
     * @param  string $collection
     *
     * @return array
     */
    public function load($collection, $environment = 'main')
    {

        $items = array();


        // First we'll get the main configuration with empty environment. Once we have
        // that we can check for any environment specific settings, which will get
        // merged on top of the main arrays to make the environments cascade.
        $list = DB::table($this->table)->where('environment', 'main')->where('key', 'LIKE', $collection . '%')->lists(
            'value',
            'key'
        );

        // convert dotted list back to multidimensional array
        foreach ($list as $key => $value) {
            // remove namespace and group from key
            list(, , $key) = $this->parseKey($key);
            $value = json_decode($value);
            array_set($items, $key, $value);
        }


        // Finally we're ready to check for the environment specific configuration
        // which will be merged on top of the main arrays so that they get
        // precedence over them if we are currently in an environments setup.

        if ($environment != 'main') {
            $list = DB::table($this->table)->where('environment', $environment)->where(
                'key',
                'LIKE',
                    $collection . '%'
            )->lists('value', 'key');

            $eitems = array();

            foreach ($list as $key => $value) {
                // remove namespace and group from key
                list(, , $key) = $this->parseKey($key);
                $value = json_decode($value);
                array_set($eitems, $key, $value);
            }

            $items = array_replace_recursive($items, $eitems);
            unset($eitems);
        }

        return $items;
    }

    /**
     * Save item to the database or update the existing one
     *
     * @param string $key
     * @param mixed $value
     * @param string $environment
     *
     * @return void
     *
     * @throws Exceptions\SaveException
     */
    public function store($key, $value, $environment = 'main')
    {

        if (!is_array($value)) {
            $value = array($key => $value);
        } else {
            $value = array_dot($value);

            foreach ($value as $k => $v) {
                $value[$key . '.' . $k] = $v;
                unset($value[$k]);
            }
        }

        foreach ($value as $k => $v) {
            $this->_store($k, $v, $environment);
        }

    }


    /**
     * @param string $key
     * @param string $value
     * @param string $environment
     * @throws Exceptions\SaveException
     */
    private function _store($key, $value, $environment = 'main')
    {

        $provider = $this;
        $table = $this->table;

        DB::transaction(
            function () use (&$provider, $table, $key, $value, $environment) {

                // remove old keys
                // set 1.2.3.4
                // set 1.2.3.4.5
                // set 1 - will keep previous 2 records in database, and that's bad =)
                $provider->forget($key, $environment);


                // Try to insert a pair of key => value to DB.
                // In case of exception - update them.
                // This code should be replaced with insert_with_update method after its being implemented
                // See http://laravel.uservoice.com/forums/175973-laravel-4/suggestions/3535821-provide-support-for-bulk-insert-with-update-such-


                $value = json_encode($value);

                try {

                    DB::table($table)->insert(array('environment' => $environment, 'key' => $key, 'value' => $value));

                } catch (\Exception $e) {

                    try {

                        DB::table($table)->where('environment', $environment)->where('key', $key)->update(
                            array('value' => $value)
                        );

                    } catch (\Exception $e) {

                        throw new SaveException("Cannot save to database: " . $e->getMessage());

                    }

                }
            }
        );
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
    public function forget($key, $environment = 'main')
    {

        try {

            DB::table($this->table)->where('environment', $environment)->where('key', 'LIKE', $key . '%')->delete();

        } catch (\Exception $e) {

            throw new SaveException("Cannot remove item from database: " . $e->getMessage());

        }
    }

    /**
     * Clear the table with settings
     *
     * @return void
     *
     * @throws Exceptions\SaveException
     */
    public function clear()
    {

        try {

            DB::table($this->table)->truncate();

        } catch (\Exception $e) {

            throw new SaveException("Cannot clear database: " . $e->getMessage());

        }

    }


    /**
     * Return query builder with list of settings from database
     *
     * @param string $wildcard
     * @param string $environment
     *
     * @return Illuminate\Database\Query\Builder
     */
    public function listDb($wildcard = null, $environment = 'main')
    {

        $query = DB::table($this->table);
        if (!empty($wildcard)) {
            $query = $query->where('key', 'LIKE', $wildcard . '%');
        }
        if (!empty($environment)) {
            $query = $query->where('environment', $environment);
        }

        return $query;

    }


}
