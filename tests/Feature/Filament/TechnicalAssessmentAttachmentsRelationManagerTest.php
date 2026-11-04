<?php

use App\Enums\AttachmentCategory;
use App\Enums\AttachmentDirection;
use App\Enums\TechnicalAssessmentDirection;
use App\Enums\TechnicalAssessmentStatus;
use App\Enums\TechnicalAssessmentType;
use App\Filament\Resources\TechnicalAssessments\Pages\EditTechnicalAssessment;
use App\Filament\Resources\TechnicalAssessments\RelationManagers\AttachmentsRelationManager;
use App\Models\JobApplication;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PHPUnit\Framework\AssertionFailedError;

beforeEach(function (): void {
    Storage::fake('local');

    $user = new User([
        'name' => 'Technical Assessment Attachments Test User',
        'email' => 'technical-assessment-attachments@example.com',
        'password' => 'password',
    ]);

    $user->save();

    Livewire::actingAs($user);
});

test('it creates an attachment linked to both the assessment and its job application', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Technical Assessment Attachment Company'),
        'job_title' => 'Backend Developer',
    ]);

    $assessment = $jobApplication->technicalAssessments()->create([
        'test_type' => TechnicalAssessmentType::AtHome,
        'direction' => TechnicalAssessmentDirection::Received,
        'status' => TechnicalAssessmentStatus::Pending,
        'received_at' => '2026-08-15 09:00:00',
    ]);

    Livewire::test(AttachmentsRelationManager::class, [
        'ownerRecord' => $assessment,
        'pageClass' => EditTechnicalAssessment::class,
    ])
        ->callAction(
            TestAction::make('create')->table(),
            data: [
                'file_path' => UploadedFile::fake()->create(
                    'technical-test.txt',
                    10,
                    'text/plain',
                ),
                'name' => 'Technical Test Document',
                'category' => AttachmentCategory::Application->value,
                'direction' => AttachmentDirection::Received->value,
            ],
        )
        ->assertHasNoFormErrors();

    $attachment = $assessment->attachments()->sole();

    expect($attachment->technical_assessment_id)
        ->toBe($assessment->id)
        ->and($attachment->job_application_id)
        ->toBe($jobApplication->id)
        ->and($attachment->name)
        ->toBe('Technical Test Document')
        ->and(Storage::disk('local')->exists($attachment->file_path))
        ->toBeTrue();
});

test('it only shows attachments from the owner technical assessment', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Assessment Attachments Isolation Company'),
        'job_title' => 'Backend Developer',
    ]);

    $firstAssessment = $jobApplication->technicalAssessments()->create([
        'test_type' => TechnicalAssessmentType::AtHome,
        'direction' => TechnicalAssessmentDirection::Received,
        'status' => TechnicalAssessmentStatus::Pending,
    ]);

    $secondAssessment = $jobApplication->technicalAssessments()->create([
        'test_type' => TechnicalAssessmentType::AtHome,
        'direction' => TechnicalAssessmentDirection::Received,
        'status' => TechnicalAssessmentStatus::Pending,
    ]);

    $firstAttachment = $firstAssessment->attachments()->create([
        'job_application_id' => $jobApplication->id,
        'name' => 'First assessment document',
        'category' => AttachmentCategory::TechnicalTest,
        'direction' => AttachmentDirection::Received,
        'file_path' => 'attachments/first-assessment.txt',
        'original_name' => 'first-assessment.txt',
        'mime_type' => 'text/plain',
        'size' => 100,
    ]);

    $secondAttachment = $secondAssessment->attachments()->create([
        'job_application_id' => $jobApplication->id,
        'name' => 'Second assessment document',
        'category' => AttachmentCategory::TechnicalTest,
        'direction' => AttachmentDirection::Received,
        'file_path' => 'attachments/second-assessment.txt',
        'original_name' => 'second-assessment.txt',
        'mime_type' => 'text/plain',
        'size' => 100,
    ]);

    Livewire::test(AttachmentsRelationManager::class, [
        'ownerRecord' => $firstAssessment,
        'pageClass' => EditTechnicalAssessment::class,
    ])
        ->assertCountTableRecords(1)
        ->assertCanSeeTableRecords([$firstAttachment])
        ->assertCanNotSeeTableRecords([$secondAttachment]);
});

