<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Semi_Automatic;
use Illuminate\Database\Seeder;

class Semi_AutomaticSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Crea estaciones semi-automáticas para producción.
     */
    public function run(): void
    {
        $areas = Area::all();

        if ($areas->isEmpty()) {
            $this->command->warn('Skipping Semi_AutomaticSeeder: No hay áreas');
            return;
        }

        $semiArea = $areas->firstWhere('name', 'Área Semi-Automática') ?? $areas->first();

        $semiAutos = [
            ['number' => 'SA-001', 'employees' => 1, 'comments' => 'Estación de soldadura semi-automática'],
            ['number' => 'SA-002', 'employees' => 2, 'comments' => 'Estación de ensamble asistido'],
            ['number' => 'SA-003', 'employees' => 1, 'comments' => 'Estación de pruebas funcionales'],
            ['number' => 'SA-004', 'employees' => 1, 'comments' => 'Estación de etiquetado'],
            ['number' => 'SA-005', 'employees' => 2, 'comments' => 'Estación de verificación'],
        ];

        $createdCount = 0;

        foreach ($semiAutos as $data) {
            $semiAuto = Semi_Automatic::firstOrCreate(
                ['number' => $data['number']],
                array_merge($data, [
                    'area_id' => $semiArea->id,
                    'active' => true,
                ])
            );

            if ($semiAuto->wasRecentlyCreated) {
                $createdCount++;
            }
        }

        $this->command->info("✅ Semi_AutomaticSeeder completado!");
        $this->command->info("   - Semi-automáticos creados: {$createdCount}");
    }
}
