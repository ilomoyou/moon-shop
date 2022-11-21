<?php


namespace App\Services;


use App\enum\OrderEnum;
use App\Exceptions\BusinessException;
use App\Exceptions\NotFoundException;
use App\Inputs\OrderSubmitInput;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\CouponUser;
use App\Models\GoodsProduct;
use App\Models\Order;
use App\Models\OrderGoods;
use App\util\ResponseCode;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrderService extends BaseService
{
    /**
     * 提交订单
     * @param $userId
     * @param  OrderSubmitInput  $input
     * @return Order
     * @throws BusinessException
     * @throws NotFoundException
     */
    public function submitOrder($userId, OrderSubmitInput $input)
    {
        // 验证团购规则的有效性
        if (!empty($input->grouponRulesId)) {
            GrouponService::getInstance()->checkGrouponValid($userId, $input->grouponRulesId);
        }

        // 获取收货地址
        $address = AddressService::getInstance()->getUserAddress($userId, $input->addressId);

        // 获取购物车的商品列表
        $checkedGoodsList = CartService::getInstance()->getCartListByCheckedOrId($userId, $input->cartId);

        // 团购优惠价格合计
        $discountTotalPrice = 0;
        // 减去团购优惠后商品总价合计
        $checkedGoodsPrice = CartService::getInstance()->countGoodsTotalPriceSubtractDiscount($checkedGoodsList,
            $input->grouponRulesId, $discountTotalPrice);

        // 获取优惠券优惠金额
        $couponPrice = 0;
        if ($input->couponId > 0) {
            $coupon = Coupon::getCouponById($input->couponId);
            $couponUser = CouponUser::getCouponUserById($input->userCouponId);
            $usable = CouponService::getInstance()->checkCouponDiscountsValidity($coupon, $couponUser,
                $checkedGoodsPrice);
            if ($usable) {
                $couponPrice = $coupon->discount;
            }
        }

        // 运费
        $freightPrice = $this->getFreight($checkedGoodsPrice);

        // 计算订单金额
        $orderTotalPrice = $this->countOrderTotalPrice($checkedGoodsPrice, $freightPrice, $couponPrice);

        // 订单保存
        $order = new Order();
        $order->user_id = $userId;
        $order->order_sn = $this->generateOrderSn();
        $order->order_status = OrderEnum::STATUS_CREATE;
        $order->consignee = $address->name;
        $order->mobile = $address->tel;
        $order->address = $address->province.$address->city.$address->county." ".$address->address_detail;
        $order->message = $input->message;
        $order->integral_price = 0;
        $order->goods_price = $checkedGoodsPrice;
        $order->freight_price = $freightPrice;
        $order->coupon_price = $couponPrice;
        $order->groupon_price = $discountTotalPrice;
        $order->order_price = $orderTotalPrice;
        $order->actual_price = $orderTotalPrice;
        $order->save();

        // 保存订单商品记录
        $this->saveOrderGoods($checkedGoodsList, $order->id);

        // 清除购物车商品记录
        CartService::getInstance()->clearCartGoods($userId, $input->cartId);

        // TODO 减库存

        // 添加团购记录
        GrouponService::getInstance()->openOrJoinGroupon($userId, $order->id, $input->grouponRulesId,
            $input->grouponLinkId);

        // TODO 设置超时任务

        return $order;
    }

    /**
     * 保存订单商品记录
     * @param  Cart[]|Collection  $checkedGoodsList
     * @param $orderId
     */
    private function saveOrderGoods($checkedGoodsList, $orderId)
    {
        foreach ($checkedGoodsList as $cart) {
            $orderGoods = new OrderGoods();
            $orderGoods->order_id = $orderId;
            $orderGoods->goods_id = $cart->goods_id;
            $orderGoods->goods_sn = $cart->goods_sn;
            $orderGoods->goods_name = $cart->goods_name;
            $orderGoods->product_id = $cart->product_id;
            $orderGoods->pic_url = $cart->pic_url;
            $orderGoods->price = $cart->price;
            $orderGoods->number = $cart->number;
            $orderGoods->specifications = $cart->specifications;
            $orderGoods->save();
        }
    }

    /**
     * 减库存
     * @param  Cart[]|Collection  $goodsList
     * @throws BusinessException
     */
    public function reduceProductsStock($goodsList)
    {
        $productIds = $goodsList->pluck('product_id')->toArray();
        $productList = GoodsProduct::getGoodsProductListByIds($productIds)->keyBy('id');
        foreach ($goodsList as $cart) {
            $product = $productList->get($cart->product_id);
            GoodsProductService::getInstance()->checkGoodsProductStock($product, $cart->number);
            GoodsProductService::getInstance()->reduceStock($product->id, $cart->number);
        }
    }

    /**
     * 生成订单编号
     * @return mixed
     * @throws BusinessException
     * @throws \Exception
     */
    public function generateOrderSn()
    {
        // 校验订单编号的唯一性并进行重试
        return retry(5, function () {
            $orderSn = date('YmdHis').Str::random(6);
            if (!$this->checkOrderSnUnique($orderSn)) {
                return $orderSn;
            }
            Log::warning("订单编号获取失败, orderSn: $orderSn");
            throw new BusinessException(ResponseCode::FAIL, '订单编号获取失败');
        });
    }

    /**
     * 校验订单编号的唯一性
     * @param $orderSn
     * @return bool
     */
    public function checkOrderSnUnique($orderSn)
    {
        return Order::query()->where('order_sn', $orderSn)->exists();
    }

    /**
     * 获取运费
     * @param $goodsPrice
     * @return float|int
     */
    public function getFreight($goodsPrice)
    {
        $freightPrice = 0;
        $freightMin = SystemService::getInstance()->getFreightMin();
        if (bccomp($freightMin, $goodsPrice, 2) == 1) {
            $freightPrice = SystemService::getInstance()->getFreightValue();
        }
        return $freightPrice;
    }

    /**
     * 计算订单金额
     * @param $goodsPrice
     * @param $freightPrice
     * @param $discountPrice
     * @return mixed
     */
    public function countOrderTotalPrice($goodsPrice, $freightPrice, $discountPrice)
    {
        // 计算订单金额
        $orderTotalPrice = bcadd($goodsPrice, $freightPrice, 2); // 加运费
        $orderTotalPrice = bcsub($orderTotalPrice, $discountPrice, 2); // 减优惠券优惠金额
        return max(0, $orderTotalPrice);
    }
}
