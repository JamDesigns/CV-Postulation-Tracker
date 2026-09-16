<?php

use App\Enums\AttachmentCategory;
use App\Enums\AttachmentDirection;
use App\Filament\Resources\JobApplications\Pages\EditJobApplication;
use App\Filament\Resources\JobApplications\RelationManagers\AttachmentsRelationManager;
use App\Models\Attachment;
use App\Models\JobApplication;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function (): void {
    Storage::fake('local');

    $user = new User([
        'name' => 'Attachments Relation Manager Test User',
        'email' => 'attachments-relation-manager@example.com',
        'password' => 'password',
    ]);

    $user->save();

    Livewire::actingAs($user);
});

test('it can load the attachments relation manager', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Attachments Relation Manager Test Company'),
        'job_title' => 'Attachments Relation Manager Test Job',
    ]);

    Livewire::test(AttachmentsRelationManager::class, [
        'ownerRecord' => $jobApplication,
        'pageClass' => EditJobApplication::class,
    ])
        ->assertOk()
        ->assertCountTableRecords(0);
});

test('it only shows attachments from the owner job application', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Owner Attachment Company'),
        'job_title' => 'Owner Attachment Job',
    ]);

    $otherJobApplication = JobApplication::query()->create([
        'company_id' => companyId('Other Attachment Company'),
        'job_title' => 'Other Attachment Job',
    ]);

    $attachment = Attachment::query()->create([
        'job_application_id' => $jobApplication->id,
        'name' => 'Visible Attachment',
        'category' => AttachmentCategory::Application,
        'direction' => AttachmentDirection::Internal,
        'file_path' => 'attachments/visible.txt',
        'original_name' => 'visible.txt',
        'mime_type' => 'text/plain',
        'size' => 10,
    ]);

    $otherAttachment = Attachment::query()->create([
        'job_application_id' => $otherJobApplication->id,
        'name' => 'Hidden Attachment',
        'category' => AttachmentCategory::Application,
        'direction' => AttachmentDirection::Internal,
        'file_path' => 'attachments/hidden.txt',
        'original_name' => 'hidden.txt',
        'mime_type' => 'text/plain',
        'size' => 10,
    ]);

    Livewire::test(AttachmentsRelationManager::class, [
        'ownerRecord' => $jobApplication,
        'pageClass' => EditJobApplication::class,
    ])
        ->assertCanSeeTableRecords([$attachment])
        ->assertCanNotSeeTableRecords([$otherAttachment]);
});

test('it can create an attachment for the owner job application', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Create Attachment Company'),
        'job_title' => 'Create Attachment Job',
    ]);

    Livewire::test(AttachmentsRelationManager::class, [
        'ownerRecord' => $jobApplication,
        'pageClass' => EditJobApplication::class,
    ])
        ->callAction(
            TestAction::make('create')->table(),
            data: [
                'file_path' => UploadedFile::fake()->create(
                    'application-document.txt',
                    10,
                    'text/plain',
                ),
                'name' => 'Application Document',
                'category' => AttachmentCategory::Application->value,
                'direction' => AttachmentDirection::Received->value,
                'document_date' => '2026-09-15',
                'notes' => 'Attachment created from relation manager test.',
            ],
        )
        ->assertHasNoFormErrors();

    $attachment = Attachment::query()->sole();

    expect($attachment->job_application_id)
        ->toBe($jobApplication->id)
        ->and($attachment->name)
        ->toBe('Application Document')
        ->and($attachment->category)
        ->toBe(AttachmentCategory::Application)
        ->and($attachment->direction)
        ->toBe(AttachmentDirection::Received)
        ->and($attachment->document_date?->toDateString())
        ->toBe('2026-09-15')
        ->and($attachment->notes)
        ->toBe('Attachment created from relation manager test.')
        ->and(Storage::disk('local')->exists($attachment->file_path))
        ->toBeTrue();
});

test('it requires another category description when category is other', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Other Category Validation Company'),
        'job_title' => 'Other Category Validation Job',
    ]);

    Livewire::test(AttachmentsRelationManager::class, [
        'ownerRecord' => $jobApplication,
        'pageClass' => EditJobApplication::class,
    ])
        ->callAction(
            TestAction::make('create')->table(),
            data: [
                'file_path' => UploadedFile::fake()->create(
                    'other-document.txt',
                    10,
                    'text/plain',
                ),
                'name' => 'Other Document',
                'category' => AttachmentCategory::Other->value,
                'category_other' => null,
                'direction' => AttachmentDirection::Internal->value,
            ],
        )
        ->assertHasFormErrors([
            'category_other' => 'required',
        ]);

    expect(Attachment::query()->count())->toBe(0);
});

