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
        Schema::create('job_application_exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_application_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('currency', 3);
            $table->decimal('rate', 20, 10);
            $table->date('rate_date');
            $table->timestamps();

            $table->unique(
                ['job_application_id', 'currency'],
                'job_application_exchange_rates_unique',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_application_exchange_rates');
    }
};
