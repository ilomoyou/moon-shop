<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class Category extends BaseModel
{
    /**
     * 获取一级类目列表
     * @return Category[]|Collection
     */
    public static function getL1List()
    {
        return Category::query()
            ->where('level', 'L1')
            ->where('deleted', 0)
            ->get();
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
            ->where('deleted', 0)
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
            ->where('deleted', 0)
            ->first();
    }

    /**
     * 根据ID获取对应类目
     * @param $id
     * @return Category[]|Collection|Model|null
     */
    public static function getCategoryById($id)
    {
        return Category::query()->where('deleted', 0)->find($id);
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
            ->where('deleted', 0)
            ->where('level', 'L2')
            ->whereIn('id', $ids)->get();
    }
}
