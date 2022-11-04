<?php


namespace App\Services;


use App\Exceptions\ParametersException;
use App\Models\Address;

class AddressService extends BaseService
{
    /**
     * 保存用户地址
     * @param $userId
     * @param  array  $data
     * @return Address|null
     * @throws ParametersException
     */
    public function saveAddress($userId, array $data) {
        if (isset($data['id']) && !is_null($data['id'])) {
            $address = Address::getAddress($userId, $data['id']);
            if (empty($address)) {
                throw new ParametersException('address not found');
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
}
