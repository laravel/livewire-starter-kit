<?php

namespace Database\Seeders;

use App\Models\Standard;
use App\Models\StandardConfiguration;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Seeder para migrar datos de standards existentes a standard_configurations
 *
 * Este seeder realiza la migracion de datos desde la estructura legacy
 * (persons_1, persons_2, persons_3, units_per_hour en tabla standards)
 * hacia la nueva estructura normalizada (tabla standard_configurations).
 *
 * Estrategia de migracion:
 * 1. Lee cada standard no migrado
 * 2. Determina el tipo de estacion de trabajo
 * 3. Crea configuraciones basadas en persons_1, persons_2, persons_3
 * 4. Marca configuraciones que requieren revision manual
 * 5. Actualiza el flag is_migrated en el standard
 *
 * IMPORTANTE:
 * - Este seeder es idempotente: puede ejecutarse multiples veces
 * - Solo procesa standards con is_migrated = false
 * - Usa transacciones para garantizar integridad
 * - Registra errores para revision posterior
 *
 * Referencia: Spec 06 - Plan de Migracion de Datos
 *
 * @example php artisan db:seed --class=MigrateStandardsToConfigurationsSeeder
 */
class MigrateStandardsToConfigurationsSeeder extends Seeder
{
    /**
     * Contadores para reporte final
     */
    protected int $migratedCount = 0;
    protected int $skippedCount = 0;
    protected int $configurationsCreated = 0;
    protected array $errors = [];
    protected array $warnings = [];

    /**
     * Ejecuta la migracion de datos
     */
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('========================================');
        $this->command->info('  MIGRACION DE STANDARDS A CONFIGURATIONS');
        $this->command->info('========================================');
        $this->command->info('');

        // Obtener standards pendientes de migracion
        $standards = Standard::whereNull('deleted_at')
                            ->where('is_migrated', false)
                            ->get();

        $total = $standards->count();

        if ($total === 0) {
            $this->command->info('No hay standards pendientes de migracion.');
            $this->command->info('Todos los standards ya estan migrados o no existen.');
            return;
        }

        $this->command->info("Standards a migrar: {$total}");
        $this->command->info('');

        // Barra de progreso
        $progressBar = $this->command->getOutput()->createProgressBar($total);
        $progressBar->start();

        // Procesar cada standard en una transaccion
        DB::transaction(function () use ($standards, $progressBar) {
            foreach ($standards as $standard) {
                try {
                    $this->migrateStandard($standard);
                    $this->migratedCount++;
                } catch (\Exception $e) {
                    $this->errors[] = [
                        'standard_id' => $standard->id,
                        'part_id' => $standard->part_id,
                        'error' => $e->getMessage(),
                    ];
                    Log::error("Error migrando standard #{$standard->id}: {$e->getMessage()}");
                }

                $progressBar->advance();
            }
        });

        $progressBar->finish();
        $this->command->info('');
        $this->command->info('');

