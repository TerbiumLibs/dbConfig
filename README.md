# Laravel Config with DB-storage support
This package expands the default laravel Config class, so fallback capability is built in

## Installation
Require this package in your composer.json:

    "terbium/db-config": "dev-master"

And add the ServiceProvider to the providers array in app/config/app.php

    'Terbium\DbConfig\DbConfigServiceProvider',

Run migration to create settings table
	'php artisan migrate --packadge="terbium/db-config"'

Publish config using artisan CLI (if you wont to cascade default config).
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
    Config::store($key, $value, $environment = 'production') // key should always be with group (`app.foo`, `app.bar` but not `foobar`)

###Remove item from the database
	Config::forget($key, $environment = 'production')

###Clear all current items (they will be reloaded on next usage)
	Config::clear()

###Clear the table with settings
	Config::clearDb()

###Return query builder with list of settings from database
	Config::listDb($wildcard = null, $environment = 'production')

