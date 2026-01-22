<?php

namespace App\Livewire\Admin\PurchaseOrders;

use App\Models\Part;
use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderService;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class POEdit extends Component
{
    use WithFileUploads;

    public PurchaseOrder $purchaseOrder;
    public string $po_number = '';
    public string $wo = '';
    public ?int $part_id = null;
    public string $po_date = '';
    public string $due_date = '';
    public $quantity = 0;  // Changed from int to mixed to avoid type issues
    public string $unit_price = '';
    public string $comments = '';
    public $pdf_file = null;

    // Price validation feedback
    public ?float $expected_price = null;
    public bool $price_valid = false;
    public string $price_message = '';

    protected PurchaseOrderService $purchaseOrderService;

    public function boot(PurchaseOrderService $purchaseOrderService): void
    {
        $this->purchaseOrderService = $purchaseOrderService;
    }

    public function mount(PurchaseOrder $purchaseOrder): void
    {
        $this->purchaseOrder = $purchaseOrder;
        $this->po_number = $purchaseOrder->po_number;
        $this->wo = $purchaseOrder->wo ?? '';
        $this->part_id = $purchaseOrder->part_id;
        $this->po_date = $purchaseOrder->po_date->format('Y-m-d');
        $this->due_date = $purchaseOrder->due_date->format('Y-m-d');
        $this->quantity = $purchaseOrder->quantity;
        $this->unit_price = (string) $purchaseOrder->unit_price;
        $this->comments = $purchaseOrder->comments ?? '';

        $this->validatePrice();
    }

    protected function rules(): array
    {
        return [
            'po_number' => 'required|string|max:255|unique:purchase_orders,po_number,' . $this->purchaseOrder->id,
            'wo' => 'nullable|string|max:255',
            'part_id' => 'required|exists:parts,id',
            'po_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:po_date',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'comments' => 'nullable|string',
            'pdf_file' => 'nullable|file|mimes:pdf|max:10240',
        ];
    }

    protected function messages(): array
    {
        return [
            'po_number.required' => 'El número de PO es obligatorio.',
            'po_number.unique' => 'Ya existe una orden de compra con este número.',
            'part_id.required' => 'Debe seleccionar una parte.',
            'part_id.exists' => 'La parte seleccionada no existe.',
            'po_date.required' => 'La fecha de PO es obligatoria.',
            'due_date.required' => 'La fecha de entrega es obligatoria.',
            'due_date.after_or_equal' => 'La fecha de entrega debe ser igual o posterior a la fecha de PO.',
            'quantity.required' => 'La cantidad es obligatoria.',
            'quantity.min' => 'La cantidad debe ser al menos 1.',
            'unit_price.required' => 'El precio unitario es obligatorio.',
            'unit_price.min' => 'El precio unitario debe ser mayor o igual a 0.',
            'pdf_file.mimes' => 'El archivo debe ser un PDF.',
            'pdf_file.max' => 'El archivo no debe superar los 10MB.',
        ];
    }

    public function updatedPartId(): void
    {
        $this->validatePrice();
    }

    public function updatedQuantity(): void
    {
        $this->validatePrice();
    }

    public function updatedUnitPrice(): void
    {
        $this->validatePrice();
    }

    protected function validatePrice(): void
    {
        if (!$this->part_id || !$this->quantity || !$this->unit_price) {
            $this->expected_price = null;
            $this->price_valid = false;
            $this->price_message = '';
            return;
        }

        // Get detailed detection result
        $priceDetectionService = app(\App\Services\POPriceDetectionService::class);
        $detection = $priceDetectionService->detectPriceForPart(
            $this->part_id,
            (int) $this->quantity
        );

        if (!$detection->found) {
            $this->expected_price = null;
            $this->price_valid = false;
            $this->price_message = $detection->error ?? 'No se pudo detectar el precio.';
            return;
        }

        $this->expected_price = $detection->price->getPriceForQuantity((int) $this->quantity);

        if ($this->expected_price === null) {
            $this->price_valid = false;
            $this->price_message = 'No se pudo calcular el precio para la cantidad especificada.';
            return;
        }

        $poPrice = (float) $this->unit_price;
        $tolerance = 0.0001;

        if (abs($poPrice - $this->expected_price) <= $tolerance) {
            $this->price_valid = true;
            $typeLabel = \App\Models\Price::WORKSTATION_TYPES[$detection->workstationType] ?? $detection->workstationType;
            $this->price_message = "El precio es válido para tipo de estación: {$typeLabel}";
        } else {
            $this->price_valid = false;
            $typeLabel = \App\Models\Price::WORKSTATION_TYPES[$detection->workstationType] ?? $detection->workstationType;
            $this->price_message = sprintf(
                'El precio no coincide. Precio esperado: $%.4f (Tipo: %s)',
                $this->expected_price,
                $typeLabel
            );
        }
    }

    public function updatePO(): void
    {
        $this->validate();

        $pdfPath = $this->purchaseOrder->pdf_path;
        if ($this->pdf_file) {
            // Delete old PDF if exists
            if ($this->purchaseOrder->pdf_path) {
                Storage::disk('public')->delete($this->purchaseOrder->pdf_path);
            }
            $pdfPath = $this->pdf_file->store('purchase-orders', 'public');
        }

        $this->purchaseOrder->update([
            'po_number' => $this->po_number,
            'wo' => $this->wo ?: null,
            'part_id' => $this->part_id,
            'po_date' => $this->po_date,
            'due_date' => $this->due_date,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'comments' => $this->comments ?: null,
            'pdf_path' => $pdfPath,
        ]);

        // Re-validate price if status was pending correction
        if ($this->purchaseOrder->status === PurchaseOrder::STATUS_PENDING_CORRECTION) {
            $validation = $this->purchaseOrderService->validatePrice($this->purchaseOrder);
            
            if ($validation['valid']) {
                $this->purchaseOrder->update(['status' => PurchaseOrder::STATUS_PENDING]);
                session()->flash('flash.banner', 'Orden de compra actualizada. El precio ahora es válido.');
                session()->flash('flash.bannerStyle', 'success');
            } else {
                session()->flash('flash.banner', 'Orden de compra actualizada pero aún requiere corrección de precio.');
                session()->flash('flash.bannerStyle', 'warning');
            }
        } else {
            session()->flash('flash.banner', 'Orden de compra actualizada correctamente.');
            session()->flash('flash.bannerStyle', 'success');
        }

        $this->redirect(route('admin.purchase-orders.index'), navigate: true);
    }

    /**
     * Delete the current PDF file.
     */
    public function deletePdf(): void
    {
        if ($this->purchaseOrder->pdf_path) {
            Storage::disk('public')->delete($this->purchaseOrder->pdf_path);
            $this->purchaseOrder->update(['pdf_path' => null]);
            $this->purchaseOrder = $this->purchaseOrder->fresh();
            
            session()->flash('flash.banner', 'PDF eliminado correctamente.');
            session()->flash('flash.bannerStyle', 'success');
        }
    }

    public function render()
    {
        return view('livewire.admin.purchase-orders.po-edit', [
            'parts' => Part::active()->orderBy('number')->get(),
        ]);
    }
}
