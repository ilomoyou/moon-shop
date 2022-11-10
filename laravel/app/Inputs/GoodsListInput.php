<?php


namespace App\Inputs;


use Illuminate\Validation\Rule;

class GoodsListInput extends Input
{
    // 定义所需要的参数
    public $categoryId;
    public $brandId;
    public $keyword;
    public $isNew;
    public $isHot;
    public $page = 1;
    public $limit = 10;
    public $sort = 'add_time';
    public $order = 'desc';

    /**
     * 参数校验规则
     * @return array
     */
    protected function rules()
    {
        return [
            'categoryId' => 'integer|digits_between:1,20',
            'brandId' => 'integer|digits_between:1,20',
            'keyword' => 'string',
            'isNew' => 'boolean',
            'isHot' => 'boolean',
            'page' => 'integer',
            'limit' => 'integer|max:100',
            'sort' => Rule::in(['add_time', 'retail_price', 'name']),
            'order' => Rule::in(['desc', 'asc']),
        ];
    }
}
