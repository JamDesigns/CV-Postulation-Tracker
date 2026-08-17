<?php

use App\Models\TechnicalDossierVersion;

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
