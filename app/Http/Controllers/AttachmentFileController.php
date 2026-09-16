<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AttachmentFileController extends Controller
{
    public function show(Attachment $attachment): BinaryFileResponse
    {
        abort_unless(
            Storage::disk('local')->exists($attachment->file_path),
            404,
        );

        return response()->file(
            Storage::disk('local')->path($attachment->file_path),
            [
                'Content-Type' => $attachment->mime_type,
            ],
        )->setContentDisposition(
            'inline',
            $attachment->original_name,
        );
    }

    public function download(Attachment $attachment): BinaryFileResponse
    {
        abort_unless(
            Storage::disk('local')->exists($attachment->file_path),
            404,
        );

        return response()->download(
            Storage::disk('local')->path($attachment->file_path),
            $attachment->original_name,
            [
                'Content-Type' => $attachment->mime_type,
            ],
        );
    }
}
