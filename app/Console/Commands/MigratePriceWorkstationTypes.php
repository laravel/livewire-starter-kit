<?php

namespace App\Console\Commands;

use App\Models\Price;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigratePriceWorkstationTypes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prices:migrate-workstation-types {--dry-run : Ejecutar en modo simulación sin modificar datos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migra precios existentes asignando workstation_type y desactivando duplicados';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 Ejecutando en modo DRY-RUN (no se modificarán datos)');
            $this->newLine();
        }

        $this->info('📊 Analizando precios existentes...');
        $this->newLine();

        // Estadísticas iniciales
        $totalPrices = Price::count();
        $activePrices = Price::where('active', true)->count();
        
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Total de precios', $totalPrices],
                ['Precios activos', $activePrices],
            ]
        );
        $this->newLine();

        // 1. Analizar precios sin workstation_type o con valor null
        $pricesWithoutType = Price::whereNull('workstation_type')
            ->orWhere('workstation_type', '')
            ->get();

        if ($pricesWithoutType->count() > 0) {
            $this->warn("⚠️  Encontrados {$pricesWithoutType->count()} precios sin workstation_type");
            
            if (!$dryRun) {
                $this->info('Asignando workstation_type por defecto (table)...');
                Price::whereNull('workstation_type')
                    ->orWhere('workstation_type', '')
                    ->update(['workstation_type' => Price::WORKSTATION_TABLE]);
                $this->info("✅ Asignados {$pricesWithoutType->count()} precios a tipo 'table'");
            } else {
                $this->line("   → Se asignarían {$pricesWithoutType->count()} precios a tipo 'table'");
            }
            $this->newLine();
        } else {
            $this->info('✅ Todos los precios tienen workstation_type asignado');
            $this->newLine();
        }

        // 2. Detectar y desactivar precios duplicados activos del mismo tipo
        $this->info('🔍 Buscando precios duplicados activos...');
        
        $duplicates = DB::select("
            SELECT 
                part_id, 
                workstation_type, 
                COUNT(*) as count,
                GROUP_CONCAT(id ORDER BY effective_date DESC) as price_ids
            FROM prices
            WHERE active = 1
            GROUP BY part_id, workstation_type
            HAVING count > 1
        ");

        if (count($duplicates) > 0) {
            $this->warn("⚠️  Encontrados " . count($duplicates) . " grupos de precios duplicados");
            $this->newLine();

            $deactivatedCount = 0;
            $report = [];

            foreach ($duplicates as $duplicate) {
                $priceIds = explode(',', $duplicate->price_ids);
                $keepId = array_shift($priceIds); // Mantener el más reciente
                $deactivateIds = $priceIds;

                $part = \App\Models\Part::find($duplicate->part_id);
                $typeLabel = Price::WORKSTATION_TYPES[$duplicate->workstation_type] ?? $duplicate->workstation_type;

                $report[] = [
                    'Part' => $part->number ?? "ID: {$duplicate->part_id}",
                    'Tipo' => $typeLabel,
                    'Mantener' => $keepId,
                    'Desactivar' => implode(', ', $deactivateIds),
                ];

                if (!$dryRun) {
                    Price::whereIn('id', $deactivateIds)->update(['active' => false]);
                    $deactivatedCount += count($deactivateIds);
                }
            }

            $this->table(
                ['Part', 'Tipo', 'Mantener ID', 'Desactivar IDs'],
                $report
            );

            if (!$dryRun) {
                $this->info("✅ Desactivados {$deactivatedCount} precios duplicados");
            } else {
                $totalToDeactivate = array_sum(array_map(fn($d) => count(explode(',', $d->price_ids)) - 1, $duplicates));
                $this->line("   → Se desactivarían {$totalToDeactivate} precios duplicados");
            }
            $this->newLine();
        } else {
            $this->info('✅ No se encontraron precios duplicados activos');
            $this->newLine();
        }

        // 3. Validar que todos los precios cumplan las reglas de unicidad
        $this->info('🔍 Validando reglas de unicidad...');
        
        $violations = DB::select("
            SELECT 
                part_id, 
                workstation_type, 
                COUNT(*) as count
            FROM prices
            WHERE active = 1
            GROUP BY part_id, workstation_type
            HAVING count > 1
        ");

        if (count($violations) > 0) {
            $this->error("❌ Aún existen {count($violations)} violaciones de unicidad");
            $this->warn('   Ejecute el comando nuevamente para corregirlas');
        } else {
            $this->info('✅ Todas las reglas de unicidad se cumplen correctamente');
        }
        $this->newLine();

        // 4. Generar reporte final
        $this->info('📋 Reporte Final:');
        $this->newLine();

        $finalStats = [
            ['Total de precios', Price::count()],
            ['Precios activos', Price::where('active', true)->count()],
            ['Precios por tipo:', ''],
        ];

        foreach (Price::WORKSTATION_TYPES as $type => $label) {
            $count = Price::where('workstation_type', $type)->where('active', true)->count();
            $finalStats[] = ["  - {$label}", $count];
        }

        $this->table(['Métrica', 'Valor'], $finalStats);
        $this->newLine();

        if ($dryRun) {
            $this->warn('⚠️  Modo DRY-RUN: No se realizaron cambios en la base de datos');
            $this->info('   Ejecute sin --dry-run para aplicar los cambios');
        } else {
            $this->info('✅ Migración completada exitosamente');
        }

        return Command::SUCCESS;
    }
}
