<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table): void {
            $table->unique(
                ['company_name', 'job_title', 'job_url'],
                'job_applications_company_title_url_unique'
            );
        });

        DB::statement(
            'CREATE UNIQUE INDEX job_applications_company_title_without_url_unique
            ON job_applications (company_name, job_title)
            WHERE job_url IS NULL'
        );

        Schema::table('cv_versions', function (Blueprint $table): void {
            $table->unique(
                'name',
                'cv_versions_name_unique'
            );
        });

        Schema::table('technical_dossier_versions', function (Blueprint $table): void {
            $table->unique(
                ['name', 'version_label', 'language'],
                'technical_dossiers_name_version_language_unique'
            );
        });

        Schema::table('interviews', function (Blueprint $table): void {
            $table->unique(
                ['job_application_id', 'interview_at', 'interview_type'],
                'interviews_application_at_type_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interviews', function (Blueprint $table): void {
            $table->dropUnique('interviews_application_at_type_unique');
        });

        Schema::table('technical_dossier_versions', function (Blueprint $table): void {
            $table->dropUnique('technical_dossiers_name_version_language_unique');
        });

        Schema::table('cv_versions', function (Blueprint $table): void {
            $table->dropUnique('cv_versions_name_unique');
        });

        DB::statement(
            'DROP INDEX IF EXISTS job_applications_company_title_without_url_unique'
        );

        Schema::table('job_applications', function (Blueprint $table): void {
            $table->dropUnique('job_applications_company_title_url_unique');
        });
    }
};
