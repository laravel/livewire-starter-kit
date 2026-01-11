<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\ProductionStatus;
use App\Models\Table;
use Illuminate\Database\Seeder;

class TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Crea mesas de trabajo para producción.
     */
    public function run(): void
    {
        $areas = Area::all();

        if ($areas->isEmpty()) {
            $this->command->warn('Skipping TableSeeder: No hay áreas');
            return;
        }

        $lineAreas = $areas->filter(fn($a) => str_contains($a->name, 'Línea'));

        $tables = [
            ['number' => 'MT-001', 'employees' => 4, 'comments' => 'Mesa de ensamble principal línea 1'],
            ['number' => 'MT-002', 'employees' => 3, 'comments' => 'Mesa secundaria línea 1'],
            ['number' => 'MT-003', 'employees' => 4, 'comments' => 'Mesa principal línea 2'],
            ['number' => 'MT-004', 'employees' => 3, 'comments' => 'Mesa secundaria línea 2'],
            ['number' => 'MT-005', 'employees' => 2, 'comments' => 'Mesa para inspección visual'],
            ['number' => 'MT-006', 'employees' => 2, 'comments' => 'Mesa para empaque final'],
            ['number' => 'MT-007', 'employees' => 2, 'comments' => 'Mesa para retrabajo'],
            ['number' => 'MT-008', 'employees' => 3, 'comments' => 'Mesa auxiliar multiuso'],
        ];

        $createdCount = 0;

        foreach ($tables as $index => $data) {
            $area = $lineAreas->isNotEmpty() 
                ? $lineAreas->values()[$index % $lineAreas->count()] 
                : $areas->first();

            $table = Table::firstOrCreate(
                ['number' => $data['number']],
                array_merge($data, [
                    'area_id' => $area->id,
                    'active' => true,
                ])
            );

            if ($table->wasRecentlyCreated) {
                $createdCount++;
            }
        }

        $this->command->info("✅ TableSeeder completado!");
        $this->command->info("   - Mesas creadas: {$createdCount}");
    }
}
