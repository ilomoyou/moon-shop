<?php

namespace Database\Factories;

use App\Models\Goods;
use App\Models\GoodsProduct;
use App\Models\GoodsSpecification;
use App\Models\GrouponRules;
use Illuminate\Database\Eloquent\Factories\Factory;

class GoodsProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        /** @var Goods $goods */
        $goods = Goods::factory()->create();
        /** @var GoodsSpecification $spec */
        $spec = GoodsSpecification::factory()->create([
            "goods_id" => $goods->id
        ]);
        return [
            "goods_id" => $goods->id,
            "specifications" => [$spec->value],
            "price" => 999,
            "number" => 100,
            "url" => $this->faker->imageUrl(),
        ];
    }

    /**
     * 团购场景
     * @return GoodsProductFactory
     */
    public function groupon()
    {
        return $this->state(function () {
            return [];
        })->afterCreating(function (GoodsProduct $product) {
            $goods = Goods::getGoodsById($product->goods_id);
            GrouponRules::factory()->create([
                'goods_id' => $product->goods_id,
                'goods_name' => $goods->name,
                'pic_url' => 'http://yanxuan.nosdn.127.net/8ab2d3287af0cefa2cc539e40600621d.png',
                'discount' => 1
            ]);
        });
    }
}
