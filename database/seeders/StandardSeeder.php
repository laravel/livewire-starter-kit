<?php

namespace Database\Seeders;

use App\Models\Part;
use App\Models\Standard;
use App\Models\StandardConfiguration;
use App\Models\Table;
use App\Models\Machine;
use App\Models\Semi_Automatic;
use Illuminate\Database\Seeder;

class StandardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Crea estándares activos para las partes existentes usando StandardConfiguration.
     * NO usa campos legacy para evitar inconsistencias con Prices.
     */
    public function run(): void
    {
        $parts = Part::active()->get();
        
        if ($parts->isEmpty()) {
            $this->command->warn('Skipping StandardSeeder: No hay partes activas');
            return;
        }

        $createdCount = 0;
        
        // Mapeo de Price workstation_type a StandardConfiguration workstation_type
        // Price usa: 'table', 'machine', 'semi_automatic'
        // StandardConfiguration usa: 'manual', 'machine', 'semi_automatic'
        $priceTypes = ['table', 'machine', 'semi_automatic'];
        $configTypes = ['manual', 'machine', 'semi_automatic'];

        foreach ($parts as $index => $part) {
            // Verificar si ya tiene estándar activo
            if ($part->standards()->where('active', true)->exists()) {
                continue;
            }

            // Determinar el tipo de workstation para esta parte (rotativo)
            $priceType = $priceTypes[$index % 3];
            $configType = $configTypes[$index % 3];

            // Crear Standard SIN campos legacy
            $standard = Standard::create([
                'part_id' => $part->id,
                'work_table_id' => null,  // NO usar campos legacy
                'machine_id' => null,
                'semi_auto_work_table_id' => null,
                'persons_1' => 1,
                'persons_2' => 2,
                'persons_3' => 3,
                'units_per_hour' => rand(100, 350),
                'active' => true,
                'is_migrated' => true,  // Marcar como migrado
                'description' => "Estándar de producción para {$part->number}",
            ]);

            // Crear configuraciones para diferentes cantidades de personas
            $baseProductivity = rand(100, 200);
            
            StandardConfiguration::create([
                'standard_id' => $standard->id,
                'workstation_type' => $configType,  // Usar 'manual', 'machine', o 'semi_automatic'
                'workstation_id' => null,
                'persons_required' => 1,
                'units_per_hour' => $baseProductivity,
                'is_default' => true,  // Primera configuración es default
                'notes' => "Configuración para 1 persona",
            ]);

            StandardConfiguration::create([
                'standard_id' => $standard->id,
                'workstation_type' => $configType,
                'workstation_id' => null,
                'persons_required' => 2,
                'units_per_hour' => (int)($baseProductivity * 1.7),
                'is_default' => false,
                'notes' => "Configuración para 2 personas",
            ]);

            StandardConfiguration::create([
                'standard_id' => $standard->id,
                'workstation_type' => $configType,
                'workstation_id' => null,
                'persons_required' => 3,
                'units_per_hour' => (int)($baseProductivity * 2.3),
                'is_default' => false,
                'notes' => "Configuración para 3 personas",
            ]);

            $createdCount++;
        }

        $this->command->info("✅ StandardSeeder completado!");
        $this->command->info("   - Estándares creados: {$createdCount}");
        $this->command->info("   - Partes con estándar activo: " . Part::whereHas('standards', fn($q) => $q->where('active', true)->where('is_migrated', true))->count());
    }
}
