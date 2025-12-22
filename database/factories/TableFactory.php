<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Table>
 */
class TableFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $brands = ['FANUC', 'ABB', 'KUKA', 'Yaskawa', 'Universal Robots', 'Staubli', 'Comau', 'Kawasaki', 'Denso', 'Epson'];
        $tableTypes = ['Assembly Table', 'Inspection Table', 'Packaging Table', 'Quality Control Table', 'Manual Assembly', 'Testing Station'];

        return [
            'number' => $this->faker->unique()->bothify('TBL-####'),
            'name' => $this->faker->randomElement($tableTypes) . ' ' . $this->faker->numberBetween(1, 100),
            'employees' => $this->faker->numberBetween(1, 8),
            'active' => $this->faker->boolean(85),
            'comments' => $this->faker->optional(0.5)->sentence(10),
            'area_id' => \App\Models\Area::inRandomOrder()->first()?->id ?? \App\Models\Area::factory(),
            'standard_id' => $this->faker->boolean(70) ? \App\Models\Standard::inRandomOrder()->first()?->id : null,
            'production_status_id' => \App\Models\ProductionStatus::inRandomOrder()->first()?->id ?? \App\Models\ProductionStatus::factory(),
            'brand' => $this->faker->boolean(80) ? $this->faker->randomElement($brands) : null,
            'model' => $this->faker->boolean(70) ? strtoupper($this->faker->bothify('??-####')) : null,
            's_n' => $this->faker->boolean(60) ? $this->faker->bothify('SN-########') : null,
            'asset_number' => $this->faker->boolean(70) ? 'AST-' . $this->faker->numerify('######') : null,
            'description' => $this->faker->optional(0.6)->sentence(12),
        ];
    }
}
