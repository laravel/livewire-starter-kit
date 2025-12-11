<?php

namespace App\Livewire\Admin\PurchaseOrders;

use App\Models\Part;
use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderService;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class POCreate extends Component
{
    use WithFileUploads;

    public string $po_number = '';
    public ?int $part_id = null;
    public string $po_date = '';
    public string $due_date = '';
    public int $quantity = 0;
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

    public function mount(): void
    {
        $this->po_date = now()->format('Y-m-d');
        $this->due_date = now()->addDays(30)->format('Y-m-d');
    }

    protected function rules(): array
    {
        return [
            'po_number' => 'required|string|max:255|unique:purchase_orders,po_number',
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

        $this->expected_price = $this->purchaseOrderService->getExpectedPrice(
            $this->part_id,
            $this->quantity
        );

        if ($this->expected_price === null) {
            $this->price_valid = false;
            $this->price_message = 'No hay precio registrado para esta parte.';
            return;
        }

        $poPrice = (float) $this->unit_price;
        $tolerance = 0.0001;

        if (abs($poPrice - $this->expected_price) <= $tolerance) {
            $this->price_valid = true;
            $this->price_message = 'El precio es válido.';
        } else {
            $this->price_valid = false;
            $this->price_message = sprintf(
                'El precio no coincide. Precio esperado: $%.4f',
                $this->expected_price
            );
        }
    }

    public function savePO(): void
    {
        $this->validate();

        $pdfPath = null;
        if ($this->pdf_file) {
            $pdfPath = $this->pdf_file->store('purchase-orders', 'public');
        }

        $purchaseOrder = PurchaseOrder::create([
            'po_number' => $this->po_number,
            'part_id' => $this->part_id,
            'po_date' => $this->po_date,
            'due_date' => $this->due_date,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'status' => PurchaseOrder::STATUS_PENDING,
            'comments' => $this->comments ?: null,
            'pdf_path' => $pdfPath,
        ]);

        // Validate price and update status accordingly
        $validation = $this->purchaseOrderService->validatePrice($purchaseOrder);
        
        if (!$validation['valid']) {
            $this->purchaseOrderService->markAsPendingCorrection(
                $purchaseOrder,
                $validation['message']
            );
            
            session()->flash('flash.banner', 'Orden de compra creada pero requiere corrección de precio.');
            session()->flash('flash.bannerStyle', 'warning');
        } else {
            session()->flash('flash.banner', 'Orden de compra creada correctamente.');
            session()->flash('flash.bannerStyle', 'success');
        }

        $this->redirect(route('admin.purchase-orders.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.purchase-orders.po-create', [
            'parts' => Part::active()->orderBy('number')->get(),
        ]);
    }
}
