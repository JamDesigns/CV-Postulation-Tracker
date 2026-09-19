<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('job_application_events', function (Blueprint $table): void {
            $table
                ->string('communication_channel')
                ->nullable()
                ->after('type');

            $table->index('communication_channel');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_application_events', function (Blueprint $table): void {
            $table->dropIndex(['communication_channel']);
            $table->dropColumn('communication_channel');
        });
    }
};
