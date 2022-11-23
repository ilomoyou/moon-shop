<?php


namespace App\util;


use App\enum\OrderEnum;

trait OrderStatusTrait
{
    /**
     * 是否可以取消订单
     * @return bool
     */
    public function canCancelHandle()
    {
        return $this->order_status == OrderEnum::STATUS_CREATE;
    }

    /**
     * 是否可以支付
     * @return bool
     */
    public function canPayHandle()
    {
        return $this->order_status == OrderEnum::STATUS_CREATE;
    }
}
