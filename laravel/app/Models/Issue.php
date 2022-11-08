<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Collection;

class Issue extends BaseModel
{
    protected $table = 'issue';

    /**
     * 获取商品常见问题
     * @param  int  $page
     * @param  int  $limit
     * @return Issue[]|Collection
     */
    public static function getGoodsIssueList(int $page = 1, int $limit = 4)
    {
        return Issue::query()->forPage($page, $limit)->get();
    }
}
