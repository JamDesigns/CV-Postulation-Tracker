<?php

use App\Enums\AttachmentCategory;
use App\Enums\AttachmentDirection;
use App\Models\Attachment;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

function attachmentFileTestUser(): User
{
    $user = new User([
        'name' => 'Attachment File Test User',
        'email' => 'attachment-file-test@example.com',
        'password' => 'password',
    ]);

    $user->save();

    return $user;
}

function attachmentFileTestAttachment(
    string $originalName = 'attachment-file-test.txt',
): Attachment {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Attachment File Test Company'),
        'job_title' => 'Attachment File Test Job',
    ]);

    $path = 'attachments/job-applications/'
        .$jobApplication->id
        .'/attachment-file-test.txt';

    Storage::disk('local')->put(
        $path,
        'attachment file test content',
    );

    return Attachment::query()->create([
        'job_application_id' => $jobApplication->id,
        'name' => 'Attachment File Test',
        'category' => AttachmentCategory::Application,
        'direction' => AttachmentDirection::Internal,
        'file_path' => $path,
        'original_name' => $originalName,
    ]);
}

beforeEach(function (): void {
    Storage::fake('local');
});

test('an authenticated user can open an attachment inline', function () {
    $attachment = attachmentFileTestAttachment();

    actingAs(attachmentFileTestUser());

    $response = get(route(
        'filament.admin.attachments.file',
        $attachment,
    ));

    $response->assertOk();

    expect($response->headers->get('content-type'))
        ->toContain('text/plain')
        ->and($response->headers->get('content-disposition'))
        ->toContain('inline')
        ->toContain($attachment->original_name);
});

test('an authenticated user can download an attachment with its original name', function () {
    $attachment = attachmentFileTestAttachment(
        'original-document.txt',
    );

    actingAs(attachmentFileTestUser());

    $response = get(route(
        'filament.admin.attachments.download',
        $attachment,
    ));

    $response->assertOk();

    expect($response->headers->get('content-disposition'))
        ->toContain('attachment')
        ->toContain('original-document.txt');
});

test('opening an attachment returns not found when the physical file is missing', function () {
    $attachment = attachmentFileTestAttachment();

    Storage::disk('local')->delete($attachment->file_path);

    actingAs(attachmentFileTestUser());

    get(route(
        'filament.admin.attachments.file',
        $attachment,
    ))->assertNotFound();
});

test('downloading an attachment returns not found when the physical file is missing', function () {
    $attachment = attachmentFileTestAttachment();

    Storage::disk('local')->delete($attachment->file_path);

    actingAs(attachmentFileTestUser());

    get(route(
        'filament.admin.attachments.download',
        $attachment,
    ))->assertNotFound();
});

test('attachment file routes require authentication', function (
    string $routeName,
) {
    $attachment = attachmentFileTestAttachment();

    get(route(
        $routeName,
        $attachment,
    ))->assertRedirect();
})->with([
    'open file' => 'filament.admin.attachments.file',
    'download file' => 'filament.admin.attachments.download',
]);
