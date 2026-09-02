<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->renameColumn('salary', 'salary_min');
        });

        Schema::table('job_applications', function (Blueprint $table) {
            $table->decimal('salary_max', 12, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn('salary_max');
        });

        Schema::table('job_applications', function (Blueprint $table) {
            $table->renameColumn('salary_min', 'salary');
        });
    }
};
