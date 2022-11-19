<?php


namespace App\Http\Controllers\Wx;


use App\Exceptions\BusinessException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ParametersException;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\CouponUser;
use App\Models\GrouponRules;
use App\Services\AddressService;
use App\Services\CartService;
use App\Services\CouponService;
use App\Services\GoodsProductService;
use App\Services\GoodsService;
use App\Services\SystemService;
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

    /**
     * 下单前信息确认
     * @return JsonResponse
     * @throws NotFoundException
     * @throws ParametersException
     */
    public function checkout()
    {
        $cartId = $this->verifyInteger('cartId', 0);
        $addressId = $this->verifyInteger('addressId', 0);
        $couponId = $this->verifyInteger('couponId', 0);
        $userCouponId = $this->verifyInteger('userCouponId', 0);
        $grouponRulesId = $this->verifyInteger('grouponRulesId', 0);

        // 获取地址
        if (empty($addressId)) {
            $address = AddressService::getInstance()->getDefaultAddress($this->userId());
            $addressId = $address->id ?? 0;
        } else {
            $address = AddressService::getInstance()->getAddressById($this->userId(), $addressId);
        }

        // 获取购物车的商品列表
        if (empty($cartId)) {
            $cartList = CartService::getInstance()->getCheckedCartList($this->userId());
        } else {
            $cart = CartService::getInstance()->getCartById($this->userId(), $cartId);
            $cartList = collect([$cart]);
        }

        // 价格计算
        $totalAmount = 0; // 订单总金额
        $grouponAmount = 0; // 团购优惠金额

        // 团购优惠
        $grouponRules = GrouponRules::getGrouponRuleById($grouponRulesId);
        foreach ($cartList as $cart) {
            if ($grouponRules && $grouponRules->goods_id == $cart->goods_id) {
                $price = bcsub($cart->price, $grouponRules->discount, 2);
                $discounts = bcmul($grouponRules->discount, $cart->number, 2);
                $grouponAmount = bcadd($grouponAmount, $discounts);
            } else {
                $price = $cart->price;
            }
            $amount = bcmul($price, $cart->number, 2);
            $totalAmount = bcadd($totalAmount, $amount, 2);
        }

        // 获取适合当前价格的优惠券列表, 并根据优惠折扣进行降序排序
        $couponUserList = CouponUser::getUsableCouponList($this->userId());
        $couponIds = $couponUserList->pluck('coupon_id')->unique()->toArray();
        $couponList = Coupon::getCouponListByIds($couponIds)->keyBy('id');
        $couponUserList->filter(function (CouponUser $couponUser) use ($couponList, $totalAmount) {
            /** @var Coupon $coupon */
            $coupon = $couponList->get($couponUser->coupon_id);
            return CouponService::getInstance()->checkCouponDiscountsValidity($coupon, $couponUser, $totalAmount);
        })->sortByDesc(function (CouponUser $couponUser) use ($couponList) {
            /** @var Coupon $coupon */
            $coupon = $couponList->get($couponUser->coupon_id);
            return $coupon->discount;
        });

        /**
         * 选择优惠券
         * couponId = -1|null 不使用优惠券
         * couponId = 0 自动选择优惠券
         * 其他 用户选择优惠券，验证是否可用
         */
        $couponDiscountAmount = 0; // 优惠券优惠金额
        if ($couponId == -1 || is_null($couponId)) {
            $couponId = -1;
            $userCouponId = -1;
        } elseif ($couponId == 0) {
            /** @var CouponUser $couponUser */
            $couponUser = $couponUserList->first();
            $couponId = $couponUser->coupon_id ?? 0;
            $userCouponId = $couponUser->id ?? 0;
            $couponDiscountAmount = Coupon::getCouponById($couponId)->discount ?? 0;
        } else {
            $coupon = Coupon::getCouponById($couponId);
            $couponUser = CouponUser::getCouponUserById($userCouponId);
            $usable = CouponService::getInstance()->checkCouponDiscountsValidity($coupon, $couponUser, $totalAmount);
            if ($usable) {
                $couponDiscountAmount = $coupon->discount ?? 0;
            }
        }

        // 运费
        $freightAmount = 0;
        $freightMin = SystemService::getInstance()->getFreightMin();
        if (bccomp($freightMin, $totalAmount)) {
            $freightAmount = SystemService::getInstance()->getFreightValue();
        }

        // 计算订单最终金额
        $orderFinalAmount = bcadd($totalAmount, $freightAmount, 2); // 加运费
        $orderFinalAmount = bcsub($orderFinalAmount, $couponDiscountAmount, 2); // 减优惠券优惠金额

        return $this->success([
            'cartId' => $cartId,
            'addressId' => $addressId,
            'grouponRulesId' => $grouponRulesId,
            'grouponPrice' => $grouponAmount,
            'goodsTotalPrice' => $totalAmount,
            'freightPrice' => $freightAmount,
            'couponId' => $couponId,
            'userCouponId' => $userCouponId,
            'availableCouponLength' => $couponUserList->count(),
            'couponPrice' => $couponDiscountAmount,
            'orderTotalPrice' => $orderFinalAmount,
            'actualPrice' => $orderFinalAmount,
            'checkedAddress' => $address,
            'checkedGoodsList' => $cartList
        ]);
    }
}
