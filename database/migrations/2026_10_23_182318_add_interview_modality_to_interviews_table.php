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
        Schema::table('interviews', function (Blueprint $table): void {
            $table->string('interview_modality')
                ->nullable()
                ->after('interview_type');

            $table->index('interview_modality');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interviews', function (Blueprint $table): void {
            $table->dropIndex(['interview_modality']);
            $table->dropColumn('interview_modality');
        });
    }
};
