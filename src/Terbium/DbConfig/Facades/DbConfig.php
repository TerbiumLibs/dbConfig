<?php namespace Terbium\DbConfig\Facades;

use Illuminate\Support\Facades\Facade;

class DbConfig extends Facade {

    /**
* Get the registered name of the component.
*
* @return string
*/
    protected static function getFacadeAccessor() { return 'db-config'; }

}

