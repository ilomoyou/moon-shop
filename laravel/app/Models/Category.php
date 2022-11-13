<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Category
 *
 * @property int $id
 * @property string $name 类目名称
 * @property string $keywords 类目关键字，以JSON数组格式
 * @property string|null $desc 类目广告语介绍
 * @property int $pid 父类目ID
 * @property string|null $icon_url 类目图标
 * @property string|null $pic_url 类目图片
 * @property string|null $level
 * @property int|null $sort_order 排序
 * @property \Illuminate\Support\Carbon|null $add_time 创建时间
 * @property \Illuminate\Support\Carbon|null $update_time 更新时间
 * @property bool|null $deleted 逻辑删除
 * @method static \Illuminate\Database\Eloquent\Builder|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereAddTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereDesc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereIconUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category wherePicUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category wherePid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereUpdateTime($value)
 * @mixin \Eloquent
 */
class Category extends BaseModel
{
    /**
     * 获取一级类目列表
     * @return Category[]|Collection
     */
    public static function getL1List()
    {
        return Category::query()->where('level', 'L1')->get();
    }

    /**
     * 根据一级类目的ID获取二级类目列表
     * @param  int  $pid
     * @return Category[]|Collection
     */
    public static function getL2ListByPid(int $pid)
    {
        return Category::query()
            ->where('level', 'L2')
            ->where('pid', $pid)
            ->get();
    }

    /**
     * 根据ID获取一级类目
     * @param $id
     * @return Category|Model|object|null
     */
    public static function getL1ById($id)
    {
        return Category::query()
            ->where('level', 'L1')
            ->where('id', $id)
            ->first();
    }

    /**
     * 根据ID获取对应类目
     * @param $id
     * @return Category[]|Collection|Model|null
     */
    public static function getCategoryById($id)
    {
        return Category::query()->find($id);
    }

    /**
     * 根据多个ID获取二级类目列表
     * @param  array  $ids
     * @return Category[]|Collection
     */
    public static function getL2ListByIds(array $ids)
    {
        if (empty($ids)) {
            return Collection::empty();
        }
        return Category::query()
            ->where('level', 'L2')
            ->whereIn('id', $ids)->get();
    }
}
