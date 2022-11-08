<?php


namespace App\Models;


class Footprint extends BaseModel
{
    protected $table = 'footprint';

    protected $fillable = [
        'user_id',
        'goods_id'
    ];
}
