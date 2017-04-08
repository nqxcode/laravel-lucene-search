<?php namespace tests\functional;

use tests\models\Order;
use tests\models\Product;
use Nqxcode\LuceneSearch\Support\Collection;

class CollectionTest extends BaseTestCase
{
    /** @var Product[]|\Illuminate\Database\Eloquent\Collection */
    private $productExisted;
    /** @var Product */
    private $productNotFilled;
    /** @var Product */
    private $productNotExisted;
    /** @var Order[] */
    private $ordersExisted;
    /** @var  Order */
    private $orderNotFilled;
    /** @var  Order */
    private $orderNotExisted;

    public function setUp()
    {
        parent::setUp();

        Product::unguard();

        $this->productExisted[] = Product::create(['name' => 'p1']);
        $this->productExisted[] = Product::create(['name' => 'p2']);
        $this->productExisted[] = Product::create(['name' => 'p3']);

        $this->productNotFilled = new Product;
        $this->productNotFilled->id = $this->productExisted[2]->id;

        $this->productNotExisted = new Product;
        $this->productNotExisted->id = 999;

        $this->ordersExisted[] = Order::create(['name' => 'order1']);
        $this->ordersExisted[] = Order::create(['name' => 'order2']);

        $this->orderNotFilled = new Order;
        $this->orderNotFilled->id = $this->ordersExisted[1]->id;

        $this->orderNotExisted = new Order;
        $this->orderNotExisted->id = 999;
    }

    public function testReloadWithNotFilled()
    {
        /** @var Product[]|Collection $c */
        $c = Collection::make([$this->productExisted[0], $this->productExisted[1], $this->productNotFilled]);

        $c->reload();

        $this->assertCount(3, $c);
        $this->assertEquals($this->productNotFilled->getAttributes(), $c[2]->getAttributes());
        $this->assertEquals($c[2]->getOriginal(), $c[2]->getAttributes());
    }

    public function testReloadWithAllExisted()
    {
        /** @var Product[]|Collection $c */
        $c = Collection::make([$this->productExisted[0], $this->productExisted[1], $this->productNotFilled]);

        $c->reload();

        $this->assertCount(3, $c);
    }

    public function testReloadWithNotExisted()
    {
        /** @var Product[]|Collection $c */
        $c = Collection::make([$this->productExisted[0], $this->productExisted[1], $this->productNotExisted]);

        $c->reload();

        $this->assertCount(2, $c);
    }

    public function testReloadWithDifferentModels()
    {
        /** @var Product[]|Collection $c */
        $c = Collection::make([
            $this->productExisted[0],
            $this->orderNotFilled,
            $this->orderNotExisted,
            $this->productNotFilled
        ]);

        $c->reload();

        $this->assertCount(3, $c);
        $this->assertTrue($c[0] instanceof Product && $c[0]->id === $this->productExisted[0]->id);
        $this->assertTrue($c[1] instanceof Order && $c[1]->id === $this->orderNotFilled->id);
        $this->assertTrue($c[2] instanceof Product && $c[2]->id === $this->productNotFilled->id);
    }
}
