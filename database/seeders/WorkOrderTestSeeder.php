<?php

namespace Database\Seeders;

use App\Models\Part;
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

        // Usar partes existentes que ya tienen estándares y configuraciones
        $parts = $this->getExistingParts();

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

    private function getExistingParts(): array
    {
        // Usar partes que ya tienen estándares activos con configuraciones
        $partsWithStandards = Part::active()
            ->whereHas('standards', function ($q) {
                $q->where('active', true)->has('configurations');
            })
            ->take(5)
            ->get();

        if ($partsWithStandards->count() < 5) {
            $this->command->warn('WorkOrderTestSeeder: No hay suficientes partes con estándares. Se necesitan 5, hay ' . $partsWithStandards->count());
        }

        $parts = [];
        foreach ($partsWithStandards->values() as $i => $part) {
            $parts['part' . ($i + 1)] = $part;
        }

        return $parts;
    }

    // Prices already exist from PriceSeeder — no need to create them here

    /**
     * Escenario 1: Flujo exitoso completo
     * - PO con precio correcto
     * - PO aprobada automáticamente
     * - WO creada con estado "Open"
     */
    private function createScenario1_SuccessfulFlow(Part $part, User $user): void
    {
        $price = $part->prices()->where('active', true)->first();
        $unitPrice = $price ? $price->sample_price : 1.0000;

        $po = PurchaseOrder::firstOrCreate(
            ['po_number' => 'PO-TEST-001'],
            [
                'part_id' => $part->id,
                'po_date' => Carbon::now()->subDays(5),
                'due_date' => Carbon::now()->addDays(10),
                'quantity' => 500,
                'unit_price' => $unitPrice,
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
        $price = $part->prices()->where('active', true)->first();
        $wrongPrice = $price ? round($price->sample_price * 0.85, 4) : 1.7500;

        PurchaseOrder::firstOrCreate(
            ['po_number' => 'PO-TEST-002'],
            [
                'part_id' => $part->id,
                'po_date' => Carbon::now()->subDays(3),
                'due_date' => Carbon::now()->addDays(15),
                'quantity' => 500,
                'unit_price' => $wrongPrice,
                'status' => PurchaseOrder::STATUS_PENDING_CORRECTION,
                'comments' => 'Escenario 2: ERROR - Precio no coincide',
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
        $price = $part->prices()->where('active', true)->first();
        $unitPrice = $price ? $price->sample_price : 0.6500;

        PurchaseOrder::firstOrCreate(
            ['po_number' => 'PO-TEST-003'],
            [
                'part_id' => $part->id,
                'po_date' => Carbon::now()->subDays(7),
                'due_date' => Carbon::now()->addDays(5),
                'quantity' => 1000,
                'unit_price' => $unitPrice,
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
        $price = $part->prices()->where('active', true)->first();
        $unitPrice = $price ? $price->sample_price : 2.9000;

        $po = PurchaseOrder::firstOrCreate(
            ['po_number' => 'PO-TEST-004'],
            [
                'part_id' => $part->id,
                'po_date' => Carbon::now()->subDays(10),
                'due_date' => Carbon::now()->addDays(3),
                'quantity' => 1000,
                'unit_price' => $unitPrice,
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
        $price = $part->prices()->where('active', true)->first();
        $unitPrice = $price ? $price->sample_price : 0.9000;

        $po = PurchaseOrder::firstOrCreate(
            ['po_number' => 'PO-TEST-005'],
            [
                'part_id' => $part->id,
                'po_date' => Carbon::now()->subDays(20),
                'due_date' => Carbon::now()->subDays(5),
                'quantity' => 2000,
                'unit_price' => $unitPrice,
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
