<?php


namespace App\Models;


class GoodsProduct extends BaseModel
{
    protected $casts = [
        'specifications' => 'array',
        'price' => 'float'
    ];
}
