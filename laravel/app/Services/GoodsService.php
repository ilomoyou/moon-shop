<?php


namespace App\Services;


use App\Models\Category;
use App\Models\Goods;
use Illuminate\Database\Eloquent\Builder;

class GoodsService extends BaseService
{
    public function getGoodsList($categoryId, $brandId, $isNew, $isHot, $keyword, $columns = ['*'], $sort = 'add_time', $order = 'desc', $page = 1, $limit = 10)
    {
        $query = $this->getQueryByGoodsFilter($brandId, $isNew, $isHot, $keyword);
        if (!empty($categoryId)) {
            $query->where('category_id', $categoryId);
        }
        return $query->orderBy($sort, $order)->paginate($limit, $columns, 'page', $page);
    }

    public function getL2CategoryList($brandId, $isNew, $isHot, $keyword)
    {
        $query = $this->getQueryByGoodsFilter($brandId, $isNew, $isHot, $keyword);
        $categoryIds = $query->select(['category_id'])->pluck('category_id')->unique()->toArray();
        return Category::getL2ListByIds($categoryIds);
    }

    private function getQueryByGoodsFilter($brandId, $isNew, $isHot, $keyword)
    {
        $query = Goods::query()->where('is_on_sale', 1)->where('deleted', 0);
        if (!empty($brandId)) {
            $query->where('brand_id', $brandId);
        }
        if (!is_null($isNew)) {
            $query->where('is_new', $isNew);
        }
        if (!is_null($isHot)) {
            $query->where('is_hot', $isHot);
        }

        if (!empty($keyword)) {
            $query = $query->where(function (Builder $builder) use ($keyword) {
                $builder->where('keywords', 'like', "%$keyword%")
                    ->orWhere('name', 'like', "%$keyword%");
            });
        }
        return $query;
    }
}
