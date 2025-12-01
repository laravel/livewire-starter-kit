<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shift>
 */
class ShiftFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'start_time' => $this->faker->time(),
            'end_time' => $this->faker->time(),
            'active' => $this->faker->boolean(),
            'comments' => $this->faker->sentence(),
        ];
    }
}
