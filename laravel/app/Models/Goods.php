<?php


namespace App\Models;


class Goods extends BaseModel
{
    protected $table = 'goods';

    protected $casts = [
        'counter_price' => 'float',
        'retail_price' => 'float',
        'is_new' => 'boolean',
        'is_hot' => 'boolean'
    ];

    /**
     * 获取在售商品总数
     * @return int
     */
    public static function countGoodsOnSale()
    {
        return Goods::query()
            ->where('is_on_sale', 1)
            ->where('deleted', 0)
            ->count('id');
    }
}
