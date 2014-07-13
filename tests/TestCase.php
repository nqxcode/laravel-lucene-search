<?php
use \Mockery as m;

class TestCase extends Orchestra\Testbench\TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    protected function tearDown()
    {
        m::close();
    }

    /**
     * Define environment setup.
     *
     * @param Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // reset base path to point to our package's src directory
        $app['path.base'] = __DIR__ . '/../src';

        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', array(
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ));
    }

    protected function getPackageProviders()
    {
        return array('Nqxcode\LaravelSearch\LaravelSearchServiceProvider');
    }

    protected function getPackageAliases()
    {
        return array(
            'Search' => 'Nqxcode\LaravelSearch\Facade',
        );
    }
}
