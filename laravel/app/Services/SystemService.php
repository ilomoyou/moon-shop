<?php


namespace App\Services;


use App\Models\System;

class SystemService extends BaseService
{
    // 运费相关配置
    const EXPRESS_FREIGHT_MIN = 'litemall_express_freight_min';
    const EXPRESS_FREIGHT_VALUE = 'litemall_express_freight_value';

    // 订单相关配置
    const ORDER_UNPAID = 'litemall_order_unpaid';

    /**
     * 获取未支付订单超时时间
     * @return int
     */
    public function getOrderUnpaidDelayMinutes()
    {
        return (int) $this->get(self::ORDER_UNPAID);
    }

    /**
     * 获取免运费最小金额
     * @return float
     */
    public function getFreightMin()
    {
        return (double) $this->get(self::EXPRESS_FREIGHT_MIN);
    }

    /**
     * 获取运费
     * @return float
     */
    public function getFreightValue()
    {
        return (double) $this->get(self::EXPRESS_FREIGHT_VALUE);
    }

    /**
     * 获取系统配置
     * @param $key
     * @return bool|mixed|null
     */
    public function get($key)
    {
        $system = System::query()->where('key_name', $key)->first(['key_value']);
        $value = $system['key_value'] ?? null;

        if ($value == 'false' || $value == 'FALSE') {
            return false;
        }
        if ($value == 'true' || $value == 'TRUE') {
            return true;
        }
        return $value;
    }
}
