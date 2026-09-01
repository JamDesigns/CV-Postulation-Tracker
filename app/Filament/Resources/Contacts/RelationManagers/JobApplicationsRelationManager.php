<?php

namespace App\Filament\Resources\Contacts\RelationManagers;

use App\Enums\ApplicationContactRole;
use App\Enums\ApplicationContactSource;
use App\Enums\ApplicationStatus;
use App\Filament\Resources\JobApplications\JobApplicationResource;
use App\Models\JobApplication;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class JobApplicationsRelationManager extends RelationManager
{
    protected static string $relationship = 'jobApplications';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('job-applications.plural_model_label');
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('company_name')
                    ->label(__('job-applications.fields.company_name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('job_title')
                    ->label(__('job-applications.fields.job_title'))
                    ->searchable()
                    ->wrap(),

                TextColumn::make('status')
                    ->label(__('job-applications.fields.status'))
                    ->formatStateUsing(
                        fn (ApplicationStatus|string|null $state): ?string => $state instanceof ApplicationStatus
                            ? $state->label()
                            : ApplicationStatus::tryFrom((string) $state)?->label() ?? $state,
                    )
                    ->badge(),

                TextColumn::make('role')
                    ->label(__('contacts.fields.role'))
                    ->state(
                        fn (JobApplication $record): ?string => $record->pivot?->role instanceof ApplicationContactRole
                            ? $record->pivot->role->label()
                            : ApplicationContactRole::tryFrom((string) $record->pivot?->role)?->label(),
                    )
                    ->placeholder('-'),

                IconColumn::make('is_primary')
                    ->label(__('contacts.fields.is_primary'))
                    ->state(fn (JobApplication $record): bool => (bool) $record->pivot?->is_primary)
                    ->boolean()
                    ->alignCenter(),

                TextColumn::make('source')
                    ->label(__('contacts.fields.source'))
                    ->state(
                        fn (JobApplication $record): ?string => $record->pivot?->source instanceof ApplicationContactSource
                            ? $record->pivot->source->label()
                            : ApplicationContactSource::tryFrom((string) $record->pivot?->source)?->label(),
                    )
                    ->placeholder('-'),

                TextColumn::make('next_action_at')
                    ->label(__('job-applications.fields.next_action_at'))
                    ->date('d/m/Y')
                    ->placeholder('-')
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('viewApplication')
                    ->label(__('job-applications.actions.view'))
                    ->icon('heroicon-o-eye')
                    ->url(
                        fn (JobApplication $record): string => JobApplicationResource::getUrl(
                            'view',
                            ['record' => $record],
                        ),
                    ),
            ]);
    }
}
