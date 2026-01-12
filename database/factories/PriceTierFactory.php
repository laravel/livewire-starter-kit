<?php

namespace Database\Factories;

use App\Models\Price;
use App\Models\PriceTier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PriceTier>
 */
class PriceTierFactory extends Factory
{
    protected $model = PriceTier::class;

    public function definition(): array
    {
        return [
            'price_id' => Price::factory(),
            'min_quantity' => $this->faker->numberBetween(1, 1000),
            'max_quantity' => $this->faker->optional(0.7)->numberBetween(1001, 100000),
            'tier_price' => $this->faker->randomFloat(4, 0.01, 100),
        ];
    }
}