test('it excludes attachments from other technical assessments from table actions', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Assessment Attachment Action Isolation Company'),
        'job_title' => 'Backend Developer',
    ]);

    $firstAssessment = $jobApplication->technicalAssessments()->create([
        'test_type' => TechnicalAssessmentType::AtHome,
        'direction' => TechnicalAssessmentDirection::Received,
        'status' => TechnicalAssessmentStatus::Pending,
    ]);

    $secondAssessment = $jobApplication->technicalAssessments()->create([
        'test_type' => TechnicalAssessmentType::AtHome,
        'direction' => TechnicalAssessmentDirection::Received,
        'status' => TechnicalAssessmentStatus::Pending,
    ]);

    $otherAttachment = $secondAssessment->attachments()->create([
        'job_application_id' => $jobApplication->id,
        'name' => 'Original document',
        'category' => AttachmentCategory::TechnicalTest,
        'direction' => AttachmentDirection::Received,
        'file_path' => 'attachments/other-assessment.txt',
        'original_name' => 'other-assessment.txt',
        'mime_type' => 'text/plain',
        'size' => 100,
    ]);

    $component = Livewire::test(AttachmentsRelationManager::class, [
        'ownerRecord' => $firstAssessment,
        'pageClass' => EditTechnicalAssessment::class,
    ]);

    $component
        ->assertCountTableRecords(0)
        ->assertCanNotSeeTableRecords([$otherAttachment]);

    expect(
        $component->instance()
            ->getFilteredTableQuery()
            ->whereKey($otherAttachment->getKey())
            ->exists()
    )
        ->toBeFalse()
        ->and($otherAttachment->fresh()->name)
        ->toBe('Original document');
});

test('it cannot edit an attachment belonging to another technical assessment', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Assessment Attachment Edit Isolation Company'),
        'job_title' => 'Backend Developer',
    ]);

    $firstAssessment = $jobApplication->technicalAssessments()->create([
        'test_type' => TechnicalAssessmentType::AtHome,
        'direction' => TechnicalAssessmentDirection::Received,
        'status' => TechnicalAssessmentStatus::Pending,
    ]);

    $secondAssessment = $jobApplication->technicalAssessments()->create([
        'test_type' => TechnicalAssessmentType::AtHome,
        'direction' => TechnicalAssessmentDirection::Received,
        'status' => TechnicalAssessmentStatus::Pending,
    ]);

    $otherAttachment = $secondAssessment->attachments()->create([
        'job_application_id' => $jobApplication->id,
        'name' => 'Original document',
        'category' => AttachmentCategory::TechnicalTest,
        'direction' => AttachmentDirection::Received,
        'file_path' => 'attachments/other-assessment.txt',
        'original_name' => 'other-assessment.txt',
        'mime_type' => 'text/plain',
        'size' => 100,
    ]);

    $exception = null;

    try {
        Livewire::test(AttachmentsRelationManager::class, [
            'ownerRecord' => $firstAssessment,
            'pageClass' => EditTechnicalAssessment::class,
        ])->callAction(
            TestAction::make('edit')->table($otherAttachment),
            data: [
                'name' => 'Unauthorized modification',
            ],
        );
    } catch (Throwable $caught) {
        $exception = $caught;
    }

    if ($exception !== null) {
        expect($exception)
            ->not->toBeInstanceOf(AssertionFailedError::class)
            ->not->toBeInstanceOf(Error::class);
    }

    $otherAttachment->refresh();

    expect($otherAttachment->name)
        ->toBe('Original document');

    expect($otherAttachment->technical_assessment_id)
        ->toBe($secondAssessment->id);
});

test('it cannot delete an attachment belonging to another technical assessment', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Assessment Attachment Delete Isolation Company'),
        'job_title' => 'Backend Developer',
    ]);

    $firstAssessment = $jobApplication->technicalAssessments()->create([
        'test_type' => TechnicalAssessmentType::AtHome,
        'direction' => TechnicalAssessmentDirection::Received,
        'status' => TechnicalAssessmentStatus::Pending,
    ]);

    $secondAssessment = $jobApplication->technicalAssessments()->create([
        'test_type' => TechnicalAssessmentType::AtHome,
        'direction' => TechnicalAssessmentDirection::Received,
        'status' => TechnicalAssessmentStatus::Pending,
    ]);

    $otherAttachment = $secondAssessment->attachments()->create([
        'job_application_id' => $jobApplication->id,
        'name' => 'Protected document',
        'category' => AttachmentCategory::TechnicalTest,
        'direction' => AttachmentDirection::Received,
        'file_path' => 'attachments/protected-document.txt',
        'original_name' => 'protected-document.txt',
        'mime_type' => 'text/plain',
        'size' => 100,
    ]);

    try {
        Livewire::test(AttachmentsRelationManager::class, [
            'ownerRecord' => $firstAssessment,
            'pageClass' => EditTechnicalAssessment::class,
        ])->callAction(
            TestAction::make('delete')->table($otherAttachment),
        );
    } catch (Throwable $exception) {
        if (
            $exception instanceof AssertionFailedError
            || $exception instanceof Error
        ) {
            throw $exception;
        }
    }

    expect($otherAttachment->fresh())
        ->not->toBeNull();

    expect($otherAttachment->fresh()->technical_assessment_id)
        ->toBe($secondAssessment->id);
});

