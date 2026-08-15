<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Enums\JobApplicationEventType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplicationEvent extends Model
{
    protected $fillable = [
        'job_application_id',
        'type',
        'occurred_at',
        'title',
        'body',
        'status_from',
        'status_to',
        'next_action_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => JobApplicationEventType::class,
            'occurred_at' => 'datetime',
            'status_from' => ApplicationStatus::class,
            'status_to' => ApplicationStatus::class,
            'next_action_at' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (JobApplicationEvent $event): void {
            if ($event->status_from !== null) {
                return;
            }

            $event->status_from = $event->jobApplication?->status;
        });

        static::created(function (JobApplicationEvent $event): void {
            $jobApplication = $event->jobApplication;

            if ($jobApplication === null) {
                return;
            }

            $attributes = [
                'next_action_at' => $event->next_action_at,
            ];

            if ($event->status_to instanceof ApplicationStatus) {
                $attributes['status'] = $event->status_to;
                $attributes['next_step'] = $event->status_to->nextStep();

                if (in_array($event->status_to, [
                    ApplicationStatus::Rejected,
                    ApplicationStatus::Hired,
                ], true)) {
                    $attributes['next_action_at'] = null;
                }
            }

            $jobApplication->forceFill($attributes)->save();
        });
    }

    public function jobApplication(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class);
    }
}
