<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'name',
        'website',
        'linkedin_url',
        'notes',
    ];

    public function jobApplications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }
}
