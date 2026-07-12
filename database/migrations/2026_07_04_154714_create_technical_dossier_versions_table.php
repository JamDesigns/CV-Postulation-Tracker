<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('technical_dossier_versions', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('version_label', 50);
            $table->string('language', 10);
            $table->string('pdf_path')->nullable();
            $table->string('docx_path')->nullable();
            $table->text('summary')->nullable();
            $table->text('content_snapshot')->nullable();
            $table->boolean('is_active')->default(false);
            $table->date('published_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('job_applications', function (Blueprint $table): void {
            $table
                ->foreignId('technical_dossier_version_id')
                ->nullable()
                ->after('dossier_sent')
                ->constrained('technical_dossier_versions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('technical_dossier_version_id');
        });

        Schema::dropIfExists('technical_dossier_versions');
    }
};
