<?php namespace tests\functional;

use tests\TestCase;
use ZendSearch\Lucene\Search\Query\Boolean;
use ZendSearch\Lucene\Search\QueryParser;
use Config;

use Search;

class SearchTestWithoutFilters extends BaseTestCase
{
    protected function configure()
    {
        // Test search without analyser filters and stopwords.

        Config::set('laravel-lucene-search::analyzer.filters', []);
        Config::set('laravel-lucene-search::analyzer.stopwords', []);

        parent::configure();
    }

    public function testSearchQueryChain()
    {
        $query = Search::find('small clock');
        $this->assertEquals(5, $query->count());

        $query = Search::find('small clock')->where('description', 'not big analog', ['proximity' => 1]);
        $this->assertEquals(1, $query->count());

        $query = Search::find('small clock')->where('description', 'big analog', ['proximity' => 1]);
        $this->assertEquals(2, $query->count());

        $query = Search::find('simple clock', '*', ['phrase' => true]);
        $this->assertEquals(0, $query->count());

        $query = Search::find('simple clock', '*', ['phrase' => true, 'proximity' => 1]);
        $this->assertEquals(1, $query->count());

        $query = Search::where('name', 'clock');
        $this->assertEquals(3, $query->count());

        $query = Search::where('name', 'clock')->where('description', 'not very big');
        $this->assertEquals(1, $query->count());

        $query = Search::where('name', 'not published product')->where('description', 'not published product');
        $this->assertEquals(0, $query->count());

    }

    public function testSearchQueryChainWithQueryFilter()
    {
        $q = new Boolean;
        $q->addSubquery(QueryParser::parse('name:("clock")'), true);

        $query = Search::rawQuery($q)
            ->addFilter(function (Boolean $query) {
                $query->addSubquery(QueryParser::parse('description:("not very big")'), true);
            });
        $this->assertEquals(1, $query->count());
    }

    public function testSearchRawQuery()
    {
        $query = Search::rawQuery('description:big');
        $this->assertEquals(2, $query->count());

        $query = Search::rawQuery(function () {
            return 'description:big';
        });
        $this->assertEquals(2, $query->count());

        $query = Search::rawQuery(function () {
            $query = new Boolean;
            $query->addSubquery(QueryParser::parse('description:big OR name:monitor'));
            return $query;
        });
        $this->assertEquals(3, $query->count());
    }

    public function testSearchHighlightResults()
    {
        Search::find('nearly all words must be highlighted')->get();
        $highlighted = Search::highlight('all words');
        $this->assertEquals('<span class="highlight">all</span> <span class="highlight">words</span>', $highlighted);

        Search::find('почти все слова должны быть выделены')->get();
        $highlighted = Search::highlight('все слова');
        $this->assertEquals('<span class="highlight">все</span> <span class="highlight">слова</span>', $highlighted);
    }
}
