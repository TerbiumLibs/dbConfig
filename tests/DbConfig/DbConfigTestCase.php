<?php  namespace DbConfig;

use Illuminate\Support\Facades\Artisan;
use Mockery as m;

/**
 * Class DbConfigTestCase
 */
class DbConfigTestCase extends \PHPUnit_Framework_TestCase {

    protected $useDatabase = true;

    protected $artisan;



    public function setUp()
    {
        parent::setUp();


        if($this->useDatabase)
        {

            $this->artisan = \App::make('artisan');

	        $this->setUpDb();
        }
    }

    public function teardown()
    {
        m::close();
	    if($this->useDatabase)
        {
            $this->teardownDb();
        }
    }

    public function setUpDb()
    {


        $this->artisan->call( 'migrate', array(
            '--bench'  => 'terbium/db-config',
            '--env' => 'testing',
	        ));
    }

    public function teardownDb()
    {
//	    $this->artisan->call( 'migrate:reset');
    }

}
