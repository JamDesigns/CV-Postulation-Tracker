<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table): void {
            $table->string('rejection_reason')
                ->nullable()
                ->after('status')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table): void {
            $table->dropIndex(
                'job_applications_rejection_reason_index',
            );

            $table->dropColumn('rejection_reason');
        });
    }
};
