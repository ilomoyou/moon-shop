<?php


use App\enum\OrderEnum;
use App\Exceptions\BusinessException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ParametersException;
use App\Inputs\OrderSubmitInput;
use App\Models\Cart;
use App\Models\GoodsProduct;
use App\Models\GrouponRules;
use App\Models\Order;
use App\Models\OrderGoods;
use App\Models\User;
use App\Services\AddressService;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * 减库存
     * @throws BusinessException
     * @throws NotFoundException
     */
    public function testReduceStock()
    {
        /** @var GoodsProduct $product1 */
        $product1 = GoodsProduct::factory()->create(['price' => 11.3]);
        /** @var GoodsProduct $product2 */
        $product2 = GoodsProduct::factory()->groupon()->create(['price' => 20.56]);
        /** @var GoodsProduct $product3 */
        $product3 = GoodsProduct::factory()->create(['price' => 10.6]);
        CartService::getInstance()->addCartOrBuyNow($this->user->id, $product1->goods_id, $product1->id, 2);
        CartService::getInstance()->addCartOrBuyNow($this->user->id, $product2->goods_id, $product2->id, 5);
        CartService::getInstance()->addCartOrBuyNow($this->user->id, $product3->goods_id, $product3->id, 3);
        CartService::getInstance()->updateChecked($this->user->id, [$product1->id], false);

        $checkedGoodsList = CartService::getInstance()->getCartListByCheckedOrId($this->user->id);
        OrderService::getInstance()->reduceProductsStock($checkedGoodsList);

        $this->assertEquals($product2->number - 5, $product2->refresh()->number);
        $this->assertEquals($product3->number - 3, $product3->refresh()->number);
    }

    public function testSubmit()
    {
        $this->user = User::factory()->defaultAddress()->create();
        $address = AddressService::getInstance()->getUserAddress($this->user->id);

        /** @var GoodsProduct $product1 */
        $product1 = GoodsProduct::factory()->create(['price' => 11.3]);
        /** @var GoodsProduct $product2 */
        $product2 = GoodsProduct::factory()->groupon()->create(['price' => 20.56]);
        /** @var GoodsProduct $product3 */
        $product3 = GoodsProduct::factory()->create(['price' => 10.6]);
        CartService::getInstance()->addCartOrBuyNow($this->user->id, $product1->goods_id, $product1->id, 2);
        CartService::getInstance()->addCartOrBuyNow($this->user->id, $product2->goods_id, $product2->id, 5);
        CartService::getInstance()->addCartOrBuyNow($this->user->id, $product3->goods_id, $product3->id, 3);
        CartService::getInstance()->updateChecked($this->user->id, [$product1->id], false);

        $checkedGoodsList = CartService::getInstance()->getCartListByCheckedOrId($this->user->id);
        $discountTotalPrice = 0;
        $grouponRulesId = GrouponRules::whereGoodsId($product2->goods_id)->first()->id ?? null;
        $goodsTotalPrice = CartService::getInstance()->countGoodsTotalPriceSubtractDiscount($checkedGoodsList,
            $grouponRulesId, $discountTotalPrice);
        // (20.56 - 1)*5 + 10.6*3 = 129.6
        $this->assertEquals(129.6, $goodsTotalPrice);

        $input = OrderSubmitInput::new([
            'cartId' => 0,
            'couponId' => 0,
            'addressId' => $address->id,
            'grouponRulesId' => $grouponRulesId,
            'message' => '备注'
        ]);
        $order = OrderService::getInstance()->submitOrder($this->user->id, $input);
        $this->assertNotEmpty($order->id);
        $this->assertEquals($goodsTotalPrice, $order->goods_price);
        $this->assertEquals($goodsTotalPrice, $order->actual_price);
        $this->assertEquals($goodsTotalPrice, $order->order_price);
        $this->assertEquals($discountTotalPrice, $order->groupon_price);
        $this->assertEquals('备注', $order->message);

        $orderGoodsList = OrderGoods::whereOrderId($order->id)->get()->toArray();
        $this->assertCount(2, $orderGoodsList);

        $productIds = Cart::getCartList($this->user->id)->pluck('product_id')->toArray();
        $this->assertEquals([$product1->id], $productIds);
    }

    /**
     * 创建订单
     * @return Order
     * @throws BusinessException
     * @throws NotFoundException
     * @throws ParametersException
     */
    private function createOrder()
    {
        $this->user = User::factory()->defaultAddress()->create();
        $address = AddressService::getInstance()->getUserAddress($this->user->id);

        /** @var GoodsProduct $product1 */
        $product1 = GoodsProduct::factory()->create(['price' => 11.3]);
        /** @var GoodsProduct $product2 */
        $product2 = GoodsProduct::factory()->groupon()->create(['price' => 20.56]);
        /** @var GoodsProduct $product3 */
        $product3 = GoodsProduct::factory()->create(['price' => 10.6]);
        CartService::getInstance()->addCartOrBuyNow($this->user->id, $product1->goods_id, $product1->id, 2);
        CartService::getInstance()->addCartOrBuyNow($this->user->id, $product2->goods_id, $product2->id, 5);
        CartService::getInstance()->addCartOrBuyNow($this->user->id, $product3->goods_id, $product3->id, 3);
        CartService::getInstance()->updateChecked($this->user->id, [$product1->id], false);

        $grouponRulesId = GrouponRules::whereGoodsId($product2->goods_id)->first()->id ?? null;
        $input = OrderSubmitInput::new([
            'cartId' => 0,
            'couponId' => 0,
            'addressId' => $address->id,
            'grouponRulesId' => $grouponRulesId,
            'message' => '备注'
        ]);
        return OrderService::getInstance()->submitOrder($this->user->id, $input);
    }

    /**
     * 取消订单单元测试
     * @throws BusinessException
     * @throws NotFoundException
     * @throws ParametersException
     * @throws Throwable
     */
    public function testCancel()
    {
        $order = $this->createOrder();
        OrderService::getInstance()->userCancelOrder($this->user->id, $order->id);
        $this->assertEquals(OrderEnum::STATUS_CANCEL, $order->refresh()->order_status);
        $goodsList = OrderGoods::getOrderGoodsListByOrderId($order->id);
        $productIds = $goodsList->pluck('product_id')->toArray();
        $products = GoodsProduct::getGoodsProductListByIds($productIds);
        $this->assertEquals([100, 100], $products->pluck('number')->toArray());
    }
}
