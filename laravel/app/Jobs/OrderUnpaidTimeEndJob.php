<?php

namespace App\Jobs;

use App\Exceptions\BusinessException;
use App\Services\OrderService;
use App\Services\SystemService;
use App\util\ResponseCode;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OrderUnpaidTimeEndJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $userId;
    private $orderId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($userId, $orderId)
    {
        $this->userId = $userId;
        $this->orderId = $orderId;
        $delayTime = SystemService::getInstance()->getOrderUnpaidDelayMinutes();
        $this->delay(now()->addMinutes($delayTime)); // 设置延迟时间
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws BusinessException
     * @throws \Throwable
     */
    public function handle()
    {
        try {
            OrderService::getInstance()->systemCancelOrder($this->userId, $this->orderId);
        } catch (BusinessException $exception) {
            throw new BusinessException(ResponseCode::FAIL, '系统自动取消订单异常');
        }
    }
}
