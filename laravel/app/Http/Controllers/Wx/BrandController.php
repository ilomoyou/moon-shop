<?php


namespace App\Http\Controllers\Wx;


use App\Exceptions\NotFoundException;
use App\Exceptions\ParametersException;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;

class BrandController extends BaseController
{
    protected $only = [];

    /**
     * 获取品牌列表
     * @return JsonResponse
     * @throws ParametersException
     */
    public function list()
    {
        $page = $this->verifyInteger('page', 1);
        $limit = $this->verifyPerPageLimit('limit');
        $sort = $this->verifyEnum('sort', 'add_time', ['sort_order', 'name', 'add_time']);
        $order = $this->verifySortValues('order');

        $columns = ['id', 'name', 'desc', 'pic_url', 'floor_price'];
        $list = Brand::getBrandList($page, $limit, $sort, $order, $columns);
        return $this->successPaginate($list);
    }

    /**
     * 获取品牌详情
     * @return JsonResponse
     * @throws NotFoundException
     * @throws ParametersException
     */
    public function detail()
    {
        $id = $this->verifyId('id');
        $brand = Brand::getBrand($id);
        if (is_null($brand)) {
            throw new NotFoundException('brand is not found');
        }
        return $this->success($brand->toArray());
    }
}
