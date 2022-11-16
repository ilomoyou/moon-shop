<?php


namespace App\Services;


use App\Exceptions\BusinessException;
use App\Exceptions\NotFoundException;
use App\Inputs\GoodsListInput;
use App\Models\Category;
use App\Models\Goods;
use App\util\ResponseCode;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

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
     * 根据商品ID获取商品信息
     * @param $goodsId
     * @return Goods|Collection|Model
     * @throws NotFoundException
     */
    public function getGoodsById($goodsId)
    {
        $goods = Goods::getGoodsById($goodsId);
        if (is_null($goods)) {
            throw new NotFoundException('goods is not found');
        }
        return $goods;
    }

    /**
     * 校验商品是否下架
     * @param  Goods  $goods
     * @return void
     * @throws BusinessException
     */
    public function checkGoodsIsOnSale(Goods $goods) : void
    {
        if (!$goods->is_on_sale) {
            throw new BusinessException(ResponseCode::GOODS_UNSHELVE);
        }
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
