<?php

namespace App\Console\Commands;

use App\Models\Part;
use App\Models\Price;
use App\Models\Standard;
use Illuminate\Console\Command;

class DiagnosePriceStandardMismatch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prices:diagnose-mismatch 
                            {--fix : Automatically fix mismatches by updating Standards to match Prices}
                            {--show-all : Show all parts, not just mismatches}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Diagnose and optionally fix mismatches between Standard assembly_mode and Price workstation_type';

    /**
     * Mapeo de assembly mode a workstation type
     */
    private const ASSEMBLY_MODE_MAP = [
        'manual' => Price::WORKSTATION_TABLE,
        'semi_automatic' => Price::WORKSTATION_SEMI_AUTOMATIC,
        'machine' => Price::WORKSTATION_MACHINE,
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Diagnosticando inconsistencias entre Standards y Prices...');
        $this->newLine();

        $parts = Part::with(['standards' => function ($query) {
            $query->active();
        }, 'prices' => function ($query) {
            $query->active();
        }])->get();

        $mismatches = [];
        $matches = [];
        $noStandard = [];
        $noPrice = [];

        foreach ($parts as $part) {
            $standard = $part->standards->first();
            $price = $part->prices->first();

            // Caso 1: No tiene Standard activo
            if (!$standard) {
                if ($price) {
                    $noStandard[] = [
                        'part' => $part,
                        'price' => $price,
                    ];
                }
                continue;
            }

            // Caso 2: No tiene Price activo
            if (!$price) {
                $noPrice[] = [
                    'part' => $part,
                    'standard' => $standard,
                ];
                continue;
            }

            // Caso 3: Tiene ambos - verificar consistencia
            $assemblyMode = $standard->getAssemblyMode();
            $expectedWorkstationType = $this->mapAssemblyMode($assemblyMode);
            $actualWorkstationType = $price->workstation_type;

            $data = [
                'part' => $part,
                'standard' => $standard,
                'price' => $price,
                'assembly_mode' => $assemblyMode,
                'expected_type' => $expectedWorkstationType,
                'actual_type' => $actualWorkstationType,
                'has_legacy_fields' => $this->hasLegacyFields($standard),
                'has_configurations' => $standard->configurations()->exists(),
            ];

            if ($expectedWorkstationType !== $actualWorkstationType) {
                $mismatches[] = $data;
            } else {
                $matches[] = $data;
            }
        }

        // Mostrar resultados
        $this->displayResults($mismatches, $matches, $noStandard, $noPrice);

        // Opción de fix
        if ($this->option('fix') && count($mismatches) > 0) {
            $this->newLine();
            if ($this->confirm('¿Deseas corregir las inconsistencias actualizando los Standards para que coincidan con los Prices?')) {
                $this->fixMismatches($mismatches);
            }
        }

        return Command::SUCCESS;
    }

    /**
     * Muestra los resultados del diagnóstico
     */
    private function displayResults(array $mismatches, array $matches, array $noStandard, array $noPrice): void
    {
        // Resumen
        $this->info('📊 RESUMEN:');
        $this->table(
            ['Categoría', 'Cantidad'],
            [
                ['✅ Coincidencias (Standard y Price consistentes)', count($matches)],
                ['❌ Inconsistencias (Standard y Price NO coinciden)', count($mismatches)],
                ['⚠️  Partes con Price pero sin Standard activo', count($noStandard)],
                ['⚠️  Partes con Standard pero sin Price activo', count($noPrice)],
            ]
        );

        $this->newLine();

        // Mostrar inconsistencias
        if (count($mismatches) > 0) {
            $this->error('❌ INCONSISTENCIAS ENCONTRADAS:');
            $this->newLine();

            $tableData = [];
            foreach ($mismatches as $data) {
                $tableData[] = [
                    $data['part']->number,
                    $data['standard']->id,
                    $data['assembly_mode'] ?? 'NULL',
                    $data['expected_type'] ?? 'NULL',
                    $data['price']->id,
                    $data['actual_type'],
                    $data['has_legacy_fields'] ? 'Sí' : 'No',
                    $data['has_configurations'] ? 'Sí' : 'No',
                ];
            }

            $this->table(
                ['Part', 'Std ID', 'Assembly Mode', 'Expected Type', 'Price ID', 'Actual Type', 'Legacy?', 'Configs?'],
                $tableData
            );

            $this->newLine();
            $this->warn('💡 Estas partes tienen inconsistencias entre el Standard y el Price.');
            $this->warn('   El sistema buscará precios basándose en el Standard, pero no los encontrará.');
        }

        // Mostrar coincidencias si se solicita
        if ($this->option('show-all') && count($matches) > 0) {
            $this->newLine();
            $this->info('✅ COINCIDENCIAS (Standard y Price consistentes):');
            $this->newLine();

            $tableData = [];
            foreach ($matches as $data) {
                $tableData[] = [
                    $data['part']->number,
                    $data['standard']->id,
                    $data['assembly_mode'] ?? 'NULL',
                    $data['price']->id,
                    $data['actual_type'],
                    $data['has_legacy_fields'] ? 'Sí' : 'No',
                    $data['has_configurations'] ? 'Sí' : 'No',
                ];
            }

            $this->table(
                ['Part', 'Std ID', 'Assembly Mode', 'Price ID', 'Type', 'Legacy?', 'Configs?'],
                $tableData
            );
        }

        // Mostrar partes sin Standard
        if (count($noStandard) > 0) {
            $this->newLine();
            $this->warn('⚠️  PARTES CON PRICE PERO SIN STANDARD ACTIVO:');
            $tableData = [];
            foreach ($noStandard as $data) {
                $tableData[] = [
                    $data['part']->number,
                    $data['price']->id,
                    $data['price']->workstation_type,
                ];
            }
            $this->table(['Part', 'Price ID', 'Type'], $tableData);
        }

        // Mostrar partes sin Price
        if (count($noPrice) > 0) {
            $this->newLine();
            $this->warn('⚠️  PARTES CON STANDARD PERO SIN PRICE ACTIVO:');
            $tableData = [];
            foreach ($noPrice as $data) {
                $tableData[] = [
                    $data['part']->number,
                    $data['standard']->id,
                    $data['standard']->getAssemblyMode() ?? 'NULL',
                ];
            }
            $this->table(['Part', 'Std ID', 'Assembly Mode'], $tableData);
        }
    }

    /**
     * Corrige las inconsistencias
     */
    private function fixMismatches(array $mismatches): void
    {
        $this->info('🔧 Corrigiendo inconsistencias...');
        $this->newLine();

        $fixed = 0;
        $errors = 0;

        foreach ($mismatches as $data) {
            $standard = $data['standard'];
            $price = $data['price'];
            $part = $data['part'];

            try {
                // Estrategia: Actualizar el Standard para que coincida con el Price
                // Esto significa limpiar los campos legacy y crear/actualizar configuraciones

                $this->line("Procesando Part {$part->number} (Standard ID: {$standard->id})...");

                // Limpiar campos legacy
                $standard->work_table_id = null;
                $standard->semi_auto_work_table_id = null;
                $standard->machine_id = null;

                // Verificar si ya tiene una configuración con el tipo correcto
                $existingConfig = $standard->configurations()
                    ->where('workstation_type', $price->workstation_type)
                    ->first();

                if (!$existingConfig) {
                    // Crear configuración basada en el Price
                    $standard->configurations()->create([
                        'workstation_type' => $price->workstation_type,
                        'persons_required' => $standard->persons_1 ?? 1,
                        'units_per_hour' => $standard->units_per_hour ?? 100,
                        'is_default' => true,
                    ]);
                    $this->info("  ✓ Creada configuración tipo '{$price->workstation_type}'");
                } else {
                    // Marcar la configuración existente como default
                    $standard->configurations()->update(['is_default' => false]);
                    $existingConfig->update(['is_default' => true]);
                    $this->info("  ✓ Configuración existente marcada como default");
                }

                $standard->is_migrated = true;
                $standard->save();

                $this->info("  ✅ Standard actualizado correctamente");
                $fixed++;

            } catch (\Exception $e) {
                $this->error("  ❌ Error al procesar Part {$part->number}: {$e->getMessage()}");
                $errors++;
            }
        }

        $this->newLine();
        $this->info("✅ Proceso completado:");
        $this->info("   - Corregidos: {$fixed}");
        if ($errors > 0) {
            $this->error("   - Errores: {$errors}");
        }
    }

    /**
     * Mapea assembly_mode a workstation_type
     */
    private function mapAssemblyMode(?string $assemblyMode): ?string
    {
        if (!$assemblyMode) {
            return null;
        }

        return self::ASSEMBLY_MODE_MAP[$assemblyMode] ?? null;
    }

    /**
     * Verifica si el Standard tiene campos legacy
     */
    private function hasLegacyFields(Standard $standard): bool
    {
        return $standard->work_table_id !== null
            || $standard->semi_auto_work_table_id !== null
            || $standard->machine_id !== null;
    }
}
