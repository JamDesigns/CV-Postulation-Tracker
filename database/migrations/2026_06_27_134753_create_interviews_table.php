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
        Schema::create('interviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('job_application_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->dateTime('interview_at')->nullable();
            $table->string('interview_type')->default('hr');

            $table->text('people')->nullable();

            $table->longText('expected_questions')->nullable();
            $table->longText('strengths_to_defend')->nullable();
            $table->longText('risks_to_clarify')->nullable();

            $table->string('result')->default('pending');
            $table->longText('notes')->nullable();

            $table->timestamps();

            $table->index('interview_at');
            $table->index('interview_type');
            $table->index('result');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interviews');
    }
};
