<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductionStatus>
 */
class ProductionStatusFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = ['En Producción', 'Detenida', 'Pausa', 'Mantenimiento', 'En Espera', 'Configuración'];
        $colors = ['#10b981', '#ef4444', '#f59e0b', '#3b82f6', '#6b7280', '#8b5cf6'];

        return [
            'name' => $this->faker->unique()->randomElement($statuses),
            'color' => $this->faker->randomElement($colors),
            'order' => $this->faker->numberBetween(1, 10),
            'active' => $this->faker->boolean(85),
            'description' => $this->faker->optional(0.6)->sentence(8),
        ];
    }
}
