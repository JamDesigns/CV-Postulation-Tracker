<?php

use App\Enums\ApplicationStatus;
use App\Enums\Currency;
use App\Enums\NextActionUrgency;
use App\Models\CvVersion;
use App\Models\JobApplication;
use App\Models\TechnicalDossierVersion;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

$ecbXml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<gesmes:Envelope xmlns:gesmes="http://www.gesmes.org/xml/2002-08-01"
    xmlns="http://www.ecb.int/vocabulary/2002-08-01/eurofxref">
    <Cube>
        <Cube time="2026-08-13">
            <Cube currency="USD" rate="1.2000" />
            <Cube currency="GBP" rate="0.8000" />
        </Cube>
    </Cube>
</gesmes:Envelope>
XML;

beforeEach(function () {
    Cache::forget('ecb.exchange_rates.latest');
});

afterEach(function () {
    Carbon::setTestNow();
    app()->setLocale(config('app.locale'));
});

test('it removes the technical dossier when dossier sent is false', function () {
    $technicalDossierVersion = TechnicalDossierVersion::query()->create([
        'name' => 'Dosier técnico español',
        'version_label' => 'v1',
        'language' => 'spanish',
    ]);

    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Test Company'),
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
        'company_id' => companyId('Test Company'),
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
        'company_id' => companyId('Test Company'),
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

test('it stores the exchange rate snapshot when created with a salary', function () use ($ecbXml) {
    Http::fake([
        'https://www.ecb.europa.eu/*' => Http::response($ecbXml),
    ]);

    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Test Company'),
        'job_title' => 'Full Stack Developer',
        'salary_min' => 42000,
        'currency' => Currency::USD,
    ]);

    $gbpRate = $jobApplication->exchangeRates()
        ->where('currency', Currency::GBP->value)
        ->firstOrFail();

    expect((float) $gbpRate->rate)
        ->toBe(0.6666666667)
        ->and($gbpRate->rate_date->toDateString())
        ->toBe('2026-08-13');

    assertDatabaseCount('job_application_exchange_rates', 3);
});

test('it preserves the historical rates when only the salary minimum changes', function () use ($ecbXml) {
    Http::fake([
        'https://www.ecb.europa.eu/*' => Http::response($ecbXml),
    ]);

    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Test Company'),
        'job_title' => 'Full Stack Developer',
        'salary_min' => 42000,
        'currency' => Currency::USD,
    ]);

    $jobApplication->exchangeRates()
        ->where('currency', Currency::GBP->value)
        ->update(['rate' => 0.5]);

    $jobApplication->forceFill([
        'salary_min' => 43000,
    ])->save();

    $storedRate = $jobApplication->exchangeRates()
        ->where('currency', Currency::GBP->value)
        ->value('rate');

    expect((float) $storedRate)->toBe(0.5);

    assertDatabaseCount('job_application_exchange_rates', 3);
});

test('it preserves the historical rates when only the salary maximum changes', function () use ($ecbXml) {
    Http::fake([
        'https://www.ecb.europa.eu/*' => Http::response($ecbXml),
    ]);

    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Test Company'),
        'job_title' => 'Full Stack Developer',
        'salary_min' => 42000,
        'salary_max' => 50000,
        'currency' => Currency::USD,
    ]);

    $jobApplication->exchangeRates()
        ->where('currency', Currency::GBP->value)
        ->update(['rate' => 0.5]);

    $jobApplication->forceFill([
        'salary_max' => 55000,
    ])->save();

    $storedRate = $jobApplication->exchangeRates()
        ->where('currency', Currency::GBP->value)
        ->value('rate');

    expect((float) $storedRate)->toBe(0.5);

    assertDatabaseCount('job_application_exchange_rates', 3);
});

test('it allows a salary range when the maximum is greater than or equal to the minimum', function () {
    $jobApplication = JobApplication::withoutEvents(
        fn (): JobApplication => JobApplication::query()->create([
            'company_id' => companyId('Test Company'),
            'job_title' => 'Full Stack Developer',
            'salary_min' => 42000,
            'salary_max' => 50000,
            'currency' => Currency::EUR,
        ]),
    );

    expect((float) $jobApplication->salary_min)
        ->toBe(42000.0)
        ->and((float) $jobApplication->salary_max)
        ->toBe(50000.0);
});

test('it rejects a salary maximum without a salary minimum', function () {
    expect(fn () => JobApplication::query()->create([
        'company_id' => companyId('Test Company'),
        'job_title' => 'Full Stack Developer',
        'salary_max' => 50000,
        'currency' => Currency::EUR,
    ]))->toThrow(ValidationException::class);
});

