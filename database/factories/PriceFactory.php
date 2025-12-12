<?php

namespace Database\Factories;

use App\Models\Part;
use App\Models\Price;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Price>
 */
class PriceFactory extends Factory
{
    protected $model = Price::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $unitPrice = $this->faker->randomFloat(4, 0.01, 100);
        
        return [
            'part_id' => Part::factory(),
            'unit_price' => $unitPrice,
            'tier_1_999' => $this->faker->optional(0.7)->randomFloat(4, $unitPrice * 0.95, $unitPrice),
            'tier_1000_10999' => $this->faker->optional(0.6)->randomFloat(4, $unitPrice * 0.85, $unitPrice * 0.95),
            'tier_11000_99999' => $this->faker->optional(0.5)->randomFloat(4, $unitPrice * 0.75, $unitPrice * 0.85),
            'tier_100000_plus' => $this->faker->optional(0.4)->randomFloat(4, $unitPrice * 0.65, $unitPrice * 0.75),
            'effective_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'active' => $this->faker->boolean(80),
            'comments' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the price is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => true,
        ]);
    }

    /**
     * Indicate that the price is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }

    /**
     * Set all pricing tiers.
     */
    public function withAllTiers(): static
    {
        return $this->state(function (array $attributes) {
            $unitPrice = $attributes['unit_price'] ?? $this->faker->randomFloat(4, 0.01, 100);
            
            return [
                'unit_price' => $unitPrice,
                'tier_1_999' => round($unitPrice * 0.98, 4),
                'tier_1000_10999' => round($unitPrice * 0.90, 4),
                'tier_11000_99999' => round($unitPrice * 0.80, 4),
                'tier_100000_plus' => round($unitPrice * 0.70, 4),
            ];
        });
    }

    /**
     * Set no pricing tiers (only unit price).
     */
    public function withoutTiers(): static
    {
        return $this->state(fn (array $attributes) => [
            'tier_1_999' => null,
            'tier_1000_10999' => null,
            'tier_11000_99999' => null,
            'tier_100000_plus' => null,
        ]);
    }
}
