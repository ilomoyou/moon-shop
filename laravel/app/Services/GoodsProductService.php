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

    /**
     * 减库存
     * 处理并发: CAS乐观锁,先比较后更新
     *
     * @param $productId
     * @param $number
     * @return false|int
     * @throws BusinessException
     */
    public function reduceStock($productId, $number)
    {
        $row = GoodsProduct::query()
            ->where('id', $productId)
            ->where('number', '>=', $number)
            ->decrement('number', $number);
        if ($row <= 0) {
            throw new BusinessException(ResponseCode::GOODS_NO_STOCK);
        }
        return $row;
    }

    /**
     * 还原库存
     * @param $productId
     * @param $number
     * @return false|int
     * @throws BusinessException
     * @throws \Throwable
     */
    public function restoreStock($productId, $number)
    {
        $product = GoodsProduct::getGoodsProductById($productId);
        $product->number = $product->number + $number;
        $row = $product->cas();
        if ($row <= 0) {
            throw new BusinessException(ResponseCode::UPDATED_FAIL, '还原库存失败');
        }
        return $row;
    }
}
