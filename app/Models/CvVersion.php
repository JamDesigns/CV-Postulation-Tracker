<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class CvVersion extends Model
{
    protected $fillable = [
        'name',
        'language',
        'base_profile',
        'pdf_path',
        'docx_path',
        'highlighted_stack',
        'highlighted_experience',
        'adaptation_notes',
    ];

    protected static function booted(): void
    {
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

    public function jobApplication(): HasOne
    {
        return $this->hasOne(JobApplication::class);
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
