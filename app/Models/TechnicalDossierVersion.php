<?php

namespace App\Models;

use App\Enums\CvLanguage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TechnicalDossierVersion extends Model
{
    protected $fillable = [
        'name',
        'version_label',
        'language',
        'pdf_path',
        'docx_path',
        'content_snapshot',
        'is_active',
        'published_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'language' => CvLanguage::class,
            'is_active' => 'boolean',
            'published_at' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::updated(function (TechnicalDossierVersion $technicalDossierVersion): void {
            foreach (['pdf_path', 'docx_path'] as $attribute) {
                if (! $technicalDossierVersion->wasChanged($attribute)) {
                    continue;
                }

                $previousPath = $technicalDossierVersion->getOriginal($attribute);

                if (blank($previousPath)) {
                    continue;
                }

                Storage::disk('local')->delete($previousPath);
            }
        });

        static::deleted(function (TechnicalDossierVersion $technicalDossierVersion): void {
            Storage::disk('local')->delete(
                array_filter([
                    $technicalDossierVersion->pdf_path,
                    $technicalDossierVersion->docx_path,
                ]),
            );
        });

        static::saved(function (TechnicalDossierVersion $technicalDossierVersion): void {
            if (! $technicalDossierVersion->is_active) {
                return;
            }

            $language = $technicalDossierVersion->language instanceof CvLanguage
                ? $technicalDossierVersion->language->value
                : (string) $technicalDossierVersion->language;

            self::query()
                ->where('language', '=', $language, 'and')
                ->where('id', '<>', $technicalDossierVersion->getKey(), 'and')
                ->where('is_active', '=', true, 'and')
                ->update([
                    'is_active' => false,
                ]);
        });
    }

    public function jobApplications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function pdfFriendlyName(): string
    {
        return $this->friendlyFileName('pdf');
    }

    public function docxFriendlyName(): string
    {
        return $this->friendlyFileName('docx');
    }

    private function friendlyFileName(string $extension): string
    {
        $language = $this->language instanceof CvLanguage
            ? strtoupper($this->language->value)
            : strtoupper((string) $this->language);

        $version = Str::of($this->version_label)
            ->ascii()
            ->replaceMatches('/[^A-Za-z0-9\-]+/', '_')
            ->trim('_')
            ->toString();

        return "Dosier_tecnico_Jose_Mosquera_{$language}_{$version}.{$extension}";
    }
}
