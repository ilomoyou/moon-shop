<?php


namespace App\Services;


use App\Models\Comment;
use App\Models\Goods;
use App\Models\User;
use Illuminate\Support\Arr;

class CommentService extends BaseService
{
    /**
     * 获取商品相关评价信息
     * @param $goodsId
     * @param  int  $page
     * @param  int  $limit
     * @return array
     */
    public function getCommentWithUserInfo($goodsId, int $page = 1, int $limit = 2)
    {
        $comments = Goods::getGoodsById($goodsId)->getGoodsComment($page, $limit);
        $userIds = Arr::pluck($comments->items(), 'user_id');
        $userIds = array_unique($userIds);
        $users = User::getUsersByIds($userIds)->keyBy('id');
        $data = collect($comments->items())->map(function (Comment $comment) use ($users) {
            $user = $users->get($comment->user_id);
            $comment = $comment->toArray();
            $comment['picList'] = $comment['picUrls'];
            $comment = Arr::only($comment, ['id', 'content', 'adminContent', 'picList', 'addTime']);
            $comment['nickname'] = $user->nickname ?? '';
            $comment['avatar'] = $user->avatar ?? '';
            return $comment;
        });
        return [
            'count' => $comments->total(),
            'data' => $data
        ];
    }
}
