<?php


namespace App\Http\Controllers\Wx;


use App\Exceptions\ParametersException;
use App\Inputs\OrderSubmitInput;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
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
        $order = DB::transaction(function () use ($input) {
            return OrderService::getInstance()->submitOrder($this->userId(), $input);
        });
        return $this->success([
            'orderId' => $order->id,
            'grouponLinkId' => $input->grouponLinkId ?? 0
        ]);
    }
}
