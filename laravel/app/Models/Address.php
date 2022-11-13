<?php

namespace App\Models;

use App\Exceptions\NotFoundException;
use Illuminate\Database\Eloquent\Collection;

class Address extends BaseModel
{
    protected $casts = [
        'is_default' => 'boolean'
    ];

    /**
     * 通过用户id获取用户地址列表
     * @param  int  $userId
     * @return Address[]|Collection
     */
    public static function getAddressListByUserId(int $userId)
    {
        return Address::query()
            ->where('user_id', $userId)
            ->where('deleted', 0)->get();
    }

    /**
     * 获取用户地址详情
     * @param $userId
     * @param $addressId
     * @return Address|null
     */
    public static function getAddress($userId, $addressId)
    {
        return Address::query()
            ->where('user_id', $userId)
            ->where('id', $addressId)
            ->where('deleted', 0)->first();
    }

    /**
     * 删除用户地址
     * @param $userId
     * @param $addressId
     * @return bool|null
     * @throws NotFoundException
     */
    public static function remove($userId, $addressId)
    {
        $address = Address::getAddress($userId, $addressId);
        if (is_null($address)) {
            throw new NotFoundException('address is not found');
        }
        return $address->delete();
    }

    /**
     * 重置默认地址
     * @param $userId
     * @return int
     */
    public static function resetDefault($userId)
    {
        return Address::query()
            ->where('user_id', $userId)
            ->where('is_default', 1)
            ->update(['is_default' => 0]);
    }
}
