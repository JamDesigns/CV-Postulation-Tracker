<?php

use App\Models\CvVersion;
use App\Models\JobApplication;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

test('it deletes replaced cv files after update', function () {
    Storage::fake('local');

    $oldPdfPath = 'cv-versions/pdf/old-cv.pdf';
    $oldDocxPath = 'cv-versions/docx/old-cv.docx';
    $newPdfPath = 'cv-versions/pdf/new-cv.pdf';
    $newDocxPath = 'cv-versions/docx/new-cv.docx';

    Storage::disk('local')->put($oldPdfPath, 'old pdf');
    Storage::disk('local')->put($oldDocxPath, 'old docx');
    Storage::disk('local')->put($newPdfPath, 'new pdf');
    Storage::disk('local')->put($newDocxPath, 'new docx');

    $cvVersion = CvVersion::query()->create([
        'name' => 'Replace Files CV',
        'pdf_path' => $oldPdfPath,
        'docx_path' => $oldDocxPath,
    ]);

    $cvVersion->forceFill([
        'pdf_path' => $newPdfPath,
        'docx_path' => $newDocxPath,
    ])->save();

    $disk = Storage::disk('local');

    expect($disk->exists($oldPdfPath))
        ->toBeFalse()
        ->and($disk->exists($oldDocxPath))
        ->toBeFalse()
        ->and($disk->exists($newPdfPath))
        ->toBeTrue()
        ->and($disk->exists($newDocxPath))
        ->toBeTrue();
});

test('it deletes cv files when the cv version is deleted', function () {
    Storage::fake('local');

    $pdfPath = 'cv-versions/pdf/deleted-cv.pdf';
    $docxPath = 'cv-versions/docx/deleted-cv.docx';

    Storage::disk('local')->put($pdfPath, 'pdf');
    Storage::disk('local')->put($docxPath, 'docx');

    $cvVersion = CvVersion::query()->create([
        'name' => 'Deleted Files CV',
        'pdf_path' => $pdfPath,
        'docx_path' => $docxPath,
    ]);

    CvVersion::destroy($cvVersion->getKey());

    $disk = Storage::disk('local');

    expect($disk->exists($pdfPath))
        ->toBeFalse()
        ->and($disk->exists($docxPath))
        ->toBeFalse();
});

test('it prevents disabling reuse when the CV is assigned to multiple applications', function () {
    $cvVersion = CvVersion::query()->create([
        'name' => 'Reusable CV',
        'is_reusable' => true,
    ]);

    JobApplication::query()->create([
        'cv_version_id' => $cvVersion->id,
        'company_id' => companyId('First Reusable CV Company'),
        'job_title' => 'Frontend Developer',
    ]);

    JobApplication::query()->create([
        'cv_version_id' => $cvVersion->id,
        'company_id' => companyId('Second Reusable CV Company'),
        'job_title' => 'Backend Developer',
    ]);

    expect(fn () => $cvVersion->forceFill([
        'is_reusable' => false,
    ])->save())->toThrow(ValidationException::class);

    expect($cvVersion->fresh()->is_reusable)->toBeTrue();
});