test('it can edit an attachment belonging to the owner technical assessment', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Assessment Attachment Edit Company'),
        'job_title' => 'Backend Developer',
    ]);

    $assessment = $jobApplication->technicalAssessments()->create([
        'test_type' => TechnicalAssessmentType::AtHome,
        'direction' => TechnicalAssessmentDirection::Received,
        'status' => TechnicalAssessmentStatus::Pending,
    ]);

    Storage::fake('local');

    Storage::disk('local')->put(
        'attachments/assessment-document.txt',
        'Original assessment document content',
    );

    $attachment = $assessment->attachments()->create([
        'job_application_id' => $jobApplication->id,
        'name' => 'Original document',
        'category' => AttachmentCategory::TechnicalTest,
        'direction' => AttachmentDirection::Received,
        'file_path' => 'attachments/assessment-document.txt',
        'original_name' => 'assessment-document.txt',
        'mime_type' => 'text/plain',
        'size' => 100,
    ]);

    Livewire::test(AttachmentsRelationManager::class, [
        'ownerRecord' => $assessment,
        'pageClass' => EditTechnicalAssessment::class,
    ])
        ->callAction(
            TestAction::make('edit')->table($attachment),
            data: [
                'name' => 'Updated document',
                'category' => AttachmentCategory::TechnicalTest->value,
                'direction' => AttachmentDirection::Received->value,
            ],
        )
        ->assertHasNoFormErrors();

    $attachment->refresh();

    expect($attachment->name)->toBe('Updated document')
        ->and($attachment->technical_assessment_id)->toBe($assessment->id)
        ->and($attachment->job_application_id)->toBe($jobApplication->id);
});

test('it can delete an attachment belonging to the owner technical assessment', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Assessment Attachment Delete Company'),
        'job_title' => 'Backend Developer',
    ]);

    $assessment = $jobApplication->technicalAssessments()->create([
        'test_type' => TechnicalAssessmentType::AtHome,
        'direction' => TechnicalAssessmentDirection::Received,
        'status' => TechnicalAssessmentStatus::Pending,
    ]);

    $attachment = $assessment->attachments()->create([
        'job_application_id' => $jobApplication->id,
        'name' => 'Document to delete',
        'category' => AttachmentCategory::TechnicalTest,
        'direction' => AttachmentDirection::Received,
        'file_path' => 'attachments/document-to-delete.txt',
        'original_name' => 'document-to-delete.txt',
        'mime_type' => 'text/plain',
        'size' => 100,
    ]);

    Livewire::test(AttachmentsRelationManager::class, [
        'ownerRecord' => $assessment,
        'pageClass' => EditTechnicalAssessment::class,
    ])
        ->callAction(
            TestAction::make('delete')->table($attachment),
        )
        ->assertHasNoFormErrors();

    expect($attachment->fresh())->toBeNull();

    expect($jobApplication->technicalAssessments()
        ->whereKey($assessment->id)
        ->exists())->toBeTrue();

    expect($jobApplication->exists)->toBeTrue();
});

test('it deletes the physical file when deleting a technical assessment attachment', function () {
    Storage::fake('local');

    $filePath = 'attachments/assessment-file-to-delete.txt';

    Storage::disk('local')->put(
        $filePath,
        'Technical assessment document content',
    );

    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Assessment Attachment Physical Delete Company'),
        'job_title' => 'Backend Developer',
    ]);

    $assessment = $jobApplication->technicalAssessments()->create([
        'test_type' => TechnicalAssessmentType::AtHome,
        'direction' => TechnicalAssessmentDirection::Received,
        'status' => TechnicalAssessmentStatus::Pending,
    ]);

    $attachment = $assessment->attachments()->create([
        'job_application_id' => $jobApplication->id,
        'name' => 'Document to delete',
        'category' => AttachmentCategory::TechnicalTest,
        'direction' => AttachmentDirection::Received,
        'file_path' => $filePath,
        'original_name' => 'assessment-file-to-delete.txt',
        'mime_type' => 'text/plain',
        'size' => 37,
    ]);

    expect(Storage::disk('local')->exists($filePath))->toBeTrue();

    Livewire::test(AttachmentsRelationManager::class, [
        'ownerRecord' => $assessment,
        'pageClass' => EditTechnicalAssessment::class,
    ])
        ->callAction(
            TestAction::make('delete')->table($attachment),
        )
        ->assertHasNoFormErrors();

    expect($attachment->fresh())->toBeNull();

    expect(Storage::disk('local')->exists($filePath))->toBeFalse();

    expect($assessment->fresh())->not->toBeNull()
        ->and($jobApplication->fresh())->not->toBeNull();
});
