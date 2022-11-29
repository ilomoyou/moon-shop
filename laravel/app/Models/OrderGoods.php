<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * App\Models\OrderGoods
 *
 * @property int $id
 * @property int $order_id 订单表的订单ID
 * @property int $goods_id 商品表的商品ID
 * @property string $goods_name 商品名称
 * @property string $goods_sn 商品编号
 * @property int $product_id 商品货品表的货品ID
 * @property int $number 商品货品的购买数量
 * @property string $price 商品货品的售价
 * @property string $specifications 商品货品的规格列表
 * @property string $pic_url 商品货品图片或者商品图片
 * @property int|null $comment 订单商品评论，如果是-1，则超期不能评价；如果是0，则可以评价；如果其他值，则是comment表里面的评论ID。
 * @property \Illuminate\Support\Carbon|null $add_time 创建时间
 * @property \Illuminate\Support\Carbon|null $update_time 更新时间
 * @property bool|null $deleted 逻辑删除
 * @method static Builder|OrderGoods newModelQuery()
 * @method static Builder|OrderGoods newQuery()
 * @method static Builder|OrderGoods query()
 * @method static Builder|OrderGoods whereAddTime($value)
 * @method static Builder|OrderGoods whereComment($value)
 * @method static Builder|OrderGoods whereDeleted($value)
 * @method static Builder|OrderGoods whereGoodsId($value)
 * @method static Builder|OrderGoods whereGoodsName($value)
 * @method static Builder|OrderGoods whereGoodsSn($value)
 * @method static Builder|OrderGoods whereId($value)
 * @method static Builder|OrderGoods whereNumber($value)
 * @method static Builder|OrderGoods whereOrderId($value)
 * @method static Builder|OrderGoods wherePicUrl($value)
 * @method static Builder|OrderGoods wherePrice($value)
 * @method static Builder|OrderGoods whereProductId($value)
 * @method static Builder|OrderGoods whereSpecifications($value)
 * @method static Builder|OrderGoods whereUpdateTime($value)
 * @mixin \Eloquent
 */
class OrderGoods extends BaseModel
{
    protected $casts = [
        'specifications' => 'array'
    ];

    /**
     * 获取订单相关商品列表
     * @param $orderId
     * @return OrderGoods[]|Collection
     */
    public static function getOrderGoodsListByOrderId($orderId)
    {
        return self::query()->where('order_id', $orderId)->get();
    }

    /**
     * 获取订单商品列表
     * @param $orderIds
     * @return Builder[]|Collection|\Illuminate\Support\Collection
     */
    public static function getOrderGoodsListByOrderIds($orderIds)
    {
        if (empty($orderIds)) {
            return collect();
        }
        return self::query()->whereIn('order_id', $orderIds)->get();
    }

    /**
     * 统计订单商品数量
     * @param $orderId
     * @return int
     */
    public static function countOrderGoodsByOrderId($orderId)
    {
        return self::whereOrderId($orderId)->count(['id']);
    }
}
