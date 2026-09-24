<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->decimal('salary_expectation_min', 12, 2)->nullable();
            $table->decimal('salary_expectation_max', 12, 2)->nullable();
            $table->string('salary_expectation_currency', 3)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn([
                'salary_expectation_min',
                'salary_expectation_max',
                'salary_expectation_currency',
            ]);
        });
    }
};
