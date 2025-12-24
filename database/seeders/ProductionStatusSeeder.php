<?php

namespace Database\Seeders;

use App\Models\ProductionStatus;
use Illuminate\Database\Seeder;

class ProductionStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'name' => 'En Producción',
                'color' => '#10b981', // Green
                'order' => 1,
                'active' => true,
                'description' => 'Mesa/Máquina activa en proceso de producción'
            ],
            [
                'name' => 'Pausa',
                'color' => '#f59e0b', // Amber
                'order' => 2,
                'active' => true,
                'description' => 'Temporalmente pausada'
            ],
            [
                'name' => 'Detenida',
                'color' => '#ef4444', // Red
                'order' => 3,
                'active' => true,
                'description' => 'Detenida por problema o falla'
            ],
            [
                'name' => 'Mantenimiento',
                'color' => '#3b82f6', // Blue
                'order' => 4,
                'active' => true,
                'description' => 'En mantenimiento programado o correctivo'
            ],
            [
                'name' => 'En Espera',
                'color' => '#6b7280', // Gray
                'order' => 5,
                'active' => true,
                'description' => 'Esperando materiales o instrucciones'
            ],
            [
                'name' => 'Configuración',
                'color' => '#8b5cf6', // Purple
                'order' => 6,
                'active' => true,
                'description' => 'En proceso de configuración o setup'
            ],
        ];

        foreach ($statuses as $status) {
            ProductionStatus::create($status);
        }

        $this->command->info('Production statuses seeded successfully!');
    }
}
