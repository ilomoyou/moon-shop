<?php


namespace App\Models;


use App\enum\CollectTypeEnum;

class Collect extends BaseModel
{
    /**
     * 统计商品收藏量
     * @param $userId
     * @param $goodsId
     * @return int
     */
    public static function countByGoodsId($userId, $goodsId)
    {
        return Collect::query()
            ->where('user_id', $userId)
            ->where('value_id', $goodsId)
            ->where('type', CollectTypeEnum::GOODS)
            ->count('id');
    }
}