test('it rejects a salary maximum lower than the salary minimum', function () {
    expect(fn () => JobApplication::query()->create([
        'company_id' => companyId('Test Company'),
        'job_title' => 'Full Stack Developer',
        'salary_min' => 50000,
        'salary_max' => 42000,
        'currency' => Currency::EUR,
    ]))->toThrow(ValidationException::class);
});

test('it leaves the offer snapshot date null when no snapshot exists', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Test Company'),
        'job_title' => 'Full Stack Developer',
    ]);

    expect($jobApplication->offer_snapshot)
        ->toBeNull()
        ->and($jobApplication->offer_snapshot_at)
        ->toBeNull();
});

test('it stores the offer snapshot date only when the snapshot is first added', function () {
    Carbon::setTestNow('2026-09-03 10:14:13');

    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Test Company'),
        'job_title' => 'Full Stack Developer',
        'offer_snapshot' => 'Original job offer.',
    ]);

    expect($jobApplication->offer_snapshot_at?->toDateTimeString())
        ->toBe('2026-09-03 10:14:13');

    Carbon::setTestNow('2026-09-04 12:00:00');

    $jobApplication->forceFill([
        'offer_snapshot' => 'Updated job offer.',
    ])->save();

    $jobApplication->refresh();

    expect($jobApplication->offer_snapshot)
        ->toBe('Updated job offer.')
        ->and($jobApplication->offer_snapshot_at?->toDateTimeString())
        ->toBe('2026-09-03 10:14:13');
});

test('it stores the application form snapshot as structured question and answer data', function () {
    $formSnapshot = [
        [
            'question' => 'Do you have authorization to work in Spain?',
            'answer' => 'Yes',
        ],
        [
            'question' => 'How many years of Angular experience do you have?',
            'answer' => '6+ years',
        ],
    ];

    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Test Company'),
        'job_title' => 'Full Stack Developer',
        'application_form_snapshot' => $formSnapshot,
    ]);

    $jobApplication->refresh();

    expect($jobApplication->application_form_snapshot)
        ->toBe($formSnapshot);
});

test('it replaces the historical rates when the source currency changes', function () use ($ecbXml) {
    Http::fake([
        'https://www.ecb.europa.eu/*' => Http::response($ecbXml),
    ]);

    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Test Company'),
        'job_title' => 'Full Stack Developer',
        'salary_min' => 42000,
        'currency' => Currency::USD,
    ]);

    $jobApplication->forceFill([
        'currency' => Currency::GBP,
    ])->save();

    $eurRate = $jobApplication->exchangeRates()
        ->where('currency', Currency::EUR->value)
        ->value('rate');

    $usdRate = $jobApplication->exchangeRates()
        ->where('currency', Currency::USD->value)
        ->value('rate');

    $gbpRate = $jobApplication->exchangeRates()
        ->where('currency', Currency::GBP->value)
        ->value('rate');

    expect((float) $eurRate)->toBe(1.25)
        ->and((float) $usdRate)->toBe(1.5)
        ->and((float) $gbpRate)->toBe(1.0);

    assertDatabaseCount('job_application_exchange_rates', 3);
});

test('it deletes the exchange rates when the application is deleted', function () {
    $jobApplication = JobApplication::withoutEvents(
        fn (): JobApplication => JobApplication::query()->create([
            'company_id' => companyId('Test Company'),
            'job_title' => 'Full Stack Developer',
            'salary_min' => 42000,
            'currency' => Currency::USD,
        ]),
    );

    $exchangeRate = $jobApplication->exchangeRates()->create([
        'currency' => Currency::EUR,
        'rate' => 0.85,
        'rate_date' => '2026-08-13',
    ]);

    JobApplication::query()
        ->whereKey($jobApplication->getKey())
        ->delete();

    assertDatabaseMissing('job_application_exchange_rates', [
        'id' => $exchangeRate->id,
    ]);
});

test('it creates the snapshot when a salary is added later', function () use ($ecbXml) {
    Http::fake([
        'https://www.ecb.europa.eu/*' => Http::response($ecbXml),
    ]);

    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Test Company'),
        'job_title' => 'Full Stack Developer',
        'currency' => Currency::USD,
    ]);

    assertDatabaseCount('job_application_exchange_rates', 0);

    $jobApplication->forceFill([
        'salary_min' => 42000,
    ])->save();

    assertDatabaseCount('job_application_exchange_rates', 3);
});

