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
            'type'           => JobApplicationEventType::class,
            'occurred_at'    => 'datetime',
            'status_from'    => ApplicationStatus::class,
            'status_to'      => ApplicationStatus::class,
            'next_action_at' => 'date',
        ];
    }

    public function jobApplication(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class);
    }
}
