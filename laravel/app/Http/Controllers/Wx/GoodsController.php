<?php


namespace App\Http\Controllers\Wx;


use App\enum\SearchHistoryFromEnum;
use App\Exceptions\NotFoundException;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Collect;
use App\Models\Goods;
use App\Models\Issue;
use App\Services\CommentService;
use App\Services\FootprintService;
use App\Services\GoodsService;
use App\Services\SearchHistoryService;
use App\util\ResponseCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GoodsController extends BaseController
{
    protected $only = [];

    /**
     * 获取商品列表
     * @param  Request  $request
     * @return JsonResponse
     */
    public function list(Request $request)
    {
        $categoryId = $request->input('categoryId');
        $brandId = $request->input('brandId');
        $keyword = $request->input('keyword');
        $isNew = $request->input('isNew');
        $isHot = $request->input('isHot');
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'add_time');
        $order = $request->input('order', 'desc');

        // todo 验证参数

        // 保存搜索历史关键字
        if ($this->isLogin() && !empty($keyword)) {
            $isPass = SearchHistoryService::getInstance()->getHistoryByKeyword($this->userId(), $keyword);
            if (!$isPass) {
                SearchHistoryService::getInstance()->save($this->userId(), $keyword, SearchHistoryFromEnum::WX);
            }
        }

        $columns = ['id', 'name', 'brief', 'pic_url', 'is_new', 'is_hot', 'counter_price', 'retail_price'];
        $goodsList = GoodsService::getInstance()->getGoodsList($categoryId, $brandId, $isNew, $isHot, $keyword, $columns, $sort, $order, $page, $limit);
        $categoryList = GoodsService::getInstance()->getL2CategoryList($brandId, $isNew, $isHot, $keyword);

        $goodsList = $this->paginate($goodsList);
        $goodsList['filterCategoryList'] = $categoryList;
        return $this->success($goodsList);
    }

    /**
     * 获取商品详情信息
     * @param  Request  $request
     * @return JsonResponse
     * @throws NotFoundException
     */
    public function detail(Request $request)
    {
        $id = $request->input('id');
        if (empty($id)) {
            return $this->fail(ResponseCode::PARAM_ILLEGAL);
        }

        $goods = Goods::getGoodsById($id);
        if (empty($goods)) {
            throw new NotFoundException('good is not found');
        }

        $attribute = $goods->getGoodsAttribute();
        $spec = $goods->getGoodsSpecification();
        $product = $goods->getGoodsProduct();
        $issue = Issue::getGoodsIssueList();
        $brand = $goods->brand_id ? Brand::getBrand($goods->brand_id) : (object) [];
        $comment = CommentService::getInstance()->getCommentWithUserInfo($goods->id);

        // 用户收藏
        $userHasCollect = 0;
        if ($this->isLogin()) {
            $userHasCollect = Collect::countByGoodsId($this->userId(), $id);
            FootprintService::getInstance()->saveFootprint($this->userId(), $id);
        }

        // todo 团购信息
        // todo 系统配置

        return $this->success([
            'info' => $goods,
            'userHasCollect' => $userHasCollect,
            'issue' => $issue,
            'comment' => $comment,
            'specificationList' => $spec,
            'productList' => $product,
            'attribute' => $attribute,
            'brand' => $brand,
            'groupon' => [],
            'share' => false,
            'shareImage' => $goods->share_url
        ]);
    }

    /**
     * 统计在售商品总数
     * @return JsonResponse
     */
    public function count()
    {
        $count = Goods::countGoodsOnSale();
        return $this->success($count);
    }

    /**
     * 根据分类ID获取当前商品分类信息
     * @param  Request  $request
     * @return JsonResponse
     * @throws NotFoundException
     */
    public function category(Request $request)
    {
        $id = $request->input('id');
        if (empty($id)) {
            return $this->fail(ResponseCode::PARAM_ILLEGAL);
        }

        $current = Category::getCategoryById($id);
        if (empty($current)) {
            throw new NotFoundException('category is not found');
        }

        $parent = null;
        $children = null;
        if ($current->pid === 0) {
            $parent = $current;
            $children = Category::getL2ListByPid($current->id);
            $current = $children->first() ?? $current;
        } else {
            $parent = Category::getL1ById($current->pid);
            $children = Category::getL2ListByPid($current->pid);
        }

        return $this->success([
            'parentCategory' => $parent,
            'currentCategory' => $current,
            'childrenCategory' => $children
        ]);
    }
}
