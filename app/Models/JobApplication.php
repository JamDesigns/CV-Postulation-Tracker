<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Enums\Currency;
use App\Enums\CvLanguage;
use App\Enums\NextActionUrgency;
use App\Enums\SourceType;
use App\Enums\WorkMode;
use App\Services\ExchangeRateService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

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
        'salary',
        'currency',
        'recruiter_name',
        'recruiter_url',
        'recruiter_email',
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
            'source' => SourceType::class,
            'status' => ApplicationStatus::class,
            'sent_at' => 'date',
            'next_action_at' => 'date',
            'work_mode' => WorkMode::class,
            'salary' => 'decimal:2',
            'currency' => Currency::class,
            'dossier_sent' => 'boolean',
            'technical_dossier_version_id' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (JobApplication $jobApplication): void {
            if (in_array($jobApplication->status, [
                ApplicationStatus::Rejected,
                ApplicationStatus::Hired,
            ], true)) {
                $jobApplication->next_step = null;
                $jobApplication->next_action_at = null;
            }

            if (! $jobApplication->dossier_sent) {
                $jobApplication->technical_dossier_version_id = null;

                return;
            }

            $shouldAssignTechnicalDossier = ! $jobApplication->exists
                || $jobApplication->isDirty('dossier_sent')
                || $jobApplication->isDirty('cv_version_id');

            if (! $shouldAssignTechnicalDossier) {
                return;
            }

            $jobApplication->technical_dossier_version_id = self::activeTechnicalDossierVersionIdForCv(
                $jobApplication->cv_version_id,
            );
        });

        static::created(function (JobApplication $jobApplication): void {
            app(ExchangeRateService::class)->replaceSnapshot($jobApplication);
        });

        static::updated(function (JobApplication $jobApplication): void {
            $salaryWasAdded = $jobApplication->wasChanged('salary')
                && $jobApplication->getOriginal('salary') === null;

            $salaryOrCurrencyWasCleared = (
                $jobApplication->wasChanged('salary')
                || $jobApplication->wasChanged('currency')
            ) && (
                $jobApplication->salary === null
                || $jobApplication->currency === null
            );

            $shouldReplaceSnapshot = $jobApplication->wasChanged('currency')
                || $salaryWasAdded
                || $salaryOrCurrencyWasCleared;

            if (! $shouldReplaceSnapshot) {
                return;
            }

            app(ExchangeRateService::class)->replaceSnapshot($jobApplication);
        });
    }

    private static function activeTechnicalDossierVersionIdForCv(int|string|null $cvVersionId): ?int
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

    public function nextActionUrgency(): NextActionUrgency
    {
        if ($this->next_action_at === null || $this->next_action_at === '') {
            return NextActionUrgency::NoDate;
        }

        $nextActionDate = $this->next_action_at instanceof Carbon
            ? $this->next_action_at->copy()->startOfDay()
            : Carbon::parse($this->next_action_at)->startOfDay();

        $today = Carbon::today();

        if ($nextActionDate->isBefore($today)) {
            return NextActionUrgency::Overdue;
        }

        if ($nextActionDate->isToday()) {
            return NextActionUrgency::Today;
        }

        return NextActionUrgency::Upcoming;
    }

    public function nextActionUrgencyLabel(): string
    {
        return $this->nextActionUrgency()->label();
    }

    public function nextActionUrgencyColor(): string
    {
        return $this->nextActionUrgency()->color();
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

    public function exchangeRates(): HasMany
    {
        return $this->hasMany(JobApplicationExchangeRate::class);
    }
}
