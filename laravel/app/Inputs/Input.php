<?php


namespace App\Inputs;


use App\Exceptions\ParametersException;
use Illuminate\Support\Facades\Validator;

class Input
{
    /**
     * 对外暴露提供的参数获取方法
     * @param  array|null  $data 外部传参
     * @return Input|static
     * @throws ParametersException
     */
    public static function new(array $data = null)
    {
        return (new static())->fill($data);
    }

    /**
     * 数据填充、校验
     * @param  array|null  $data 外部传参
     * @return $this
     * @throws ParametersException
     */
    protected function fill(array $data = null)
    {
        // 获取所有参数
        if (is_null($data)) {
            $data = request()->input();
        }

        // 参数校验
        $validator = Validator::make($data, $this->rules());
        if ($validator->fails()) {
            throw new ParametersException($validator->errors()->first());
        }

        // 通过反射完成成员变量的数据填充
        $map = get_object_vars($this);
        $keys = array_keys($map);
        collect($data)->map(function ($v, $k) use ($keys) {
            // 过滤掉除成员变量外其他参数
            if (in_array($k, $keys)) {
                $this->$k = $v;
            }
        });

        return $this;
    }

    /**
     * 校验规则
     * @return array
     */
    protected function rules()
    {
        return [];
    }
}
