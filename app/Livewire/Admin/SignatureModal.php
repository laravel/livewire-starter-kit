<?php

namespace App\Livewire\Admin;

use App\Models\PurchaseOrder;
use App\Services\SignatureService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SignatureModal extends Component
{
    public bool $showModal = false;
    public ?int $purchaseOrderId = null;
    public ?string $signatureData = null;
    public bool $saveForFuture = false;
    public bool $updateSavedSignature = false;
    public ?string $savedSignatureUrl = null;
    public bool $useSavedSignature = false;

    protected $listeners = ['openSignatureModal' => 'openModal'];

    protected SignatureService $signatureService;

    public function boot(SignatureService $signatureService): void
    {
        $this->signatureService = $signatureService;
    }

    public function openModal(int $purchaseOrderId): void
    {
        $this->purchaseOrderId = $purchaseOrderId;
        $this->showModal = true;
        $this->signatureData = null;
        $this->useSavedSignature = false;
        
        // Check if user has a saved signature
        $userSignature = $this->signatureService->getUserSignature(Auth::user());
        if ($userSignature) {
            $this->savedSignatureUrl = $userSignature->signature_url;
        } else {
            $this->savedSignatureUrl = null;
        }
        
        $this->dispatch('signature-modal-opened');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->purchaseOrderId = null;
        $this->signatureData = null;
        $this->saveForFuture = false;
        $this->updateSavedSignature = false;
        $this->savedSignatureUrl = null;
        $this->useSavedSignature = false;
    }

    public function clearSignature(): void
    {
        $this->signatureData = null;
        $this->useSavedSignature = false;
        $this->dispatch('clear-signature-pad');
    }

    public function useSaved(): void
    {
        $this->useSavedSignature = true;
        $this->dispatch('use-saved-signature', url: $this->savedSignatureUrl);
    }

    public function confirmSignature(): void
    {
        if (!$this->purchaseOrderId) {
            session()->flash('error', 'No se especificó el documento a firmar.');
            return;
        }

        $purchaseOrder = PurchaseOrder::findOrFail($this->purchaseOrderId);
        
        try {
            // Determine which signature to use
            if ($this->useSavedSignature && $this->savedSignatureUrl) {
                // Use saved signature
                $userSignature = $this->signatureService->getUserSignature(Auth::user());
                $signaturePath = $userSignature->signature_path;
            } else {
                // Use new signature
                if (!$this->signatureData) {
                    session()->flash('error', 'Debe dibujar una firma.');
                    return;
                }
                
                // Capture and store the new signature
                $signaturePath = $this->signatureService->captureSignature($this->signatureData);
                
                // Save for future if requested or update existing signature
                if ($this->saveForFuture || $this->updateSavedSignature) {
                    $this->signatureService->saveUserSignature(Auth::user(), $this->signatureData);
                }
            }
            
            // Sign the document
            $this->signatureService->signDocument($purchaseOrder, Auth::user(), $signaturePath);
            
            session()->flash('flash.banner', 'Documento firmado correctamente.');
            session()->flash('flash.bannerStyle', 'success');
            
            $this->closeModal();
            $this->dispatch('signature-completed');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al firmar el documento: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.signature-modal');
    }
}
