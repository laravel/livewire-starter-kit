<?php

namespace App\Services;

use App\Models\DocumentSignature;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Models\UserSignature;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SignatureService
{
    /**
     * Capture and store a signature from base64 data.
     *
     * @param string $base64Data Base64 encoded PNG image data
     * @return string Path to the stored signature file
     */
    public function captureSignature(string $base64Data): string
    {
        // Remove data URL prefix if present (e.g., "data:image/png;base64,")
        $base64Data = preg_replace('/^data:image\/\w+;base64,/', '', $base64Data);

        // Decode base64 to binary
        $imageData = base64_decode($base64Data);

        // Generate unique filename
        $filename = 'signature_' . Str::uuid() . '.png';
        $path = 'signatures/' . $filename;

        // Store the file
        Storage::disk('public')->put($path, $imageData);

        return $path;
    }

    /**
     * Save a user's signature for future use.
     *
     * @param User $user
     * @param string $base64Data Base64 encoded PNG image data
     * @return UserSignature
     */
    public function saveUserSignature(User $user, string $base64Data): UserSignature
    {
        // Capture and store the signature
        $signaturePath = $this->captureSignature($base64Data);

        // Delete old signature if exists
        $oldSignature = $user->signature;
        if ($oldSignature) {
            Storage::disk('public')->delete($oldSignature->signature_path);
            $oldSignature->delete();
        }

        // Create new user signature record
        return UserSignature::create([
            'user_id' => $user->id,
            'signature_path' => $signaturePath,
        ]);
    }

    /**
     * Get a user's saved signature.
     *
     * @param User $user
     * @return UserSignature|null
     */
    public function getUserSignature(User $user): ?UserSignature
    {
        return $user->signature;
    }

    /**
     * Sign a document (Purchase Order) - Creates signed PDF.
     *
     * @param PurchaseOrder $purchaseOrder
     * @param User $user
     * @param string $signaturePath Path to the signature file (already stored)
     * @return DocumentSignature
     */
    public function signDocument(PurchaseOrder $purchaseOrder, User $user, string $signaturePath): DocumentSignature
    {
        // Generate signed PDF
        $signedPdfPath = $this->generateSignedPdf($purchaseOrder, $signaturePath, $user);

        return DocumentSignature::create([
            'purchase_order_id' => $purchaseOrder->id,
            'user_id' => $user->id,
            'signature_path' => $signaturePath,
            'signed_pdf_path' => $signedPdfPath,
            'signed_at' => now(),
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Generate a signed PDF by adding signature to original PDF.
     *
     * @param PurchaseOrder $purchaseOrder
     * @param string $signaturePath
     * @param User $user
     * @return string Path to signed PDF
     */
    protected function generateSignedPdf(PurchaseOrder $purchaseOrder, string $signaturePath, User $user): string
    {
        $originalPdfPath = Storage::disk('public')->path($purchaseOrder->pdf_path);
        $signatureImagePath = Storage::disk('public')->path($signaturePath);

        // Create signed PDF filename
        $signedFilename = 'signed_' . basename($purchaseOrder->pdf_path);
        $signedPdfPath = 'purchase-orders/signed/' . $signedFilename;
        $signedPdfFullPath = Storage::disk('public')->path($signedPdfPath);

        // Ensure directory exists
        $directory = dirname($signedPdfFullPath);
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        try {
            // Use FPDI to add signature to existing PDF
            $pdf = new \setasign\Fpdi\Fpdi();

            // Get page count
            $pageCount = $pdf->setSourceFile($originalPdfPath);

            // Copy all pages
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($templateId);

                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($templateId);

                // Add signature on last page
                if ($pageNo === $pageCount) {
                    // Signature dimensions - larger size
                    $signatureWidth = 80;  // Increased from 40
                    $signatureHeight = 40; // Increased from 20

                    // Center horizontally, position in lower third of page
                    $x = ($size['width'] - $signatureWidth) / 2;
                    $y = $size['height'] - $signatureHeight - 60; // More space from bottom

                    // Add signature image (centered)
                    $pdf->Image($signatureImagePath, $x, $y, $signatureWidth, $signatureHeight, 'PNG');

                    // Add signature info text
                    $pdf->SetFont('Arial', '', 8);
                    $pdf->SetXY($x, $y + $signatureHeight + 2);
                    $pdf->Cell($signatureWidth, 4, 'Firmado por: ' . $user->name, 0, 1, 'C');
                    $pdf->SetX($x);
                    $pdf->Cell($signatureWidth, 4, now()->format('d/m/Y H:i'), 0, 0, 'C');
                }
            }

            // Save signed PDF
            $pdf->Output('F', $signedPdfFullPath);

            return $signedPdfPath;

        } catch (\Exception $e) {
            // If PDF generation fails, just return original path
            \Log::error('Error generating signed PDF: ' . $e->getMessage());
            return $purchaseOrder->pdf_path;
        }
    }

    /**
     * Get all signatures for a document in chronological order.
     *
     * @param PurchaseOrder $purchaseOrder
     * @return Collection
     */
    public function getDocumentSignatures(PurchaseOrder $purchaseOrder): Collection
    {
        return $purchaseOrder->signatures()
            ->with('user')
            ->chronological()
            ->get();
    }
}
