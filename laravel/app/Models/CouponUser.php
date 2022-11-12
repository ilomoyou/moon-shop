<?php


namespace App\Models;


use App\Inputs\PageInput;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CouponUser extends BaseModel
{
    protected $table = 'coupon_user';

    protected $fillable = [
        'user_id',
        'coupon_id',
        'start_time',
        'end_time'
    ];

    /**
     * 统计该优惠券已被领取的总量
     * @param $couponId
     * @return int
     */
    public static function countCouponByCouponId($couponId)
    {
        return CouponUser::query()
            ->where('coupon_id', $couponId)
            ->where('deleted', 0)->count('id');
    }

    /**
     * 统计该用户已领取此优惠券的总量
     * @param $userId
     * @param $couponId
     * @return int
     */
    public static function countCouponByUserId($userId, $couponId)
    {
        return CouponUser::query()
            ->where('user_id', $userId)
            ->where('coupon_id', $couponId)
            ->where('deleted', 0)->count('id');
    }

    /**
     * 根据用户ID获取我的优惠券
     * @param $userId
     * @param $status
     * @param  PageInput  $page
     * @param  string[]  $columns
     * @return LengthAwarePaginator
     */
    public static function getCouponUserListByUserId($userId, $status, PageInput $page, array $columns = ['*'])
    {
        return CouponUser::query()
            ->where('user_id', $userId)
            ->where('status', $status)
            ->where('deleted', 0)
            ->orderBy($page->sort, $page->order)
            ->paginate($page->limit, $columns, 'page', $page->page);
    }
}
