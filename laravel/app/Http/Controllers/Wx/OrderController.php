<?php


namespace App\Http\Controllers\Wx;


use App\enum\OrderEnum;
use App\Exceptions\BusinessException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ParametersException;
use App\Inputs\OrderSubmitInput;
use App\Inputs\PageInput;
use App\Models\Groupon;
use App\Models\Order;
use App\Models\OrderGoods;
use App\Services\OrderService;
use App\util\ResponseCode;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Yansongda\LaravelPay\Facades\Pay;
use Yansongda\Pay\Exceptions\GatewayException;
use Yansongda\Pay\Exceptions\InvalidArgumentException;
use Yansongda\Pay\Exceptions\InvalidConfigException;
use Yansongda\Pay\Exceptions\InvalidSignException;

class OrderController extends BaseController
{
    protected $except = ['wxNotify', 'alipayNotify', 'alipayReturn'];

    /**
     * 提交订单
     * @return JsonResponse
     * @throws ParametersException
     * @throws \Throwable
     */
    public function submit()
    {
        $input = OrderSubmitInput::new();

        // 使用原子锁处理重复请求问题
        $lockKey = sprintf('order_submit_%s_%s', $this->userId(), md5(serialize($input)));
        $lock = Cache::lock($lockKey, 5);
        if (!$lock->get()) {
            return $this->fail(ResponseCode::FAIL, '请勿重复请求!');
        }

        $order = DB::transaction(function () use ($input) {
            return OrderService::getInstance()->submitOrder($this->userId(), $input);
        });
        return $this->success([
            'orderId' => $order->id,
            'grouponLinkId' => $input->grouponLinkId ?? 0
        ]);
    }

    /**
     * 用户主动取消订单
     * @return JsonResponse
     * @throws ParametersException
     * @throws \Throwable
     */
    public function cancel()
    {
        $orderId = $this->verifyId('orderId');
        OrderService::getInstance()->userCancelOrder($this->userId(), $orderId);
        return $this->success();
    }

    /**
     * 申请退款
     * @return JsonResponse
     * @throws ParametersException
     * @throws BusinessException
     * @throws NotFoundException
     * @throws \Throwable
     */
    public function refund()
    {
        $orderId = $this->verifyId('orderId');
        OrderService::getInstance()->refund($this->userId(), $orderId);
        return $this->success();
    }

    /**
     * 确认收货
     * @return JsonResponse
     * @throws BusinessException
     * @throws NotFoundException
     * @throws ParametersException
     * @throws \Throwable
     */
    public function confirm()
    {
        $orderId = $this->verifyId('orderId');
        $order = Order::getOrderByUserIdAndId($this->userId(), $orderId);
        OrderService::getInstance()->confirm($order);
        return $this->success();
    }

    /**
     * 获取订单详情
     * @return JsonResponse
     * @throws NotFoundException
     * @throws ParametersException
     */
    public function detail()
    {
        $orderId = $this->verifyId('orderId');
        $order = OrderService::getInstance()->detail($this->userId(), $orderId);
        return $this->success($order);
    }

    /**
     * 订单列表
     * @return JsonResponse
     * @throws ParametersException
     */
    public function list()
    {
        $page = PageInput::new();
        $showType = $this->verifyEnum('showType', 0, array_keys(OrderEnum::SHOW_TYPE_STATUS_MAP));
        $status = OrderEnum::SHOW_TYPE_STATUS_MAP[$showType];

        $orderListWithPage = OrderService::getInstance()->getOrderListByStatus($this->userId(), $page, $status);
        $orderList = collect($orderListWithPage->items());
        $orderIds = $orderList->pluck('id')->toArray();
        if (empty($orderIds)) {
            $this->successPaginate($orderListWithPage);
        }

        $grouponOrderIds = Groupon::getGrouponOrderIdListInOrderIds($orderIds);
        $orderGoodsList = OrderGoods::getOrderGoodsListByOrderIds($orderIds)->groupBy('order_id');
        $list = $orderList->map(function (Order $order) use ($orderGoodsList, $grouponOrderIds) {
            /** @var Collection $goodsList */
            $goodsList = $orderGoodsList->get($order->id);
            $goodsListVO = $goodsList->map(function (OrderGoods $orderGoods) {
               return OrderService::getInstance()->coverOrderGoodsVo($orderGoods);
            });
            return OrderService::getInstance()->coverOrderVo($order, $grouponOrderIds, $goodsListVO);
        });

        return $this->successPaginate($orderListWithPage, $list->toArray());
    }

    /**
     * 删除订单
     * @return JsonResponse
     * @throws BusinessException
     * @throws NotFoundException
     * @throws ParametersException
     */
    public function delete()
    {
        $orderId = $this->verifyId('orderId');
        OrderService::getInstance()->delete($this->userId(), $orderId);
        return $this->success();
    }

    /**
     * 微信H5支付
     * @return RedirectResponse
     * @throws BusinessException
     * @throws NotFoundException
     * @throws ParametersException
     */
    public function h5pay()
    {
        $orderId = $this->verifyId('orderId');
        $order = OrderService::getInstance()->getWxPayOrder($this->userId(), $orderId);
        return Pay::wechat()->wap($order);
    }

    /**
     * 微信支付回调
     * @return Response
     * @throws \Throwable
     * @throws InvalidArgumentException
     * @throws InvalidSignException
     */
    public function wxNotify()
    {
        $data = Pay::wechat()->verify();
        $data = $data->toArray();
        Log::info('wxNotify', $data);
        DB::transaction(function () use ($data) {
            OrderService::getInstance()->wxNotify($data);
        });
        return Pay::wechat()->success();
    }

    /**
     * 支付宝支付
     * @return JsonResponse
     * @throws BusinessException
     * @throws NotFoundException
     * @throws ParametersException
     */
    public function h5alipay()
    {
        $orderId = $this->verifyId('orderId');
        $order = OrderService::getInstance()->getAlipayPayOrder($this->userId(), $orderId);
        return $this->success(Pay::alipay()->wap($order)->getContent());
    }

    /**
     * 支付宝支付回调
     * @return Response
     * @throws InvalidSignException
     * @throws \Throwable
     * @throws InvalidConfigException
     */
    public function alipayNotify()
    {
        $data = Pay::alipay()->verify()->toArray();
        Log::info('alipayNotify', $data);
        DB::transaction(function () use ($data) {
           OrderService::getInstance()->alipayNotify($data);
        });
        return Pay::alipay()->success();
    }

    /**
     * 支付宝同步回调
     * @return Application|\Illuminate\Http\RedirectResponse|Redirector
     * @throws InvalidConfigException
     * @throws InvalidSignException
     * @throws \Throwable
     * @throws GatewayException
     */
    public function alipayReturn()
    {
        $data = Pay::alipay()->find(request()->input())->toArray();
        Log::info('alipayReturn', $data);
        DB::transaction(function () use ($data) {
            OrderService::getInstance()->alipayNotify($data);
        });
        return redirect(env('H5_URL') . '/#/user/order/list/0');
    }
}
