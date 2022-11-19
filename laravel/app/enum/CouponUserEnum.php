<?php


namespace App\enum;


class CouponUserEnum
{
    /**
     * 优惠券使用状态
     */
    const STATUS_USABLE = 0; // 未使用
    const STATUS_USED = 1; // 已使用
    const STATUS_EXPIRED = 2; // 已过期
    const STATUS_OUT = 3; // 已下架
}
