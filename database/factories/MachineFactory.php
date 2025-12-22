<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Machine>
 */
class MachineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $machineTypes = ['CNC Mill', 'Lathe', 'Press', 'Welder', 'Robot Arm'];
        $brands = ['FANUC', 'ABB', 'KUKA', 'Yaskawa', 'Universal Robots'];

        return [
            'name' => $this->faker->randomElement($machineTypes) . ' ' . $this->faker->numberBetween(1, 100),
            'brand' => $this->faker->randomElement($brands),
            'model' => strtoupper($this->faker->bothify('??-####')),
            'sn' => $this->faker->bothify('SN-########'),
            'asset_number' => $this->faker->unique()->numerify('AST-######'),
            'employees' => $this->faker->numberBetween(1, 3),
            'setup_time' => $this->faker->randomFloat(2, 0.5, 4.0),
            'maintenance_time' => $this->faker->randomFloat(2, 1.0, 8.0),
            'active' => $this->faker->boolean(85),
            'comments' => $this->faker->optional(0.5)->sentence(10),
            'area_id' => \App\Models\Area::inRandomOrder()->first()?->id ?? \App\Models\Area::factory(),
        ];
    }
}
