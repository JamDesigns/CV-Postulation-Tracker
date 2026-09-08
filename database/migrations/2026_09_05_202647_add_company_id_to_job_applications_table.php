<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table): void {
            $table->foreignId('company_id')
                ->nullable()
                ->after('cv_version_id')
                ->constrained('companies')
                ->restrictOnDelete();
        });

        $now = now();

        $companyNames = DB::table('job_applications')
            ->select('company_name')
            ->distinct()
            ->orderBy('company_name')
            ->pluck('company_name');

        foreach ($companyNames as $companyName) {
            $companyId = DB::table('companies')->insertGetId([
                'name' => $companyName,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('job_applications')
                ->where('company_name', $companyName)
                ->update([
                    'company_id' => $companyId,
                ]);
        }

        if (
            DB::table('job_applications')
                ->whereNull('company_id')
                ->exists()
        ) {
            throw new RuntimeException(
                'Unable to assign a company to every job application.',
            );
        }

        Schema::table('job_applications', function (Blueprint $table): void {
            $table->dropUnique(
                'job_applications_company_title_url_unique',
            );

            $table->dropIndex(
                'job_applications_company_name_index',
            );
        });

        DB::statement(
            'DROP INDEX IF EXISTS job_applications_company_title_without_url_unique',
        );

        Schema::table('job_applications', function (Blueprint $table): void {
            $table->foreignId('company_id')
                ->nullable(false)
                ->change();

            $table->dropColumn('company_name');
        });

        Schema::table('job_applications', function (Blueprint $table): void {
            $table->unique(
                ['company_id', 'job_title', 'job_url'],
                'job_applications_company_title_url_unique',
            );
        });

        DB::statement(
            'CREATE UNIQUE INDEX job_applications_company_title_without_url_unique
            ON job_applications (company_id, job_title)
            WHERE job_url IS NULL',
        );
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table): void {
            $table->string('company_name')
                ->nullable()
                ->after('company_id');
        });

        $applications = DB::table('job_applications')
            ->select(['id', 'company_id'])
            ->orderBy('id')
            ->get();

        foreach ($applications as $application) {
            $companyName = DB::table('companies')
                ->where('id', $application->company_id)
                ->value('name');

            if ($companyName === null) {
                throw new RuntimeException(
                    "Unable to restore company name for job application {$application->id}.",
                );
            }

            DB::table('job_applications')
                ->where('id', $application->id)
                ->update([
                    'company_name' => $companyName,
                ]);
        }

        DB::statement(
            'DROP INDEX IF EXISTS job_applications_company_title_without_url_unique',
        );

        Schema::table('job_applications', function (Blueprint $table): void {
            $table->dropUnique(
                'job_applications_company_title_url_unique',
            );

            $table->dropConstrainedForeignId('company_id');

            $table->string('company_name')
                ->nullable(false)
                ->change();

            $table->index('company_name');
        });

        Schema::table('job_applications', function (Blueprint $table): void {
            $table->unique(
                ['company_name', 'job_title', 'job_url'],
                'job_applications_company_title_url_unique',
            );
        });

        DB::statement(
            'CREATE UNIQUE INDEX job_applications_company_title_without_url_unique
            ON job_applications (company_name, job_title)
            WHERE job_url IS NULL',
        );
    }
};
