<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            $contactsByName = [];
            $contactsByEmail = [];
            $contactsByUrl = [];
            $contactData = [];

            foreach (DB::table('contacts')->get(['id', 'name', 'url', 'email']) as $contact) {
                $contactId = (int) $contact->id;

                $contactData[$contactId] = [
                    'name' => $this->clean($contact->name),
                    'url' => $this->clean($contact->url),
                    'email' => $this->clean($contact->email),
                ];

                $this->registerContact(
                    $contactId,
                    $contactData[$contactId],
                    $contactsByName,
                    $contactsByEmail,
                    $contactsByUrl,
                );
            }

            $applications = DB::table('job_applications')
                ->where(function ($query): void {
                    $query
                        ->whereNotNull('recruiter_name')
                        ->orWhereNotNull('recruiter_url')
                        ->orWhereNotNull('recruiter_email');
                })
                ->orderBy('id')
                ->get([
                    'id',
                    'recruiter_name',
                    'recruiter_url',
                    'recruiter_email',
                ]);

            foreach ($applications as $application) {
                $name = $this->clean($application->recruiter_name);
                $url = $this->clean($application->recruiter_url);
                $email = $this->clean($application->recruiter_email);

                if ($name === null && $url === null && $email === null) {
                    continue;
                }

                $nameKey = $this->normalize($name);
                $emailKey = $this->normalize($email);
                $urlKey = $this->normalizeUrl($url);

                $contactId = null;

                if ($nameKey !== null && isset($contactsByName[$nameKey])) {
                    $contactId = $contactsByName[$nameKey];
                } elseif ($emailKey !== null && isset($contactsByEmail[$emailKey])) {
                    $contactId = $contactsByEmail[$emailKey];
                } elseif ($urlKey !== null && isset($contactsByUrl[$urlKey])) {
                    $contactId = $contactsByUrl[$urlKey];
                }

                if ($contactId === null) {
                    $contactId = (int) DB::table('contacts')->insertGetId([
                        'name' => $name,
                        'organization' => null,
                        'url' => $url,
                        'email' => $email,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $contactData[$contactId] = [
                        'name' => $name,
                        'url' => $url,
                        'email' => $email,
                    ];
                } else {
                    $updates = [];

                    foreach ([
                        'name' => $name,
                        'url' => $url,
                        'email' => $email,
                    ] as $field => $value) {
                        if (
                            $contactData[$contactId][$field] === null
                            && $value !== null
                        ) {
                            $updates[$field] = $value;
                            $contactData[$contactId][$field] = $value;
                        }
                    }

                    if ($updates !== []) {
                        $updates['updated_at'] = now();

                        DB::table('contacts')
                            ->where('id', $contactId)
                            ->update($updates);
                    }
                }

                $this->registerContact(
                    $contactId,
                    $contactData[$contactId],
                    $contactsByName,
                    $contactsByEmail,
                    $contactsByUrl,
                );

                $relationExists = DB::table('contact_job_application')
                    ->where('contact_id', $contactId)
                    ->where('job_application_id', $application->id)
                    ->exists();

                if (! $relationExists) {
                    DB::table('contact_job_application')->insert([
                        'contact_id' => $contactId,
                        'job_application_id' => $application->id,
                        'role' => 'recruiter',
                        'is_primary' => true,
                        'source' => null,
                        'context' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });
    }

    public function down(): void
    {
        // Intentionally left empty.
        // Migrated contacts may later be enriched or reused by other applications,
        // so deleting them automatically during rollback could destroy valid data.
    }

    private function registerContact(
        int $contactId,
        array $contact,
        array &$contactsByName,
        array &$contactsByEmail,
        array &$contactsByUrl,
    ): void {
        $nameKey = $this->normalize($contact['name']);
        $emailKey = $this->normalize($contact['email']);
        $urlKey = $this->normalizeUrl($contact['url']);

        if ($nameKey !== null) {
            $contactsByName[$nameKey] = $contactId;
        }

        if ($emailKey !== null) {
            $contactsByEmail[$emailKey] = $contactId;
        }

        if ($urlKey !== null) {
            $contactsByUrl[$urlKey] = $contactId;
        }
    }

    private function clean(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function normalize(?string $value): ?string
    {
        $value = $this->clean($value);

        if ($value === null) {
            return null;
        }

        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return Str::lower($value);
    }

    private function normalizeUrl(?string $value): ?string
    {
        $value = $this->clean($value);

        if ($value === null) {
            return null;
        }

        return Str::lower(rtrim($value, '/'));
    }
};
