<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BaseModel extends Model
{
    public const CREATED_AT = 'add_time';

    public const UPDATED_AT = 'update_time';

    protected $casts = [
        'deleted' => 'boolean'
    ];

    /**
     * 重写父类toArray 下划线转驼峰
     * @return array
     */
    public function toArray()
    {
        $items = parent::toArray();
        $keys = array_keys($items);
        $humpKeys = array_map(function ($item) {
            return lcfirst(Str::studly($item));
        }, $keys);
        $values = array_values($items);
        return array_combine($humpKeys, $values);
    }

}
