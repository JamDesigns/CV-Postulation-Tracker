<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table): void {
            $table->dropColumn([
                'recruiter_name',
                'recruiter_url',
                'recruiter_email',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table): void {
            $table->string('recruiter_name')->nullable();
            $table->text('recruiter_url')->nullable();
            $table->string('recruiter_email')->nullable();
        });
    }
};
