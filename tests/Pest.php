<?php

use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()
    ->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

function companyId(string $name): int
{
    return (int) Company::query()
        ->firstOrCreate(['name' => $name])
        ->getKey();
}
