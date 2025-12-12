<?php

namespace Database\Seeders;

use App\Models\Part;
use App\Models\Price;
use App\Models\PurchaseOrder;
use App\Models\StatusWO;
use App\Models\User;
use App\Models\WOStatusLog;
use App\Models\WorkOrder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seeder de pruebas para verificar el flujo completo del módulo de Work Orders.
 * 
 * Escenarios de prueba:
 * 1. PO con precio correcto → Aprobada → WO creada automáticamente (Flujo exitoso)
 * 2. PO con precio incorrecto → Pendiente de corrección (Error de precio)
 * 3. PO rechazada manualmente (Rechazo manual)
 * 4. WO en progreso con piezas parcialmente enviadas
 * 5. WO completada con todas las piezas enviadas
 */
class WorkOrderTestSeeder extends Seeder
{
    public function run(): void
    {
        // Asegurar que existan los estados de WO
        $this->ensureStatusesExist();

        // Crear usuario de prueba si no existe
        $user = User::firstOrCreate(
            ['email' => 'test@flexcon.com'],
            [
                'name' => 'Usuario de Prueba',
                'password' => bcrypt('password'),
            ]
        );

        // Crear partes de prueba
        $parts = $this->createTestParts();

        // Crear precios para las partes
        $this->createTestPrices($parts);

        // Escenario 1: PO con precio correcto → Aprobada → WO creada
        $this->createScenario1_SuccessfulFlow($parts['part1'], $user);

        // Escenario 2: PO con precio incorrecto → Pendiente de corrección
        $this->createScenario2_PriceError($parts['part2'], $user);

        // Escenario 3: PO rechazada manualmente
        $this->createScenario3_RejectedPO($parts['part3'], $user);

        // Escenario 4: WO en progreso con piezas parciales
        $this->createScenario4_InProgressWO($parts['part4'], $user);

        // Escenario 5: WO completada
        $this->createScenario5_CompletedWO($parts['part5'], $user);

        $this->command->info('✅ Datos de prueba creados exitosamente!');
        $this->command->info('');
        $this->command->info('Escenarios creados:');
        $this->command->info('1. PO-TEST-001: Flujo exitoso (PO aprobada + WO creada)');
        $this->command->info('2. PO-TEST-002: Error de precio (PO pendiente de corrección)');
        $this->command->info('3. PO-TEST-003: PO rechazada manualmente');
        $this->command->info('4. PO-TEST-004: WO en progreso (50% completado)');
        $this->command->info('5. PO-TEST-005: WO completada (100% enviado)');
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

    private function createTestParts(): array
    {
        $parts = [];

        for ($i = 1; $i <= 5; $i++) {
            $parts["part{$i}"] = Part::firstOrCreate(
                ['number' => "PART-TEST-{$i}"],
                [
                    'item_number' => "ITEM-{$i}",
                    'unit_of_measure' => 'PCS',
                    'active' => true,
                    'description' => "Parte de prueba #{$i} para verificación de flujo",
                    'notes' => "Parte creada por WorkOrderTestSeeder",
                ]
            );
        }

        return $parts;
    }

    private function createTestPrices(array $parts): void
    {
        // Precios para cada parte con diferentes tiers
        $priceData = [
            'part1' => ['unit' => 1.5000, 't1' => 1.5000, 't2' => 1.3000, 't3' => 1.1000, 't4' => 0.9000],
            'part2' => ['unit' => 2.0000, 't1' => 2.0000, 't2' => 1.8000, 't3' => 1.5000, 't4' => 1.2000],
            'part3' => ['unit' => 0.7500, 't1' => 0.7500, 't2' => 0.6500, 't3' => 0.5500, 't4' => 0.4500],
            'part4' => ['unit' => 3.2500, 't1' => 3.2500, 't2' => 2.9000, 't3' => 2.5000, 't4' => 2.0000],
            'part5' => ['unit' => 1.0000, 't1' => 1.0000, 't2' => 0.9000, 't3' => 0.8000, 't4' => 0.7000],
        ];

        foreach ($priceData as $key => $prices) {
            Price::firstOrCreate(
                ['part_id' => $parts[$key]->id, 'active' => true],
                [
                    'unit_price' => $prices['unit'],
                    'tier_1_999' => $prices['t1'],
                    'tier_1000_10999' => $prices['t2'],
                    'tier_11000_99999' => $prices['t3'],
                    'tier_100000_plus' => $prices['t4'],
                    'effective_date' => Carbon::now()->subMonth(),
                    'active' => true,
                    'comments' => 'Precio de prueba',
                ]
            );
        }
    }

    /**
     * Escenario 1: Flujo exitoso completo
     * - PO con precio correcto
     * - PO aprobada automáticamente
     * - WO creada con estado "Open"
     */
    private function createScenario1_SuccessfulFlow(Part $part, User $user): void
    {
        $po = PurchaseOrder::firstOrCreate(
            ['po_number' => 'PO-TEST-001'],
            [
                'part_id' => $part->id,
                'po_date' => Carbon::now()->subDays(5),
                'due_date' => Carbon::now()->addDays(10),
                'quantity' => 500, // Tier 1-999, precio: 1.5000
                'unit_price' => 1.5000, // Precio correcto
                'status' => PurchaseOrder::STATUS_APPROVED,
                'comments' => 'Escenario 1: PO aprobada con precio correcto',
            ]
        );

        // Crear WO asociada
        $openStatus = StatusWO::where('name', 'Open')->first();
        $wo = WorkOrder::firstOrCreate(
            ['wo_number' => 'WO-' . Carbon::now()->year . '-00001'],
            [
                'purchase_order_id' => $po->id,
                'status_id' => $openStatus->id,
                'sent_pieces' => 0,
                'scheduled_send_date' => $po->due_date,
                'opened_date' => Carbon::now()->subDays(4),
                'comments' => 'WO creada automáticamente desde PO aprobada',
            ]
        );

        // Log de creación
        WOStatusLog::firstOrCreate(
            ['work_order_id' => $wo->id, 'to_status_id' => $openStatus->id, 'from_status_id' => null],
            [
                'user_id' => $user->id,
                'comments' => 'Work Order creada desde PO-TEST-001',
            ]
        );
    }


    /**
     * Escenario 2: Error de precio
     * - PO con precio incorrecto (no coincide con el registrado)
     * - PO marcada como "Pendiente de corrección"
     * - NO se crea WO
     */
    private function createScenario2_PriceError(Part $part, User $user): void
    {
        PurchaseOrder::firstOrCreate(
            ['po_number' => 'PO-TEST-002'],
            [
                'part_id' => $part->id,
                'po_date' => Carbon::now()->subDays(3),
                'due_date' => Carbon::now()->addDays(15),
                'quantity' => 500, // Tier 1-999, precio esperado: 2.0000
                'unit_price' => 1.7500, // Precio INCORRECTO (debería ser 2.0000)
                'status' => PurchaseOrder::STATUS_PENDING_CORRECTION,
                'comments' => 'Escenario 2: ERROR - Precio no coincide. Precio en PO: $1.7500, Precio esperado: $2.0000',
            ]
        );
        // NO se crea WO porque el precio es incorrecto
    }

    /**
     * Escenario 3: PO rechazada manualmente
     * - PO rechazada por el usuario
     * - NO se crea WO
     */
    private function createScenario3_RejectedPO(Part $part, User $user): void
    {
        PurchaseOrder::firstOrCreate(
            ['po_number' => 'PO-TEST-003'],
            [
                'part_id' => $part->id,
                'po_date' => Carbon::now()->subDays(7),
                'due_date' => Carbon::now()->addDays(5),
                'quantity' => 1000, // Tier 1000-10999
                'unit_price' => 0.6500, // Precio correcto para tier
                'status' => PurchaseOrder::STATUS_REJECTED,
                'comments' => 'Escenario 3: PO rechazada manualmente por el usuario - Cliente canceló el pedido',
            ]
        );
        // NO se crea WO porque la PO fue rechazada
    }

    /**
     * Escenario 4: WO en progreso
     * - PO aprobada
     * - WO creada y en progreso
     * - 50% de piezas enviadas
     */
    private function createScenario4_InProgressWO(Part $part, User $user): void
    {
        $po = PurchaseOrder::firstOrCreate(
            ['po_number' => 'PO-TEST-004'],
            [
                'part_id' => $part->id,
                'po_date' => Carbon::now()->subDays(10),
                'due_date' => Carbon::now()->addDays(3),
                'quantity' => 1000, // Cantidad total
                'unit_price' => 2.9000, // Precio correcto para tier 1000-10999
                'status' => PurchaseOrder::STATUS_APPROVED,
                'comments' => 'Escenario 4: PO aprobada, WO en progreso',
            ]
        );

        $openStatus = StatusWO::where('name', 'Open')->first();
        $inProgressStatus = StatusWO::where('name', 'In Progress')->first();

        $wo = WorkOrder::firstOrCreate(
            ['wo_number' => 'WO-' . Carbon::now()->year . '-00004'],
            [
                'purchase_order_id' => $po->id,
                'status_id' => $inProgressStatus->id,
                'sent_pieces' => 500, // 50% enviado
                'scheduled_send_date' => $po->due_date,
                'opened_date' => Carbon::now()->subDays(9),
                'eq' => 'EQ-001',
                'pr' => 'Línea A - 5 operadores',
                'comments' => 'WO en progreso - 50% completado',
            ]
        );

        // Log de creación
        WOStatusLog::firstOrCreate(
            ['work_order_id' => $wo->id, 'to_status_id' => $openStatus->id, 'from_status_id' => null],
            [
                'user_id' => $user->id,
                'comments' => 'Work Order creada desde PO-TEST-004',
            ]
        );

        // Log de cambio a In Progress
        WOStatusLog::firstOrCreate(
            ['work_order_id' => $wo->id, 'from_status_id' => $openStatus->id, 'to_status_id' => $inProgressStatus->id],
            [
                'user_id' => $user->id,
                'comments' => 'Iniciando producción - Primer lote de 500 piezas enviado',
            ]
        );
    }

    /**
     * Escenario 5: WO completada
     * - PO aprobada
     * - WO completada al 100%
     * - Todas las piezas enviadas
     */
    private function createScenario5_CompletedWO(Part $part, User $user): void
    {
        $po = PurchaseOrder::firstOrCreate(
            ['po_number' => 'PO-TEST-005'],
            [
                'part_id' => $part->id,
                'po_date' => Carbon::now()->subDays(20),
                'due_date' => Carbon::now()->subDays(5),
                'quantity' => 2000,
                'unit_price' => 0.9000, // Precio correcto para tier 1000-10999
                'status' => PurchaseOrder::STATUS_APPROVED,
                'comments' => 'Escenario 5: PO aprobada, WO completada',
            ]
        );

        $openStatus = StatusWO::where('name', 'Open')->first();
        $inProgressStatus = StatusWO::where('name', 'In Progress')->first();
        $completedStatus = StatusWO::where('name', 'Completed')->first();

        $wo = WorkOrder::firstOrCreate(
            ['wo_number' => 'WO-' . Carbon::now()->year . '-00005'],
            [
                'purchase_order_id' => $po->id,
                'status_id' => $completedStatus->id,
                'sent_pieces' => 2000, // 100% enviado
                'scheduled_send_date' => $po->due_date,
                'actual_send_date' => Carbon::now()->subDays(6),
                'opened_date' => Carbon::now()->subDays(18),
                'eq' => 'EQ-002',
                'pr' => 'Línea B - 8 operadores',
                'comments' => 'WO completada exitosamente - Entregada antes de fecha límite',
            ]
        );

        // Log de creación
        WOStatusLog::firstOrCreate(
            ['work_order_id' => $wo->id, 'to_status_id' => $openStatus->id, 'from_status_id' => null],
            [
                'user_id' => $user->id,
                'comments' => 'Work Order creada desde PO-TEST-005',
            ]
        );

        // Log de cambio a In Progress
        WOStatusLog::firstOrCreate(
            ['work_order_id' => $wo->id, 'from_status_id' => $openStatus->id, 'to_status_id' => $inProgressStatus->id],
            [
                'user_id' => $user->id,
                'comments' => 'Iniciando producción',
            ]
        );

        // Log de cambio a Completed
        WOStatusLog::firstOrCreate(
            ['work_order_id' => $wo->id, 'from_status_id' => $inProgressStatus->id, 'to_status_id' => $completedStatus->id],
            [
                'user_id' => $user->id,
                'comments' => 'Producción completada - 2000 piezas enviadas',
            ]
        );
    }
}
