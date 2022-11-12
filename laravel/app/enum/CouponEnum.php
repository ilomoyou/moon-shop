<?php


namespace App\enum;


class CouponEnum
{
    /**
     * 优惠券类型
     */
    const TYPE_COMMON = 0; // 通用券
    const TYPE_REGISTER = 1; // 注册赠券
    const TYPE_CODE = 2; // 优惠券码兑换

    /**
     * 优惠券商品限制类型
     */
    const GOODS_TYPE_ALL = 0; // 全商品
    const GOODS_TYPE_CATEGORY = 1; // 类目限制
    const GOODS_TYPE_ARRAY = 2; // 商品限制

    /**
     * 优惠券状态
     */
    const STATUS_NORMAL = 0; // 正常可用
    const STATUS_EXPIRED = 1; // 过期
    const STATUS_OUT = 2; // 下架

    /**
     * 优惠券有效时间限制
     */
    const TIME_TYPE_DAYS = 0; // 基于领取时间的有效天数days
    const TIME_TYPE_TIME = 1; // start_time和end_time是优惠券有效期

    /**
     * 获取优惠券状态值
     * @return int[]
     */
    public static function getStatusValues()
    {
        return [
            self::STATUS_NORMAL,
            self::STATUS_EXPIRED,
            self::STATUS_OUT
        ];
    }
}