test('it clears the snapshot when the salary is removed', function () use ($ecbXml) {
    Http::fake([
        'https://www.ecb.europa.eu/*' => Http::response($ecbXml),
    ]);

    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Test Company'),
        'job_title' => 'Full Stack Developer',
        'salary_min' => 42000,
        'currency' => Currency::USD,
    ]);

    assertDatabaseCount('job_application_exchange_rates', 3);

    $jobApplication->forceFill([
        'salary_min' => null,
    ])->save();

    assertDatabaseCount('job_application_exchange_rates', 0);
});

test('it preserves the technical dossier already sent when the application is updated', function () {
    $cvVersion = CvVersion::query()->create([
        'name' => 'CV Full Stack ES',
        'language' => 'spanish',
    ]);

    $originalDossier = TechnicalDossierVersion::query()->create([
        'name' => 'Dosier técnico español',
        'version_label' => 'v1',
        'language' => 'spanish',
        'is_active' => true,
    ]);

    $jobApplication = JobApplication::query()->create([
        'cv_version_id' => $cvVersion->id,
        'company_id' => companyId('Test Company'),
        'job_title' => 'Full Stack Developer',
        'dossier_sent' => true,
    ]);

    expect($jobApplication->technical_dossier_version_id)
        ->toBe($originalDossier->id);

    $newDossier = TechnicalDossierVersion::query()->create([
        'name' => 'Dosier técnico español',
        'version_label' => 'v2',
        'language' => 'spanish',
        'is_active' => true,
    ]);

    expect($newDossier->is_active)->toBeTrue()
        ->and($originalDossier->fresh()->is_active)->toBeFalse();

    $jobApplication->forceFill([
        'location' => 'Barcelona',
    ])->save();

    $jobApplication->refresh();

    expect($jobApplication->technical_dossier_version_id)
        ->toBe($originalDossier->id);
});

test('it assigns the active technical dossier when dossier sent is enabled later', function () {
    $cvVersion = CvVersion::query()->create([
        'name' => 'CV Backend ES',
        'language' => 'spanish',
    ]);

    $activeDossier = TechnicalDossierVersion::query()->create([
        'name' => 'Dosier técnico backend',
        'version_label' => 'v1',
        'language' => 'spanish',
        'is_active' => true,
    ]);

    $jobApplication = JobApplication::query()->create([
        'cv_version_id' => $cvVersion->id,
        'company_id' => companyId('Another Test Company'),
        'job_title' => 'Backend Developer',
        'dossier_sent' => false,
    ]);

    expect($jobApplication->technical_dossier_version_id)
        ->toBeNull();

    $jobApplication->forceFill([
        'dossier_sent' => true,
    ])->save();

    $jobApplication->refresh();

    expect($jobApplication->technical_dossier_version_id)
        ->toBe($activeDossier->id);
});

test('it reassigns the technical dossier when the CV changes', function () {
    $spanishCv = CvVersion::query()->create([
        'name' => 'CV Full Stack ES',
        'language' => 'spanish',
    ]);

    $englishCv = CvVersion::query()->create([
        'name' => 'CV Full Stack EN',
        'language' => 'english',
    ]);

    $spanishDossier = TechnicalDossierVersion::query()->create([
        'name' => 'Dosier técnico español',
        'version_label' => 'v1',
        'language' => 'spanish',
        'is_active' => true,
    ]);

    $englishDossier = TechnicalDossierVersion::query()->create([
        'name' => 'English technical dossier',
        'version_label' => 'v1',
        'language' => 'english',
        'is_active' => true,
    ]);

    $jobApplication = JobApplication::query()->create([
        'cv_version_id' => $spanishCv->id,
        'company_id' => companyId('CV Change Company'),
        'job_title' => 'Full Stack Developer',
        'dossier_sent' => true,
    ]);

    expect($jobApplication->technical_dossier_version_id)
        ->toBe($spanishDossier->id);

    $jobApplication->forceFill([
        'cv_version_id' => $englishCv->id,
    ])->save();

    $jobApplication->refresh();

    expect($jobApplication->technical_dossier_version_id)
        ->toBe($englishDossier->id);
});

