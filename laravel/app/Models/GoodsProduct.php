<?php


namespace App\Models;


class GoodsProduct extends BaseModel
{
    protected $table = 'goods_product';

    protected $casts = [
        'specifications' => 'array',
        'price' => 'float'
    ];
}
