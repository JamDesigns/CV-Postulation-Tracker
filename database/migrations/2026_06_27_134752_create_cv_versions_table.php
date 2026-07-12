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
        Schema::create('cv_versions', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('language')->default('spanish');
            $table->string('base_profile')->default('full_stack');

            $table->string('pdf_path')->nullable();
            $table->string('docx_path')->nullable();

            $table->text('highlighted_stack')->nullable();
            $table->text('highlighted_experience')->nullable();
            $table->text('adaptation_notes')->nullable();

            $table->timestamps();

            $table->index('language');
            $table->index('base_profile');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cv_versions');
    }
};
