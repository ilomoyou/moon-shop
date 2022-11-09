<?php


namespace App\Http\Controllers\Wx;


use App\Exceptions\NotFoundException;
use App\Exceptions\ParametersException;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CatalogController extends BaseController
{
    protected $only = [];

    /**
     * 获取全部分类列表
     * @return JsonResponse
     * @throws NotFoundException
     * @throws ParametersException
     */
    public function index()
    {
        $id = $this->verifyId('id', 0);
        $l1List = Category::getL1List();
        if (empty($id)) {
            $current = $l1List->first();
        } else {
            $current = $l1List->where('id', $id)->first();
        }

        if (empty($current)) {
            throw new NotFoundException('category is not found');
        }
        $l2List = Category::getL2ListByPid($current->id);

        return $this->success([
            'categoryList' => $l1List->toArray(),
            'currentCategory' => $current,
            'currentSubCategory' => $l2List->toArray()
        ]);
    }

    /**
     * 获取当前分类列表
     * @return JsonResponse
     * @throws NotFoundException
     * @throws ParametersException
     */
    public function current()
    {
        $id = $this->verifyIdMust('id');
        $category = Category::getL1ById($id);
        if (empty($category)) {
            throw new NotFoundException('category is not found');
        }

        $l2List = Category::getL2ListByPid($category->id);
        return $this->success([
            'currentCategory' => $category->toArray(),
            'currentSubCategory' => $l2List->toArray()
        ]);
    }

}
