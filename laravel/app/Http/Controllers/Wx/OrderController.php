<?php


namespace App\Http\Controllers\Wx;


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
}