        // Mostrar resumen
        $this->showSummary();
    }

    /**
     * Migra un standard individual a configuraciones
     *
     * @param Standard $standard
     * @throws \Exception Si hay error en la migracion
     */
    protected function migrateStandard(Standard $standard): void
    {
        // Determinar tipo de estacion de trabajo
        $workstationType = $this->determineWorkstationType($standard);
        $workstationId = $this->getWorkstationId($standard);

        // Recopilar configuraciones a crear
        $configurations = [];
        $personsValues = $this->getUniquePersonsValues($standard);

        if (empty($personsValues)) {
            // Si no hay valores de persons, crear configuracion por defecto con 1 persona
            $configurations[] = $this->buildConfiguration(
                $standard,
                $workstationType,
                $workstationId,
                1,
                true, // is_default
                'Configuracion por defecto creada durante migracion (sin valores persons_* definidos)'
            );
        } else {
            // Crear configuracion para cada valor de persons
            $isFirst = true;
            foreach ($personsValues as $source => $persons) {
                $needsReview = !$isFirst; // Las configuraciones secundarias necesitan revision de units_per_hour

                $note = $isFirst
                    ? "Migrado automaticamente desde {$source}"
                    : "Migrado desde {$source} - REQUIERE REVISION de units_per_hour (valor heredado del original)";

                $configurations[] = $this->buildConfiguration(
                    $standard,
                    $workstationType,
                    $workstationId,
                    $persons,
                    $isFirst, // Primera configuracion es default
                    $note
                );

                if ($needsReview) {
                    $this->warnings[] = [
                        'standard_id' => $standard->id,
                        'part_id' => $standard->part_id,
                        'message' => "Configuracion para {$persons} persona(s) requiere revision de units_per_hour",
                    ];
                }

                $isFirst = false;
            }
        }

        // Insertar configuraciones
        if (!empty($configurations)) {
            // Verificar que no existan configuraciones duplicadas
            foreach ($configurations as $config) {
                $exists = StandardConfiguration::where('standard_id', $config['standard_id'])
                    ->where('workstation_type', $config['workstation_type'])
                    ->where('persons_required', $config['persons_required'])
                    ->exists();

                if (!$exists) {
                    StandardConfiguration::create($config);
                    $this->configurationsCreated++;
                } else {
                    $this->skippedCount++;
                }
            }
        }

        // Marcar standard como migrado
        $standard->update(['is_migrated' => true]);
    }

    /**
     * Construye un array de configuracion
     */
    protected function buildConfiguration(
        Standard $standard,
        string $workstationType,
        ?int $workstationId,
        int $personsRequired,
        bool $isDefault,
        string $notes
    ): array {
        return [
            'standard_id' => $standard->id,
            'workstation_type' => $workstationType,
            'workstation_id' => $workstationId,
            'persons_required' => min($personsRequired, StandardConfiguration::MAX_PERSONS),
            'units_per_hour' => $standard->units_per_hour ?? 1,
            'is_default' => $isDefault,
            'notes' => $notes,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Determina el tipo de estacion de trabajo del standard
     *
     * @param Standard $standard
     * @return string Tipo de estacion: manual, semi_automatic, machine
     */
    protected function determineWorkstationType(Standard $standard): string
    {
        if ($standard->work_table_id) {
            return StandardConfiguration::TYPE_MANUAL;
        }

        if ($standard->semi_auto_work_table_id) {
            return StandardConfiguration::TYPE_SEMI_AUTOMATIC;
        }

        if ($standard->machine_id) {
            return StandardConfiguration::TYPE_MACHINE;
        }

        // Default a manual si no hay estacion asignada
        return StandardConfiguration::TYPE_MANUAL;
    }

    /**
     * Obtiene el ID de la estacion de trabajo del standard
     *
     * @param Standard $standard
     * @return int|null
     */
    protected function getWorkstationId(Standard $standard): ?int
    {
        return $standard->work_table_id
            ?? $standard->semi_auto_work_table_id
            ?? $standard->machine_id;
    }

    /**
     * Obtiene valores unicos de persons_1, persons_2, persons_3
     *
     * Filtra valores nulos, ceros y duplicados.
     * Retorna array asociativo con la fuente del valor.
     *
     * @param Standard $standard
     * @return array<string, int> ['persons_1' => 1, 'persons_2' => 2, ...]
     */
    protected function getUniquePersonsValues(Standard $standard): array
    {
        $values = [];
        $seen = [];

        // persons_1
        if ($standard->persons_1 && $standard->persons_1 > 0) {
            $p1 = (int) $standard->persons_1;
            if (!in_array($p1, $seen, true)) {
                $values['persons_1'] = $p1;
                $seen[] = $p1;
            }
        }

        // persons_2
        if ($standard->persons_2 && $standard->persons_2 > 0) {
            $p2 = (int) $standard->persons_2;
            if (!in_array($p2, $seen, true)) {
                $values['persons_2'] = $p2;
                $seen[] = $p2;
            }
        }

        // persons_3
        if ($standard->persons_3 && $standard->persons_3 > 0) {
            $p3 = (int) $standard->persons_3;
            if (!in_array($p3, $seen, true)) {
                $values['persons_3'] = $p3;
                $seen[] = $p3;
            }
        }

        return $values;
    }

    /**
     * Muestra resumen de la migracion
     */
    protected function showSummary(): void
    {
        $this->command->info('========================================');
        $this->command->info('           RESUMEN DE MIGRACION');
        $this->command->info('========================================');
        $this->command->info('');
        $this->command->info("Standards migrados exitosamente: {$this->migratedCount}");
        $this->command->info("Configuraciones creadas: {$this->configurationsCreated}");
        $this->command->info("Configuraciones omitidas (duplicadas): {$this->skippedCount}");
        $this->command->info('');

        // Mostrar advertencias
        if (!empty($this->warnings)) {
            $this->command->warn("Configuraciones que REQUIEREN REVISION: " . count($this->warnings));
            $this->command->warn('');
            $this->command->warn('Estas configuraciones fueron creadas con el mismo units_per_hour');
            $this->command->warn('del standard original. Deben ser revisadas manualmente para');
            $this->command->warn('ajustar la productividad correcta segun cantidad de personas.');
            $this->command->warn('');

            // Mostrar primeras 10 advertencias
            $displayed = 0;
            foreach ($this->warnings as $warning) {
                if ($displayed >= 10) {
                    $remaining = count($this->warnings) - 10;
                    $this->command->warn("... y {$remaining} mas.");
                    break;
                }
                $this->command->warn(
                    "  - Standard #{$warning['standard_id']}: {$warning['message']}"
                );
                $displayed++;
            }
            $this->command->info('');
        }

        // Mostrar errores
        if (!empty($this->errors)) {
            $this->command->error("Errores encontrados: " . count($this->errors));
            $this->command->error('');
            foreach ($this->errors as $error) {
                $this->command->error(
                    "  - Standard #{$error['standard_id']} (Part #{$error['part_id']}): {$error['error']}"
                );
            }
            $this->command->info('');
        }

        // Consulta para verificar
        $this->command->info('========================================');
        $this->command->info('      CONSULTAS DE VERIFICACION');
        $this->command->info('========================================');
        $this->command->info('');
        $this->command->info('Ejecute estas consultas SQL para verificar la migracion:');
        $this->command->info('');
        $this->command->line('-- Standards migrados vs total');
        $this->command->line('SELECT');
        $this->command->line('    COUNT(*) as total,');
        $this->command->line('    SUM(CASE WHEN is_migrated = 1 THEN 1 ELSE 0 END) as migrados,');
        $this->command->line('    SUM(CASE WHEN is_migrated = 0 THEN 1 ELSE 0 END) as pendientes');
        $this->command->line('FROM standards WHERE deleted_at IS NULL;');
        $this->command->info('');
        $this->command->line('-- Configuraciones que requieren revision');
        $this->command->line('SELECT sc.*, s.part_id');
        $this->command->line('FROM standard_configurations sc');
        $this->command->line('JOIN standards s ON sc.standard_id = s.id');
        $this->command->line("WHERE sc.notes LIKE '%REQUIERE REVISION%';");
        $this->command->info('');
    }
}
