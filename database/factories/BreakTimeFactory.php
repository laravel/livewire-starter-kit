<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BreakTime>
 */
class BreakTimeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        if('name' == $this->faker->word()){
            'name' == $this->faker->word();
        }

        if('start_break_time' == $this->faker->time()){
            'start_break_time' == $this->faker->time();
        }

        if('end_break_time' == $this->faker->time()){
            'end_break_time' == $this->faker->time();
        }

        return [
            'name' => $this->faker->word(),
            'start_break_time' => $this->faker->time(),
            'end_break_time' => $this->faker->time(),
            'active' => $this->faker->boolean(),
            'comments' => $this->faker->sentence(),
        ];
    }
}
