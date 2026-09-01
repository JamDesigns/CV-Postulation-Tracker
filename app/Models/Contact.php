<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Validation\ValidationException;

class Contact extends Model
{
    protected $fillable = [
        'name',
        'organization',
        'url',
        'email',
    ];

    protected static function booted(): void
    {
        static::saving(function (Contact $contact): void {
            if (
                blank($contact->name)
                && blank($contact->organization)
                && blank($contact->url)
                && blank($contact->email)
            ) {
                throw ValidationException::withMessages([
                    'name' => __('contacts.validation.at_least_one_detail'),
                ]);
            }
        });

        static::deleting(function (Contact $contact): void {
            JobApplicationContact::query()
                ->where('contact_id', $contact->getKey())
                ->where('is_primary', true)
                ->get()
                ->each(
                    fn (JobApplicationContact $applicationContact) => JobApplicationContact::restorePreviousPrimary(
                        $applicationContact,
                    ),
                );
        });
    }

    protected function displayName(): Attribute
    {
        return Attribute::get(
            fn (): string => $this->name
                ?? $this->email
                ?? $this->organization
                ?? $this->url
                ?? "#{$this->id}",
        );
    }

    public function jobApplications(): BelongsToMany
    {
        return $this->belongsToMany(JobApplication::class)
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
}
