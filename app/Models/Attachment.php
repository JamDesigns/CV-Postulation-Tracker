<?php

namespace App\Models;

use App\Enums\AttachmentCategory;
use App\Enums\AttachmentDirection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class Attachment extends Model
{
    protected $fillable = [
        'job_application_id',
        'name',
        'category',
        'category_other',
        'direction',
        'file_path',
        'original_name',
        'mime_type',
        'size',
        'document_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'category' => AttachmentCategory::class,
            'direction' => AttachmentDirection::class,
            'size' => 'integer',
            'document_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Attachment $attachment): void {
            if ($attachment->category === AttachmentCategory::Other) {
                if (blank($attachment->category_other)) {
                    throw ValidationException::withMessages([
                        'category_other' => __('attachments.validation.category_other_required'),
                    ]);
                }
            } else {
                $attachment->category_other = null;
            }

            if (
                $attachment->isDirty('file_path')
                && filled($attachment->file_path)
            ) {
                /** @var FilesystemAdapter $disk */
                $disk = Storage::disk('local');

                if ($disk->exists($attachment->file_path)) {
                    $attachment->mime_type = $disk->mimeType($attachment->file_path)
                        ?: 'application/octet-stream';

                    $attachment->size = $disk->size($attachment->file_path);
                }
            }
        });

        static::updated(function (Attachment $attachment): void {
            if (! $attachment->wasChanged('file_path')) {
                return;
            }

            $previousPath = $attachment->getOriginal('file_path');

            if (blank($previousPath)) {
                return;
            }

            Storage::disk('local')->delete($previousPath);
        });

        static::deleted(function (Attachment $attachment): void {
            if (blank($attachment->file_path)) {
                return;
            }

            Storage::disk('local')->delete($attachment->file_path);
        });
    }

    public static function suggestNameFromOriginalFilename(string $originalName): string
    {
        $decodedName = rawurldecode($originalName);
        $nameWithoutExtension = pathinfo($decodedName, PATHINFO_FILENAME);

        $normalizedName = preg_replace(
            '/[^\p{L}\p{N}]+/u',
            ' ',
            $nameWithoutExtension,
        ) ?? '';

        $normalizedName = trim($normalizedName);

        if ($normalizedName === '') {
            return '';
        }

        return collect(preg_split('/\s+/u', $normalizedName))
            ->map(
                fn (string $word): string => mb_strtoupper(
                    mb_substr($word, 0, 1),
                ).mb_substr($word, 1),
            )
            ->implode(' ');
    }

    public function jobApplication(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class);
    }
}
