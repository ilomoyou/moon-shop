<?php


namespace App\Models;


use App\enum\CollectTypeEnum;

/**
 * App\Models\Collect
 *
 * @property int $id
 * @property int $user_id 用户表的用户ID
 * @property int $value_id 如果type=0，则是商品ID；如果type=1，则是专题ID
 * @property int $type 收藏类型，如果type=0，则是商品ID；如果type=1，则是专题ID
 * @property \Illuminate\Support\Carbon|null $add_time 创建时间
 * @property \Illuminate\Support\Carbon|null $update_time 更新时间
 * @property bool|null $deleted 逻辑删除
 * @method static \Illuminate\Database\Eloquent\Builder|Collect newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Collect newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Collect query()
 * @method static \Illuminate\Database\Eloquent\Builder|Collect whereAddTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Collect whereDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Collect whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Collect whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Collect whereUpdateTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Collect whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Collect whereValueId($value)
 * @mixin \Eloquent
 */
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
