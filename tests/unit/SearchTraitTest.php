<?php namespace tests\unit;

use Mockery as m;
use Illuminate\Database\Eloquent\Model;
use Nqxcode\LuceneSearch\SearchTrait;
use tests\TestCase;

/**
 * Class SearchTraitDummyClass
 * @package unit
 */
class SearchTraitDummyClass extends Model
{
    use SearchTrait;
}

class SearchTraitTest extends TestCase
{
    use SearchTrait;

    /**
     * @var SearchTraitDummyClass
     */
    private $dummy;

    public function setUp()
    {
        parent::setUp();
        $this->dummy = new SearchTraitDummyClass();
    }

    public function testUpdateIndex()
    {
        \Search::shouldReceive('update')->with($this->dummy)->once();
        $this->dummy->updateSearchIndex();
    }

    public function testDeleteIndex()
    {
        \Search::shouldReceive('delete')->with($this->dummy)->once();
        $this->dummy->deleteSearchIndex();
    }
}
