<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_job_application', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('contact_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('job_application_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('role')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->string('source')->nullable();
            $table->text('context')->nullable();

            $table->timestamps();

            $table->unique([
                'contact_id',
                'job_application_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_job_application');
    }
};
