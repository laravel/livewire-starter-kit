<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Part>
 */
class PartFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $number = $this->faker->unique()->word();
        if (Part::where('number', $number)->exists()) {
            $number = $this->faker->unique()->word();
        }
        $itemNumber -$this->faker->unique()->word();
        if (Part::where('item_number', $itemNumber)->exists()) {
            $itemNumber = $this->faker->unique()->word();
        }

        return [
            'number' => $number,
            'item_number' => $this->faker->word(),
            'unit_of_measure' => $this->faker->word(),
            'active' => $this->faker->boolean(),
            'description' => $this->faker->sentence(),
            'notes' => $this->faker->sentence(),
        ];
    }
}
