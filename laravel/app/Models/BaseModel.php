<?php


namespace App\Models;


use App\util\BooleanSoftDeletes;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * App\Models\BaseModel
 *
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel query()
 * @mixin \Eloquent
 */
class BaseModel extends Model
{
    // 软删除
    use BooleanSoftDeletes;

    public const CREATED_AT = 'add_time';

    public const UPDATED_AT = 'update_time';

    // 类型转换公用字段
    public $defaultCasts = ['deleted' => 'boolean'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        parent::mergeCasts($this->defaultCasts);
    }

    /**
     * 重写父类改写模型与表名命名规则
     * 模型驼峰命名 -- 表名蛇形命名
     * @return string
     */
    public function getTable()
    {
        return $this->table ?? Str::snake(class_basename($this));
    }

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

    /**
     * 重写方法 统一返回时间格式
     * @param  DateTimeInterface  $date
     * @return string
     */
    public function serializeDate(DateTimeInterface $date)
    {
        return Carbon::instance($date)->toDateTimeString();
    }

}
