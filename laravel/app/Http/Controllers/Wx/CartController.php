<?php


namespace App\Http\Controllers\Wx;


use App\Exceptions\BusinessException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ParametersException;
use App\Models\Cart;
use App\Services\CartService;
use App\Services\GoodsProductService;
use App\Services\GoodsService;
use Illuminate\Http\JsonResponse;

class CartController extends BaseController
{
    /**
     * 加入购物车
     * @return JsonResponse
     * @throws BusinessException
     * @throws NotFoundException
     * @throws ParametersException
     */
    public function add()
    {
        $goodsId = $this->verifyId('goodsId');
        $productId = $this->verifyId('productId');
        $number = $this->verifyPositiveInteger('number');

        $goods = GoodsService::getInstance()->getGoodsById($goodsId);
        GoodsService::getInstance()->checkGoodsIsOnSale($goods);

        $product = GoodsProductService::getInstance()->getGoodsProductById($productId);
        $cartProduct = Cart::getCartProduct($this->userId(), $goodsId, $productId);
        if (is_null($cartProduct)) {
            // add new cart product
            CartService::getInstance()->newCart($goods, $product, $this->userId(), $number);
        } else {
            // edit cart product number
            $num = $cartProduct->number + $number;
            GoodsProductService::getInstance()->checkGoodsProductStock($product, $num);
            $cartProduct->number = $num;
            $cartProduct->save();
        }

        $count = Cart::countCartProduct($this->userId());
        return $this->success($count);
    }

    /**
     * 统计购物车商品件数
     * @return JsonResponse
     */
    public function goodsCount()
    {
        $count = Cart::countCartProduct($this->userId());
        return $this->success($count);
    }

    /**
     * 更新购物车的商品数量
     * @return JsonResponse
     * @throws BusinessException
     * @throws NotFoundException
     * @throws ParametersException
     */
    public function update()
    {
        $id = $this->verifyId('id');
        $goodsId = $this->verifyId('goodsId');
        $productId = $this->verifyId('productId');
        $number = $this->verifyPositiveInteger('number');

        $cart = CartService::getInstance()->getCartById($this->userId(), $id);
        CartService::getInstance()->checkCartParameter($cart, $goodsId, $productId);

        $goods = GoodsService::getInstance()->getGoodsById($goodsId);
        GoodsService::getInstance()->checkGoodsIsOnSale($goods);

        $product = GoodsProductService::getInstance()->getGoodsProductById($productId);
        GoodsProductService::getInstance()->checkGoodsProductStock($product, $number);

        $cart->number = $number;
        $ret = $cart->save();
        return $this->failOrSuccess($ret);
    }
}
