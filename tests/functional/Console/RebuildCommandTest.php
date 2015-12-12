<?php namespace tests\functional\Console;

use Illuminate\Console\Application;
use Symfony\Component\Console\Output\BufferedOutput;
use tests\TestCase;
use Config;

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

        Config::set('laravel-lucene-search::index.path', storage_path() . '/lucene-search/index_' . uniqid());
    }

    /**
     * @dataProvider getOutputDataProvider
     * @param $expected
     * @param $config
     */
    public function testRebuildCommand($expected, $config)
    {
        Config::set('laravel-lucene-search::index.models', $config);

        $output = new BufferedOutput();
        $this->artisan->call('search:rebuild', ['--verbose' => true], $output);

        $this->assertEquals($expected, $output->fetch());
    }

    public function getOutputDataProvider()
    {
        return [
            [
                'Creating index for model: "tests\models\Product"
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
                ]
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
                ]
            ],
        ];
    }
}
