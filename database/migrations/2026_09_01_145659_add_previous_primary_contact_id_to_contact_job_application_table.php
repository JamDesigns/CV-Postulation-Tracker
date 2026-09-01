<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_job_application', function (Blueprint $table) {
            $table->foreignId('previous_primary_contact_id')
                ->nullable()
                ->after('is_primary')
                ->constrained('contacts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('contact_job_application', function (Blueprint $table) {
            $table->dropConstrainedForeignId('previous_primary_contact_id');
        });
    }
};
