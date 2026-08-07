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
