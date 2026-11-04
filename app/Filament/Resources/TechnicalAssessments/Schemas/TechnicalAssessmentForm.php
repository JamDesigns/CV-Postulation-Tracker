<?php

namespace App\Filament\Resources\TechnicalAssessments\Schemas;

use App\Enums\TechnicalAssessmentDirection;
use App\Enums\TechnicalAssessmentModality;
use App\Enums\TechnicalAssessmentResult;
use App\Enums\TechnicalAssessmentStatus;
use App\Enums\TechnicalAssessmentType;
use App\Models\TechnicalAssessment;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class TechnicalAssessmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(self::components());
    }

    public static function components(bool $includeJobApplicationSelect = true): array
    {
        return [
            Tabs::make(__('technical-assessments.sections.form'))
                ->tabs([
                    Tabs\Tab::make(__('technical-assessments.sections.main'))
                        ->schema([
                            ...($includeJobApplicationSelect ? [
                                Select::make('job_application_id')
                                    ->label(__('technical-assessments.fields.job_application_id'))
                                    ->relationship(
                                        'jobApplication',
                                        'job_title',
                                        modifyQueryUsing: fn (Builder $query): Builder => $query
                                            ->leftJoin(
                                                'companies',
                                                'companies.id',
                                                '=',
                                                'job_applications.company_id',
                                            )
                                            ->select('job_applications.*')
                                            ->with('company'),
                                    )
                                    ->getOptionLabelFromRecordUsing(
                                        fn ($record): string => "{$record->company?->name} - {$record->job_title}",
                                    )
                                    ->searchable([
                                        'companies.name',
                                        'job_applications.job_title',
                                    ])
                                    ->preload()
                                    ->required()
                                    ->columnSpanFull(),
                            ] : []),

                            Select::make('test_type')
                                ->label(__('technical-assessments.fields.test_type'))
                                ->options(TechnicalAssessmentType::options())
                                ->required()
                                ->live(),

                            Select::make('modality')
                                ->label(__('technical-assessments.fields.modality'))
                                ->options(TechnicalAssessmentModality::options())
                                ->required(
                                    fn (Get $get): bool => $get('test_type')
                                        === TechnicalAssessmentType::Live->value,
                                )
                                ->visible(
                                    fn (Get $get): bool => $get('test_type')
                                        === TechnicalAssessmentType::Live->value,
                                )
                                ->dehydrated(
                                    fn (Get $get): bool => $get('test_type')
                                        === TechnicalAssessmentType::Live->value,
                                ),

                            Select::make('direction')
                                ->label(__('technical-assessments.fields.direction'))
                                ->options(TechnicalAssessmentDirection::options())
                                ->default(TechnicalAssessmentDirection::Received->value)
                                ->required(),

                            Select::make('status')
                                ->label(__('technical-assessments.fields.status'))
                                ->options(TechnicalAssessmentStatus::options())
                                ->default(TechnicalAssessmentStatus::Pending->value)
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, Set $set, Get $get): void {
                                    if (
                                        $state === TechnicalAssessmentStatus::Finished->value
                                        && blank($get('finished_at'))
                                    ) {
                                        $set('finished_at', now()->format('Y-m-d H:i:s'));
                                    }
                                }),

                            Select::make('result')
                                ->label(__('technical-assessments.fields.result'))
                                ->options(TechnicalAssessmentResult::options())
                                ->default(TechnicalAssessmentResult::Pending->value)
                                ->required(
                                    fn (Get $get): bool => $get('status')
                                        === TechnicalAssessmentStatus::Finished->value,
                                )
                                ->visible(
                                    fn (Get $get): bool => $get('status')
                                        === TechnicalAssessmentStatus::Finished->value,
                                )
                                ->dehydrated(
                                    fn (Get $get): bool => $get('status')
                                        === TechnicalAssessmentStatus::Finished->value,
                                ),
                        ])
                        ->columns(2),

                    Tabs\Tab::make(__('technical-assessments.sections.dates'))
                        ->schema([
                            DateTimePicker::make('received_at')
                                ->label(__('technical-assessments.fields.received_at'))
                                ->seconds(false),

                            DateTimePicker::make('scheduled_at')
                                ->label(__('technical-assessments.fields.scheduled_at'))
                                ->seconds(false)
                                ->visible(
                                    fn (Get $get): bool => $get('test_type')
                                        === TechnicalAssessmentType::Live->value,
                                ),

                            DateTimePicker::make('deadline_at')
                                ->label(__('technical-assessments.fields.deadline_at'))
                                ->seconds(false)
                                ->visible(
                                    fn (Get $get): bool => $get('test_type')
                                        === TechnicalAssessmentType::AtHome->value,
                                ),

                            DateTimePicker::make('finished_at')
                                ->label(__('technical-assessments.fields.finished_at'))
                                ->seconds(false)
                                ->visible(
                                    fn (Get $get): bool => $get('status')
                                        === TechnicalAssessmentStatus::Finished->value,
                                )
                                ->rule(function (Get $get, ?TechnicalAssessment $record): \Closure {
                                    return function (string $attribute, mixed $value, \Closure $fail) use ($get, $record): void {
                                        if (blank($value)) {
                                            return;
                                        }

                                        $receivedAt = $get('received_at');

                                        $startedAt = filled($receivedAt)
                                            ? Carbon::parse($receivedAt)
                                            : $record?->created_at;

                                        if (
                                            $startedAt !== null
                                            && Carbon::parse($value)->lt($startedAt)
                                        ) {
                                            $fail(__('technical-assessments.validation.finished_before_started'));
                                        }
                                    };
                                }),

                            DateTimePicker::make('result_communicated_at')
                                ->label(__('technical-assessments.fields.result_communicated_at'))
                                ->seconds(false)
                                ->visible(
                                    fn (Get $get): bool => $get('status')
                                        === TechnicalAssessmentStatus::Finished->value
                                        && in_array($get('result'), [
                                            TechnicalAssessmentResult::Passed->value,
                                            TechnicalAssessmentResult::NotPassed->value,
                                        ], true),
                                ),
                        ])
                        ->columns(2),

                    Tabs\Tab::make(__('technical-assessments.sections.documentation'))
                        ->schema([
                            TextInput::make('source_url')
                                ->label(__('technical-assessments.fields.source_url'))
                                ->url()
                                ->maxLength(2048),

                            TextInput::make('delivery_url')
                                ->label(__('technical-assessments.fields.delivery_url'))
                                ->url()
                                ->maxLength(2048),

                            Textarea::make('notes')
                                ->label(__('technical-assessments.fields.notes'))
                                ->rows(5)
                                ->autosize()
                                ->columnSpanFull(),
                        ])
                        ->columns(2),
                ])
                ->columnSpanFull(),
        ];
    }
}
