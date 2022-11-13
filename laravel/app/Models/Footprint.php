<?php


namespace App\Models;


/**
 * App\Models\Footprint
 *
 * @property int $id
 * @property int $user_id 用户表的用户ID
 * @property int $goods_id 浏览商品ID
 * @property \Illuminate\Support\Carbon|null $add_time 创建时间
 * @property \Illuminate\Support\Carbon|null $update_time 更新时间
 * @property bool|null $deleted 逻辑删除
 * @method static \Illuminate\Database\Eloquent\Builder|Footprint newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Footprint newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Footprint query()
 * @method static \Illuminate\Database\Eloquent\Builder|Footprint whereAddTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Footprint whereDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Footprint whereGoodsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Footprint whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Footprint whereUpdateTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Footprint whereUserId($value)
 * @mixin \Eloquent
 */
class Footprint extends BaseModel
{
    protected $fillable = [
        'user_id',
        'goods_id'
    ];
}
