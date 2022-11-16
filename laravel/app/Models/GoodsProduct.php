<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\GoodsProduct
 *
 * @property int $id
 * @property int $goods_id 商品表的商品ID
 * @property array $specifications 商品规格值列表，采用JSON数组格式
 * @property float $price 商品货品价格
 * @property int $number 商品货品数量
 * @property string|null $url 商品货品图片
 * @property \Illuminate\Support\Carbon|null $add_time 创建时间
 * @property \Illuminate\Support\Carbon|null $update_time 更新时间
 * @property int|null $deleted 逻辑删除
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsProduct query()
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsProduct whereAddTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsProduct whereDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsProduct whereGoodsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsProduct whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsProduct whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsProduct wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsProduct whereSpecifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsProduct whereUpdateTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsProduct whereUrl($value)
 * @mixin \Eloquent
 * @method static \Database\Factories\GoodsProductFactory factory(...$parameters)
 */
class GoodsProduct extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'specifications' => 'array',
        'price' => 'float'
    ];

    /**
     * @param  int  $id
     * @return GoodsProduct|Collection|Model|null
     */
    public static function getGoodsProductById(int $id)
    {
        return self::query()->find($id);
    }
}
