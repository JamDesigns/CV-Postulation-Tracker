<?php

use App\Models\Contact;
use App\Models\JobApplication;
use App\Models\JobApplicationContact;
use Illuminate\Database\UniqueConstraintViolationException;

function createApplication(string $company): JobApplication
{
    return JobApplication::query()->create([
        'company_id' => companyId($company),
        'job_title' => 'Full Stack Developer',
    ]);
}

function createContact(string $name): Contact
{
    return Contact::query()->create([
        'name' => $name,
    ]);
}

test('it allows the same contact to be attached to multiple applications', function () {
    $contact = createContact('Shared Contact');

    $firstApplication = createApplication('First Company');
    $secondApplication = createApplication('Second Company');

    $firstApplication->contacts()->attach($contact->id);
    $secondApplication->contacts()->attach($contact->id);

    expect(
        JobApplicationContact::query()
            ->where('contact_id', $contact->id)
            ->count(),
    )->toBe(2);
});

test('it prevents the same contact from being attached twice to the same application', function () {
    $contact = createContact('Unique Contact');
    $application = createApplication('Unique Company');

    $application->contacts()->attach($contact->id);

    expect(
        fn () => $application->contacts()->attach($contact->id),
    )->toThrow(UniqueConstraintViolationException::class);
});

test('it keeps only one primary contact per application', function () {
    $application = createApplication('Primary Company');

    $firstContact = createContact('First Contact');
    $secondContact = createContact('Second Contact');

    $application->contacts()->attach($firstContact->id, [
        'is_primary' => true,
    ]);

    $application->contacts()->attach($secondContact->id, [
        'is_primary' => true,
    ]);

    $firstPivot = JobApplicationContact::query()
        ->where('job_application_id', $application->id)
        ->where('contact_id', $firstContact->id)
        ->firstOrFail();

    $secondPivot = JobApplicationContact::query()
        ->where('job_application_id', $application->id)
        ->where('contact_id', $secondContact->id)
        ->firstOrFail();

    expect($firstPivot->is_primary)
        ->toBeFalse()
        ->and($secondPivot->is_primary)
        ->toBeTrue();
});

test('it stores the previous primary contact when a new primary is promoted', function () {
    $application = createApplication('Previous Primary Company');

    $firstContact = createContact('Previous Primary');
    $secondContact = createContact('New Primary');

    $application->contacts()->attach($firstContact->id, [
        'is_primary' => true,
    ]);

    $application->contacts()->attach($secondContact->id, [
        'is_primary' => true,
    ]);

    $secondPivot = JobApplicationContact::query()
        ->where('job_application_id', $application->id)
        ->where('contact_id', $secondContact->id)
        ->firstOrFail();

    expect($secondPivot->previous_primary_contact_id)
        ->toBe($firstContact->id);
});

test('it restores the previous primary when the current primary is detached', function () {
    $application = createApplication('Restore Company');

    $firstContact = createContact('Previous Primary');
    $secondContact = createContact('Current Primary');

    $application->contacts()->attach($firstContact->id, [
        'is_primary' => true,
    ]);

    $application->contacts()->attach($secondContact->id, [
        'is_primary' => true,
    ]);

    $application->contacts()->detach($secondContact->id);

    $firstPivot = JobApplicationContact::query()
        ->where('job_application_id', $application->id)
        ->where('contact_id', $firstContact->id)
        ->firstOrFail();

    expect($firstPivot->is_primary)->toBeTrue();
});

test('it preserves the previous primary chain across successive promotions and detaches', function () {
    $application = createApplication('Primary Chain Company');

    $firstContact = createContact('First Primary');
    $secondContact = createContact('Second Primary');
    $thirdContact = createContact('Third Primary');

    $application->contacts()->attach($firstContact->id, [
        'is_primary' => true,
    ]);

    $application->contacts()->attach($secondContact->id, [
        'is_primary' => true,
    ]);

    $application->contacts()->attach($thirdContact->id, [
        'is_primary' => true,
    ]);

    $secondPivot = JobApplicationContact::query()
        ->where('job_application_id', $application->id)
        ->where('contact_id', $secondContact->id)
        ->firstOrFail();

    $thirdPivot = JobApplicationContact::query()
        ->where('job_application_id', $application->id)
        ->where('contact_id', $thirdContact->id)
        ->firstOrFail();

    expect($thirdPivot->previous_primary_contact_id)
        ->toBe($secondContact->id)
        ->and($secondPivot->previous_primary_contact_id)
        ->toBe($firstContact->id);

    $application->contacts()->detach($thirdContact->id);

    $secondPivot->refresh();

    expect($secondPivot->is_primary)
        ->toBeTrue()
        ->and($secondPivot->previous_primary_contact_id)
        ->toBe($firstContact->id);

    $application->contacts()->detach($secondContact->id);

    $firstPivot = JobApplicationContact::query()
        ->where('job_application_id', $application->id)
        ->where('contact_id', $firstContact->id)
        ->firstOrFail();

    expect($firstPivot->is_primary)->toBeTrue();
});

test('it leaves the application without a primary when the detached primary had no previous primary', function () {
    $application = createApplication('No Previous Primary Company');

    $contact = createContact('Only Primary');

    $application->contacts()->attach($contact->id, [
        'is_primary' => true,
    ]);

    $pivot = JobApplicationContact::query()
        ->where('job_application_id', $application->id)
        ->where('contact_id', $contact->id)
        ->firstOrFail();

    expect($pivot->previous_primary_contact_id)->toBeNull();

    $application->contacts()->detach($contact->id);

    expect(
        JobApplicationContact::query()
            ->where('job_application_id', $application->id)
            ->where('is_primary', true)
            ->exists(),
    )->toBeFalse();
});

test('detaching a non primary contact does not change the current primary', function () {
    $application = createApplication('Non Primary Detach Company');

    $primaryContact = createContact('Primary Contact');
    $secondaryContact = createContact('Secondary Contact');

    $application->contacts()->attach($primaryContact->id, [
        'is_primary' => true,
    ]);

    $application->contacts()->attach($secondaryContact->id, [
        'is_primary' => false,
    ]);

    $application->contacts()->detach($secondaryContact->id);

    $primaryPivot = JobApplicationContact::query()
        ->where('job_application_id', $application->id)
        ->where('contact_id', $primaryContact->id)
        ->firstOrFail();

    expect($primaryPivot->is_primary)->toBeTrue();
});

test('deleting the current primary contact restores the previous primary', function () {
    $application = createApplication('Delete Primary Company');

    $firstContact = createContact('Previous Primary');
    $secondContact = createContact('Deleted Primary');

    $application->contacts()->attach($firstContact->id, [
        'is_primary' => true,
    ]);

    $application->contacts()->attach($secondContact->id, [
        'is_primary' => true,
    ]);

    $secondContact->delete();

    $firstPivot = JobApplicationContact::query()
        ->where('job_application_id', $application->id)
        ->where('contact_id', $firstContact->id)
        ->firstOrFail();

    expect($firstPivot->is_primary)
        ->toBeTrue()
        ->and(Contact::query()->whereKey($secondContact->id)->exists())
        ->toBeFalse()
        ->and(
            JobApplicationContact::query()
                ->where('job_application_id', $application->id)
                ->where('contact_id', $secondContact->id)
                ->exists(),
        )
        ->toBeFalse();
});
