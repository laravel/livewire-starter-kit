<?php

namespace Database\Seeders;

use App\Models\Part;
use App\Models\Standard;
use App\Models\Table;
use App\Models\Machine;
use App\Models\Semi_Automatic;
use Illuminate\Database\Seeder;

class StandardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Crea estándares activos para las partes existentes.
     * Necesario para que el Capacity Wizard funcione correctamente.
     */
    public function run(): void
    {
        $parts = Part::active()->get();
        
        if ($parts->isEmpty()) {
            $this->command->warn('Skipping StandardSeeder: No hay partes activas');
            return;
        }

        $tables = Table::all();
        $machines = Machine::all();
        $semiAutos = Semi_Automatic::all();

        $createdCount = 0;

        foreach ($parts as $index => $part) {
            // Verificar si ya tiene estándar activo
            if ($part->standards()->where('active', true)->exists()) {
                continue;
            }

            // Asignar workstation de forma rotativa
            $tableId = $tables->isNotEmpty() ? $tables[$index % $tables->count()]->id : null;
            $machineId = $machines->isNotEmpty() && $index % 3 === 0 ? $machines[$index % $machines->count()]->id : null;
            $semiAutoId = $semiAutos->isNotEmpty() && $index % 4 === 0 ? $semiAutos[$index % $semiAutos->count()]->id : null;

            Standard::create([
                'part_id' => $part->id,
                'work_table_id' => $tableId,
                'machine_id' => $machineId,
                'semi_auto_work_table_id' => $semiAutoId,
                'persons_1' => rand(800, 1500),
                'persons_2' => rand(1200, 2000),
                'persons_3' => rand(1800, 2800),
                'units_per_hour' => rand(100, 350), // Crítico para cálculo de capacidad
                'effective_date' => now()->subDays(rand(1, 30)),
                'active' => true,
                'description' => "Estándar de producción para {$part->number}",
            ]);

            $createdCount++;
        }

        $this->command->info("✅ StandardSeeder completado!");
        $this->command->info("   - Estándares creados: {$createdCount}");
        $this->command->info("   - Partes con estándar activo: " . Part::whereHas('standards', fn($q) => $q->where('active', true)->where('units_per_hour', '>', 0))->count());
    }
}
