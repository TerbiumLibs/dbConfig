<?php  namespace DbConfig;

use Mockery as m;

/**
 * Class DbConfigTestCase
 */
class DbConfigTestCase extends \PHPUnit_Framework_TestCase {

    protected $useDatabase = true;

    protected $artisan;

    /**
     * Creates the application.
     *
     * @return \Symfony\Component\HttpKernel\HttpKernelInterface
     */
    public function createApplication()
    {
        $unitTesting = true;

        $testEnvironment = 'testing';

        return require __DIR__.'/../../../../../bootstrap/start.php';
    }

    public function setUp()
    {
        parent::setUp();
        if($this->useDatabase)
        {
	        $this->artisan = $this->createApplication()->make('artisan');

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
