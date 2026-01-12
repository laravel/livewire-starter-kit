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

    public function definition(): array
    {
        return [
            'part_id' => Part::factory(),
            'sample_price' => $this->faker->randomFloat(4, 0.01, 100),
            'workstation_type' => $this->faker->randomElement([
                Price::WORKSTATION_TABLE,
                Price::WORKSTATION_MACHINE,
                Price::WORKSTATION_SEMI_AUTOMATIC,
            ]),
            'effective_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'active' => $this->faker->boolean(80),
            'comments' => $this->faker->optional()->sentence(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => ['active' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['active' => false]);
    }

    public function forTable(): static
    {
        return $this->state(fn (array $attributes) => ['workstation_type' => Price::WORKSTATION_TABLE]);
    }

    public function forMachine(): static
    {
        return $this->state(fn (array $attributes) => ['workstation_type' => Price::WORKSTATION_MACHINE]);
    }

    public function forSemiAutomatic(): static
    {
        return $this->state(fn (array $attributes) => ['workstation_type' => Price::WORKSTATION_SEMI_AUTOMATIC]);
    }

    /**
     * Create price with tiers based on workstation type.
     */
    public function withTiers(): static
    {
        return $this->afterCreating(function (Price $price) {
            $config = Price::getTierConfigForType($price->workstation_type);
            $basePrice = (float) $price->sample_price;
            
            foreach ($config as $index => $tierConfig) {
                $discount = 1 - (($index + 1) * 0.05); // 5% descuento por nivel
                $price->tiers()->create([
                    'min_quantity' => $tierConfig['min'],
                    'max_quantity' => $tierConfig['max'],
                    'tier_price' => round($basePrice * $discount, 4),
                ]);
            }
        });
    }
}
