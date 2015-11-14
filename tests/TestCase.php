<?php namespace tests;

use \Mockery as m;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->resetModelEvents();
    }

    public function tearDown()
    {
        m::close();
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
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

    protected function getPackageProviders($app)
    {
        return array('Nqxcode\LuceneSearch\ServiceProvider');
    }

    protected function getPackageAliases($app)
    {
        return array(
            'Search' => 'Nqxcode\LuceneSearch\Facade',
        );
    }

    /**
     * Reset model events, because they don't work in tests correctly
     */
    protected function resetModelEvents()
    {
        $dir = __DIR__ . '/models';
        $namespace = "tests\\models\\";

        // Define the models that have event listeners.
        foreach (scandir($dir) as $modelFile) {
            if (preg_match('/(.*?)\.php/', $modelFile, $matches)) {
                $className = $namespace . $matches[1];
                if (class_exists($className) && is_subclass_of($className, 'Illuminate\Database\Eloquent\Model')) {
                    // Flush any existing listeners.
                    call_user_func([$className, 'flushEventListeners']);
                    // Reregister them.
                    call_user_func([$className, 'boot']);
                }
            }
        }
    }
}
