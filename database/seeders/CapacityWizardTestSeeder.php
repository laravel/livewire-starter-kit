<?php

namespace Database\Seeders;

use App\Models\Part;
use App\Models\Price;
use App\Models\PurchaseOrder;
use App\Models\Standard;
use App\Models\StandardConfiguration;
use App\Models\StatusWO;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\Shift;
use App\Models\Table;
use App\Models\Machine;
use App\Models\Semi_Automatic;
use App\Models\WOStatusLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seeder de pruebas para el flujo completo del Capacity Wizard.
 * 
 * Este seeder crea todos los datos necesarios para probar:
 * 1. Selección de múltiples POs desde el modal
 * 2. Cálculo de horas con diferentes configuraciones
 * 3. Generación de Lista Preliminar
 * 4. Flujo de aprobación por departamentos
 * 
 * Ejecutar: php artisan db:seed --class=CapacityWizardTestSeeder
 */
class CapacityWizardTestSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Iniciando CapacityWizardTestSeeder...');
        $this->command->newLine();

        // 1. Asegurar que existan los estados de WO
        $this->ensureStatusesExist();
        $this->command->info('✅ Estados de WO verificados');

        // 2. Asegurar que existan turnos
        $this->ensureShiftsExist();
        $this->command->info('✅ Turnos verificados');

        // 3. Asegurar que existan estaciones de trabajo
        $tables = $this->ensureWorkstationsExist();
        $this->command->info('✅ Estaciones de trabajo verificadas');

        // 4. Crear partes de prueba con estándares y configuraciones
        $parts = $this->createTestPartsWithStandards($tables);
        $this->command->info('✅ Partes con estándares creadas');

        // 5. Crear precios para las partes
        $this->createTestPrices($parts);
        $this->command->info('✅ Precios creados');

        // 6. Crear empleados de prueba
        $this->createTestEmployees();
        $this->command->info('✅ Empleados creados');

        // 7. Crear POs aprobadas con WOs abiertas (listas para el wizard)
        $this->createOpenWorkOrders($parts);
        $this->command->info('✅ POs y WOs abiertas creadas');

        $this->command->newLine();
        $this->command->info('🎉 ¡CapacityWizardTestSeeder completado!');
        $this->command->newLine();
        $this->printTestingSummary();
    }

    private function ensureStatusesExist(): void
    {
        $statuses = [
            ['name' => 'Open', 'color' => '#3B82F6', 'comments' => 'Work order abierta'],
            ['name' => 'In Progress', 'color' => '#F59E0B', 'comments' => 'Work order en progreso'],
            ['name' => 'Completed', 'color' => '#10B981', 'comments' => 'Work order completada'],
            ['name' => 'Cancelled', 'color' => '#EF4444', 'comments' => 'Work order cancelada'],
            ['name' => 'On Hold', 'color' => '#6B7280', 'comments' => 'Work order en espera'],
        ];

        foreach ($statuses as $status) {
            StatusWO::firstOrCreate(['name' => $status['name']], $status);
        }
    }

    private function ensureShiftsExist(): void
    {
        $shifts = [
            [
                'name' => 'Turno A - Mañana',
                'start_time' => '06:00:00',
                'end_time' => '14:00:00',
                'comments' => 'Turno matutino 6am-2pm',
                'active' => true,
            ],
            [
                'name' => 'Turno B - Tarde',
                'start_time' => '14:00:00',
                'end_time' => '22:00:00',
                'comments' => 'Turno vespertino 2pm-10pm',
                'active' => true,
            ],
            [
                'name' => 'Turno C - Noche',
                'start_time' => '22:00:00',
                'end_time' => '06:00:00',
                'comments' => 'Turno nocturno 10pm-6am',
                'active' => true,
            ],
        ];

        foreach ($shifts as $shift) {
            Shift::firstOrCreate(['name' => $shift['name']], $shift);
        }
    }

    private function ensureWorkstationsExist(): array
    {
        // Intentar obtener mesas existentes
        $table1 = Table::first();
        $table2 = Table::skip(1)->first();

        // Si no hay mesas, crear unas nuevas con number en lugar de name
        if (!$table1) {
            $table1 = Table::create([
                'number' => 'MESA-TEST-001',
                'employees' => 3,
                'active' => true,
                'comments' => 'Mesa de trabajo para pruebas',
            ]);
        }

        if (!$table2) {
            $table2 = Table::create([
                'number' => 'MESA-TEST-002',
                'employees' => 3,
                'active' => true,
                'comments' => 'Mesa de trabajo para pruebas 2',
            ]);
        }

        return ['table1' => $table1, 'table2' => $table2];
    }

    private function createTestPartsWithStandards(array $tables): array
    {
        $partsData = [
            [
                'number' => 'WIZARD-001',
                'description' => 'Parte de prueba Wizard #1 - Conector USB',
                'configs' => [
                    ['type' => 'manual', 'persons' => 1, 'uph' => 120, 'default' => true],
                    ['type' => 'manual', 'persons' => 2, 'uph' => 200],
                    ['type' => 'manual', 'persons' => 3, 'uph' => 280],
                ],
            ],
            [
                'number' => 'WIZARD-002',
                'description' => 'Parte de prueba Wizard #2 - Cable HDMI',
                'configs' => [
                    ['type' => 'manual', 'persons' => 1, 'uph' => 80, 'default' => true],
                    ['type' => 'manual', 'persons' => 2, 'uph' => 150],
                    ['type' => 'semi_automatic', 'persons' => 1, 'uph' => 250],
                    ['type' => 'semi_automatic', 'persons' => 2, 'uph' => 400],
                ],
            ],
            [
                'number' => 'WIZARD-003',
                'description' => 'Parte de prueba Wizard #3 - Adaptador',
                'configs' => [
                    ['type' => 'manual', 'persons' => 1, 'uph' => 100],
                    ['type' => 'manual', 'persons' => 2, 'uph' => 180, 'default' => true],
                    ['type' => 'machine', 'persons' => 1, 'uph' => 500],
                ],
            ],
            [
                'number' => 'WIZARD-004',
                'description' => 'Parte de prueba Wizard #4 - Extensión',
                'configs' => [
                    ['type' => 'manual', 'persons' => 2, 'uph' => 150, 'default' => true],
                    ['type' => 'manual', 'persons' => 3, 'uph' => 220],
                ],
            ],
            [
                'number' => 'WIZARD-005',
                'description' => 'Parte de prueba Wizard #5 - Splitter',
                'configs' => [
                    ['type' => 'manual', 'persons' => 1, 'uph' => 90, 'default' => true],
                    ['type' => 'semi_automatic', 'persons' => 1, 'uph' => 300],
                    ['type' => 'machine', 'persons' => 1, 'uph' => 600],
                ],
            ],
            [
                'number' => 'WIZARD-006',
                'description' => 'Parte de prueba Wizard #6 - Hub USB',
                'configs' => [
                    ['type' => 'manual', 'persons' => 1, 'uph' => 60],
                    ['type' => 'manual', 'persons' => 2, 'uph' => 110, 'default' => true],
                    ['type' => 'manual', 'persons' => 3, 'uph' => 160],
                ],
            ],
        ];

        $parts = [];

        foreach ($partsData as $partData) {
            // Crear o encontrar la parte
            $part = Part::firstOrCreate(
                ['number' => $partData['number']],
                [
                    'item_number' => 'ITEM-' . $partData['number'],
                    'unit_of_measure' => 'PCS',
                    'active' => true,
                    'description' => $partData['description'],
                    'notes' => 'Creado por CapacityWizardTestSeeder',
                ]
            );

            // Verificar si ya tiene estándar activo con configuraciones
            $existingStandard = $part->standards()
                ->where('active', true)
                ->whereHas('configurations')
                ->first();

            if ($existingStandard) {
                $parts[$partData['number']] = $part;
                continue;
            }

            // Crear nuevo estándar
            $standard = Standard::create([
                'part_id' => $part->id,
                'work_table_id' => $tables['table1']->id ?? null,
                'persons_1' => 1,
                'persons_2' => 2,
                'persons_3' => 3,
                'units_per_hour' => $partData['configs'][0]['uph'],
                'effective_date' => now()->subDays(30),
                'active' => true,
                'is_migrated' => true,
                'description' => "Estándar para {$partData['number']}",
            ]);

            // Crear configuraciones
            foreach ($partData['configs'] as $config) {
                StandardConfiguration::create([
                    'standard_id' => $standard->id,
                    'workstation_type' => $config['type'],
                    'workstation_id' => null,
                    'persons_required' => $config['persons'],
                    'units_per_hour' => $config['uph'],
                    'is_default' => $config['default'] ?? false,
                    'notes' => "{$config['type']} - {$config['persons']} persona(s)",
                ]);
            }

            $parts[$partData['number']] = $part;
        }

        return $parts;
    }

    private function createTestPrices(array $parts): void
    {
        $basePrice = 0.50;

        foreach ($parts as $key => $part) {
            // Verificar si ya tiene precio activo
            if (Price::where('part_id', $part->id)->where('active', true)->exists()) {
                continue;
            }

            $price = Price::create([
                'part_id' => $part->id,
                'sample_price' => $basePrice,
                'workstation_type' => 'table',
                'effective_date' => Carbon::now()->subMonth(),
                'active' => true,
                'comments' => 'Precio de prueba para Capacity Wizard',
            ]);

            // Crear tiers
            $price->tiers()->createMany([
                ['min_quantity' => 1, 'max_quantity' => 999, 'tier_price' => $basePrice],
                ['min_quantity' => 1000, 'max_quantity' => 9999, 'tier_price' => $basePrice * 0.9],
                ['min_quantity' => 10000, 'max_quantity' => null, 'tier_price' => $basePrice * 0.8],
            ]);

            $basePrice += 0.25;
        }
    }

    private function createTestEmployees(): void
    {
        $shifts = Shift::all();
        
        if ($shifts->isEmpty()) {
            return;
        }

        $employeeNumber = 1000;

        foreach ($shifts as $shift) {
            // Crear 5 empleados por turno
            for ($i = 1; $i <= 5; $i++) {
                $employeeNumber++;
                
                $user = User::firstOrCreate(
                    ['email' => "employee{$employeeNumber}@flexcon.test"],
                    [
                        'name' => "Empleado {$employeeNumber}",
                        'last_name' => "Prueba",
                        'employee_number' => "EMP-{$employeeNumber}",
                        'password' => bcrypt('password'),
                        'shift_id' => $shift->id,
                        'position' => 'Operador',
                        'active' => true,
                    ]
                );

                // Asignar rol de empleado si existe
                if (!$user->hasRole('employee') && method_exists($user, 'assignRole')) {
                    try {
                        $user->assignRole('employee');
                    } catch (\Exception $e) {
                        // El rol no existe, ignorar
                    }
                }
            }
        }
    }

    private function createOpenWorkOrders(array $parts): void
    {
        $openStatus = StatusWO::where('name', 'Open')->first();
        
        if (!$openStatus) {
            $this->command->warn('⚠️ No se encontró el status "Open"');
            return;
        }

        $poData = [
            [
                'po_number' => 'PO-WIZARD-001',
                'part_key' => 'WIZARD-001',
                'quantity' => 500,
                'unit_price' => 0.50,
            ],
            [
                'po_number' => 'PO-WIZARD-002',
                'part_key' => 'WIZARD-002',
                'quantity' => 1000,
                'unit_price' => 0.75,
            ],
            [
                'po_number' => 'PO-WIZARD-003',
                'part_key' => 'WIZARD-003',
                'quantity' => 2500,
                'unit_price' => 1.00,
            ],
            [
                'po_number' => 'PO-WIZARD-004',
                'part_key' => 'WIZARD-004',
                'quantity' => 800,
                'unit_price' => 1.25,
            ],
            [
                'po_number' => 'PO-WIZARD-005',
                'part_key' => 'WIZARD-005',
                'quantity' => 3000,
                'unit_price' => 1.50,
            ],
            [
                'po_number' => 'PO-WIZARD-006',
                'part_key' => 'WIZARD-006',
                'quantity' => 1500,
                'unit_price' => 1.75,
            ],
        ];

        $year = Carbon::now()->year;
        $woCounter = WorkOrder::whereYear('opened_date', $year)->count() + 100;

        foreach ($poData as $data) {
            if (!isset($parts[$data['part_key']])) {
                continue;
            }

            $part = $parts[$data['part_key']];

            // Verificar si el PO ya existe
            $existingPO = PurchaseOrder::where('po_number', $data['po_number'])->first();
            
            if ($existingPO) {
                // Si ya tiene WO abierta, continuar
                if ($existingPO->workOrder && $existingPO->workOrder->status->name === 'Open') {
                    continue;
                }
            }

            // Crear PO
            $po = PurchaseOrder::firstOrCreate(
                ['po_number' => $data['po_number']],
                [
                    'part_id' => $part->id,
                    'po_date' => Carbon::now()->subDays(rand(1, 10)),
                    'due_date' => Carbon::now()->addDays(rand(10, 30)),
                    'quantity' => $data['quantity'],
                    'unit_price' => $data['unit_price'],
                    'status' => PurchaseOrder::STATUS_APPROVED,
                    'comments' => 'PO de prueba para Capacity Wizard - Lista para seleccionar',
                ]
            );

            // Verificar si ya tiene WO
            if ($po->workOrder) {
                continue;
            }

            // Crear WO con status "Open"
            $woCounter++;
            $woNumber = "WO-{$year}-" . str_pad($woCounter, 5, '0', STR_PAD_LEFT);

            $wo = WorkOrder::create([
                'wo_number' => $woNumber,
                'purchase_order_id' => $po->id,
                'status_id' => $openStatus->id,
                'sent_pieces' => 0,
                'scheduled_send_date' => $po->due_date,
                'opened_date' => Carbon::now(),
                'comments' => 'WO creada para pruebas del Capacity Wizard',
            ]);

            // Log de creación
            WOStatusLog::create([
                'work_order_id' => $wo->id,
                'from_status_id' => null,
                'to_status_id' => $openStatus->id,
                'user_id' => User::first()->id ?? 1,
                'comments' => 'Work Order creada por CapacityWizardTestSeeder',
            ]);
        }
    }

    private function printTestingSummary(): void
    {
        $this->command->info('📋 RESUMEN DE DATOS CREADOS:');
        $this->command->newLine();

        // Partes con estándares
        $partsWithConfigs = Part::whereHas('standards', function($q) {
            $q->where('active', true)->has('configurations');
        })->count();
        $this->command->line("   • Partes con configuraciones activas: {$partsWithConfigs}");

        // POs abiertas
        $openPOs = PurchaseOrder::where('status', PurchaseOrder::STATUS_APPROVED)
            ->whereHas('workOrder.status', function($q) {
                $q->where('name', 'Open');
            })->count();
        $this->command->line("   • POs con WO abierta (listas para wizard): {$openPOs}");

        // Turnos
        $shifts = Shift::where('active', true)->count();
        $this->command->line("   • Turnos activos: {$shifts}");

        // Empleados
        $employees = User::whereNotNull('shift_id')->count();
        $this->command->line("   • Empleados asignados a turnos: {$employees}");

        $this->command->newLine();
        $this->command->info('🧪 PARA PROBAR EL FLUJO:');
        $this->command->newLine();
        $this->command->line('   1. Ir a: /admin/capacity-calculator');
        $this->command->line('   2. En Step 1: Seleccionar turnos (verás los empleados cargados)');
        $this->command->line('   3. En Step 2: Click en "Cargar desde POs"');
        $this->command->line('   4. Seleccionar múltiples POs (PO-WIZARD-001 a 006)');
        $this->command->line('   5. Elegir configuración para cada PO (opcional)');
        $this->command->line('   6. Click "Agregar Seleccionados"');
        $this->command->line('   7. En Step 3: Agregar lotes/viajeros (opcional)');
        $this->command->line('   8. Click "Generar Lista Preliminar"');
        $this->command->line('   9. Ir a /admin/sent-lists para ver la lista creada');
        $this->command->line('  10. Aprobar por cada departamento');
        $this->command->newLine();

        // Listar POs disponibles
        $this->command->info('📦 POs DISPONIBLES PARA EL WIZARD:');
        $this->command->newLine();
        
        $pos = PurchaseOrder::with(['part', 'workOrder.status'])
            ->where('status', PurchaseOrder::STATUS_APPROVED)
            ->whereHas('workOrder.status', function($q) {
                $q->where('name', 'Open');
            })
            ->get();

        foreach ($pos as $po) {
            $this->command->line("   • {$po->po_number} | {$po->part->number} | Qty: {$po->quantity} | WO: {$po->workOrder->wo_number}");
        }

        $this->command->newLine();
    }
}
