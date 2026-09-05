<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_application_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_application_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->date('action_date');
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->unique([
                'job_application_id',
                'action_date',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_application_reminders');
    }
};
