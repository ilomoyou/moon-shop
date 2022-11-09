<?php


namespace App\Http\Controllers\Wx;


use App\Exceptions\NotFoundException;
use App\Exceptions\ParametersException;
use App\Http\Requests\AddressRequest;
use App\Models\Address;
use App\Services\AddressService;
use Illuminate\Http\JsonResponse;

class AddressController extends BaseController
{
    /**
     * 获取用户地址列表
     * @return JsonResponse
     */
    public function list()
    {
        $list = Address::getAddressListByUserId($this->user()->id);
        return $this->successPaginate($list);
    }

    /**
     * 保存地址
     * @param  AddressRequest  $request
     * @return JsonResponse
     * @throws NotFoundException
     */
    public function save(AddressRequest $request)
    {
        $validated = $request->validated();
        $address = AddressService::getInstance()->saveAddress($this->user()->id, $validated);
        return $this->success($address->id);
    }

    /**
     * 用户地址详情
     * @return JsonResponse
     * @throws NotFoundException
     * @throws ParametersException
     */
    public function detail()
    {
        $id = $this->verifyIdMust('id');
        $address = Address::getAddress($this->user()->id, $id);
        if (empty($address)) {
            throw new NotFoundException('address is not found');
        }
        return $this->success($address->toArray());
    }

    /**
     * 删除地址
     * @return JsonResponse
     * @throws NotFoundException
     * @throws ParametersException
     */
    public function delete()
    {
        $id = $this->verifyIdMust('id');
        Address::remove($this->user()->id, $id);
        return $this->success();
    }
}
