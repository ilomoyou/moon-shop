<?php


use App\Exceptions\BusinessException;
use App\Exceptions\NotFoundException;
use App\Models\GoodsProduct;
use App\Models\GrouponRules;
use App\Services\CartService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CartTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @throws BusinessException
     * @throws NotFoundException
     */
    public function testTotalPriceSubtractDiscountSimple()
    {
        /** @var GoodsProduct $product1 */
        $product1 = GoodsProduct::factory()->create(['price' => 11.3]);
        /** @var GoodsProduct $product2 */
        $product2 = GoodsProduct::factory()->create(['price' => 20.56]);
        /** @var GoodsProduct $product3 */
        $product3 = GoodsProduct::factory()->create(['price' => 10.6]);
        CartService::getInstance()->addCartOrBuyNow($this->user->id, $product1->goods_id, $product1->id, 2);
        CartService::getInstance()->addCartOrBuyNow($this->user->id, $product2->goods_id, $product2->id, 1);
        CartService::getInstance()->addCartOrBuyNow($this->user->id, $product3->goods_id, $product3->id, 3);
        CartService::getInstance()->updateChecked($this->user->id, [$product3->id], false);

        $checkedGoodsList = CartService::getInstance()->getCartListByCheckedOrId($this->user->id);
        $discountTotalPrice = 0;
        $goodsTotalPrice = CartService::getInstance()->countGoodsTotalPriceSubtractDiscount($checkedGoodsList, null, $discountTotalPrice);
        $this->assertEquals(43.16, $goodsTotalPrice);
    }

    /**
     * @throws BusinessException
     * @throws NotFoundException
     */
    public function testTotalPriceSubtractDiscountGroupon()
    {
        /** @var GoodsProduct $product1 */
        $product1 = GoodsProduct::factory()->create(['price' => 11.3]);
        /** @var GoodsProduct $product2 */
        $product2 = GoodsProduct::factory()->create(['price' => 20.56]);
        /** @var GoodsProduct $product3 */
        $product3 = GoodsProduct::factory()->create(['price' => 10.6]);
        CartService::getInstance()->addCartOrBuyNow($this->user->id, $product1->goods_id, $product1->id, 2);
        CartService::getInstance()->addCartOrBuyNow($this->user->id, $product2->goods_id, $product2->id, 5);
        CartService::getInstance()->addCartOrBuyNow($this->user->id, $product3->goods_id, $product3->id, 3);
        CartService::getInstance()->updateChecked($this->user->id, [$product1->id], false);

        $checkedGoodsList = CartService::getInstance()->getCartListByCheckedOrId($this->user->id);
        $discountTotalPrice = 0;
        $grouponRulesId = GrouponRules::whereGoodsId($product2->goods_id)->first()->id ?? null;
        $goodsTotalPrice = CartService::getInstance()->countGoodsTotalPriceSubtractDiscount($checkedGoodsList, $grouponRulesId, $discountTotalPrice);
        // (20.56 - 1)*5 + 10.6*3 = 129.6
        $this->assertEquals(129.6, $goodsTotalPrice);
    }
}
