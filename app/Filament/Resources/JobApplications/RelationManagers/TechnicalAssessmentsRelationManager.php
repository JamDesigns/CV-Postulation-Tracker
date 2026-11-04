<?php

namespace App\Filament\Resources\JobApplications\RelationManagers;

use App\Enums\TechnicalAssessmentResult;
use App\Enums\TechnicalAssessmentStatus;
use App\Enums\TechnicalAssessmentType;
use App\Filament\Resources\JobApplications\Pages\ViewJobApplication;
use App\Filament\Resources\TechnicalAssessments\Schemas\TechnicalAssessmentForm;
use App\Filament\Resources\TechnicalAssessments\Schemas\TechnicalAssessmentInfolist;
use App\Filament\Resources\TechnicalAssessments\TechnicalAssessmentResource;
use App\Models\TechnicalAssessment;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TechnicalAssessmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'technicalAssessments';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('technical-assessments.plural_model_label');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components(TechnicalAssessmentForm::components(false));
    }

    public function infolist(Schema $schema): Schema
    {
        return TechnicalAssessmentInfolist::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modelLabel(__('technical-assessments.model_label'))
            ->pluralModelLabel(__('technical-assessments.plural_model_label'))
            ->heading(__('technical-assessments.plural_model_label'))
            ->emptyStateHeading(__('technical-assessments.empty.heading'))
            ->emptyStateDescription(__('technical-assessments.empty.description'))
            ->columns([
                TextColumn::make('test_type')
                    ->label(__('technical-assessments.fields.test_type'))
                    ->formatStateUsing(
                        fn (TechnicalAssessmentType|string|null $state): ?string => $state instanceof TechnicalAssessmentType
                                ? $state->label()
                                : TechnicalAssessmentType::tryFrom($state ?? '')?->label()
                    )
                    ->badge()
                    ->sortable(),

                TextColumn::make('status')
                    ->label(__('technical-assessments.fields.status'))
                    ->formatStateUsing(
                        fn (TechnicalAssessmentStatus|string|null $state): ?string => $state instanceof TechnicalAssessmentStatus
                                ? $state->label()
                                : TechnicalAssessmentStatus::tryFrom($state ?? '')?->label()
                    )
                    ->badge()
                    ->color(
                        fn (TechnicalAssessmentStatus|string|null $state): string => $state instanceof TechnicalAssessmentStatus
                                ? $state->color()
                                : TechnicalAssessmentStatus::tryFrom($state ?? '')?->color() ?? 'info'
                    )
                    ->sortable(),

                TextColumn::make('result')
                    ->label(__('technical-assessments.fields.result'))
                    ->formatStateUsing(
                        fn (TechnicalAssessmentResult|string|null $state): ?string => $state instanceof TechnicalAssessmentResult
                                ? $state->label()
                                : TechnicalAssessmentResult::tryFrom($state ?? '')?->label()
                    )
                    ->badge()
                    ->color(
                        fn (TechnicalAssessmentResult|string|null $state): string => $state instanceof TechnicalAssessmentResult
                                ? $state->color()
                                : TechnicalAssessmentResult::tryFrom($state ?? '')?->color() ?? 'info'
                    ),

                TextColumn::make('scheduled_at')
                    ->label(__('technical-assessments.fields.scheduled_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('deadline_at')
                    ->label(__('technical-assessments.fields.deadline_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('-'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label(__('technical-assessments.actions.create'))
                    ->modalHeading(__('technical-assessments.actions.create'))
                    ->after(fn () => $this
                        ->dispatch('job-application-updated')
                        ->to(ViewJobApplication::class)),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->modalHeading(__('technical-assessments.actions.view')),
                    Action::make('openDetails')
                        ->label(__('technical-assessments.actions.open_details'))
                        ->icon(Heroicon::ArrowTopRightOnSquare)
                        ->url(
                            fn (TechnicalAssessment $record): string => TechnicalAssessmentResource::getUrl(
                                'view',
                                ['record' => $record],
                            ),
                        ),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
