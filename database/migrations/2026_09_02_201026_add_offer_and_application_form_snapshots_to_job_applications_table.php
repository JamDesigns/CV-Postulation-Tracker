<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->longText('offer_snapshot')->nullable();
            $table->timestamp('offer_snapshot_at')->nullable();
            $table->json('application_form_snapshot')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn([
                'offer_snapshot',
                'offer_snapshot_at',
                'application_form_snapshot',
            ]);
        });
    }
};
