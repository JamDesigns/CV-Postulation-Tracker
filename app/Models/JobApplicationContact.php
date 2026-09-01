<?php

namespace App\Models;

use App\Enums\ApplicationContactRole;
use App\Enums\ApplicationContactSource;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class JobApplicationContact extends Pivot
{
    protected $table = 'contact_job_application';

    public $incrementing = true;

    protected $fillable = [
        'contact_id',
        'job_application_id',
        'role',
        'is_primary',
        'source',
        'context',
    ];

    protected function casts(): array
    {
        return [
            'role' => ApplicationContactRole::class,
            'is_primary' => 'boolean',
            'source' => ApplicationContactSource::class,
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (JobApplicationContact $applicationContact): void {
            $isBeingPromoted = $applicationContact->is_primary
                && (
                    ! $applicationContact->exists
                    || $applicationContact->isDirty('is_primary')
                );

            if (
                ! $isBeingPromoted
                || blank($applicationContact->job_application_id)
            ) {
                return;
            }

            $currentPrimary = static::query()
                ->where('job_application_id', $applicationContact->job_application_id)
                ->where('is_primary', true)
                ->when(
                    $applicationContact->exists,
                    fn ($query) => $query->where('id', '!=', $applicationContact->getKey()),
                )
                ->first();

            $applicationContact->previous_primary_contact_id = $currentPrimary?->contact_id;

            static::query()
                ->where('job_application_id', $applicationContact->job_application_id)
                ->when(
                    $applicationContact->exists,
                    fn ($query) => $query->where('id', '!=', $applicationContact->getKey()),
                )
                ->update(['is_primary' => false]);
        });

        static::deleted(function (JobApplicationContact $applicationContact): void {
            if (
                ! $applicationContact->is_primary
                || blank($applicationContact->job_application_id)
                || blank($applicationContact->previous_primary_contact_id)
            ) {
                return;
            }

            $hasCurrentPrimary = static::query()
                ->where('job_application_id', $applicationContact->job_application_id)
                ->where('is_primary', true)
                ->exists();

            if ($hasCurrentPrimary) {
                return;
            }

            static::query()
                ->where('job_application_id', $applicationContact->job_application_id)
                ->where('contact_id', $applicationContact->previous_primary_contact_id)
                ->update(['is_primary' => true]);
        });
    }

    public static function restorePreviousPrimary(self $applicationContact): void
    {
        if (
            ! $applicationContact->is_primary
            || blank($applicationContact->job_application_id)
            || blank($applicationContact->previous_primary_contact_id)
        ) {
            return;
        }

        $hasAnotherPrimary = static::query()
            ->where('job_application_id', $applicationContact->job_application_id)
            ->where('is_primary', true)
            ->where('id', '!=', $applicationContact->getKey())
            ->exists();

        if ($hasAnotherPrimary) {
            return;
        }

        static::query()
            ->where('job_application_id', $applicationContact->job_application_id)
            ->where('contact_id', $applicationContact->previous_primary_contact_id)
            ->where('id', '!=', $applicationContact->getKey())
            ->update(['is_primary' => true]);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function jobApplication(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class);
    }
}
