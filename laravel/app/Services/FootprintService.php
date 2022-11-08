<?php


namespace App\Services;


use App\Models\Footprint;

class FootprintService extends BaseService
{
    /**
     * 保存用户足迹
     * @param $userId
     * @param $goodsId
     * @return bool
     */
    public function saveFootprint($userId, $goodsId)
    {
        $footprint = new Footprint();
        $footprint->fill([
            'user_id' => $userId,
            'goods_id' => $goodsId
        ]);
        return $footprint->save();
    }
}
