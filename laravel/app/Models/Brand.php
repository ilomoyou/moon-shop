<?php

namespace App\Models;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class Brand extends BaseModel
{
    protected $table = 'brand';

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
     * @param  int  $page 当前分页
     * @param  int  $limit 每页条数
     * @param  string  $sort 排序字段
     * @param  string  $order 降序|升序
     * @param  string[]  $columns 查询字段
     * @return LengthAwarePaginator
     */
    public static function getBrandList(int $page, int $limit, string $sort, string $order, array $columns = ['*'])
    {
        $query = Brand::query()->where('deleted', 0);
        if (!empty($sort) && !empty($order)) {
            $query = $query->orderBy($sort, $order);
        }
        return $query->paginate($limit, $columns, 'page', $page);
    }
}
