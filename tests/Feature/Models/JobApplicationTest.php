<?php

use App\Enums\NextActionUrgency;
use App\Models\CvVersion;
use App\Models\JobApplication;
use App\Models\TechnicalDossierVersion;
use Illuminate\Support\Carbon;

use function Pest\Laravel\assertDatabaseHas;

afterEach(function () {
    Carbon::setTestNow();
});

test('it removes the technical dossier when dossier sent is false', function () {
    $technicalDossierVersion = TechnicalDossierVersion::query()->create([
        'name' => 'Dosier técnico español',
        'version_label' => 'v1',
        'language' => 'spanish',
    ]);

    $jobApplication = JobApplication::query()->create([
        'company_name' => 'Test Company',
        'job_title' => 'Full Stack Developer',
        'dossier_sent' => false,
        'technical_dossier_version_id' => $technicalDossierVersion->id,
    ]);

    expect($jobApplication->technical_dossier_version_id)->toBeNull();

    assertDatabaseHas('job_applications', [
        'id' => $jobApplication->id,
        'dossier_sent' => false,
        'technical_dossier_version_id' => null,
    ]);
});

test('it assigns the active technical dossier matching the CV language', function () {
    $cvVersion = CvVersion::query()->create([
        'name' => 'CV Full Stack ES',
        'language' => 'spanish',
    ]);

    TechnicalDossierVersion::query()->create([
        'name' => 'Dosier técnico español inactivo',
        'version_label' => 'v1',
        'language' => 'spanish',
        'is_active' => false,
    ]);

    TechnicalDossierVersion::query()->create([
        'name' => 'English technical dossier',
        'version_label' => 'v1',
        'language' => 'english',
        'is_active' => true,
    ]);

    $activeSpanishDossier = TechnicalDossierVersion::query()->create([
        'name' => 'Dosier técnico español activo',
        'version_label' => 'v2',
        'language' => 'spanish',
        'is_active' => true,
    ]);

    $jobApplication = JobApplication::query()->create([
        'cv_version_id' => $cvVersion->id,
        'company_name' => 'Test Company',
        'job_title' => 'Full Stack Developer',
        'dossier_sent' => true,
    ]);

    expect($jobApplication->technical_dossier_version_id)
        ->toBe($activeSpanishDossier->id);

    assertDatabaseHas('job_applications', [
        'id' => $jobApplication->id,
        'dossier_sent' => true,
        'technical_dossier_version_id' => $activeSpanishDossier->id,
    ]);
});

test('it leaves the technical dossier null when no active dossier matches the CV language', function () {
    $cvVersion = CvVersion::query()->create([
        'name' => 'CV Full Stack ES',
        'language' => 'spanish',
    ]);

    TechnicalDossierVersion::query()->create([
        'name' => 'Dosier técnico español inactivo',
        'version_label' => 'v1',
        'language' => 'spanish',
        'is_active' => false,
    ]);

    TechnicalDossierVersion::query()->create([
        'name' => 'English technical dossier',
        'version_label' => 'v1',
        'language' => 'english',
        'is_active' => true,
    ]);

    $jobApplication = JobApplication::query()->create([
        'cv_version_id' => $cvVersion->id,
        'company_name' => 'Test Company',
        'job_title' => 'Full Stack Developer',
        'dossier_sent' => true,
    ]);

    expect($jobApplication->technical_dossier_version_id)->toBeNull();

    assertDatabaseHas('job_applications', [
        'id' => $jobApplication->id,
        'dossier_sent' => true,
        'technical_dossier_version_id' => null,
    ]);
});

test('it returns no date urgency when the next action has no date', function () {
    $jobApplication = new JobApplication([
        'company_name' => 'Test Company',
        'job_title' => 'Full Stack Developer',
    ]);

    expect($jobApplication->nextActionUrgency())
        ->toBe(NextActionUrgency::NoDate);
});

test('it returns overdue urgency when the next action date has passed', function () {
    Carbon::setTestNow('2026-08-04 12:00:00');

    $jobApplication = new JobApplication([
        'next_action_at' => '2026-08-03',
    ]);

    expect($jobApplication->nextActionUrgency())
        ->toBe(NextActionUrgency::Overdue);
});

test('it returns today urgency when the next action is today', function () {
    Carbon::setTestNow('2026-08-04 12:00:00');

    $jobApplication = new JobApplication([
        'next_action_at' => '2026-08-04',
    ]);

    expect($jobApplication->nextActionUrgency())
        ->toBe(NextActionUrgency::Today);
});

test('it returns upcoming urgency when the next action date is in the future', function () {
    Carbon::setTestNow('2026-08-04 12:00:00');

    $jobApplication = new JobApplication([
        'next_action_at' => '2026-08-05',
    ]);

    expect($jobApplication->nextActionUrgency())
        ->toBe(NextActionUrgency::Upcoming);
});
