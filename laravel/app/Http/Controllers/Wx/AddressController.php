<?php


namespace App\Http\Controllers\Wx;


use App\Exceptions\NotFoundException;
use App\Http\Requests\AddressRequest;
use App\Models\Address;
use App\Services\AddressService;
use App\util\ResponseCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
     * @param  Request  $request
     * @return JsonResponse
     * @throws NotFoundException
     */
    public function detail(Request $request)
    {
        $id = $request->input('id');
        if (empty($id) && !is_numeric($id)) {
            return $this->fail(ResponseCode::PARAM_ILLEGAL);
        }
        $address = Address::getAddress($this->user()->id, $id);
        if (empty($address)) {
            throw new NotFoundException('address is not found');
        }
        return $this->success($address->toArray());
    }

    /**
     * 删除地址
     * @param  Request  $request
     * @return JsonResponse
     * @throws NotFoundException
     */
    public function delete(Request $request)
    {
        $id = $request->input('id');
        if (empty($id) || !is_numeric($id)) {
            return $this->fail(ResponseCode::PARAM_ILLEGAL);
        }
        Address::remove($this->user()->id, $id);
        return $this->success();
    }
}
