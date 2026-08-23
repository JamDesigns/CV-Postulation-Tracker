<?php

use App\Models\TechnicalDossierVersion;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\assertDatabaseHas;

test('it deactivates the previous active dossier of the same language', function () {
    $previousDossier = TechnicalDossierVersion::query()->create([
        'name' => 'Dosier técnico español anterior',
        'version_label' => 'v1',
        'language' => 'spanish',
        'is_active' => true,
    ]);

    $newDossier = TechnicalDossierVersion::query()->create([
        'name' => 'Dosier técnico español nuevo',
        'version_label' => 'v2',
        'language' => 'spanish',
        'is_active' => true,
    ]);

    expect($previousDossier->fresh()->is_active)->toBeFalse()
        ->and($newDossier->is_active)->toBeTrue();

    assertDatabaseHas('technical_dossier_versions', [
        'id' => $previousDossier->id,
        'is_active' => false,
    ]);

    assertDatabaseHas('technical_dossier_versions', [
        'id' => $newDossier->id,
        'is_active' => true,
    ]);
});

test('it keeps active dossiers of other languages', function () {
    $englishDossier = TechnicalDossierVersion::query()->create([
        'name' => 'English technical dossier',
        'version_label' => 'v1',
        'language' => 'english',
        'is_active' => true,
    ]);

    $spanishDossier = TechnicalDossierVersion::query()->create([
        'name' => 'Dosier técnico español',
        'version_label' => 'v1',
        'language' => 'spanish',
        'is_active' => true,
    ]);

    expect($englishDossier->fresh()->is_active)->toBeTrue()
        ->and($spanishDossier->is_active)->toBeTrue();

    assertDatabaseHas('technical_dossier_versions', [
        'id' => $englishDossier->id,
        'is_active' => true,
    ]);

    assertDatabaseHas('technical_dossier_versions', [
        'id' => $spanishDossier->id,
        'is_active' => true,
    ]);
});

test('it stores translations for technical dossier content fields', function () {
    $dossier = TechnicalDossierVersion::query()->create([
        'name' => 'Technical dossier',
        'version_label' => 'v1',
        'language' => 'spanish',
        'is_active' => false,
    ]);

    $dossier
        ->setTranslations('content_snapshot', [
            'es' => 'Contenido técnico en español',
            'en' => 'Technical content in English',
            'fr' => 'Contenu technique en français',
        ])
        ->setTranslations('notes', [
            'es' => 'Notas del dosier',
            'en' => 'Dossier notes',
            'fr' => 'Notes du dossier',
        ])
        ->save();

    $dossier->refresh();

    expect($dossier->getTranslation('content_snapshot', 'es', false))
        ->toBe('Contenido técnico en español')
        ->and($dossier->getTranslation('content_snapshot', 'en', false))
        ->toBe('Technical content in English')
        ->and($dossier->getTranslation('content_snapshot', 'fr', false))
        ->toBe('Contenu technique en français')
        ->and($dossier->getTranslation('notes', 'es', false))
        ->toBe('Notas del dosier')
        ->and($dossier->getTranslation('notes', 'en', false))
        ->toBe('Dossier notes')
        ->and($dossier->getTranslation('notes', 'fr', false))
        ->toBe('Notes du dossier');
});

test('it returns technical dossier content for the selected locale', function () {
    $dossier = TechnicalDossierVersion::query()->create([
        'name' => 'Technical dossier',
        'version_label' => 'v1',
        'language' => 'spanish',
        'is_active' => false,
        'content_snapshot' => [
            'es' => 'Contenido técnico',
            'en' => 'Technical content',
        ],
        'notes' => [
            'es' => 'Notas en español',
            'en' => 'Notes in English',
        ],
    ]);

    $dossier->setLocale('es');

    expect($dossier->content_snapshot)
        ->toBe('Contenido técnico')
        ->and($dossier->notes)
        ->toBe('Notas en español');

    $dossier->setLocale('en');

    expect($dossier->content_snapshot)
        ->toBe('Technical content')
        ->and($dossier->notes)
        ->toBe('Notes in English');
});

test('it sanitizes the version label in friendly file names', function () {
    $dossier = TechnicalDossierVersion::query()->create([
        'name' => 'Dosier técnico',
        'version_label' => 'v2 / final: 2026',
        'language' => 'spanish',
        'is_active' => false,
    ]);

    expect($dossier->pdfFriendlyName())
        ->toBe('Dosier_tecnico_Jose_Mosquera_SPANISH_v2_final_2026.pdf')
        ->and($dossier->docxFriendlyName())
        ->toBe('Dosier_tecnico_Jose_Mosquera_SPANISH_v2_final_2026.docx');
});

test('it deletes replaced technical dossier files after update', function () {
    Storage::fake('local');

    $oldPdfPath = 'technical-dossier-versions/pdf/old-dossier.pdf';
    $oldDocxPath = 'technical-dossier-versions/docx/old-dossier.docx';
    $newPdfPath = 'technical-dossier-versions/pdf/new-dossier.pdf';
    $newDocxPath = 'technical-dossier-versions/docx/new-dossier.docx';

    Storage::disk('local')->put($oldPdfPath, 'old pdf');
    Storage::disk('local')->put($oldDocxPath, 'old docx');
    Storage::disk('local')->put($newPdfPath, 'new pdf');
    Storage::disk('local')->put($newDocxPath, 'new docx');

    $dossier = TechnicalDossierVersion::query()->create([
        'name' => 'Replace Files Dossier',
        'version_label' => 'v1',
        'language' => 'spanish',
        'is_active' => false,
        'pdf_path' => $oldPdfPath,
        'docx_path' => $oldDocxPath,
    ]);

    $dossier->forceFill([
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

test('it deletes technical dossier files when the dossier is deleted', function () {
    Storage::fake('local');

    $pdfPath = 'technical-dossier-versions/pdf/deleted-dossier.pdf';
    $docxPath = 'technical-dossier-versions/docx/deleted-dossier.docx';

    Storage::disk('local')->put($pdfPath, 'pdf');
    Storage::disk('local')->put($docxPath, 'docx');

    $dossier = TechnicalDossierVersion::query()->create([
        'name' => 'Deleted Files Dossier',
        'version_label' => 'v1',
        'language' => 'spanish',
        'is_active' => false,
        'pdf_path' => $pdfPath,
        'docx_path' => $docxPath,
    ]);

    TechnicalDossierVersion::destroy($dossier->getKey());

    $disk = Storage::disk('local');

    expect($disk->exists($pdfPath))
        ->toBeFalse()
        ->and($disk->exists($docxPath))
        ->toBeFalse();
});
