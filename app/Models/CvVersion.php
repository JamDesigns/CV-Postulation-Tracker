<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CvVersion extends Model
{
    protected $fillable = [
        'name',
        'language',
        'base_profile',
        'is_reusable',
        'pdf_path',
        'docx_path',
        'highlighted_stack',
        'highlighted_experience',
        'adaptation_notes',
    ];

    protected function casts(): array
    {
        return [
            'is_reusable' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (CvVersion $cvVersion): void {
            if (
                ! $cvVersion->isDirty('is_reusable')
                || $cvVersion->is_reusable
            ) {
                return;
            }

            if ($cvVersion->jobApplications()->count() <= 1) {
                return;
            }

            throw ValidationException::withMessages([
                'is_reusable' => __(
                    'cv-versions.validation.reusable_required_for_multiple_applications',
                ),
            ]);
        });

        static::updated(function (CvVersion $cvVersion): void {
            foreach (['pdf_path', 'docx_path'] as $attribute) {
                if (! $cvVersion->wasChanged($attribute)) {
                    continue;
                }

                $previousPath = $cvVersion->getOriginal($attribute);

                if (blank($previousPath)) {
                    continue;
                }

                Storage::disk('local')->delete($previousPath);
            }
        });

        static::deleted(function (CvVersion $cvVersion): void {
            Storage::disk('local')->delete(
                array_filter([
                    $cvVersion->pdf_path,
                    $cvVersion->docx_path,
                ]),
            );
        });
    }

    public function jobApplications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function pdfFriendlyName(): string
    {
        return $this->buildFriendlyFileName('pdf');
    }

    public function docxFriendlyName(): string
    {
        return $this->buildFriendlyFileName('docx');
    }

    private function buildFriendlyFileName(string $extension): string
    {
        $safeName = str($this->name)
            ->ascii()
            ->replaceMatches('/[^A-Za-z0-9\- ]/', '')
            ->replace(' ', '_')
            ->trim('_')
            ->toString();

        return ($safeName ?: 'cv-version').'.'.$extension;
    }
}
