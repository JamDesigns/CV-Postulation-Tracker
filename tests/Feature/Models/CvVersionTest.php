<?php

use App\Models\CvVersion;
use Illuminate\Support\Facades\Storage;

test('it stores translations for cv version content fields', function () {
    $cvVersion = CvVersion::query()->create([
        'name' => 'Full Stack CV',
    ]);

    $cvVersion
        ->setTranslations('highlighted_stack', [
            'es' => 'Angular, Laravel y TypeScript',
            'en' => 'Angular, Laravel and TypeScript',
            'fr' => 'Angular, Laravel et TypeScript',
        ])
        ->setTranslations('highlighted_experience', [
            'es' => 'Experiencia destacada en desarrollo full stack',
            'en' => 'Highlighted full-stack development experience',
            'fr' => 'Expérience mise en avant en développement full stack',
        ])
        ->setTranslations('adaptation_notes', [
            'es' => 'CV adaptado para la oferta',
            'en' => 'CV adapted for the job offer',
            'fr' => 'CV adapté à l’offre d’emploi',
        ])
        ->save();

    $cvVersion->refresh();

    expect($cvVersion->getTranslation('highlighted_stack', 'es', false))
        ->toBe('Angular, Laravel y TypeScript')
        ->and($cvVersion->getTranslation('highlighted_stack', 'en', false))
        ->toBe('Angular, Laravel and TypeScript')
        ->and($cvVersion->getTranslation('highlighted_stack', 'fr', false))
        ->toBe('Angular, Laravel et TypeScript')
        ->and($cvVersion->getTranslation('highlighted_experience', 'es', false))
        ->toBe('Experiencia destacada en desarrollo full stack')
        ->and($cvVersion->getTranslation('highlighted_experience', 'en', false))
        ->toBe('Highlighted full-stack development experience')
        ->and($cvVersion->getTranslation('highlighted_experience', 'fr', false))
        ->toBe('Expérience mise en avant en développement full stack')
        ->and($cvVersion->getTranslation('adaptation_notes', 'es', false))
        ->toBe('CV adaptado para la oferta')
        ->and($cvVersion->getTranslation('adaptation_notes', 'en', false))
        ->toBe('CV adapted for the job offer')
        ->and($cvVersion->getTranslation('adaptation_notes', 'fr', false))
        ->toBe('CV adapté à l’offre d’emploi');
});

test('it returns cv version content for the selected locale', function () {
    $cvVersion = CvVersion::query()->create([
        'name' => 'Frontend CV',
        'highlighted_stack' => [
            'es' => 'Angular y TypeScript',
            'en' => 'Angular and TypeScript',
        ],
        'highlighted_experience' => [
            'es' => 'Experiencia destacada en frontend',
            'en' => 'Highlighted frontend experience',
        ],
        'adaptation_notes' => [
            'es' => 'Adaptado para un puesto frontend',
            'en' => 'Adapted for a frontend role',
        ],
    ]);

    $cvVersion->setLocale('es');

    expect($cvVersion->highlighted_stack)
        ->toBe('Angular y TypeScript')
        ->and($cvVersion->highlighted_experience)
        ->toBe('Experiencia destacada en frontend')
        ->and($cvVersion->adaptation_notes)
        ->toBe('Adaptado para un puesto frontend');

    $cvVersion->setLocale('en');

    expect($cvVersion->highlighted_stack)
        ->toBe('Angular and TypeScript')
        ->and($cvVersion->highlighted_experience)
        ->toBe('Highlighted frontend experience')
        ->and($cvVersion->adaptation_notes)
        ->toBe('Adapted for a frontend role');
});

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
