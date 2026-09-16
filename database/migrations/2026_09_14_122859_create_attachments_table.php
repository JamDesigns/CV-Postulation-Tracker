<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('job_application_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('category');
            $table->string('category_other')->nullable();
            $table->string('direction')->default('not_specified');

            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');

            $table->date('document_date')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('category');
            $table->index('direction');
            $table->index('document_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
