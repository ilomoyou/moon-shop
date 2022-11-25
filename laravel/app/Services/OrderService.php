<?php


namespace App\Services;


use App\enum\OrderEnum;
use App\Exceptions\BusinessException;
use App\Exceptions\NotFoundException;
use App\Inputs\OrderSubmitInput;
use App\Jobs\OrderUnpaidTimeEndJob;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\CouponUser;
use App\Models\GoodsProduct;
use App\Models\Order;
use App\Models\OrderGoods;
use App\Models\User;
use App\Notifications\NewPaidOrderEmailNotify;
use App\Notifications\NewPaidOrderSmsNotify;
use App\util\ResponseCode;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Leonis\Notifications\EasySms\Channels\EasySmsChannel;
use Overtrue\EasySms\PhoneNumber;

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
        $order->message = $input->message ?: '';
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

        // 减库存
        $this->reduceProductsStock($checkedGoodsList);

        // 添加团购记录
        GrouponService::getInstance()->openOrJoinGroupon($userId, $order->id, $input->grouponRulesId,
            $input->grouponLinkId);

        // 设置超时任务
        dispatch(new OrderUnpaidTimeEndJob($userId, $order->id));

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
     * 还原库存
     * @param $orderId
     * @throws BusinessException
     * @throws \Throwable
     */
    public function restoreProductsStock($orderId)
    {
        $orderGoodsList = OrderGoods::getOrderGoodsListByOrderId($orderId);
        foreach ($orderGoodsList as $orderGoods) {
            GoodsProductService::getInstance()->restoreStock($orderGoods->product_id, $orderGoods->number);
        }
    }

    /**
     * 用户取消订单
     * @param $userId
     * @param $orderId
     * @throws \Throwable
     */
    public function userCancelOrder($userId, $orderId)
    {
        DB::transaction(function () use ($userId, $orderId) {
            $this->cancelOrder($userId, $orderId);
        });
    }

    /**
     * 管理员取消订单
     * @param $userId
     * @param $orderId
     * @throws \Throwable
     */
    public function adminCancelOrder($userId, $orderId)
    {
        DB::transaction(function () use ($userId, $orderId) {
            $this->cancelOrder($userId, $orderId, OrderEnum::CANCELLED_ROLE_ADMIN);
        });
    }

    /**
     * 系统自动取消订单
     * @param $userId
     * @param $orderId
     * @throws \Throwable
     */
    public function systemCancelOrder($userId, $orderId)
    {
        DB::transaction(function () use ($userId, $orderId) {
            $this->cancelOrder($userId, $orderId, OrderEnum::CANCELLED_ROLE_SYSTEM);
        });
    }

    /**
     * 取消订单
     * @param $userId
     * @param $orderId
     * @param  string  $role
     * @return void
     * @throws BusinessException
     * @throws NotFoundException
     * @throws \Throwable
     */
    private function cancelOrder($userId, $orderId, string $role = OrderEnum::CANCELLED_ROLE_USER): void
    {
        $order = Order::getOrderByUserIdAndId($userId, $orderId);
        if (is_null($order)) {
            throw new NotFoundException('order is not found');
        }

        if (!$order->canCancelHandle()) {
            throw new BusinessException(ResponseCode::ORDER_INVALID_OPERATION, '订单取消无效');
        }

        switch ($order) {
            case OrderEnum::CANCELLED_ROLE_SYSTEM:
                $order->order_status = OrderEnum::STATUS_AUTO_CANCEL;
                break;
            case OrderEnum::CANCELLED_ROLE_ADMIN:
                $order->order_status = OrderEnum::STATUS_ADMIN_CANCEL;
                break;
            default:
                $order->order_status = OrderEnum::STATUS_CANCEL;
        }

        // 更新订单状态
        if (!$order->cas()) {
            throw new BusinessException(ResponseCode::UPDATED_FAIL);
        }

        // 还原库存
        $this->restoreProductsStock($order->id);
    }

    /**
     * 订单支付成功
     * @param  Order  $order
     * @param $payId
     * @return Order
     * @throws BusinessException
     * @throws \Throwable
     */
    public function payOrder(Order $order, $payId)
    {
        if (!$order->canPayHandle()) {
            throw new BusinessException(ResponseCode::ORDER_PAY_FAIL, '订单支付无效');
        }

        $order->pay_id = $payId;
        $order->pay_time = now()->toDateTimeString();
        $order->order_status = OrderEnum::STATUS_PAY;
        if (!$order->cas()) {
            throw new BusinessException(ResponseCode::UPDATED_FAIL);
        }

        // 支付成功,更新团购信息
        GrouponService::getInstance()->payGrouponOrder($order->id);

        // 发送邮件通知
        Notification::route(
            'mail',
            env('NOTIFY_EMAIL_USERNAME')
        )->notify(new NewPaidOrderEmailNotify($order->id));

        // 发送短信通知
        $user = User::getUserById($order->user_id);
        Notification::route(
            EasySmsChannel::class,
            new PhoneNumber($user->mobile, 86)
        )->notify(new NewPaidOrderSmsNotify($user->username));

        return $order;
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

    /**
     * 发货
     * @param $userId
     * @param $orderId
     * @param $shipSn
     * @param $shipChannel
     * @return Order
     * @throws BusinessException
     * @throws NotFoundException
     * @throws \Throwable
     */
    public function ship($userId, $orderId, $shipSn, $shipChannel)
    {
        $order = Order::getOrderByUserIdAndId($userId, $orderId);
        if (empty($order)) {
            throw new NotFoundException('order is not found');
        }

        if (!$order->canShipHandle()) {
            throw new BusinessException(ResponseCode::ORDER_INVALID_OPERATION, '订单发货无效');
        }
        $order->order_status = OrderEnum::STATUS_SHIP;
        $order->ship_sn = $shipSn;
        $order->ship_channel = $shipChannel;
        $order->ship_time = now()->toDateTimeString();
        if (!$order->cas()) {
            throw new BusinessException(ResponseCode::UPDATED_FAIL);
        }
        return $order;
    }

    /**
     * 申请退款
     * @param $userId
     * @param $orderId
     * @return Order
     * @throws BusinessException
     * @throws NotFoundException
     * @throws \Throwable
     */
    public function refund($userId, $orderId)
    {
        $order = Order::getOrderByUserIdAndId($userId, $orderId);
        if (empty($order)) {
            throw new NotFoundException('order is not found');
        }

        if (!$order->canRefundHandle()) {
            throw new BusinessException(ResponseCode::ORDER_INVALID_OPERATION, '订单退款申请无效');
        }
        $order->order_status = OrderEnum::STATUS_REFUND;
        if (!$order->cas()) {
            throw new BusinessException(ResponseCode::UPDATED_FAIL);
        }
        return $order;
    }

    /**
     * 同意退款
     * @param  Order  $order
     * @param $refundType
     * @param $refundContent
     * @return Order
     * @throws BusinessException
     * @throws \Throwable
     */
    public function agreeRefund(Order $order, $refundType, $refundContent)
    {
        if (!$order->canAgreeRefundHandle()) {
            throw new BusinessException(ResponseCode::ORDER_INVALID_OPERATION, '订单同意退款无效');
        }

        $now = now()->toDateTimeString();
        $order->order_status = OrderEnum::STATUS_REFUND_CONFIRM;
        $order->end_time = $now;
        $order->refund_amount = $order->actual_price;
        $order->refund_type = $refundType;
        $order->refund_content = $refundContent;
        $order->refund_time = $now;
        if (!$order->cas()) {
            throw new BusinessException(ResponseCode::UPDATED_FAIL);
        }

        $this->restoreProductsStock($order->id);
        return $order;
    }

    /**
     * 确认收货
     * @param $userId
     * @param $orderId
     * @param  false  $isAuto
     * @return Order
     * @throws BusinessException
     * @throws NotFoundException
     * @throws \Throwable
     */
    public function confirm($userId, $orderId, bool $isAuto = false)
    {
        $order = Order::getOrderByUserIdAndId($userId, $orderId);
        if (empty($order)) {
            throw new NotFoundException('order is not found');
        }

        if (!$order->canConfirmHandle()) {
            throw new BusinessException(ResponseCode::ORDER_INVALID_OPERATION, '订单确认收货无效');
        }
        $order->comments = OrderGoods::countOrderGoodsByOrderId($order->id);
        $order->order_status = $isAuto ? OrderEnum::STATUS_AUTO_CONFIRM : OrderEnum::STATUS_CONFIRM;
        $order->confirm_time = now()->toDateTimeString();
        if (!$order->cas()) {
            throw new BusinessException(ResponseCode::UPDATED_FAIL);
        }
        return $order;
    }

    /**
     * 删除订单
     * @param $userId
     * @param $orderId
     * @throws BusinessException
     * @throws NotFoundException
     */
    public function delete($userId, $orderId)
    {
        $order = Order::getOrderByUserIdAndId($userId, $orderId);
        if (empty($order)) {
            throw new NotFoundException('order is not found');
        }

        if (!$order->canDeleteHandle()) {
            throw new BusinessException(ResponseCode::ORDER_INVALID_OPERATION, '订单删除无效');
        }
        $order->delete();
    }
}
