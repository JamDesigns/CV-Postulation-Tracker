<?php

namespace App\Http\Controllers;

use App\Models\CvVersion;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CvVersionFileController extends Controller
{
    public function showPdf(CvVersion $cvVersion): BinaryFileResponse
    {
        if (! $cvVersion->pdf_path || ! Storage::disk('local')->exists($cvVersion->pdf_path)) {
            abort(404);
        }

        $absolutePath = Storage::disk('local')->path($cvVersion->pdf_path);

        return response()->file($absolutePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $cvVersion->pdfFriendlyName() . '"',
        ]);
    }

    public function downloadDocx(CvVersion $cvVersion): BinaryFileResponse
    {
        if (! $cvVersion->docx_path || ! Storage::disk('local')->exists($cvVersion->docx_path)) {
            abort(404);
        }

        $absolutePath = Storage::disk('local')->path($cvVersion->docx_path);

        return response()->download($absolutePath, $cvVersion->docxFriendlyName(), [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }
}
