# Laravel Config with DB-storage support
This package extends default laravel Config, so fallback capability is built in

## Installation
Require this package in your composer.json:

    "terbium/db-config": "dev-master"

And add the ServiceProvider to the providers array in app/config/app.php

    'Terbium\DbConfig\DbConfigServiceProvider',

Run migration to create settings table

    'php artisan migrate --package="terbium/db-config"'

Publish config using artisan CLI (if you want to cascade default config).

    'php artisan config:publish terbium/db-config'

You can register the facade in the `aliases` key of your `app/config/app.php` file.

~~~
'aliases' => array(
    'DbConfig' => 'Terbium\DbConfig\Facades\DbConfig'
)
~~~

Or replace default one
~~~
'aliases' => array(
    'Config' => 'Terbium\DbConfig\Facades\DbConfig'
)
~~~


##Config

    return array(
        'table' => 'settings'
    );


##Specific commands

###Store item into database table

    Config::store($key, $value, $environment = 'production') 
    // key should always be with group (`app.foo`, `app.bar` but not `foobar`)
    // this also sets the key immediately

###Remove item from the database

    Config::forget($key, $environment = 'production')

###Clear all current items from memory (they will be reloaded on next call)

    Config::clear()

###Truncate the table with settings

    Config::clearDb()

###Return query builder with list of settings from database

    Config::listDb($wildcard = null, $environment = 'production')

