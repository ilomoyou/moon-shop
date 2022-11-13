<?php


namespace App\Services;


use App\Inputs\GoodsListInput;
use App\Models\Category;
use App\Models\Goods;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class GoodsService extends BaseService
{
    /**
     * 获取商品列表
     * @param  GoodsListInput  $input
     * @param  string[]  $columns
     * @return LengthAwarePaginator
     */
    public function getGoodsList(GoodsListInput $input, array $columns = ['*'])
    {
        $query = $this->getQueryByGoodsFilter($input);
        if (!empty($input->categoryId)) {
            $query->where('category_id', $input->categoryId);
        }
        return $query->orderBy($input->sort, $input->order)->paginate($input->limit, $columns, 'page', $input->page);
    }

    /**
     * 获取商品所属二级分类列表
     * @param  GoodsListInput  $input
     * @return Category[]|Collection
     */
    public function getL2CategoryList(GoodsListInput $input)
    {
        $query = $this->getQueryByGoodsFilter($input);
        $categoryIds = $query->select(['category_id'])->pluck('category_id')->unique()->toArray();
        return Category::getL2ListByIds($categoryIds);
    }

    /**
     * 商品相关搜索过滤
     * @param  GoodsListInput  $input
     * @return Builder
     */
    private function getQueryByGoodsFilter(GoodsListInput $input)
    {
        $query = Goods::query()->where('is_on_sale', 1);
        if (!empty($input->brandId)) {
            $query->where('brand_id', $input->brandId);
        }
        if (!is_null($input->isNew)) {
            $query->where('is_new', $input->isNew);
        }
        if (!is_null($input->isHot)) {
            $query->where('is_hot', $input->isHot);
        }

        if (!empty($input->keyword)) {
            $query = $query->where(function (Builder $builder) use ($input) {
                $builder->where('keywords', 'like', "%$input->keyword%")
                    ->orWhere('name', 'like', "%$input->keyword%");
            });
        }
        return $query;
    }
}
