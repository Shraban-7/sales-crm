<?php

declare(strict_types=1);

namespace App\Services\Quotations;

use App\Models\Quotation;
use Mpdf\Mpdf;

final class QuotationPdfService
{
    public function render(Quotation $quotation): string
    {
        $quotation->loadMissing(['deal', 'company', 'contact', 'owner', 'items']);

        $html = view('pages.quotations.pdf', ['quotation' => $quotation])->render();

        $tempDir = storage_path('app/mpdf');

        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_top' => 14,
            'margin_right' => 14,
            'margin_bottom' => 16,
            'margin_left' => 14,
            'tempDir' => $tempDir,
            'allow_output_buffering' => false,
            'curlAllowUnsafeSslRequests' => false,
        ]);

        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont = true;
        $mpdf->SetTitle($quotation->quote_number);
        $mpdf->WriteHTML($html);

        return $mpdf->Output('', 'S');
    }
}
