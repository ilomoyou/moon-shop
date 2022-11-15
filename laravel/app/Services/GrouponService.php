<?php


namespace App\Services;


use App\enum\GrouponEnum;
use App\Exceptions\BusinessException;
use App\Exceptions\NotFoundException;
use App\Models\Groupon;
use App\Models\GrouponRules;
use App\util\ResponseCode;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\AbstractFont;
use Intervention\Image\Facades\Image;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GrouponService extends BaseService
{
    /**
     * 校验用户是否可以开启或参与某个团购活动
     * @param $userId
     * @param $ruleId
     * @param  null  $linkId 为空则表示发起团购，否则为参与团购
     * @throws BusinessException
     * @throws NotFoundException
     */
    public function checkGrouponValid($userId, $ruleId, $linkId = null)
    {
        if ($ruleId == null || $ruleId <= 0) {
            return;
        }
        $rule = GrouponRules::getGrouponRuleById($ruleId);
        if (is_null($rule)) {
            throw new NotFoundException('groupon is not found');
        }

        // 判断状态
        if ($rule->status == GrouponEnum::RULE_STATUS_DOWN_EXPIRE) {
            throw new BusinessException(ResponseCode::GROUPON_EXPIRED);
        }
        if ($rule->status == GrouponEnum::RULE_STATUS_DOWN_ADMIN) {
            throw new BusinessException(ResponseCode::GROUPON_OFFLINE);
        }

        if ($linkId == null || $linkId <= 0) {
            return;
        }
        // 团购人数已满
        if (Groupon::countGrouponJoin($linkId) >= ($rule->discount_member - 1)) {
            throw new BusinessException(ResponseCode::GROUPON_FULL);
        }
        // 重复参与团购
        if (Groupon::isOpenOrJoin($userId, $linkId)) {
            throw new BusinessException(ResponseCode::GROUPON_JOIN);
        }
    }

    /**
     * 生成开团或参团记录
     * @param $userId
     * @param $orderId
     * @param $ruleId
     * @param  null  $linkId
     * @return int|mixed|null
     */
    public function openOrJoinGroupon($userId, $orderId, $ruleId, $linkId = null)
    {
        if ($ruleId == null || $ruleId <= 0) {
            return null;
        }

        $groupon = new Groupon();
        $groupon->order_id = $orderId;
        $groupon->user_id = $userId;
        $groupon->status = GrouponEnum::STATUS_NONE;
        $groupon->rules_id = $userId;

        // 开启团购
        if ($linkId == null || $linkId <= 0) {
            $groupon->creator_user_id = $userId;
            $groupon->creator_user_time = Carbon::now()->toDateTimeString();
            $groupon->groupon_id = 0;
            $groupon->save();
            return $groupon->id;
        }

        // 参与团购
        $openGroup = Groupon::getGrouponById($linkId);
        $groupon->creator_user_id = $openGroup->creator_user_id;
        $groupon->groupon_id = $linkId;
        $groupon->share_url = $openGroup->share_url;
        $groupon->save();
        return $linkId;
    }

    /**
     * 支付成功，更新团购活动状态
     * @param $orderId
     * @throws BusinessException
     * @throws NotFoundException
     */
    public function payGrouponOrder($orderId)
    {
        $groupon = Groupon::getGrouponByOrderId($orderId);
        if (is_null($groupon)) {
            throw new NotFoundException('groupon is not fount');
        }

        $rule = GrouponRules::getGrouponRuleById($groupon->rules_id);
        if ($groupon->groupon_id == 0) {
            $groupon->share_url = $this->createGrouponShareImage($rule);  // 团购发起者生成团购活动分享图片
        }
        $groupon->status = GrouponEnum::STATUS_ON; // 开团中状态
        $isSuccess = $groupon->save();
        if (!$isSuccess) {
            throw new BusinessException(ResponseCode::UPDATED_FAIL);
        }

        // 开团人
        if ($groupon->groupon_id == 0) {
            return;
        }
        // 参团人数判断
        $joinCount = Groupon::countGrouponJoin($groupon->groupon_id);
        if ($joinCount < $rule->discount_member - 1) {
            return;
        }

        // 满足开团条件，更新开团状态为成功
        $row = Groupon::query()->where(function (Builder $builder) use ($groupon) {
            return $builder->where('groupon_id', $groupon->groupon_id)
                ->orWhere('id', $groupon->id);
        })->update(['status' => GrouponEnum::STATUS_SUCCESS]);
        if ($row <= 0) {
            throw new BusinessException(ResponseCode::UPDATED_FAIL);
        }
    }


    /**
     * 创建团购分享图片
     *
     * 1. 获取链接，创建二维码
     * 2. 合成图片
     * 3. 保存图片，返回图片地址
     * @param  GrouponRules  $rules
     * @return string
     */
    public function createGrouponShareImage(GrouponRules $rules)
    {
        $shareUrl = route('home.redirectShareUrl', ['type' => 'groupon', 'id' => $rules->goods_id]);
        $qrcode = QrCode::format('png')->margin(1)->size(290)->generate($shareUrl);

        $goodsImage = Image::make($rules->pic_url)->resize(660, 660);
        $image = Image::make(resource_path('images/back_groupon.png'))
            ->insert($qrcode, 'top-left', 460, 770)
            ->insert($goodsImage, 'top-left', 71, 69)
            ->text($rules->goods_name, 65, 867, function (AbstractFont $font) {
            $font->color(array(167, 136, 69));
            $font->size(28);
            $font->file(resource_path('ttf/msyh.ttf'));
        });

        $filePath = sprintf("groupon/%s/%s.png", Carbon::now()->toDateString(), Str::random());
        Storage::disk('public')->put($filePath, $image->encode());
        return Storage::url($filePath);
    }
}
