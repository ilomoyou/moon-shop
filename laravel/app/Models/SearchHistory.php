<?php


namespace App\Models;


class SearchHistory extends BaseModel
{
    protected $fillable = [
        'user_id',
        'keyword',
        'from'
    ];
}
