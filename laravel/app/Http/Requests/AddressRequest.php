<?php

namespace App\Http\Requests;

use App\Exceptions\ParametersException;
use App\Rules\MobilePhone;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => 'integer',
            'name' => 'required|string|min:2|max:20',
            'tel' => ['required', new MobilePhone],
            'province' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'county' => 'required|string|max:100',
            'areaCode' => 'required|string|size:6',
            'addressDetail' => 'required|string|min:5',
            'isDefault' => 'required|bool',
        ];
    }

    /**
     * 获取已定义验证规则的错误消息
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => '姓名必须填写'
        ];
    }

    /**
     * 重写父类方法，处理验证失败结果返回
     * @param  Validator  $validator
     * @throws ParametersException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new ParametersException($validator->errors()->first());
    }

}
