<?php


namespace App\enum;


class OrderEnum
{
    /**
     * 订单状态
     */
    const STATUS_CREATE = 101;
    const STATUS_CANCEL = 102;
    const STATUS_AUTO_CANCEL = 103;
    const STATUS_ADMIN_CANCEL = 104;
    const STATUS_PAY = 201;
    const STATUS_REFUND = 202;
    const STATUS_REFUND_CONFIRM = 203;
    const STATUS_GROUPON_TIMEOUT = 204;
    const STATUS_SHIP = 301;
    const STATUS_CONFIRM = 401;
    const STATUS_AUTO_CONFIRM = 402;

    /**
     * 订单被取消处理的角色
     */
    const CANCELLED_ROLE_USER = 'user';
    const CANCELLED_ROLE_ADMIN = 'admin';
    const CANCELLED_ROLE_SYSTEM = 'system';

    /**
     * 订单状态描述
     */
    const STATUS_TEXT_MAP = [
        self::STATUS_CREATE => '未付款',
        self::STATUS_CANCEL => "已取消",
        self::STATUS_AUTO_CANCEL => "已取消(系统)",
        self::STATUS_ADMIN_CANCEL => "已取消(管理员)",
        self::STATUS_PAY => "已付款",
        self::STATUS_REFUND => "订单取消，退款中",
        self::STATUS_REFUND_CONFIRM => "已退款",
        self::STATUS_GROUPON_TIMEOUT => "已超时团购",
        self::STATUS_SHIP => "已发货",
        self::STATUS_CONFIRM => "已收货",
        self::STATUS_AUTO_CONFIRM => "已收货(系统)",
    ];

    /**
     * 订单列表展示类型
     */
    const SHOW_TYPE_ALL  = 0; // 全部订单
    const SHOW_TYPE_WAIT_PAY = 1; // 待付款订单
    const SHOW_TYPE_WAIT_DELIVERY = 2; // 待发货订单
    const SHOW_TYPE_WAIT_RECEIPT = 3; // 待收货订单
    const SHOW_TYPE_WAIT_COMMENT = 4; // 待评价订单

    const SHOW_TYPE_STATUS_MAP = [
        self::SHOW_TYPE_ALL => [],
        self::SHOW_TYPE_WAIT_PAY => [self::STATUS_CREATE],
        self::SHOW_TYPE_WAIT_DELIVERY => [self::STATUS_PAY],
        self::SHOW_TYPE_WAIT_RECEIPT => [self::STATUS_SHIP],
        self::SHOW_TYPE_WAIT_COMMENT => [self::STATUS_CONFIRM]
    ];
}
