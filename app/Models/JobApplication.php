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
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class JobApplication extends Model
{
    protected $fillable = [
        'cv_version_id',
        'company_id',
        'job_title',
        'job_url',
        'source',
        'status',
        'sent_at',
        'location',
        'work_mode',
        'salary_min',
        'salary_max',
        'currency',
        'main_stack',
        'dossier_sent',
        'technical_dossier_version_id',
        'message_sent',
        'adaptation_summary',
        'notes',
        'next_step',
        'next_action_at',
        'offer_snapshot',
        'application_form_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'source' => SourceType::class,
            'status' => ApplicationStatus::class,
            'sent_at' => 'date',
            'next_action_at' => 'date',
            'work_mode' => WorkMode::class,
            'salary_min' => 'decimal:2',
            'salary_max' => 'decimal:2',
            'currency' => Currency::class,
            'dossier_sent' => 'boolean',
            'technical_dossier_version_id' => 'integer',
            'offer_snapshot_at' => 'datetime',
            'application_form_snapshot' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (JobApplication $jobApplication): void {
            if (
                filled($jobApplication->offer_snapshot)
                && $jobApplication->offer_snapshot_at === null
            ) {
                $jobApplication->offer_snapshot_at = now();
            }

            $shouldValidateCvVersion = $jobApplication->cv_version_id !== null
    && (
        ! $jobApplication->exists
        || $jobApplication->isDirty('cv_version_id')
    );

            if ($shouldValidateCvVersion) {
                $cvVersion = CvVersion::query()
                    ->whereKey($jobApplication->cv_version_id)
                    ->first();

                if ($cvVersion && ! $cvVersion->is_reusable) {
                    $alreadyAssigned = JobApplication::query()
                        ->where('cv_version_id', $jobApplication->cv_version_id)
                        ->when(
                            $jobApplication->exists,
                            fn ($query) => $query->where(
                                $jobApplication->getKeyName(),
                                '<>',
                                $jobApplication->getKey(),
                            ),
                        )
                        ->exists();

                    if ($alreadyAssigned) {
                        throw ValidationException::withMessages([
                            'cv_version_id' => __(
                                'job-applications.validation.cv_version_unique',
                            ),
                        ]);
                    }
                }
            }

            if (
                $jobApplication->salary_max !== null
                && $jobApplication->salary_min === null
            ) {
                throw ValidationException::withMessages([
                    'salary_max' => __('job-applications.validation.salary_max_requires_min'),
                ]);
            }

            if (
                $jobApplication->salary_min !== null
                && $jobApplication->salary_max !== null
                && (float) $jobApplication->salary_max < (float) $jobApplication->salary_min
            ) {
                throw ValidationException::withMessages([
                    'salary_max' => __('job-applications.validation.salary_max_gte_min'),
                ]);
            }
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
            $salaryWasAdded = $jobApplication->wasChanged('salary_min') && $jobApplication->getOriginal('salary_min') === null;

            $salaryOrCurrencyWasCleared = (
                $jobApplication->wasChanged('salary_min')
                || $jobApplication->wasChanged('currency')
            ) && (
                $jobApplication->salary_min === null
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

    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class)
            ->using(JobApplicationContact::class)
            ->withPivot([
                'id',
                'role',
                'is_primary',
                'previous_primary_contact_id',
                'source',
                'context',
            ])
            ->withTimestamps();
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
