<?php

use App\Models\Company;
use App\Models\JobApplication;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;

test('it stores company data', function () {
    $company = Company::query()->create([
        'name' => 'Test Company',
        'website' => 'https://example.com',
        'linkedin_url' => 'https://www.linkedin.com/company/test-company',
        'notes' => 'Company notes',
    ]);

    expect($company->name)
        ->toBe('Test Company')
        ->and($company->website)
        ->toBe('https://example.com')
        ->and($company->linkedin_url)
        ->toBe('https://www.linkedin.com/company/test-company')
        ->and($company->notes)
        ->toBe('Company notes');
});

test('it relates companies and job applications', function () {
    $company = Company::query()->create([
        'name' => 'Related Company',
    ]);

    $jobApplication = JobApplication::query()->create([
        'company_id' => $company->id,
        'job_title' => 'Full Stack Developer',
    ]);

    expect($company->jobApplications()->first()?->is($jobApplication))
        ->toBeTrue()
        ->and($jobApplication->company?->is($company))
        ->toBeTrue();
});

test('it prevents duplicate company names', function () {
    Company::query()->create([
        'name' => 'Unique Company',
    ]);

    expect(fn () => Company::query()->create([
        'name' => 'Unique Company',
    ]))->toThrow(UniqueConstraintViolationException::class);
});

test('it prevents deleting a company with job applications', function () {
    $company = Company::query()->create([
        'name' => 'Protected Company',
    ]);

    JobApplication::query()->create([
        'company_id' => $company->id,
        'job_title' => 'Backend Developer',
    ]);

    expect(fn () => $company->delete())
        ->toThrow(QueryException::class);

    expect(Company::query()->whereKey($company->id)->exists())
        ->toBeTrue();
});
