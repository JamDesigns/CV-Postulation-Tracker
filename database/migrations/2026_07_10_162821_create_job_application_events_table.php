<?php

use App\Enums\JobApplicationEventType;
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
        Schema::create('job_application_events', function (Blueprint $table): void {
            $table->id();

            $table
                ->foreignId('job_application_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('type')->default(JobApplicationEventType::ManualNote->value);
            $table->dateTime('occurred_at')->nullable();

            $table->string('title');
            $table->text('body')->nullable();

            $table->string('status_from')->nullable();
            $table->string('status_to')->nullable();

            $table->date('next_action_at')->nullable();

            $table->timestamps();

            $table->index('type');
            $table->index('occurred_at');
            $table->index('next_action_at');
            $table->index(['job_application_id', 'occurred_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_application_events');
    }
};
