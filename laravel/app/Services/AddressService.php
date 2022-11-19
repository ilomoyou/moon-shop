<?php


namespace App\Services;


use App\Exceptions\NotFoundException;
use App\Models\Address;
use Illuminate\Database\Eloquent\Model;

class AddressService extends BaseService
{
    /**
     * 保存用户地址
     * @param $userId
     * @param  array  $data
     * @return Address|null
     * @throws NotFoundException
     */
    public function saveAddress($userId, array $data) {
        if (isset($data['id']) && !is_null($data['id'])) {
            $address = Address::getAddress($userId, $data['id']);
            if (empty($address)) {
                throw new NotFoundException('address not found');
            }
        } else {
            $address = new Address();
            $address->user_id = $userId;
        }

        if ($data['isDefault']) {
            Address::resetDefault($userId);
        }
        $address->name = $data['name'];
        $address->tel = $data['tel'];
        $address->province = $data['province'];
        $address->city = $data['city'];
        $address->county = $data['county'];
        $address->area_code = $data['areaCode'];
        $address->address_detail = $data['addressDetail'];
        $address->is_default = $data['isDefault'];
        $address->save();
        return $address;
    }

    /**
     * 根据ID获取地址信息
     * @param $userId
     * @param $addressId
     * @return Address
     * @throws NotFoundException
     */
    public function getAddressById($userId, $addressId)
    {
        $address = Address::getAddress($userId, $addressId);
        if (is_null($address)) {
            throw new NotFoundException('address is not found');
        }
        return $address;
    }

    /**
     * 获取用户默认地址
     * @param $userId
     * @return Address|Model|object
     * @throws NotFoundException
     */
    public function getDefaultAddress($userId)
    {
        $address = Address::getDefaultAddress($userId);
        if (is_null($address)) {
            throw new NotFoundException('address is not found');
        }
        return $address;
    }
}
