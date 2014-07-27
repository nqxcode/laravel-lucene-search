<?php namespace tests\functional;

use tests\TestCase;
use ZendSearch\Lucene\Search\Query\Boolean;
use ZendSearch\Lucene\Search\QueryParser;

class SearchTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        // remove search index
        rmdir_recursive($this->app['search.index_path']);

        $this->app->bind('search.models', function () {
            return
                [
                    'tests\lib\Product' => [
                        'fields' => [
                            'name',
                            'description',
                        ]
                    ]
                ];
        });

        $artisan = $this->app->make('artisan');

        // call migrations specific to our tests, e.g. to seed the db
        $artisan->call('migrate', ['--database' => 'testbench', '--path' => '../tests/migrations']);

        // call rebuild search index
        $artisan->call('search:rebuild-index');
    }

    public function testSearchQueryChain()
    {
        $query = \Search::find('small clock');
        $this->assertEquals(5, $query->count());

        $query = \Search::find('small clock')->where('description', 'not big analog', ['proximity' => 1]);
        $this->assertEquals(1, $query->count());

        $query = \Search::find('simple clock', '*', ['phrase' => true]);
        $this->assertEquals(0, $query->count());

        $query = \Search::find('simple clock', '*', ['phrase' => true, 'proximity' => 1]);
        $this->assertEquals(1, $query->count());

        $query = \Search::where('name', 'clock');
        $this->assertEquals(3, $query->count());

        $query = \Search::where('name', 'clock')->where('description', 'not very big');
        $this->assertEquals(1, $query->count());
    }

    public function testSearchRawQuery()
    {
        $query = \Search::rawQuery('description:big');
        $this->assertEquals(2, $query->count());

        $query = \Search::rawQuery(function () {
            return 'description:big';
        });
        $this->assertEquals(2, $query->count());

        $query = \Search::rawQuery(function () {
            $query = new Boolean;
            $query->addSubquery(QueryParser::parse('description:big OR name:monitor'));
            return $query;
        });
        $this->assertEquals(3, $query->count());
    }

    public function testSearchHighlightResults()
    {
        \Search::find('nearly all words should be highlighted')->get();
        $highlighted = \Search::highlightMatches('all words');
        $this->assertEquals('<span class="highlight">all</span> <span class="highlight">words</span>', $highlighted);

        \Search::find('почти все слова должны быть выделены')->get();
        $highlighted = \Search::highlightMatches('все слова');
        $this->assertEquals('<span class="highlight">все</span> <span class="highlight">слова</span>', $highlighted);
    }
}
