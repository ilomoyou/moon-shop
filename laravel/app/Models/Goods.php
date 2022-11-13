<?php


namespace App\Models;


use App\enum\CommentTypeEnum;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Goods
 *
 * @property int $id
 * @property string $goods_sn 商品编号
 * @property string $name 商品名称
 * @property int|null $category_id 商品所属类目ID
 * @property int|null $brand_id
 * @property array|null $gallery 商品宣传图片列表，采用JSON数组格式
 * @property string|null $keywords 商品关键字，采用逗号间隔
 * @property string|null $brief 商品简介
 * @property bool|null $is_on_sale 是否上架
 * @property int|null $sort_order
 * @property string|null $pic_url 商品页面商品图片
 * @property string|null $share_url 商品分享海报
 * @property bool|null $is_new 是否新品首发，如果设置则可以在新品首发页面展示
 * @property bool|null $is_hot 是否人气推荐，如果设置则可以在人气推荐页面展示
 * @property string|null $unit 商品单位，例如件、盒
 * @property float|null $counter_price 专柜价格
 * @property float|null $retail_price 零售价格
 * @property string|null $detail 商品详细介绍，是富文本格式
 * @property \Illuminate\Support\Carbon|null $add_time 创建时间
 * @property \Illuminate\Support\Carbon|null $update_time 更新时间
 * @property int|null $deleted 逻辑删除
 * @property-read Collection $goods
 * @property-read Collection|\App\Models\GoodsAttribute[] $goodsAttribute
 * @property-read int|null $goods_attribute_count
 * @property-read Collection|\App\Models\Comment[] $goodsComment
 * @property-read int|null $goods_comment_count
 * @property-read Collection|\App\Models\GoodsProduct[] $goodsProduct
 * @property-read int|null $goods_product_count
 * @property-read Collection|\App\Models\GoodsSpecification[] $goodsSpecification
 * @property-read int|null $goods_specification_count
 * @method static \Illuminate\Database\Eloquent\Builder|Goods newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Goods newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Goods query()
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereAddTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereBrandId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereBrief($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereCounterPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereDetail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereGallery($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereGoodsSn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereIsHot($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereIsNew($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereIsOnSale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods wherePicUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereRetailPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereShareUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereUpdateTime($value)
 * @mixin \Eloquent
 */
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
        return $this->goodsAttribute()->get();
    }

    /**
     * 获取商品规格
     * @return Collection|\Illuminate\Support\Collection
     */
    public function getGoodsSpecification()
    {
        $spec = $this->goodsSpecification()->get()->groupBy('specification');
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
        return $this->goodsProduct()->get();
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
        return Goods::query()->find($id);
    }

    /**
     * 获取在售商品总数
     * @return int
     */
    public static function countGoodsOnSale()
    {
        return Goods::query()
            ->where('is_on_sale', 1)
            ->count('id');
    }
}
