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
        Schema::create('technical_assessments', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('job_application_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('test_type');
            $table->string('modality')->nullable();
            $table->string('direction');
            $table->string('status')->default('pending');
            $table->string('result')->nullable();

            $table->dateTime('received_at')->nullable();
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('deadline_at')->nullable();
            $table->dateTime('finished_at')->nullable();
            $table->dateTime('result_communicated_at')->nullable();

            $table->text('source_url')->nullable();
            $table->text('delivery_url')->nullable();
            $table->longText('notes')->nullable();

            $table->timestamps();

            $table->index('test_type');
            $table->index('modality');
            $table->index('direction');
            $table->index('status');
            $table->index('result');
            $table->index('scheduled_at');
            $table->index('deadline_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('technical_assessments');
    }
};
