<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Part;
use App\Models\Area;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Standard>
 */
class StandardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'persons_1' => $this->faker->numberBetween(1, 3000),
            'persons_2' => $this->faker->numberBetween(1, 3000),
            'persons_3' => $this->faker->numberBetween(1, 3000),
            'effective_date' => $this->faker->dateTimeBetween('-1 year', '+1 month'),
            'active' => $this->faker->boolean(80),
            'description' => $this->faker->optional()->sentence(),
            'work_table_id' => random_int(1, 10),
            'semi_auto_work_table_id' => random_int(1, 10),
            'machine_id' => random_int(1, 10),
        ];
    }
}
