<?php

namespace App\Models;

use App\Exceptions\NotFoundException;
use Illuminate\Database\Eloquent\Collection;

/**
 * App\Models\Address
 *
 * @property int $id
 * @property string $name 收货人名称
 * @property int $user_id 用户表的用户ID
 * @property string $province 行政区域表的省ID
 * @property string $city 行政区域表的市ID
 * @property string $county 行政区域表的区县ID
 * @property string $address_detail 详细收货地址
 * @property string|null $area_code 地区编码
 * @property string|null $postal_code 邮政编码
 * @property string $tel 手机号码
 * @property bool $is_default 是否默认地址
 * @property \Illuminate\Support\Carbon|null $add_time 创建时间
 * @property \Illuminate\Support\Carbon|null $update_time 更新时间
 * @property int|null $deleted 逻辑删除
 * @method static \Illuminate\Database\Eloquent\Builder|Address newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Address newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Address query()
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereAddTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereAddressDetail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereAreaCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereCounty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereProvince($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereTel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereUpdateTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereUserId($value)
 * @mixin \Eloquent
 */
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
        return Address::query()->where('user_id', $userId)->get();
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
            ->first();
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
