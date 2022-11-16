<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class GoodsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "goods_sn" => $this->faker->word,
            "name" => "测试商品".$this->faker->word,
            "category_id" => 1008009,
            "brand_id" => 0,
            "gallery" => [],
            "keywords" => "",
            "brief" => "测试",
            "is_on_sale" => 1,
            "sort_order" => $this->faker->numberBetween(1, 999),
            "pic_url" => $this->faker->imageUrl(),
            "share_url" => $this->faker->url,
            "is_new" => $this->faker->boolean,
            "is_hot" => $this->faker->boolean,
            "unit" => "件",
            "counter_price" => 919,
            "retail_price" => 899,
            "detail" => $this->faker->text
        ];
    }
}
