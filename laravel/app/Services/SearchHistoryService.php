<?php


namespace App\Services;


use App\enum\SearchHistoryFromEnum;
use App\Models\SearchHistory;
use Illuminate\Database\Eloquent\Model;

class SearchHistoryService extends BaseService
{
    /**
     * 保存搜索记录
     * @param $userId
     * @param $keyword
     * @param $from
     * @return SearchHistory
     */
    public function save($userId, $keyword, $from)
    {
        $searchHistory = new SearchHistory();
        $searchHistory->fill([
            'user_id' => $userId,
            'keyword' => $keyword,
            'from' => $from
        ]);
        $searchHistory->save();
        return $searchHistory;
    }

    /**
     * 通过关键字获取历史搜索记录
     * @param $userId
     * @param $keyword
     * @return SearchHistory|Model|object|null
     */
    public function getHistoryByKeyword($userId, $keyword)
    {
        return SearchHistory::query()
            ->where('user_id', $userId)
            ->where('from', SearchHistoryFromEnum::WX)
            ->where('keyword', $keyword)

            ->first();
    }
}
