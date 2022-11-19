<?php


namespace App\Services;


use App\Exceptions\BusinessException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ParametersException;
use App\Models\Cart;
use App\Models\Goods;
use App\Models\GoodsProduct;
use App\util\ResponseCode;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class CartService extends BaseService
{
    /**
     * 加入购物车: 如果已经存在购物车货品，则增加数量，否则添加新的购物车货品项
     * 立即购买: 如果购物车内已经存在购物车货品，前者的逻辑是数量添加，这里的逻辑是数量覆盖
     *
     * @param $userId
     * @param $goodsId
     * @param $productId
     * @param $number
     * @param  bool  $buyNowFlag 立即购买标识
     * @return Cart|Model|object
     * @throws BusinessException
     * @throws NotFoundException
     */
    public function addCartOrBuyNow($userId, $goodsId, $productId, $number, bool $buyNowFlag = false)
    {
        $goods = GoodsService::getInstance()->getGoodsById($goodsId);
        GoodsService::getInstance()->checkGoodsIsOnSale($goods);

        $product = GoodsProductService::getInstance()->getGoodsProductById($productId);
        $cartProduct = Cart::getCartProduct($userId, $goodsId, $productId);
        if (is_null($cartProduct)) {
            // add new cart product
            return $this->newCart($goods, $product, $userId, $number);
        } else {
            // edit cart product number
            if (!$buyNowFlag) {
                $number = $cartProduct->number + $number;
            }
            GoodsProductService::getInstance()->checkGoodsProductStock($product, $number);
            $cartProduct->number = $number;
            $cartProduct->save();
            return $cartProduct;
        }
    }

    /**
     * 添加新的购物车货品项
     * @param  Goods  $goods
     * @param  GoodsProduct  $product
     * @param $userId
     * @param $number
     * @return Cart
     * @throws BusinessException
     */
    private function newCart(Goods $goods, GoodsProduct $product, $userId, $number)
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
     * 更新购物车货品选中状态
     * @param $userId
     * @param $productIds
     * @param $isChecked
     * @return bool|int
     */
    public function updateChecked($userId, $productIds, $isChecked)
    {
        return Cart::query()
            ->where('user_id', $userId)
            ->whereIn('product_id', $productIds)
            ->update(['checked' => $isChecked]);
    }

    /**
     * 获取有效的购物车列表信息
     * @param $userId
     * @return Cart[]|Collection
     */
    public function getValidCartList($userId)
    {
        $cartList = Cart::getCartList($userId);
        $goodsIds = $cartList->pluck('goods_id')->unique()->toArray();
        $goodsList = Goods::getGoodsListByIds($goodsIds)->keyBy('id');
        $invalidCartIds = [];
        $validCartList = $cartList->filter(function (Cart $cart) use ($goodsList, &$invalidCartIds) {
            /** @var Goods $goods */
           $goods = $goodsList->get($cart->goods_id);
           $isValid = !empty($goods) && $goods->is_on_sale;
           if (!$isValid) {
               $invalidCartIds[] = $cart->id;
           }
           return $isValid;
        });
        // 删除下架|无效购物车货品项
        Cart::deleteCartList($invalidCartIds);
        return $validCartList;
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
     * 获取被选中的购物车商品列表
     * @param $userId
     * @return Cart[]|Collection
     * @throws NotFoundException
     */
    public function getCheckedCartList($userId)
    {
        $cart = Cart::getCheckedCartList($userId);
        if (is_null($cart)) {
            throw new NotFoundException('cart list is null');
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
