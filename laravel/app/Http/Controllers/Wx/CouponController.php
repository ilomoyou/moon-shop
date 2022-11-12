<?php


namespace App\Http\Controllers\Wx;


use App\enum\CouponEnum;
use App\Exceptions\BusinessException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ParametersException;
use App\Inputs\PageInput;
use App\Models\Coupon;
use App\Models\CouponUser;
use App\Services\CouponService;
use Illuminate\Http\JsonResponse;

class CouponController extends BaseController
{
    protected $except = ['list'];

    /**
     * 获取优惠券列表
     * @return JsonResponse
     * @throws ParametersException
     */
    public function list()
    {
        $page = PageInput::new();
        $columns = ['id', 'name', 'desc', 'tag', 'discount', 'min', 'days', 'start_time', 'end_time'];
        $couponList = Coupon::getCouponList($page, $columns);
        return $this->successPaginate($couponList);
    }

    /**
     * 获取我的优惠券列表
     * @return JsonResponse
     * @throws ParametersException
     */
    public function myList()
    {
        $status = $this->verifyEnum('status', CouponEnum::STATUS_NORMAL, CouponEnum::getStatusValues());
        $page = PageInput::new();

        $couponUserList = CouponUser::getCouponUserListByUserId($this->userId(), $status, $page);
        $couponUserCollect = collect($couponUserList->items());
        $couponIds = $couponUserCollect->pluck('coupon_id')->toArray();
        $couponList = Coupon::getCouponListByIds($couponIds)->keyBy('id');
        $myCouponList = $couponUserCollect->map(function (CouponUser $couponUser) use ($couponList) {
            $coupon = $couponList->get($couponUser->coupon_id);
            return [
                'id' => $couponUser->id,
                'cid' => $coupon->id,
                'name' => $coupon->name,
                'desc' => $coupon->desc,
                'tag' => $coupon->tag,
                'min' => $coupon->min,
                'discount' => $coupon->discount,
                'start_time' => $couponUser->start_time,
                'end_time' => $couponUser->end_timie,
                'available' => false
            ];
        });

        $data = $this->paginate($couponUserList, $myCouponList->toArray());
        return $this->success($data);
    }

    /**
     * 用户领取优惠券
     * @return JsonResponse
     * @throws NotFoundException
     * @throws ParametersException
     * @throws BusinessException
     */
    public function receive()
    {
        $couponId = $this->verifyId('couponId');
        $coupon = Coupon::getCouponById($couponId);
        if (is_null($coupon)) {
            throw new NotFoundException('coupon is not found');
        }

        CouponService::getInstance()->checkCoupon($coupon, $this->userId());
        CouponService::getInstance()->receiveCoupon($coupon, $this->userId());
        return $this->success();
    }
}
