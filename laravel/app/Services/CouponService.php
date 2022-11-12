<?php


namespace App\Services;


use App\enum\CouponEnum;
use App\Exceptions\BusinessException;
use App\Models\Coupon;
use App\Models\CouponUser;
use App\util\ResponseCode;
use Carbon\Carbon;

class CouponService extends BaseService
{
    /**
     * 检测优惠券的有效性
     * @param  Coupon  $coupon
     * @param $userId
     * @throws BusinessException
     */
    public function checkCoupon(Coupon $coupon, $userId)
    {
        // 判断优惠券总量是否剩余
        if ($coupon->total > 0) {
            $fetchedCount = CouponUser::countCouponByCouponId($coupon->id);
            if ($fetchedCount >= $coupon->total) {
                throw new BusinessException(ResponseCode::COUPON_EXCEED_LIMIT);
            }
        }

        // 判断用户领券是否超过限制数量
        if ($coupon->limit > 0) {
            $userFetchedCount = CouponUser::countCouponByUserId($userId, $coupon->id);
            if ($userFetchedCount >= $coupon->limit) {
                throw new BusinessException(ResponseCode::COUPON_EXCEED_LIMIT, '该优惠券已经领取过');
            }
        }

        // 判断优惠券类型
        if ($coupon->type != CouponEnum::TYPE_COMMON) {
            throw new BusinessException(ResponseCode::COUPON_RECEIVE_FAIL, '优惠券类型不支持');
        }

        // 判断优惠券状态
        if ($coupon->status == !CouponEnum::STATUS_NORMAL) {
            throw new BusinessException(ResponseCode::COUPON_RECEIVE_FAIL);
        }
    }

    /**
     * 领取优惠券
     * @param  Coupon  $coupon
     * @param $userId
     * @return bool
     */
    public function receiveCoupon(Coupon $coupon, $userId)
    {
        $couponUser = new CouponUser();
        if ($coupon->time_type == CouponEnum::TIME_TYPE_TIME) {
            $startTime = $coupon->start_time;
            $endTime = $coupon->end_time;
        } else {
            $startTime = Carbon::now();
            $endTime = $startTime->copy()->addDays($coupon->days);
        }
        $couponUser->fill([
            'coupon_id' => $coupon->id,
            'user_id' => $userId,
            'start_time' => $startTime,
            'end_time' => $endTime
        ]);
        return $couponUser->save();
    }
}
