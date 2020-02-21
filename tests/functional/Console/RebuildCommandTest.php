<?php namespace tests\functional\Console;

use Config;
use File;
use Illuminate\Console\Application;
use Symfony\Component\Console\Output\BufferedOutput;
use tests\TestCase;

/**
 * Class RebuildCommandTest
 * @package functional
 */
class RebuildCommandTest extends TestCase
{
    /** @var \Illuminate\Foundation\Application|Application $artisan */
    private $artisan;

    public function setUp()
    {
        parent::setUp();

        $this->artisan = $this->app->make('artisan');

        // Call migrations specific to our tests, e.g. to seed the db.
        $this->artisan->call('migrate', ['--database' => 'testbench', '--path' => '../tests/migrations']);

        Config::set(
            'laravel-lucene-search::index.path',
            sys_get_temp_dir() . '/laravel-lucene-search/' . uniqid('index-', true)
        );
    }

    public function tearDown()
    {
        parent::tearDown();

        File::deleteDirectory(Config::get('laravel-lucene-search::index.path'));
    }

    /**
     * @dataProvider getOutputDataProvider
     * @param $expected
     * @param $config
     * @param $queue
     */
    public function testForceRebuildCommand($expected, $config, $queue)
    {
        Config::set('laravel-lucene-search::index.models', $config);
        Config::set('laravel-lucene-search::queue', $queue);

        $output = new BufferedOutput();
        $this->artisan->call('search:rebuild', ['--verbose' => true, '--force' => true], $output);

        $this->assertEquals($expected, $output->fetch());
    }

    /**
     * @dataProvider getOutputDataProvider
     * @param $expected
     * @param $config
     * @param $queue
     */
    public function testSoftRebuildCommand($expected, $config, $queue)
    {
        Config::set('laravel-lucene-search::index.models', $config);
        Config::set('laravel-lucene-search::queue', $queue);

        $output = new BufferedOutput();
        $this->artisan->call('search:rebuild', ['--verbose' => true], $output);

        $this->assertEquals($expected, $output->fetch());
    }

    public function getOutputDataProvider()
    {
        return [
            [
                'Creating index for model: "tests\models\Product"
    0 [>---------------------------]
    1 [->--------------------------]

Creating index for model: "tests\models\Tool"
 No available models found.

Operation is fully complete!
',
                [
                    'tests\models\Product' => [
                        'fields' => ['name', 'description'],
                    ],

                    'tests\models\Tool' => [
                        'fields' => ['name', 'description'],
                    ],
                ],

                false,
            ],
            [
                'Creating index for model: "tests\models\Tool"
 No available models found.

Operation is fully complete!
',
                [
                    'tests\models\Tool' => [
                        'fields' => ['name', 'description'],
                    ],
                ],

                false,
            ],
            [
                'Creating index for model: "tests\models\Product"
    0 [>---------------------------]
    1 [->--------------------------]

Creating index for model: "tests\models\Tool"
 No available models found.

Operation is fully complete!
',
                [
                    'tests\models\Product' => [
                        'fields' => ['name', 'description'],
                    ],

                    'tests\models\Tool' => [
                        'fields' => ['name', 'description'],
                    ],
                ],

                'search-sync-queue',
            ],
            [
                'Creating index for model: "tests\models\Tool"
 No available models found.

Operation is fully complete!
',
                [
                    'tests\models\Tool' => [
                        'fields' => ['name', 'description'],
                    ],
                ],

                'search-sync-queue',
            ],
        ];
    }
}
