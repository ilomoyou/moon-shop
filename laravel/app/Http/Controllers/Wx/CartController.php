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
     * 购物车列表
     * @return JsonResponse
     */
    public function index()
    {
        $cartList = CartService::getInstance()->getValidCartList($this->userId());
        $goodsCount = 0;
        $goodsTotalAmount = 0;
        $checkedGoodsCount = 0;
        $checkedGoodsTotalAmount = 0;
        foreach ($cartList as $item) {
            $goodsCount += $item->number;
            $amount = bcmul($item->price, $item->number, 2);
            $goodsTotalAmount = bcadd($goodsTotalAmount, $amount, 2);
            if ($item->checked) {
                $checkedGoodsCount += $item->number;
                $checkedGoodsTotalAmount = bcadd($checkedGoodsTotalAmount, $amount, 2);
            }
        }

        return $this->success([
            'cartList' => $cartList->toArray(),
            'cartTotal' => [
                'goodsCount' => $goodsCount,
                'goodsAmount' => (double) $goodsTotalAmount,
                'checkedGoodsCount' => $checkedGoodsCount,
                'checkedGoodsAmount' => (double) $checkedGoodsTotalAmount
            ]
        ]);
    }

    /**
     * 加入购物车
     * @return JsonResponse 返回购物车货品总数
     * @throws BusinessException
     * @throws NotFoundException
     * @throws ParametersException
     */
    public function add()
    {
        $goodsId = $this->verifyId('goodsId');
        $productId = $this->verifyId('productId');
        $number = $this->verifyPositiveInteger('number');

        CartService::getInstance()->addCartOrBuyNow($this->userId(), $goodsId, $productId, $number);
        $count = Cart::countCartProduct($this->userId());
        return $this->success($count);
    }

    /**
     * 立即购买
     * @return JsonResponse 返回购物车货品项ID
     * @throws BusinessException
     * @throws NotFoundException
     * @throws ParametersException
     */
    public function fastAdd()
    {
        $goodsId = $this->verifyId('goodsId');
        $productId = $this->verifyId('productId');
        $number = $this->verifyPositiveInteger('number');

        $cart = CartService::getInstance()->addCartOrBuyNow($this->userId(), $goodsId, $productId, $number, true);
        return $this->success($cart->id);
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

    /**
     * 删除购物车的商品
     * @return JsonResponse
     * @throws ParametersException
     */
    public function delete()
    {
        $productIds = $this->verifyPositiveIntegerArray('productIds');
        Cart::removeByProductIds($this->userId(), $productIds);
        return $this->index();
    }

    /**
     * 选中或未选中商品
     * @return JsonResponse
     * @throws ParametersException
     */
    public function checked()
    {
        $isChecked = $this->verifyBoolean('isChecked');
        $productIds = $this->verifyPositiveIntegerArray('productIds');
        CartService::getInstance()->updateChecked($this->userId(), $productIds, $isChecked);
        return $this->index();
    }
}
