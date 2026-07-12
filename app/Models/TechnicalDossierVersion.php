<?php
namespace App\Models;

use App\Enums\CvLanguage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class TechnicalDossierVersion extends Model
{
    protected $fillable = [
        'name',
        'version_label',
        'language',
        'pdf_path',
        'docx_path',
        'summary',
        'content_snapshot',
        'is_active',
        'published_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'language'     => CvLanguage::class,
            'is_active'    => 'boolean',
            'published_at' => 'date',
        ];
    }

    protected static function booted(): void
    {
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
            ->replace(' ', '_')
            ->toString();

        return "Dosier_tecnico_Jose_Mosquera_{$language}_{$version}.{$extension}";
    }
}
