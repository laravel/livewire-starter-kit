<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Semi_Automatic>
 */
class Semi_AutomaticFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number' => $this->faker->unique()->bothify('SA-####'),
            'employees' => $this->faker->numberBetween(1, 4),
            'active' => $this->faker->boolean(85),
            'comments' => $this->faker->optional(0.5)->sentence(10),
            'area_id' => \App\Models\Area::inRandomOrder()->first()?->id ?? \App\Models\Area::factory(),
        ];
    }
}
