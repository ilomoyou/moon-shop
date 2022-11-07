<?php


namespace App\Models;


class SearchHistory extends BaseModel
{
    protected $table = 'search_history';

    protected $fillable = [
        'user_id',
        'keyword',
        'from'
    ];
}
