<?php


namespace App\Models;


use App\Inputs\PageInput;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * App\Models\CouponUser
 *
 * @property int $id
 * @property int $user_id 用户ID
 * @property int $coupon_id 优惠券ID
 * @property int|null $status 使用状态, 如果是0则未使用；如果是1则已使用；如果是2则已过期；如果是3则已经下架；
 * @property string|null $used_time 使用时间
 * @property string|null $start_time 有效期开始时间
 * @property string|null $end_time 有效期截至时间
 * @property int|null $order_id 订单ID
 * @property \Illuminate\Support\Carbon|null $add_time 创建时间
 * @property \Illuminate\Support\Carbon|null $update_time 更新时间
 * @property bool|null $deleted 逻辑删除
 * @method static \Illuminate\Database\Eloquent\Builder|CouponUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CouponUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CouponUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|CouponUser whereAddTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CouponUser whereCouponId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CouponUser whereDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CouponUser whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CouponUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CouponUser whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CouponUser whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CouponUser whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CouponUser whereUpdateTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CouponUser whereUsedTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CouponUser whereUserId($value)
 * @mixin \Eloquent
 */
class CouponUser extends BaseModel
{
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
        return CouponUser::query()->where('coupon_id', $couponId)->count('id');
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
            ->count('id');
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
            ->orderBy($page->sort, $page->order)
            ->paginate($page->limit, $columns, 'page', $page->page);
    }
}
