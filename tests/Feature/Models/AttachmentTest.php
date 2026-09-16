<?php

use App\Enums\AttachmentCategory;
use App\Enums\AttachmentDirection;
use App\Models\Attachment;
use App\Models\JobApplication;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    Storage::fake('local');
});

function attachmentTestJobApplication(): JobApplication
{
    return JobApplication::query()->create([
        'company_id' => companyId('Attachment Test Company'),
        'job_title' => 'Attachment Test Job',
    ]);
}

test('it suggests a normalized name from the original filename', function (
    string $originalName,
    string $expectedName,
) {
    expect(Attachment::suggestNameFromOriginalFilename($originalName))
        ->toBe($expectedName);
})->with([
    'url encoded spaces' => [
        'prueba%20tecnica_backend.pdf',
        'Prueba Tecnica Backend',
    ],
    'repeated separators' => [
        'oferta---backend__senior.pdf',
        'Oferta Backend Senior',
    ],
    'brackets and parentheses' => [
        'documentacion(empresa)[final].docx',
        'Documentacion Empresa Final',
    ],
    'symbols' => [
        'entrevista+tecnica@empresa.pdf',
        'Entrevista Tecnica Empresa',
    ],
    'unicode accents' => [
        'documentación técnica.pdf',
        'Documentación Técnica',
    ],
]);

test('it requires another category when category is other', function () {
    $jobApplication = attachmentTestJobApplication();

    expect(fn () => Attachment::query()->create([
        'job_application_id' => $jobApplication->id,
        'name' => 'Other attachment',
        'category' => AttachmentCategory::Other,
        'category_other' => null,
        'direction' => AttachmentDirection::Internal,
        'file_path' => 'attachments/test.txt',
        'original_name' => 'test.txt',
        'mime_type' => 'text/plain',
        'size' => 4,
    ]))->toThrow(ValidationException::class);
});

test('it clears another category when category is not other', function () {
    $jobApplication = attachmentTestJobApplication();

    $attachment = Attachment::query()->create([
        'job_application_id' => $jobApplication->id,
        'name' => 'Other attachment',
        'category' => AttachmentCategory::Other,
        'category_other' => 'Documento adicional',
        'direction' => AttachmentDirection::Internal,
        'file_path' => 'attachments/test.txt',
        'original_name' => 'test.txt',
        'mime_type' => 'text/plain',
        'size' => 4,
    ]);

    $attachment->category = AttachmentCategory::Application;
    $attachment->save();

    expect($attachment->fresh()->category_other)->toBeNull();
});

test('it stores file metadata from the physical file', function () {
    $jobApplication = attachmentTestJobApplication();

    $path = 'attachments/job-applications/'
        .$jobApplication->id
        .'/metadata-test.txt';

    Storage::disk('local')->put($path, 'attachment content');

    $attachment = Attachment::query()->create([
        'job_application_id' => $jobApplication->id,
        'name' => 'Metadata Test',
        'category' => AttachmentCategory::Application,
        'direction' => AttachmentDirection::Internal,
        'file_path' => $path,
        'original_name' => 'metadata-test.txt',
    ]);

    expect($attachment->mime_type)
        ->toBe('text/plain')
        ->and($attachment->size)
        ->toBe(strlen('attachment content'));
});

test('it deletes the previous physical file when replacing it', function () {
    $jobApplication = attachmentTestJobApplication();

    $oldPath = 'attachments/job-applications/'
        .$jobApplication->id
        .'/old-file.txt';

    $newPath = 'attachments/job-applications/'
        .$jobApplication->id
        .'/new-file.txt';

    Storage::disk('local')->put($oldPath, 'old content');
    Storage::disk('local')->put($newPath, 'new attachment content');

    $attachment = Attachment::query()->create([
        'job_application_id' => $jobApplication->id,
        'name' => 'Old File',
        'category' => AttachmentCategory::Application,
        'direction' => AttachmentDirection::Internal,
        'file_path' => $oldPath,
        'original_name' => 'old-file.txt',
    ]);

    $attachment->forceFill([
        'file_path' => $newPath,
        'original_name' => 'new-file.txt',
    ])->save();

    $disk = Storage::disk('local');
    $freshAttachment = $attachment->fresh();

    expect($disk->exists($oldPath))
        ->toBeFalse()
        ->and($disk->exists($newPath))
        ->toBeTrue()
        ->and($freshAttachment->original_name)
        ->toBe('new-file.txt')
        ->and($freshAttachment->size)
        ->toBe(strlen('new attachment content'));
});

test('it deletes the physical file when the attachment is deleted', function () {
    $jobApplication = attachmentTestJobApplication();

    $path = 'attachments/job-applications/'
        .$jobApplication->id
        .'/deleted-file.txt';

    Storage::disk('local')->put($path, 'deleted content');

    $attachment = Attachment::query()->create([
        'job_application_id' => $jobApplication->id,
        'name' => 'Deleted File',
        'category' => AttachmentCategory::Application,
        'direction' => AttachmentDirection::Internal,
        'file_path' => $path,
        'original_name' => 'deleted-file.txt',
    ]);

    $attachmentId = $attachment->id;

    $attachment->delete();

    expect(Storage::disk('local')->exists($path))
        ->toBeFalse()
        ->and(
            Attachment::query()
                ->whereKey($attachmentId)
                ->exists(),
        )
        ->toBeFalse();
});

test('it deletes attachments and their physical files when the job application is deleted', function () {
    $jobApplication = attachmentTestJobApplication();

    $path = 'attachments/job-applications/'
        .$jobApplication->id
        .'/parent-delete-test.txt';

    Storage::disk('local')->put($path, 'parent delete content');

    $attachment = Attachment::query()->create([
        'job_application_id' => $jobApplication->id,
        'name' => 'Parent Delete Test',
        'category' => AttachmentCategory::Application,
        'direction' => AttachmentDirection::Internal,
        'file_path' => $path,
        'original_name' => 'parent-delete-test.txt',
    ]);

    $jobApplicationId = $jobApplication->id;
    $attachmentId = $attachment->id;

    JobApplication::destroy($jobApplicationId);

    expect(
        JobApplication::query()
            ->whereKey($jobApplicationId)
            ->exists(),
    )
        ->toBeFalse()
        ->and(
            Attachment::query()
                ->whereKey($attachmentId)
                ->exists(),
        )
        ->toBeFalse()
        ->and(Storage::disk('local')->exists($path))
        ->toBeFalse();
});
