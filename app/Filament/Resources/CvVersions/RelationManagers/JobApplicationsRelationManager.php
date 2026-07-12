<?php

namespace App\Filament\Resources\CvVersions\RelationManagers;

use App\Enums\ApplicationStatus;
use App\Enums\SourceType;
use App\Enums\WorkMode;
use App\Filament\Resources\JobApplications\JobApplicationResource;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class JobApplicationsRelationManager extends RelationManager
{
    protected static string $relationship = 'jobApplications';

    protected static ?string $relatedResource = JobApplicationResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company_name')
                    ->label(__('job-applications.fields.company_name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('job_title')
                    ->label(__('job-applications.fields.job_title'))
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->lineClamp(2),

                TextColumn::make('status')
                    ->label(__('job-applications.fields.status'))
                    ->formatStateUsing(fn(ApplicationStatus|string|null $state): ?string => $state instanceof ApplicationStatus
                        ? $state->label()
                        : ApplicationStatus::tryFrom($state ?? '')?->label() ?? $state)
                    ->badge()
                    ->sortable(),

                TextColumn::make('sent_at')
                    ->label(__('job-applications.fields.sent_at'))
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('source')
                    ->label(__('job-applications.fields.source'))
                    ->formatStateUsing(fn(SourceType|string|null $state): ?string => $state instanceof SourceType
                        ? $state->label()
                        : SourceType::tryFrom($state ?? '')?->label() ?? $state)
                    ->badge()
                    ->sortable(),

                TextColumn::make('work_mode')
                    ->label(__('job-applications.fields.work_mode'))
                    ->formatStateUsing(fn(WorkMode|string|null $state): ?string => $state instanceof WorkMode
                        ? $state->label()
                        : WorkMode::tryFrom($state ?? '')?->label() ?? $state)
                    ->badge()
                    ->sortable(),

                TextColumn::make('location')
                    ->label(__('job-applications.fields.location'))
                    ->searchable()
                    ->wrap()
                    ->lineClamp(2),

                IconColumn::make('dossier_sent')
                    ->label(__('job-applications.fields.dossier_sent'))
                    ->boolean()
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                ]),
            ])
            ->defaultSort('sent_at', 'desc');
    }
}
