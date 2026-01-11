<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Machine;
use Illuminate\Database\Seeder;

class MachineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Crea máquinas para producción.
     */
    public function run(): void
    {
        $areas = Area::all();

        if ($areas->isEmpty()) {
            $this->command->warn('Skipping MachineSeeder: No hay áreas');
            return;
        }

        $machineArea = $areas->firstWhere('name', 'Área de Máquinas') ?? $areas->first();

        $machines = [
            ['name' => 'CNC Fresadora 1', 'brand' => 'Haas', 'model' => 'VF-2', 'employees' => 1, 'setup_time' => 30, 'maintenance_time' => 60],
            ['name' => 'CNC Fresadora 2', 'brand' => 'Haas', 'model' => 'VF-4', 'employees' => 1, 'setup_time' => 45, 'maintenance_time' => 60],
            ['name' => 'CNC Torno 1', 'brand' => 'Mazak', 'model' => 'QT-200', 'employees' => 1, 'setup_time' => 25, 'maintenance_time' => 45],
            ['name' => 'Prensa Hidráulica 1', 'brand' => 'Schuler', 'model' => 'PH-50', 'employees' => 2, 'setup_time' => 15, 'maintenance_time' => 30],
            ['name' => 'Prensa Hidráulica 2', 'brand' => 'Schuler', 'model' => 'PH-100', 'employees' => 2, 'setup_time' => 20, 'maintenance_time' => 45],
            ['name' => 'Inyectora 1', 'brand' => 'Engel', 'model' => 'Victory 80', 'employees' => 1, 'setup_time' => 60, 'maintenance_time' => 90],
        ];

        $createdCount = 0;

        foreach ($machines as $index => $data) {
            $machine = Machine::firstOrCreate(
                ['name' => $data['name']],
                array_merge($data, [
                    'area_id' => $machineArea->id,
                    'active' => true,
                    'sn' => 'SN-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                    'asset_number' => 'AST-MCH-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                ])
            );

            if ($machine->wasRecentlyCreated) {
                $createdCount++;
            }
        }

        $this->command->info("✅ MachineSeeder completado!");
        $this->command->info("   - Máquinas creadas: {$createdCount}");
    }
}
