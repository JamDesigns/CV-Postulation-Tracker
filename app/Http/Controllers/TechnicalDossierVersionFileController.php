<?php

namespace App\Http\Controllers;

use App\Models\TechnicalDossierVersion;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TechnicalDossierVersionFileController extends Controller
{
    public function showPdf(TechnicalDossierVersion $technicalDossierVersion): BinaryFileResponse
    {
        abort_unless($technicalDossierVersion->pdf_path, 404);
        abort_unless(Storage::disk('local')->exists($technicalDossierVersion->pdf_path), 404);

        return response()->file(
            Storage::disk('local')->path($technicalDossierVersion->pdf_path),
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $technicalDossierVersion->pdfFriendlyName() . '"',
            ],
        );
    }

    public function downloadDocx(TechnicalDossierVersion $technicalDossierVersion): BinaryFileResponse
    {
        abort_unless($technicalDossierVersion->docx_path, 404);
        abort_unless(Storage::disk('local')->exists($technicalDossierVersion->docx_path), 404);

        return response()->download(
            Storage::disk('local')->path($technicalDossierVersion->docx_path),
            $technicalDossierVersion->docxFriendlyName(),
        );
    }
}