test('it prevents the same CV version from being assigned to multiple applications', function () {
    $cvVersion = CvVersion::query()->create([
        'name' => 'CV Unique Application',
        'language' => 'spanish',
    ]);

    JobApplication::query()->create([
        'cv_version_id' => $cvVersion->id,
        'company_id' => companyId('First Company'),
        'job_title' => 'Full Stack Developer',
    ]);

    expect(fn () => JobApplication::query()->create([
        'cv_version_id' => $cvVersion->id,
        'company_id' => companyId('Second Company'),
        'job_title' => 'Backend Developer',
    ]))->toThrow(ValidationException::class);
});

test('it allows a reusable CV version to be assigned to multiple applications', function () {
    $cvVersion = CvVersion::query()->create([
        'name' => 'CV Reusable Application',
        'language' => 'spanish',
        'is_reusable' => true,
    ]);

    $firstApplication = JobApplication::query()->create([
        'cv_version_id' => $cvVersion->id,
        'company_id' => companyId('First Reusable Company'),
        'job_title' => 'Full Stack Developer',
    ]);

    $secondApplication = JobApplication::query()->create([
        'cv_version_id' => $cvVersion->id,
        'company_id' => companyId('Second Reusable Company'),
        'job_title' => 'Backend Developer',
    ]);

    expect($firstApplication->cv_version_id)
        ->toBe($cvVersion->id)
        ->and($secondApplication->cv_version_id)
        ->toBe($cvVersion->id);
});

test('it allows multiple applications without a CV version', function () {
    $firstApplication = JobApplication::query()->create([
        'company_id' => companyId('First Pending Company'),
        'job_title' => 'Frontend Developer',
    ]);

    $secondApplication = JobApplication::query()->create([
        'company_id' => companyId('Second Pending Company'),
        'job_title' => 'Backend Developer',
    ]);

    expect($firstApplication->cv_version_id)
        ->toBeNull()
        ->and($secondApplication->cv_version_id)
        ->toBeNull();
});

test('it clears the next step and action date when the application reaches a final status', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Final Status Company'),
        'job_title' => 'Full Stack Developer',
        'status' => ApplicationStatus::Interview,
        'next_step' => 'Prepare next interview',
        'next_action_at' => '2026-08-25',
    ]);

    $jobApplication->forceFill([
        'status' => ApplicationStatus::Rejected,
    ])->save();

    $jobApplication->refresh();

    expect($jobApplication->status)
        ->toBe(ApplicationStatus::Rejected)
        ->and($jobApplication->next_step)
        ->toBeNull()
        ->and($jobApplication->next_action_at)
        ->toBeNull();
});

test('it prevents duplicate applications without a URL for the same company and title', function () {
    $companyId = companyId('Duplicate Company');

    JobApplication::query()->create([
        'company_id' => $companyId,
        'job_title' => 'Full Stack Developer',
    ]);

    expect(fn () => JobApplication::query()->create([
        'company_id' => $companyId,
        'job_title' => 'Full Stack Developer',
    ]))->toThrow(UniqueConstraintViolationException::class);
});

test('it prevents duplicate applications with the same URL for the same company and title', function () {
    $companyId = companyId('Duplicate URL Company');

    JobApplication::query()->create([
        'company_id' => $companyId,
        'job_title' => 'Backend Developer',
        'job_url' => 'https://example.com/jobs/backend-developer',
    ]);

    expect(fn () => JobApplication::query()->create([
        'company_id' => $companyId,
        'job_title' => 'Backend Developer',
        'job_url' => 'https://example.com/jobs/backend-developer',
    ]))->toThrow(UniqueConstraintViolationException::class);
});

test('it allows the same job title for different companies', function () {
    $firstApplication = JobApplication::query()->create([
        'company_id' => companyId('First Same Title Company'),
        'job_title' => 'Frontend Developer',
    ]);

    $secondApplication = JobApplication::query()->create([
        'company_id' => companyId('Second Same Title Company'),
        'job_title' => 'Frontend Developer',
    ]);

    expect($firstApplication->job_title)
        ->toBe('Frontend Developer')
        ->and($secondApplication->job_title)
        ->toBe('Frontend Developer');
});

test('it allows the same company and job title when the URLs are different', function () {
    $companyId = companyId('Different URLs Company');

    $firstApplication = JobApplication::query()->create([
        'company_id' => $companyId,
        'job_title' => 'Backend Developer',
        'job_url' => 'https://example.com/jobs/backend-developer-1',
    ]);

    $secondApplication = JobApplication::query()->create([
        'company_id' => $companyId,
        'job_title' => 'Backend Developer',
        'job_url' => 'https://example.com/jobs/backend-developer-2',
    ]);

    expect($firstApplication->id)
        ->not->toBe($secondApplication->id);
});
