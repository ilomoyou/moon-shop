<?php


namespace App\Services;


use App\Exceptions\BusinessException;
use App\Models\Cart;
use App\Models\Goods;
use App\Models\GoodsProduct;
use App\util\ResponseCode;

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
}
