<?php

namespace App\Models;

use App\Enums\Currency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplicationExchangeRate extends Model
{
    protected $fillable = [
        'currency',
        'rate',
        'rate_date',
    ];

    protected function casts(): array
    {
        return [
            'currency' => Currency::class,
            'rate' => 'decimal:10',
            'rate_date' => 'date',
        ];
    }

    public function jobApplication(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class);
    }
}
