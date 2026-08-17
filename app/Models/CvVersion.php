<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Translatable\HasTranslations;

class CvVersion extends Model
{
    use HasTranslations;

    public array $translatable = [
        'highlighted_stack',
        'highlighted_experience',
        'adaptation_notes',
    ];

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

    public function jobApplications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
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
