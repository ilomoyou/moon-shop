<?php


namespace App\Models;


use App\enum\CommentTypeEnum;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Goods extends BaseModel
{
    protected $casts = [
        'counter_price' => 'float',
        'retail_price' => 'float',
        'is_new' => 'boolean',
        'is_hot' => 'boolean',
        'gallery' => 'array',
        'is_on_sale' => 'boolean'
    ];

    /**
     * 关联商品参数
     * @return HasMany
     */
    public function goodsAttribute()
    {
        return $this->hasMany('App\Models\GoodsAttribute', 'goods_id', 'id');
    }

    /**
     * 关联商品规格
     * @return HasMany
     */
    public function goodsSpecification()
    {
        return $this->hasMany('App\Models\GoodsSpecification', 'goods_id', 'id');
    }

    /**
     * 关联商品货品
     * @return HasMany
     */
    public function goodsProduct()
    {
        return $this->hasMany('App\Models\GoodsProduct', 'goods_id', 'id');
    }


    /**
     * 关联商品评论
     * @return HasMany
     */
    public function goodsComment()
    {
        return $this->hasMany('App\Models\Comment', 'value_id', 'id');
    }

    /**
     * 获取商品参数
     * @return Collection
     */
    public function getGoodsAttribute()
    {
        return $this->goodsAttribute()->where('deleted', 0)->get();
    }

    /**
     * 获取商品规格
     * @return Collection|\Illuminate\Support\Collection
     */
    public function getGoodsSpecification()
    {
        $spec = $this->goodsSpecification()->where('deleted', 0)->get()->groupBy('specification');
        return $spec->map(function ($v, $k) {
            return ['name' => $k, 'valueList' => $v];
        })->values();
    }

    /**
     * 获取商品货品
     * @return Collection
     */
    public function getGoodsProduct()
    {
        return $this->goodsProduct()->where('deleted', 0)->get();
    }

    /**
     * 获取商品评论
     * @param  int  $page
     * @param  int  $limit
     * @param  string  $sort
     * @param  string  $order
     * @return LengthAwarePaginator
     */
    public function getGoodsComment(int $page = 1, int $limit = 2, string $sort = 'add_time', string $order = 'desc')
    {
        return $this->goodsComment()
            ->where('type', CommentTypeEnum::GOODS)
            ->where('deleted', 0)
            ->orderBy($sort, $order)
            ->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * 根据ID获取商品详情
     * @param  int  $id
     * @return Goods|Collection|Model|null
     */
    public static function getGoodsById(int $id)
    {
        return Goods::query()->where('deleted', 0)->find($id);
    }

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
