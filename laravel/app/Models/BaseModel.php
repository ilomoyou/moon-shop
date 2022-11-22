<?php


namespace App\Models;


use App\util\BooleanSoftDeletes;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\JsonEncodingException;
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
     * 乐观锁更新 先比较后更新(CAS: compare and save)
     * @return bool|int
     * @throws \Throwable
     */
    public function cas()
    {
        // 判断需要更新的数据是否存在
        throw_if(!$this->exists, \Exception::class, 'model not exists when cas!');

        $dirty = $this->getDirty(); // 返回模型所有被修改过的字段
        if (empty($dirty)) {
            return 0;
        }

        // 如果需要更新update_time,则代入更新字段到查询构造器中去
        if ($this->usesTimestamps()) {
            $this->updateTimestamps();
            $dirty = $this->getDirty();
        }

        // 需要更新的字段必须在查询中存在
        $diff = array_diff(array_keys($dirty), array_keys($this->original));
        throw_if(!empty($diff), \Exception::class, sprintf("key %s not exists when cas!", implode(',', $diff)));

        // 注册模型事件casing
        if ($this->fireModelEvent('casing') === false) {
            return 0;
        }

        // 数据比较
        $query = $this->newModelQuery()->where($this->getKeyName(), $this->getKey());
        foreach ($dirty as $key => $value) {
            $query = $query->where($key, $this->getOriginal($key));
        }

        // 数据更新
        $row =  $query->update($dirty);
        if ($row > 0) {
            $this->syncChanges();
            $this->fireModelEvent('cased', false); // 注册模型事件cased
            $this->syncOriginal();
        }

        return $row;
    }

    /**
     * Register a saving model event with the dispatcher.
     *
     * @param  \Illuminate\Events\QueuedClosure|\Closure|string  $callback
     * @return void
     */
    public static function casing($callback)
    {
        static::registerModelEvent('casing', $callback);
    }

    /**
     * Register a saved model event with the dispatcher.
     *
     * @param  \Illuminate\Events\QueuedClosure|\Closure|string  $callback
     * @return void
     */
    public static function cased($callback)
    {
        static::registerModelEvent('cased', $callback);
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

    /**
     * 重写方法 处理 casts array数据转换 中文乱码问题
     * @param  string  $key
     * @param  mixed  $value
     * @return string
     */
    protected function castAttributeAsJson($key, $value)
    {
        $value = json_encode($value, JSON_UNESCAPED_UNICODE);

        if ($value === false) {
            throw JsonEncodingException::forAttribute(
                $this, $key, json_last_error_msg()
            );
        }

        return $value;
    }

}
