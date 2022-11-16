<?php


namespace App\Http\Controllers\Wx;


use App\Exceptions\BusinessException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ParametersException;
use App\Models\Cart;
use App\Models\Goods;
use App\Models\GoodsProduct;
use App\Services\CartService;
use App\util\ResponseCode;
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

        $goods = Goods::getGoodsById($goodsId);
        if (is_null($goods)) {
            throw new NotFoundException('goods is not found');
        }
        if (!$goods->is_on_sale) {
            throw new BusinessException(ResponseCode::GOODS_UNSHELVE);
        }

        $product = GoodsProduct::getGoodsProductById($productId);
        if (is_null($product)) {
            throw new NotFoundException('goods_product is not found');
        }

        $cartProduct = Cart::getCartProduct($this->userId(), $goodsId, $productId);
        if (is_null($cartProduct)) {
            // add new cart product
            CartService::getInstance()->newCart($goods, $product, $this->userId(), $number);
        } else {
            // edit cart product number
            $num = $cartProduct->number + $number;
            if ($num > $product->number) {
                throw new BusinessException(ResponseCode::GOODS_NO_STOCK);
            }
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
}
