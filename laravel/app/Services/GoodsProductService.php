<?php


namespace App\Services;


use App\Exceptions\BusinessException;
use App\Exceptions\NotFoundException;
use App\Models\GoodsProduct;
use App\util\ResponseCode;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class GoodsProductService extends BaseService
{
    /**
     * 根据货品ID获取货品信息
     * @param $productId
     * @return GoodsProduct|Collection|Model
     * @throws NotFoundException
     */
    public function getGoodsProductById($productId)
    {
        $product = GoodsProduct::getGoodsProductById($productId);
        if (is_null($product)) {
            throw new NotFoundException('product is not found');
        }
        return $product;
    }

    /**
     * 校验货品库存
     * @param  GoodsProduct  $goodsProduct
     * @param $number
     * @throws BusinessException
     */
    public function checkGoodsProductStock(GoodsProduct $goodsProduct, $number) : void
    {
        if ($goodsProduct->number < $number) {
            throw new BusinessException(ResponseCode::GOODS_NO_STOCK);
        }
    }
}
