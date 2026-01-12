<?php

namespace Database\Seeders;

use App\Models\ProductionStatus;
use Illuminate\Database\Seeder;

class ProductionStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Crea estados de producción para mesas, máquinas y semi-automáticos.
     */
    public function run(): void
    {
        $statuses = [
            ['name' => 'Disponible', 'description' => 'Equipo disponible para producción', 'color' => '#22c55e'],
            ['name' => 'En Uso', 'description' => 'Equipo actualmente en producción', 'color' => '#3b82f6'],
            ['name' => 'Mantenimiento', 'description' => 'Equipo en mantenimiento programado', 'color' => '#f59e0b'],
            ['name' => 'Fuera de Servicio', 'description' => 'Equipo no disponible', 'color' => '#ef4444'],
            ['name' => 'En Espera', 'description' => 'Equipo esperando material o instrucciones', 'color' => '#8b5cf6'],
        ];

        $createdCount = 0;

        foreach ($statuses as $data) {
            $status = ProductionStatus::firstOrCreate(
                ['name' => $data['name']],
                $data
            );

            if ($status->wasRecentlyCreated) {
                $createdCount++;
            }
        }

        $this->command->info("✅ ProductionStatusSeeder completado!");
        $this->command->info("   - Estados creados: {$createdCount}");
    }
}
