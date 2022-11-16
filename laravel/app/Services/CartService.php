<?php


namespace App\Services;


use App\Exceptions\BusinessException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ParametersException;
use App\Models\Cart;
use App\Models\Goods;
use App\Models\GoodsProduct;
use App\util\ResponseCode;
use Illuminate\Database\Eloquent\Model;

class CartService extends BaseService
{
    /**
     * 添加购物车
     * @param  Goods  $goods
     * @param  GoodsProduct  $product
     * @param $userId
     * @param $number
     * @return Cart
     * @throws BusinessException
     */
    public function newCart(Goods $goods, GoodsProduct $product, $userId, $number)
    {
        if ($number > $product->number) {
            throw new BusinessException(ResponseCode::GOODS_NO_STOCK);
        }

        $cart = new Cart();
        $cart->goods_sn = $goods->goods_sn;
        $cart->goods_name = $goods->name;
        $cart->pic_url = $product->url ?: $goods->pic_url;
        $cart->price = $product->price;
        $cart->specifications = $product->specifications;
        $cart->user_id = $userId;
        $cart->checked = true;
        $cart->number = $number;
        $cart->goods_id = $goods->id;
        $cart->product_id = $product->id;
        $cart->save();
        return $cart;
    }

    /**
     * 获取用户购物车信息
     * @param $userId
     * @param $cartId
     * @return Cart|Model|object
     * @throws NotFoundException
     */
    public function getCartById($userId, $cartId)
    {
        $cart = Cart::getCartById($userId, $cartId);
        if (is_null($cart)) {
            throw new NotFoundException('cart is not found');
        }
        return $cart;
    }

    /**
     * 校验购物车参数
     * @param  Cart  $cart
     * @param $goodsId
     * @param $productId
     * @throws ParametersException
     */
    public function checkCartParameter(Cart $cart, $goodsId, $productId) : void
    {
        if ($cart->goods_id != $goodsId || $cart->product_id != $productId) {
            throw new ParametersException('购物车参数校验异常');
        }
    }
}
