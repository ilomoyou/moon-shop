<?php


namespace App\Models;


use App\enum\GrouponEnum;
use App\Inputs\PageInput;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\GroupRules
 *
 * @method static \Illuminate\Database\Eloquent\Builder|GroupRules newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GroupRules newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GroupRules query()
 * @mixin \Eloquent
 * @property int $id
 * @property int $goods_id 商品表的商品ID
 * @property string $goods_name 商品名称
 * @property string|null $pic_url 商品图片或者商品货品图片
 * @property string $discount 优惠金额
 * @property int $discount_member 达到优惠条件的人数
 * @property string|null $expire_time 团购过期时间
 * @property int|null $status 团购规则状态，正常上线则0，到期自动下线则1，管理手动下线则2
 * @property \Illuminate\Support\Carbon $add_time 创建时间
 * @property \Illuminate\Support\Carbon|null $update_time 更新时间
 * @property bool|null $deleted 逻辑删除
 * @method static \Illuminate\Database\Eloquent\Builder|GrouponRules whereAddTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GrouponRules whereDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GrouponRules whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GrouponRules whereDiscountMember($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GrouponRules whereExpireTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GrouponRules whereGoodsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GrouponRules whereGoodsName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GrouponRules whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GrouponRules wherePicUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GrouponRules whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GrouponRules whereUpdateTime($value)
 * @method static \Database\Factories\GrouponRulesFactory factory(...$parameters)
 */
class GrouponRules extends BaseModel
{
    use HasFactory;

    /**
     * 获取团购规则列表
     * @param  PageInput  $page
     * @param  string[]  $columns
     * @return LengthAwarePaginator
     */
    public static function getGrouponRuleList(PageInput $page, array $columns = ['*'])
    {
        return GrouponRules::whereStatus(GrouponEnum::RULE_STATUS_ON)
            ->orderBy($page->sort, $page->order)
            ->paginate($page->limit, $columns, 'page', $page->page);
    }

    /**
     * 根据ID获取团购规则详情
     * @param $id
     * @param  string[]  $columns
     * @return GrouponRules|Collection|Model|null
     */
    public static function getGrouponRuleById($id, array $columns = ['*'])
    {
        return GrouponRules::query()->find($id, $columns);
    }
}
