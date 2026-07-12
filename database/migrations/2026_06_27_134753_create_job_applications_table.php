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
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cv_version_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('company_name');
            $table->string('job_title');
            $table->text('job_url')->nullable();

            $table->string('source')->default('linkedin');
            $table->string('status')->default('pending');
            $table->date('sent_at')->nullable();

            $table->string('location')->nullable();
            $table->string('work_mode')->default('not_specified');

            $table->string('recruiter_name')->nullable();
            $table->text('recruiter_url')->nullable();

            $table->text('main_stack')->nullable();

            $table->boolean('dossier_sent')->default(false);

            $table->longText('message_sent')->nullable();
            $table->text('adaptation_summary')->nullable();
            $table->text('notes')->nullable();
            $table->text('next_step')->nullable();

            $table->timestamps();

            $table->index('company_name');
            $table->index('status');
            $table->index('source');
            $table->index('work_mode');
            $table->index('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
