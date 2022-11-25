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

    /**
     * 是否可发货
     * @return bool
     */
    public function canShipHandle()
    {
        return $this->order_status == OrderEnum::STATUS_PAY;
    }

    /**
     * 是否可退款
     * @return bool
     */
    public function canRefundHandle()
    {
        return $this->order_status == OrderEnum::STATUS_PAY;
    }

    /**
     * 是否可同意退款
     * @return bool
     */
    public function canAgreeRefundHandle()
    {
        return $this->order_status == OrderEnum::STATUS_REFUND;
    }

    /**
     * 是否可确认收货
     * @return bool
     */
    public function canConfirmHandle()
    {
        return $this->order_status == OrderEnum::STATUS_SHIP;
    }

    /**
     * 是否可删除
     * @return bool
     */
    public function canDeleteHandle()
    {
        return in_array($this->order_status, [
            OrderEnum::STATUS_CANCEL,
            OrderEnum::STATUS_AUTO_CANCEL,
            OrderEnum::STATUS_ADMIN_CANCEL,
            OrderEnum::STATUS_REFUND_CONFIRM,
            OrderEnum::STATUS_CONFIRM,
            OrderEnum::STATUS_AUTO_CONFIRM
        ]);
    }
}
