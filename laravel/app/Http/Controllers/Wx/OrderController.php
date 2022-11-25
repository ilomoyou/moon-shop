<?php


namespace App\Http\Controllers\Wx;


use App\Exceptions\BusinessException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ParametersException;
use App\Inputs\OrderSubmitInput;
use App\Services\OrderService;
use App\util\ResponseCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OrderController extends BaseController
{
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
     * @throws ParametersException
     * @throws \Throwable
     */
    public function cancel()
    {
        $orderId = $this->verifyId('orderId');
        OrderService::getInstance()->userCancelOrder($this->userId(), $orderId);
        $this->success();
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
        OrderService::getInstance()->confirm($this->userId(), $orderId);
        return $this->success();
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
}
