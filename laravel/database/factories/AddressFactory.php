<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'user_id' => 0,
            'province' => '浙江省',
            'city' => '杭州市',
            'county' => '西湖区',
            'address_detail' => $this->faker->streetAddress,
            'area_code' => '',
            'postal_code' => $this->faker->postcode,
            'tel' => $this->faker->phoneNumber,
            'is_default' => 0,
        ];
    }
}
