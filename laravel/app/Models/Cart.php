<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Cart
 *
 * @property int $id
 * @property int|null $user_id 用户表的用户ID
 * @property int|null $goods_id 商品表的商品ID
 * @property string|null $goods_sn 商品编号
 * @property string|null $goods_name 商品名称
 * @property int|null $product_id 商品货品表的货品ID
 * @property string|null $price 商品货品的价格
 * @property int|null $number 商品货品的数量
 * @property string|null $specifications 商品规格值列表，采用JSON数组格式
 * @property int|null $checked 购物车中商品是否选择状态
 * @property string|null $pic_url 商品图片或者商品货品图片
 * @property \Illuminate\Support\Carbon|null $add_time 创建时间
 * @property \Illuminate\Support\Carbon|null $update_time 更新时间
 * @property bool|null $deleted 逻辑删除
 * @method static \Illuminate\Database\Eloquent\Builder|Cart newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cart newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cart query()
 * @method static \Illuminate\Database\Eloquent\Builder|Cart whereAddTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cart whereChecked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cart whereDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cart whereGoodsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cart whereGoodsName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cart whereGoodsSn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cart whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cart whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cart wherePicUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cart wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cart whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cart whereSpecifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cart whereUpdateTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cart whereUserId($value)
 * @mixin \Eloquent
 */
class Cart extends BaseModel
{
    protected $casts = [
        'checked' => 'boolean',
        'specifications' => 'array'
    ];

    /**
     * 获取购物车货品
     * @param $userId
     * @param $goodsId
     * @param $productId
     * @return Cart|Model|object|null
     */
    public static function getCartProduct($userId, $goodsId, $productId)
    {
        return self::query()
            ->where('user_id', $userId)
            ->where('goods_id', $goodsId)
            ->where('product_id', $productId)
            ->first();
    }

    /**
     * 统计购物车货品数量
     * @param $userId
     * @return int|mixed
     */
    public static function countCartProduct($userId)
    {
        return Cart::query()->where('user_id', $userId)->sum('number');
    }
}
