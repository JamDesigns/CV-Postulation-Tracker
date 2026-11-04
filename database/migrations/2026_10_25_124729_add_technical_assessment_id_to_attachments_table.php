<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->foreignId('technical_assessment_id')
                ->nullable()
                ->after('job_application_id')
                ->constrained('technical_assessments')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->dropForeign(['technical_assessment_id']);
            $table->dropColumn('technical_assessment_id');
        });
    }
};
