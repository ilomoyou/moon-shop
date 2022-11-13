<?php


namespace App\Models;


use App\enum\CouponEnum;
use App\Inputs\PageInput;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class Coupon extends BaseModel
{
    protected $casts = [
        'discount' => 'float',
        'min' => 'float'
    ];

    /**
     * 根据ID获取优惠券
     * @param $id
     * @param  string[]  $columns
     * @return Coupon|Collection|Model|null
     */
    public static function getCouponById($id, array $columns = ['*'])
    {
        return Coupon::query()
            ->where('id', $id)
            ->where('deleted', 0)->find($id, $columns);
    }

    /**
     * 获取优惠券列表
     * @param  PageInput  $page
     * @param  array|string[]  $columns
     * @return LengthAwarePaginator
     */
    public static function getCouponList(PageInput $page, array $columns = ['*'])
    {
        return Coupon::query()
            ->where('type', CouponEnum::TYPE_COMMON)
            ->where('status', CouponEnum::STATUS_NORMAL)
            ->where('deleted', 0)
            ->orderBy($page->sort, $page->order)
            ->paginate($page->limit, $columns, 'page', $page->page);
    }

    /**
     * 根据多个ID获取优惠券列表
     * @param  array  $ids
     * @param  string[]  $columns
     * @return Coupon[]|Collection
     */
    public static function getCouponListByIds(array $ids, array $columns = ['*'])
    {
        return Coupon::query()
            ->whereIn('id', $ids)
            ->where('deleted', 0)->get($columns);
    }
}
