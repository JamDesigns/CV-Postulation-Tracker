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

        $applicationLocales = DB::table('job_applications')
            ->pluck('language', 'id');

        $this->copyToJson(
            table: 'job_applications',
            columns: ['adaptation_summary', 'next_step'],
            localeUsing: fn (object $record): string => $this->locale($record->language),
        );

        $this->copyToJson(
            table: 'job_application_events',
            columns: ['title', 'body'],
            localeUsing: fn (object $record): string => $this->locale(
                $applicationLocales[$record->job_application_id] ?? null,
            ),
        );

        $this->copyToJson(
            table: 'interviews',
            columns: [
                'people',
                'expected_questions',
                'strengths_to_defend',
                'risks_to_clarify',
                'notes',
            ],
            localeUsing: fn (object $record): string => $this->locale(
                $applicationLocales[$record->job_application_id] ?? null,
            ),
        );

        $this->copyToJson(
            table: 'technical_dossier_versions',
            columns: ['content_snapshot', 'notes'],
            localeUsing: fn (object $record): string => $this->locale($record->language),
        );

        $this->copyToJson(
            table: 'cv_versions',
            columns: [
                'highlighted_stack',
                'highlighted_experience',
                'adaptation_notes',
            ],
            localeUsing: fn (object $record): string => $this->locale($record->language),
        );

        $this->replaceWithTranslationColumns(
            'job_applications',
            ['adaptation_summary', 'next_step'],
        );

        $this->replaceWithTranslationColumns(
            'job_application_events',
            ['title', 'body'],
        );

        $this->replaceWithTranslationColumns(
            'interviews',
            [
                'people',
                'expected_questions',
                'strengths_to_defend',
                'risks_to_clarify',
                'notes',
            ],
        );

        $this->replaceWithTranslationColumns(
            'technical_dossier_versions',
            ['content_snapshot', 'notes'],
            ['summary'],
        );

        $this->replaceWithTranslationColumns(
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
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table): void {
            $table->text('adaptation_summary_original')->nullable();
            $table->text('next_step_original')->nullable();
        });

        Schema::table('job_application_events', function (Blueprint $table): void {
            $table->string('title_original')->nullable();
            $table->text('body_original')->nullable();
        });

        Schema::table('interviews', function (Blueprint $table): void {
            $table->text('people_original')->nullable();
            $table->longText('expected_questions_original')->nullable();
            $table->longText('strengths_to_defend_original')->nullable();
            $table->longText('risks_to_clarify_original')->nullable();
            $table->longText('notes_original')->nullable();
        });

        Schema::table('technical_dossier_versions', function (Blueprint $table): void {
            $table->text('content_snapshot_original')->nullable();
            $table->text('notes_original')->nullable();
        });

        Schema::table('cv_versions', function (Blueprint $table): void {
            $table->text('highlighted_stack_original')->nullable();
            $table->text('highlighted_experience_original')->nullable();
            $table->text('adaptation_notes_original')->nullable();
        });

        $applicationLocales = DB::table('job_applications')
            ->pluck('language', 'id');

        $this->copyFromJson(
            table: 'job_applications',
            columns: ['adaptation_summary', 'next_step'],
            localeUsing: fn (object $record): string => $this->locale($record->language),
        );

        $this->copyFromJson(
            table: 'job_application_events',
            columns: ['title', 'body'],
            localeUsing: fn (object $record): string => $this->locale(
                $applicationLocales[$record->job_application_id] ?? null,
            ),
        );

        $this->copyFromJson(
            table: 'interviews',
            columns: [
                'people',
                'expected_questions',
                'strengths_to_defend',
                'risks_to_clarify',
                'notes',
            ],
            localeUsing: fn (object $record): string => $this->locale(
                $applicationLocales[$record->job_application_id] ?? null,
            ),
        );

        $this->copyFromJson(
            table: 'technical_dossier_versions',
            columns: ['content_snapshot', 'notes'],
            localeUsing: fn (object $record): string => $this->locale($record->language),
        );

        $this->copyFromJson(
            table: 'cv_versions',
            columns: [
                'highlighted_stack',
                'highlighted_experience',
                'adaptation_notes',
            ],
            localeUsing: fn (object $record): string => $this->locale($record->language),
        );

        $this->restoreOriginalColumns(
            'job_applications',
            ['adaptation_summary', 'next_step'],
        );

        $this->restoreOriginalColumns(
            'job_application_events',
            ['title', 'body'],
        );

        $this->restoreOriginalColumns(
            'interviews',
            [
                'people',
                'expected_questions',
                'strengths_to_defend',
                'risks_to_clarify',
                'notes',
            ],
        );

        $this->restoreOriginalColumns(
            'technical_dossier_versions',
            ['content_snapshot', 'notes'],
        );

        Schema::table('technical_dossier_versions', function (Blueprint $table): void {
            $table->text('summary')->nullable();
        });

        $this->restoreOriginalColumns(
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
     * @param  array<int, string>  $columns
     */
    private function copyToJson(
        string $table,
        array $columns,
        Closure $localeUsing,
    ): void {
        DB::table($table)
            ->orderBy('id')
            ->chunkById(100, function ($records) use ($table, $columns, $localeUsing): void {
                foreach ($records as $record) {
                    $locale = $localeUsing($record);
                    $values = [];

                    foreach ($columns as $column) {
                        $value = $record->{$column};

                        $values["{$column}_translations"] = $value === null
                            ? null
                            : json_encode(
                                [$locale => $value],
                                JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
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
    private function copyFromJson(
        string $table,
        array $columns,
        Closure $localeUsing,
    ): void {
        DB::table($table)
            ->orderBy('id')
            ->chunkById(100, function ($records) use ($table, $columns, $localeUsing): void {
                foreach ($records as $record) {
                    $locale = $localeUsing($record);
                    $values = [];

                    foreach ($columns as $column) {
                        $values["{$column}_original"] = $this->translation(
                            $record->{$column},
                            $locale,
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
     * @param  array<int, string>  $additionalColumnsToDrop
     */
    private function replaceWithTranslationColumns(
        string $tableName,
        array $columns,
        array $additionalColumnsToDrop = [],
    ): void {
        Schema::table($tableName, function (Blueprint $table) use (
            $columns,
            $additionalColumnsToDrop,
        ): void {
            $table->dropColumn([
                ...$columns,
                ...$additionalColumnsToDrop,
            ]);
        });

        Schema::table($tableName, function (Blueprint $table) use ($columns): void {
            foreach ($columns as $column) {
                $table->renameColumn("{$column}_translations", $column);
            }
        });
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function restoreOriginalColumns(
        string $tableName,
        array $columns,
    ): void {
        Schema::table($tableName, function (Blueprint $table) use ($columns): void {
            $table->dropColumn($columns);
        });

        Schema::table($tableName, function (Blueprint $table) use ($columns): void {
            foreach ($columns as $column) {
                $table->renameColumn("{$column}_original", $column);
            }
        });
    }

    private function locale(mixed $language): string
    {
        return match ($language) {
            'en', 'english' => 'en',
            'fr', 'french' => 'fr',
            default => 'es',
        };
    }

    private function translation(mixed $value, string $locale): ?string
    {
        if ($value === null) {
            return null;
        }

        $translations = is_array($value)
            ? $value
            : json_decode((string) $value, true, flags: JSON_THROW_ON_ERROR);

        return $translations[$locale]
            ?? collect($translations)->first();
    }
};
