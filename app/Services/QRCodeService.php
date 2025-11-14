<?php

namespace App\Services;

use Milon\Barcode\DNS2D;
use Illuminate\Support\Facades\Log;

class QRCodeService
{
    /**
     * Generate QR code HTML
     */
    public function generateQRCodeHTML(string $qrContent, int $width = 4, int $height = 4): string
    {
        try {
            $qr = new DNS2D();
            return $qr->getBarcodeHTML($qrContent, 'QRCODE', $width, $height);
        } catch (\Exception $e) {
            Log::error('QR code generation failed', [
                'error' => $e->getMessage(),
                'qr_content' => $qrContent
            ]);
            return '';
        }
    }
}
