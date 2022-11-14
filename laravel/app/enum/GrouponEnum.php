<?php


namespace App\enum;


class GrouponEnum
{
    /**
     * 开团状态
     */
    const STATUS_NONE = 0; // 开团未支付
    const STATUS_ON = 1; // 开团中
    const STATUS_SUCCESS = 2; // 开头成功
    const STATUS_FAIL = 3; // 开团失败

    /**
     * 团购规则状态
     */
    const RULE_STATUS_ON = 0; // 正常上线
    const RULE_STATUS_EXPIRE = 1; // 到期自动下线
    const RULE_STATUS_ADMIN = 2; // 管理手动下线
}
