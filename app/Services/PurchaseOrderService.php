<?php

namespace App\Services;

use App\Models\Price;
use App\Models\PurchaseOrder;
use App\Models\StatusWO;
use App\Models\WOStatusLog;
use App\Models\WorkOrder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseOrderService
{
    /**
     * Validate the price of a purchase order against the registered price.
     * 
     * @param PurchaseOrder $purchaseOrder
     * @return array{valid: bool, expected_price: float|null, message: string}
     */
    public function validatePrice(PurchaseOrder $purchaseOrder): array
    {
        // Use POPriceDetectionService to get the correct price based on workstation type
        $priceDetectionService = app(POPriceDetectionService::class);
        $detection = $priceDetectionService->detectPrice($purchaseOrder);

        if (!$detection->found) {
            return [
                'valid' => false,
                'expected_price' => null,
                'message' => $detection->error ?? 'No se pudo detectar el precio correcto.',
            ];
        }

        // Get the expected price based on quantity tier
        $expectedPrice = $detection->price->getPriceForQuantity($purchaseOrder->quantity);

        if ($expectedPrice === null) {
            return [
                'valid' => false,
                'expected_price' => null,
                'message' => 'No se pudo determinar el precio para la cantidad especificada.',
            ];
        }

        // Compare prices (using tolerance for decimal precision)
        $poPrice = (float) $purchaseOrder->unit_price;
        $tolerance = 0.0001; // Allow small floating point differences

        if (abs($poPrice - $expectedPrice) <= $tolerance) {
            return [
                'valid' => true,
                'expected_price' => $expectedPrice,
                'message' => 'El precio es válido.',
            ];
        }

        $typeLabel = Price::WORKSTATION_TYPES[$detection->workstationType] ?? $detection->workstationType;
        return [
            'valid' => false,
            'expected_price' => $expectedPrice,
            'message' => sprintf(
                'El precio no coincide. Precio en PO: $%.4f, Precio esperado: $%.4f (Tipo de estación: %s)',
                $poPrice,
                $expectedPrice,
                $typeLabel
            ),
        ];
    }

    /**
     * Mark a purchase order as pending price correction.
     * 
     * @param PurchaseOrder $purchaseOrder
     * @param string $reason
     * @return PurchaseOrder
     */
    public function markAsPendingCorrection(PurchaseOrder $purchaseOrder, string $reason): PurchaseOrder
    {
        $purchaseOrder->update([
            'status' => PurchaseOrder::STATUS_PENDING_CORRECTION,
            'comments' => $reason,
        ]);

        return $purchaseOrder;
    }

    /**
     * Approve a purchase order after price validation.
     * 
     * @param PurchaseOrder $purchaseOrder
     * @return array{success: bool, message: string, purchase_order: PurchaseOrder}
     */
    public function approve(PurchaseOrder $purchaseOrder): array
    {
        // First validate the price
        $validation = $this->validatePrice($purchaseOrder);

        if (!$validation['valid']) {
            // Mark as pending correction
            $this->markAsPendingCorrection($purchaseOrder, $validation['message']);

            return [
                'success' => false,
                'message' => $validation['message'],
                'purchase_order' => $purchaseOrder->fresh(),
            ];
        }

        // Price is valid, approve the PO
        $purchaseOrder->update([
            'status' => PurchaseOrder::STATUS_APPROVED,
        ]);

        return [
            'success' => true,
            'message' => 'Orden de compra aprobada correctamente.',
            'purchase_order' => $purchaseOrder->fresh(),
        ];
    }

    /**
     * Reject a purchase order.
     * 
     * @param PurchaseOrder $purchaseOrder
     * @param string|null $reason
     * @return PurchaseOrder
     */
    public function reject(PurchaseOrder $purchaseOrder, ?string $reason = null): PurchaseOrder
    {
        $purchaseOrder->update([
            'status' => PurchaseOrder::STATUS_REJECTED,
            'comments' => $reason ?? $purchaseOrder->comments,
        ]);

        return $purchaseOrder;
    }

    /**
     * Get the expected price for a purchase order based on quantity.
     * 
     * @param int $partId
     * @param int $quantity
     * @return float|null
     */
    public function getExpectedPrice(int $partId, int $quantity): ?float
    {
        // Create a temporary PO to use POPriceDetectionService
        $tempPO = new PurchaseOrder([
            'part_id' => $partId,
            'quantity' => $quantity,
        ]);

        $priceDetectionService = app(POPriceDetectionService::class);
        $detection = $priceDetectionService->detectPrice($tempPO);

        if (!$detection->found) {
            return null;
        }

        return $detection->price->getPriceForQuantity($quantity);
    }

    /**
     * Create a Work Order from an approved Purchase Order.
     * 
     * @param PurchaseOrder $purchaseOrder
     * @return array{success: bool, message: string, work_order: WorkOrder|null}
     */
    public function createFromPO(PurchaseOrder $purchaseOrder): array
    {
        // Verify PO is approved
        if ($purchaseOrder->status !== PurchaseOrder::STATUS_APPROVED) {
            return [
                'success' => false,
                'message' => 'Solo se pueden crear Work Orders de POs aprobadas.',
                'work_order' => null,
            ];
        }

        // Check if WO already exists
        if ($purchaseOrder->workOrder) {
            return [
                'success' => false,
                'message' => 'Ya existe una Work Order para esta PO.',
                'work_order' => $purchaseOrder->workOrder,
            ];
        }

        // Get the "Open" status
        $openStatus = StatusWO::where('name', 'Open')->first();

        if (!$openStatus) {
            return [
                'success' => false,
                'message' => 'No se encontró el estado "Open" para Work Orders.',
                'work_order' => null,
            ];
        }

        return DB::transaction(function () use ($purchaseOrder, $openStatus) {
            // Generate WO number
            $woNumber = WorkOrder::generateWONumber();

            // Create the Work Order
            $workOrder = WorkOrder::create([
                'wo_number' => $woNumber,
                'purchase_order_id' => $purchaseOrder->id,
                'status_id' => $openStatus->id,
                'sent_pieces' => 0,
                'scheduled_send_date' => $purchaseOrder->due_date,
                'opened_date' => Carbon::now(),
            ]);

            // Log the initial status
            WOStatusLog::create([
                'work_order_id' => $workOrder->id,
                'from_status_id' => null,
                'to_status_id' => $openStatus->id,
                'user_id' => Auth::id(),
                'comments' => 'Work Order creada desde PO ' . $purchaseOrder->po_number,
            ]);

            return [
                'success' => true,
                'message' => 'Work Order creada correctamente.',
                'work_order' => $workOrder,
            ];
        });
    }

    /**
     * Approve a PO and automatically create a Work Order.
     * 
     * @param PurchaseOrder $purchaseOrder
     * @return array{success: bool, message: string, purchase_order: PurchaseOrder, work_order: WorkOrder|null}
     */
    public function approveAndCreateWO(PurchaseOrder $purchaseOrder): array
    {
        // First approve the PO
        $approvalResult = $this->approve($purchaseOrder);

        if (!$approvalResult['success']) {
            return [
                'success' => false,
                'message' => $approvalResult['message'],
                'purchase_order' => $approvalResult['purchase_order'],
                'work_order' => null,
            ];
        }

        // Then create the Work Order
        $woResult = $this->createFromPO($approvalResult['purchase_order']);

        return [
            'success' => $woResult['success'],
            'message' => $woResult['success'] 
                ? 'PO aprobada y Work Order creada correctamente.'
                : $woResult['message'],
            'purchase_order' => $approvalResult['purchase_order'],
            'work_order' => $woResult['work_order'],
        ];
    }

    /**
     * Update Work Order status with logging.
     * 
     * @param WorkOrder $workOrder
     * @param int $newStatusId
     * @param string|null $comments
     * @return WorkOrder
     */
    public function updateWorkOrderStatus(WorkOrder $workOrder, int $newStatusId, ?string $comments = null): WorkOrder
    {
        $oldStatusId = $workOrder->status_id;

        // Update the status
        $workOrder->update([
            'status_id' => $newStatusId,
        ]);

        // Log the change
        WOStatusLog::create([
            'work_order_id' => $workOrder->id,
            'from_status_id' => $oldStatusId,
            'to_status_id' => $newStatusId,
            'user_id' => Auth::id(),
            'comments' => $comments,
        ]);

        return $workOrder->fresh();
    }
}
