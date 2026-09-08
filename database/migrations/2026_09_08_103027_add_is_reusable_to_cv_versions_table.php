<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cv_versions', function (Blueprint $table): void {
            $table->boolean('is_reusable')
                ->default(false)
                ->after('base_profile');
        });

        DB::table('cv_versions')
            ->where(
                'name',
                'CV General para Validaciones y Autocandidaturas',
            )
            ->update([
                'is_reusable' => true,
            ]);

        Schema::table('job_applications', function (Blueprint $table): void {
            $table->dropUnique(
                'job_applications_cv_version_id_unique',
            );
        });
    }

    public function down(): void
    {
        $hasDuplicateCvUsage = DB::table('job_applications')
            ->select('cv_version_id')
            ->whereNotNull('cv_version_id')
            ->groupBy('cv_version_id')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($hasDuplicateCvUsage) {
            throw new RuntimeException(
                'Cannot restore CV uniqueness while a CV version is assigned to multiple job applications.',
            );
        }

        Schema::table('job_applications', function (Blueprint $table): void {
            $table->unique(
                'cv_version_id',
                'job_applications_cv_version_id_unique',
            );
        });

        Schema::table('cv_versions', function (Blueprint $table): void {
            $table->dropColumn('is_reusable');
        });
    }
};
