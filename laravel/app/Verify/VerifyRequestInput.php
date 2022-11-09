<?php


namespace App\Verify;


use App\Exceptions\ParametersException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

trait VerifyRequestInput
{
    /**
     * 验证ID
     * @param $key
     * @param  null  $default
     * @return mixed|null
     * @throws ParametersException
     */
    public function verifyId($key, $default = null)
    {
        return $this->verifyData($key, $default, 'integer|digits_between:1,20');
    }

    /**
     * 验证整数
     * @param $key
     * @param  null  $default
     * @return mixed|null
     * @throws ParametersException
     */
    public function verifyInteger($key, $default = null)
    {
        return $this->verifyData($key, $default, 'integer');
    }

    /**
     * 验证字符串
     * @param $key
     * @param  null  $default
     * @return mixed|null
     * @throws ParametersException
     */
    public function verifyString($key, $default = null)
    {
        return $this->verifyData($key, $default, 'string');
    }

    /**
     * 验证布尔值
     * @param $key
     * @param  null  $default
     * @return mixed|null
     * @throws ParametersException
     */
    public function verifyBoolean($key, $default = null)
    {
        return $this->verifyData($key, $default, 'boolean');
    }

    /**
     * 验证枚举
     * @param $key
     * @param  null  $default
     * @param  array  $enum
     * @return mixed|null
     * @throws ParametersException
     */
    public function verifyEnum($key, $default = null, array $enum = [])
    {
        return $this->verifyData($key, $default, Rule::in($enum));
    }

    /**
     * 验证排序 升序|降序
     * @param $key
     * @param  null  $default
     * @return mixed|null
     * @throws ParametersException
     */
    public function verifySortValues($key, $default = null)
    {
        return $this->verifyData($key, $default, Rule::in(['desc', 'asc']));
    }

    /**
     * 分页每页数量限制
     * @param $key
     * @param  null  $default
     * @return mixed|null
     * @throws ParametersException
     */
    public function verifyPerPageLimit($key, $default = null)
    {
        return $this->verifyData($key, $default, 'integer|max:100');
    }

    /**
     * 参数校验统一处理
     * @param $key
     * @param $default
     * @param $rule
     * @return mixed|null
     * @throws ParametersException
     */
    private function verifyData($key, $default, $rule)
    {
        $value = request()->input($key, $default);
        $validator = Validator::make([$key => $value], [$key => $rule]);
        if (is_null($default) && is_null($value)) {
            return null;
        }
        if ($validator->fails()) {
            throw new ParametersException($validator->errors()->first());
        }
        return $value;
    }
}
