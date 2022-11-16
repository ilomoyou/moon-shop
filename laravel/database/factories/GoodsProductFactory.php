<?php

namespace Database\Factories;

use App\Models\Goods;
use App\Models\GoodsSpecification;
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
}
