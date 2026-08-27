<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table): void {
            $table->text('adaptation_summary_plain')->nullable();
            $table->text('next_step_plain')->nullable();
        });

        Schema::table('job_application_events', function (Blueprint $table): void {
            $table->string('title_plain')->nullable();
            $table->text('body_plain')->nullable();
        });

        Schema::table('interviews', function (Blueprint $table): void {
            $table->text('people_plain')->nullable();
            $table->longText('expected_questions_plain')->nullable();
            $table->longText('strengths_to_defend_plain')->nullable();
            $table->longText('risks_to_clarify_plain')->nullable();
            $table->longText('notes_plain')->nullable();
        });

        Schema::table('technical_dossier_versions', function (Blueprint $table): void {
            $table->text('content_snapshot_plain')->nullable();
            $table->text('notes_plain')->nullable();
        });

        Schema::table('cv_versions', function (Blueprint $table): void {
            $table->text('highlighted_stack_plain')->nullable();
            $table->text('highlighted_experience_plain')->nullable();
            $table->text('adaptation_notes_plain')->nullable();
        });

        $this->copySpanishContent(
            table: 'job_applications',
            columns: ['adaptation_summary', 'next_step'],
        );

        $this->copySpanishContent(
            table: 'job_application_events',
            columns: ['title', 'body'],
        );

        $this->copySpanishContent(
            table: 'interviews',
            columns: [
                'people',
                'expected_questions',
                'strengths_to_defend',
                'risks_to_clarify',
                'notes',
            ],
        );

        $this->copySpanishContent(
            table: 'technical_dossier_versions',
            columns: ['content_snapshot', 'notes'],
        );

        $this->copySpanishContent(
            table: 'cv_versions',
            columns: [
                'highlighted_stack',
                'highlighted_experience',
                'adaptation_notes',
            ],
        );

        $this->replaceJsonColumns(
            'job_applications',
            ['adaptation_summary', 'next_step'],
        );

        $this->replaceJsonColumns(
            'job_application_events',
            ['title', 'body'],
        );

        $this->replaceJsonColumns(
            'interviews',
            [
                'people',
                'expected_questions',
                'strengths_to_defend',
                'risks_to_clarify',
                'notes',
            ],
        );

        $this->replaceJsonColumns(
            'technical_dossier_versions',
            ['content_snapshot', 'notes'],
        );

        $this->replaceJsonColumns(
            'cv_versions',
            [
                'highlighted_stack',
                'highlighted_experience',
                'adaptation_notes',
            ],
        );

        Schema::table('job_application_events', function (Blueprint $table): void {
            $table->string('title')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table): void {
            $table->json('adaptation_summary_translations')->nullable();
            $table->json('next_step_translations')->nullable();
        });

        Schema::table('job_application_events', function (Blueprint $table): void {
            $table->json('title_translations')->nullable();
            $table->json('body_translations')->nullable();
        });

        Schema::table('interviews', function (Blueprint $table): void {
            $table->json('people_translations')->nullable();
            $table->json('expected_questions_translations')->nullable();
            $table->json('strengths_to_defend_translations')->nullable();
            $table->json('risks_to_clarify_translations')->nullable();
            $table->json('notes_translations')->nullable();
        });

        Schema::table('technical_dossier_versions', function (Blueprint $table): void {
            $table->json('content_snapshot_translations')->nullable();
            $table->json('notes_translations')->nullable();
        });

        Schema::table('cv_versions', function (Blueprint $table): void {
            $table->json('highlighted_stack_translations')->nullable();
            $table->json('highlighted_experience_translations')->nullable();
            $table->json('adaptation_notes_translations')->nullable();
        });

        $this->copyToSpanishJson(
            table: 'job_applications',
            columns: ['adaptation_summary', 'next_step'],
        );

        $this->copyToSpanishJson(
            table: 'job_application_events',
            columns: ['title', 'body'],
        );

        $this->copyToSpanishJson(
            table: 'interviews',
            columns: [
                'people',
                'expected_questions',
                'strengths_to_defend',
                'risks_to_clarify',
                'notes',
            ],
        );

        $this->copyToSpanishJson(
            table: 'technical_dossier_versions',
            columns: ['content_snapshot', 'notes'],
        );

        $this->copyToSpanishJson(
            table: 'cv_versions',
            columns: [
                'highlighted_stack',
                'highlighted_experience',
                'adaptation_notes',
            ],
        );

        $this->restoreJsonColumns(
            'job_applications',
            ['adaptation_summary', 'next_step'],
        );

        $this->restoreJsonColumns(
            'job_application_events',
            ['title', 'body'],
        );

        $this->restoreJsonColumns(
            'interviews',
            [
                'people',
                'expected_questions',
                'strengths_to_defend',
                'risks_to_clarify',
                'notes',
            ],
        );

        $this->restoreJsonColumns(
            'technical_dossier_versions',
            ['content_snapshot', 'notes'],
        );

        $this->restoreJsonColumns(
            'cv_versions',
            [
                'highlighted_stack',
                'highlighted_experience',
                'adaptation_notes',
            ],
        );

        Schema::table('job_application_events', function (Blueprint $table): void {
            $table->json('title')->nullable(false)->change();
        });
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function copySpanishContent(string $table, array $columns): void
    {
        DB::table($table)
            ->orderBy('id')
            ->chunkById(100, function ($records) use ($table, $columns): void {
                foreach ($records as $record) {
                    $values = [];

                    foreach ($columns as $column) {
                        $values["{$column}_plain"] = $this->spanishValue(
                            $record->{$column},
                        );
                    }

                    DB::table($table)
                        ->where('id', $record->id)
                        ->update($values);
                }
            });
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function copyToSpanishJson(string $table, array $columns): void
    {
        DB::table($table)
            ->orderBy('id')
            ->chunkById(100, function ($records) use ($table, $columns): void {
                foreach ($records as $record) {
                    $values = [];

                    foreach ($columns as $column) {
                        $value = $record->{$column};

                        $values["{$column}_translations"] = $value === null
                            ? null
                            : json_encode(
                                ['es' => $value],
                                JSON_THROW_ON_ERROR
                                    | JSON_UNESCAPED_UNICODE
                                    | JSON_UNESCAPED_SLASHES,
                            );
                    }

                    DB::table($table)
                        ->where('id', $record->id)
                        ->update($values);
                }
            });
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function replaceJsonColumns(string $tableName, array $columns): void
    {
        Schema::table($tableName, function (Blueprint $table) use ($columns): void {
            $table->dropColumn($columns);
        });

        Schema::table($tableName, function (Blueprint $table) use ($columns): void {
            foreach ($columns as $column) {
                $table->renameColumn("{$column}_plain", $column);
            }
        });
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function restoreJsonColumns(string $tableName, array $columns): void
    {
        Schema::table($tableName, function (Blueprint $table) use ($columns): void {
            $table->dropColumn($columns);
        });

        Schema::table($tableName, function (Blueprint $table) use ($columns): void {
            foreach ($columns as $column) {
                $table->renameColumn("{$column}_translations", $column);
            }
        });
    }

    private function spanishValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            return $value['es']
                ?? collect($value)->first();
        }

        $value = (string) $value;

        if (! json_validate($value)) {
            return $value;
        }

        $translations = json_decode($value, true);

        if (! is_array($translations)) {
            return $value;
        }

        return $translations['es']
            ?? collect($translations)->first();
    }
};
