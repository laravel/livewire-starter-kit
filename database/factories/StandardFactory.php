<?php

namespace Database\Factories;

use App\Models\Machine;
use App\Models\Part;
use App\Models\Semi_Automatic;
use App\Models\Table;
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
            'part_id' => Part::factory(),
            'work_table_id' => $this->faker->boolean(60) ? Table::inRandomOrder()->first()?->id : null,
            'semi_auto_work_table_id' => $this->faker->boolean(40) ? Semi_Automatic::inRandomOrder()->first()?->id : null,
            'machine_id' => $this->faker->boolean(50) ? Machine::inRandomOrder()->first()?->id : null,
            'persons_1' => $this->faker->numberBetween(1, 3000),
            'persons_2' => $this->faker->numberBetween(1, 3000),
            'persons_3' => $this->faker->numberBetween(1, 3000),
            'effective_date' => $this->faker->dateTimeBetween('-1 year', '+1 month'),
            'active' => $this->faker->boolean(80),
            'description' => $this->faker->optional()->sentence(),
        ];
    }
}
