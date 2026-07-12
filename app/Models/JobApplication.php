<?php
namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Enums\CvLanguage;
use App\Enums\SourceType;
use App\Enums\WorkMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobApplication extends Model
{
    protected $fillable = [
        'cv_version_id',
        'company_name',
        'job_title',
        'job_url',
        'source',
        'status',
        'sent_at',
        'location',
        'work_mode',
        'recruiter_name',
        'recruiter_url',
        'main_stack',
        'dossier_sent',
        'technical_dossier_version_id',
        'message_sent',
        'adaptation_summary',
        'notes',
        'next_step',
        'next_action_at',
    ];

    protected function casts(): array
    {
        return [
            'source'                       => SourceType::class,
            'status'                       => ApplicationStatus::class,
            'sent_at'                      => 'date',
            'next_action_at'               => 'date',
            'work_mode'                    => WorkMode::class,
            'dossier_sent'                 => 'boolean',
            'technical_dossier_version_id' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (JobApplication $jobApplication): void {
            if (! $jobApplication->dossier_sent) {
                $jobApplication->technical_dossier_version_id = null;

                return;
            }

            $jobApplication->technical_dossier_version_id = self::activeTechnicalDossierVersionIdForCv(
                $jobApplication->cv_version_id,
            );
        });
    }

    private static function activeTechnicalDossierVersionIdForCv(int | string | null $cvVersionId): ?int
    {
        if ($cvVersionId === null || $cvVersionId === '') {
            return null;
        }

        $cvVersion = CvVersion::query()
            ->whereKey($cvVersionId)
            ->first();

        if (! $cvVersion) {
            return null;
        }

        $language = $cvVersion->language instanceof CvLanguage
            ? $cvVersion->language->value
            : (string) $cvVersion->language;

        $technicalDossierVersion = TechnicalDossierVersion::query()
            ->where('language', '=', $language, 'and')
            ->where('is_active', '=', true, 'and')
            ->orderBy('published_at', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        return $technicalDossierVersion ? (int) $technicalDossierVersion->getKey() : null;
    }

    public function cvVersion(): BelongsTo
    {
        return $this->belongsTo(CvVersion::class);
    }

    public function technicalDossierVersion(): BelongsTo
    {
        return $this->belongsTo(TechnicalDossierVersion::class);
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(Interview::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(JobApplicationEvent::class);
    }
}
