# Laravel Config with DB-storage support
This package extends default laravel Config, so fallback capability is built in

### For Laravel 5, please use the [2.* branch](https://github.com/TerbiumLibs/dbConfig/tree/master)!

## Installation
Require this package in your composer.json:

~~~json
"terbium/db-config": "1.*"
~~~

And add the ServiceProvider to the providers array in app/config/app.php

~~~php
'Terbium\DbConfig\DbConfigServiceProvider',
~~~

Run migration to create settings table

~~~bash
php artisan migrate --package="terbium/db-config"
~~~

Publish config using artisan CLI (if you want to cascade default config).

~~~bash
php artisan config:publish terbium/db-config
~~~

You can register the facade in the `aliases` key of your `app/config/app.php` file.

~~~php
'aliases' => array(
    'DbConfig' => 'Terbium\DbConfig\Facades\DbConfig'
)
~~~

Or replace default one
~~~php
'aliases' => array(
    'Config' => 'Terbium\DbConfig\Facades\DbConfig'
)
~~~

##Config

~~~php
return array(
    'table' => 'settings'
);
~~~

##Specific commands

###Store item into database table

~~~php
Config::store($key, $value, $environment = 'production') 
// key should always be with group (`app.foo`, `app.bar` but not `foobar`)
// this also sets the key immediately
~~~

###Remove item from the database

~~~php
Config::forget($key, $environment = 'production')
~~~

###Clear all current items from memory (they will be reloaded on next call)

~~~php
Config::clear()
~~~

###Truncate the table with settings

~~~php
Config::clearDb()
~~~

###Return query builder with list of settings from database

~~~php
Config::listDb($wildcard = null, $environment = 'production')
~~~
