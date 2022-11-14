<?php


namespace App\Http\Controllers\Wx;


use App\Exceptions\ParametersException;
use App\Inputs\PageInput;
use App\Models\Goods;
use App\Models\GrouponRules;
use Illuminate\Http\JsonResponse;

class GrouponController extends BaseController
{
    /**
     * 团购列表
     * @return JsonResponse
     * @throws ParametersException
     */
    public function list()
    {
        $page = PageInput::new();
        $grouponRuleList = GrouponRules::getGrouponRuleList($page);

        $rules = collect($grouponRuleList->items());
        $goodsIds = $rules->pluck('goods_id')->toArray();
        $goodsList = Goods::getGoodsListByIds($goodsIds)->keyBy('id');
        $voList = $rules->map(function (GrouponRules $grouponRules) use ($goodsList) {
            /** @var Goods $goods */
            $goods = $goodsList->get($grouponRules->goods_id);
            return [
                'id' => $goods->id,
                'name' => $goods->name,
                'brief' => $goods->brief,
                'picUrl' => $goods->pic_url,
                'counterPrice' => $goods->counter_price,
                'retailPrice' => $goods->retail_price,
                'grouponPrice' => bcsub($goods->retail_price, $grouponRules->discount, 2),
                'grouponDiscount' => $grouponRules->discount,
                'grouponMember' => $grouponRules->discount_member,
                'expireTime' => $grouponRules->expire_time
            ];
        });

        $list = $this->paginate($grouponRuleList, $voList->toArray());
        return $this->success($list);
    }
}
