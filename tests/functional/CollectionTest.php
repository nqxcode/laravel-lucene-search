<?php namespace tests\functional;

use tests\models\Product;
use Nqxcode\LuceneSearch\Support\Collection;

class CollectionTest extends BaseTestCase
{
    /** @var Product[]|\Illuminate\Database\Eloquent\Collection */
    private $existed;
    /** @var Product */
    private $notFilled;
    /** @var Product */
    private $notExisted;

    public function setUp()
    {
        parent::setUp();

        Product::unguard();

        $this->existed[] = Product::create(['name' => 'p1']);
        $this->existed[] = Product::create(['name' => 'p2']);
        $this->existed[] = Product::create(['name' => 'p3']);

        $this->notFilled = new Product;
        $this->notFilled->id = $this->existed[2]->id;

        $this->notExisted = new Product;
        $this->notExisted->id = 999;
    }

    public function testReload()
    {
        /** @var Product[]|Collection $c */
        $c = Collection::make([$this->existed[0], $this->existed[1], $this->notFilled]);

        $c->reload();

        $this->assertCount(3, $c);
        foreach ($c as $m) {
            $this->assertEquals($m->getOriginal(), $m->getAttributes());
        }

        $c = Collection::make([$this->existed[0], $this->existed[1], $this->notExisted]);

        $c->reload();

        $this->assertCount(2, $c);
        foreach ($c as $m) {
            $this->assertEquals($m->getOriginal(), $m->getAttributes());
        }
    }
}
