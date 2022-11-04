<?php


namespace App\Http\Controllers\Wx;


use App\Exceptions\BusinessException;
use App\Exceptions\ParametersException;
use App\Http\Requests\AddressRequest;
use App\Models\Address;
use App\Services\AddressService;
use App\util\ResponseCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AddressController extends BaseController
{
    /**
     * 获取用户地址列表
     * @return JsonResponse
     */
    public function list()
    {
        $list = Address::getAddressListByUserId($this->user()->id);
        // 转驼峰
        $list = $list->map(function (Address $address) {
            $item = [];
            $address = $address->toArray();
            foreach ($address as $key => $value) {
                $key = lcfirst(Str::studly($key));
                $item[$key] = $value;
            }
            return $item;
        });
        return $this->success([
            'page' => 1,
            'pages' => 1,
            'limit' => $list->count(),
            'total' => $list->count(),
            'list' => $list->toArray()
        ]);
    }

    /**
     * 保存地址
     * @param  AddressRequest  $request
     * @return JsonResponse
     * @throws ParametersException
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
     * @throws ParametersException
     */
    public function detail(Request $request)
    {
        $id = $request->input('id');
        if (empty($id) && !is_numeric($id)) {
            return $this->fail(ResponseCode::PARAM_ILLEGAL);
        }
        $address = Address::getAddress($this->user()->id, $id);
        if (empty($address)) {
            throw new ParametersException('用户地址不存在');
        }
        return $this->success($address->toArray());
    }

    /**
     * 删除地址
     * @param  Request  $request
     * @return JsonResponse
     * @throws BusinessException
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
