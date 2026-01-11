<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Department;
use Illuminate\Database\Seeder;

class AreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Crea áreas de producción asociadas a departamentos.
     */
    public function run(): void
    {
        $departments = Department::all();

        if ($departments->isEmpty()) {
            $this->command->warn('No hay departamentos. Ejecute DepartmentSeeder primero.');
            return;
        }

        $prodDept = $departments->firstWhere('name', 'Producción') ?? $departments->first();

        $areas = [
            ['name' => 'Línea 1', 'description' => 'Línea de ensamble principal', 'department_id' => $prodDept->id],
            ['name' => 'Línea 2', 'description' => 'Línea de ensamble secundaria', 'department_id' => $prodDept->id],
            ['name' => 'Línea 3', 'description' => 'Línea de productos especiales', 'department_id' => $prodDept->id],
            ['name' => 'Área de Máquinas', 'description' => 'Zona de maquinado CNC', 'department_id' => $prodDept->id],
            ['name' => 'Área Semi-Automática', 'description' => 'Estaciones semi-automáticas', 'department_id' => $prodDept->id],
            ['name' => 'Inspección', 'description' => 'Área de inspección de calidad', 'department_id' => $departments->firstWhere('name', 'Calidad')?->id ?? $prodDept->id],
        ];

        $createdCount = 0;

        foreach ($areas as $data) {
            $area = Area::firstOrCreate(
                ['name' => $data['name']],
                $data
            );

            if ($area->wasRecentlyCreated) {
                $createdCount++;
            }
        }

        $this->command->info("✅ AreaSeeder completado!");
        $this->command->info("   - Áreas creadas: {$createdCount}");
    }
}
