<?php

namespace App\Models;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Brand
 *
 * @property int $id
 * @property string $name 品牌商名称
 * @property string $desc 品牌商简介
 * @property string $pic_url 品牌商页的品牌商图片
 * @property int|null $sort_order
 * @property float|null $floor_price 品牌商的商品低价，仅用于页面展示
 * @property \Illuminate\Support\Carbon|null $add_time 创建时间
 * @property \Illuminate\Support\Carbon|null $update_time 更新时间
 * @property int|null $deleted 逻辑删除
 * @method static \Illuminate\Database\Eloquent\Builder|Brand newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Brand newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Brand query()
 * @method static \Illuminate\Database\Eloquent\Builder|Brand whereAddTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Brand whereDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Brand whereDesc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Brand whereFloorPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Brand whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Brand whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Brand wherePicUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Brand whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Brand whereUpdateTime($value)
 * @mixin \Eloquent
 */
class Brand extends BaseModel
{
    protected $casts = [
        'floor_price' => 'float'
    ];

    /**
     * 根据ID获取品牌信息
     * @param  int  $id
     * @return Brand[]|Collection|Model|null
     */
    public static function getBrand(int $id)
    {
        return Brand::query()->find($id);
    }

    /**
     * 获取品牌列表
     * @param  int  $page  当前分页
     * @param  int  $limit  每页条数
     * @param  string  $sort  排序字段
     * @param  string  $order  降序|升序
     * @param  string[]  $columns  查询字段
     * @return LengthAwarePaginator
     */
    public static function getBrandList(int $page, int $limit, string $sort, string $order, array $columns = ['*'])
    {
        $query = Brand::query();
        if (!empty($sort) && !empty($order)) {
            $query = $query->orderBy($sort, $order);
        }
        return $query->paginate($limit, $columns, 'page', $page);
    }
}