test('it rejects attachments larger than twenty megabytes', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Attachment Size Validation Company'),
        'job_title' => 'Attachment Size Validation Job',
    ]);

    Livewire::test(AttachmentsRelationManager::class, [
        'ownerRecord' => $jobApplication,
        'pageClass' => EditJobApplication::class,
    ])
        ->callAction(
            TestAction::make('create')->table(),
            data: [
                'file_path' => UploadedFile::fake()->create(
                    'oversized-document.txt',
                    20481,
                    'text/plain',
                ),
                'name' => 'Oversized Document',
                'category' => AttachmentCategory::Application->value,
                'direction' => AttachmentDirection::Internal->value,
            ],
        )
        ->assertHasFormErrors([
            'file_path',
        ]);

    expect(Attachment::query()->count())->toBe(0);
});

test('it accepts attachments of exactly twenty megabytes', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Attachment Exact Size Company'),
        'job_title' => 'Attachment Exact Size Job',
    ]);

    Livewire::test(AttachmentsRelationManager::class, [
        'ownerRecord' => $jobApplication,
        'pageClass' => EditJobApplication::class,
    ])
        ->callAction(
            TestAction::make('create')->table(),
            data: [
                'file_path' => UploadedFile::fake()->create(
                    'twenty-megabytes.txt',
                    20480,
                    'text/plain',
                ),
                'name' => 'Twenty Megabytes',
                'category' => AttachmentCategory::Application->value,
                'direction' => AttachmentDirection::Internal->value,
            ],
        )
        ->assertHasNoFormErrors();

    expect(Attachment::query()->count())
        ->toBe(1);
});

test('it rejects zip attachments', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Attachment Extension Validation Company'),
        'job_title' => 'Attachment Extension Validation Job',
    ]);

    Livewire::test(AttachmentsRelationManager::class, [
        'ownerRecord' => $jobApplication,
        'pageClass' => EditJobApplication::class,
    ])
        ->callAction(
            TestAction::make('create')->table(),
            data: [
                'file_path' => UploadedFile::fake()->create(
                    'archive.zip',
                    10,
                    'application/zip',
                ),
                'name' => 'Archive',
                'category' => AttachmentCategory::Application->value,
                'direction' => AttachmentDirection::Internal->value,
            ],
        )
        ->assertHasFormErrors([
            'file_path',
        ]);

    expect(Attachment::query()->count())->toBe(0);
});

test('it accepts office documents detected as zip mime type', function (
    string $filename,
) {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Office Attachment Validation Company'),
        'job_title' => 'Office Attachment Validation Job',
    ]);

    Livewire::test(AttachmentsRelationManager::class, [
        'ownerRecord' => $jobApplication,
        'pageClass' => EditJobApplication::class,
    ])
        ->callAction(
            TestAction::make('create')->table(),
            data: [
                'file_path' => UploadedFile::fake()->create(
                    $filename,
                    10,
                    'application/zip',
                ),
                'name' => 'Office Document',
                'category' => AttachmentCategory::Application->value,
                'direction' => AttachmentDirection::Internal->value,
            ],
        )
        ->assertHasNoFormErrors();

    expect(Attachment::query()->count())->toBe(1);
})->with([
    'docx' => 'document.docx',
    'xlsx' => 'spreadsheet.xlsx',
]);

test('it suggests the attachment name when a file is selected', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Attachment Name Suggestion Company'),
        'job_title' => 'Attachment Name Suggestion Job',
    ]);

    Livewire::test(AttachmentsRelationManager::class, [
        'ownerRecord' => $jobApplication,
        'pageClass' => EditJobApplication::class,
    ])
        ->mountAction(
            TestAction::make('create')->table(),
        )
        ->fillForm([
            'file_path' => UploadedFile::fake()->create(
                'prueba%20tecnica_backend.pdf',
                10,
                'application/pdf',
            ),
        ])
        ->assertSchemaStateSet([
            'name' => 'Prueba Tecnica Backend',
        ]);
});

test('it regenerates the attachment name when the file is replaced', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Attachment Replacement Name Company'),
        'job_title' => 'Attachment Replacement Name Job',
    ]);

    $oldPath = 'attachments/job-applications/'
        .$jobApplication->id
        .'/old-document.txt';

    Storage::disk('local')->put($oldPath, 'old document');

    $attachment = Attachment::query()->create([
        'job_application_id' => $jobApplication->id,
        'name' => 'Existing Custom Name',
        'category' => AttachmentCategory::Application,
        'direction' => AttachmentDirection::Internal,
        'file_path' => $oldPath,
        'original_name' => 'old-document.txt',
    ]);

    Livewire::test(AttachmentsRelationManager::class, [
        'ownerRecord' => $jobApplication,
        'pageClass' => EditJobApplication::class,
    ])
        ->mountAction(
            TestAction::make('edit')->table($attachment),
        )
        ->fillForm([
            'file_path' => [],
        ])
        ->fillForm([
            'file_path' => UploadedFile::fake()->create(
                '04-recruiter_communications.txt',
                10,
                'text/plain',
            ),
        ])
        ->assertSchemaStateSet([
            'name' => '04 Recruiter Communications',
        ]);
});
