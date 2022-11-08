<?php


namespace App\Models;


class Comment extends BaseModel
{
    protected $table = 'comment';

    protected $casts = [
        'pic_urls' => 'array'
    ];
}
