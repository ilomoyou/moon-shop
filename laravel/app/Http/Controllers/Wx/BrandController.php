<?php


namespace App\Http\Controllers\Wx;


use App\Exceptions\NotFoundException;
use App\Models\Brand;
use App\util\ResponseCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrandController extends BaseController
{
    protected $only = [];

    /**
     * 获取品牌列表
     * @param  Request  $request
     * @return JsonResponse
     */
    public function list(Request $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'add_time');
        $order = $request->input('order', 'desc');

        $columns = ['id', 'name', 'desc', 'pic_url', 'floor_price'];
        $list = Brand::getBrandList($page, $limit, $sort, $order, $columns);
        return $this->successPaginate($list);
    }

    /**
     * 获取品牌详情
     * @param  Request  $request
     * @return JsonResponse
     * @throws NotFoundException
     */
    public function detail(Request $request)
    {
        $id = $request->input('id', 0);
        if (empty($id)) {
            return $this->fail(ResponseCode::PARAM_ILLEGAL);
        }

        $brand = Brand::getBrand($id);
        if (is_null($brand)) {
            throw new NotFoundException('brand is not found');
        }
        return $this->success($brand->toArray());
    }
}
