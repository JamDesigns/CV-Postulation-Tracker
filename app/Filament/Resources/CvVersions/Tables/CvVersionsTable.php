<?php
namespace App\Filament\Resources\CvVersions\Tables;

use App\Enums\BaseProfile;
use App\Enums\CvLanguage;
use App\Enums\InterviewResult;
use App\Enums\InterviewType;
use App\Enums\JobApplicationEventType;
use App\Filament\Resources\JobApplications\Schemas\JobApplicationInfolist;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CvVersionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('cv-versions.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->lineClamp(3)
                    ->limit(50),

                TextColumn::make('language')
                    ->label(__('cv-versions.fields.language'))
                    ->formatStateUsing(fn(?string $state): ?string => CvLanguage::tryFrom($state)?->label() ?? $state)
                    ->sortable(),

                TextColumn::make('base_profile')
                    ->label(__('cv-versions.fields.base_profile'))
                    ->formatStateUsing(fn(?string $state): ?string => BaseProfile::tryFrom($state)?->label() ?? $state)
                    ->sortable(),

                TextColumn::make('pdf_path')
                    ->label(__('cv-versions.fields.pdf_path'))
                    ->state(fn($record): ?string => $record->pdf_path ? $record->pdfFriendlyName() : null)
                    ->placeholder('-')
                    ->icon(fn($record): ?Heroicon => $record->pdf_path ? Heroicon::ArrowTopRightOnSquare : null)
                    ->iconColor('primary')
                    ->url(fn($record): ?string => $record->pdf_path ? route('filament.admin.cv-versions.pdf', $record) : null)
                    ->openUrlInNewTab()
                    ->toggleable()
                    ->limit(40),

                TextColumn::make('docx_path')
                    ->label(__('cv-versions.fields.docx_path'))
                    ->state(fn($record): ?string => $record->docx_path ? $record->docxFriendlyName() : null)
                    ->placeholder('-')
                    ->icon(fn($record): ?Heroicon => $record->docx_path ? Heroicon::ArrowDownTray : null)
                    ->iconColor('primary')
                    ->url(fn($record): ?string => $record->docx_path ? route('filament.admin.cv-versions.docx', $record) : null)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->limit(40),

                TextColumn::make('highlighted_stack')
                    ->label(__('cv-versions.fields.highlighted_stack'))
                    ->wrap()
                    ->lineClamp(3)
                    ->limit(50)
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('language')
                    ->label(__('cv-versions.fields.language'))
                    ->options(CvLanguage::options()),

                SelectFilter::make('base_profile')
                    ->label(__('cv-versions.fields.base_profile'))
                    ->options(BaseProfile::options()),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    Action::make('viewJobApplication')
                        ->label(__('cv-application-detail-modal.actions.view_job_application'))
                        ->modalHeading(__('cv-application-detail-modal.heading'))
                        ->modalCancelActionLabel(__('cv-application-detail-modal.actions.close'))
                        ->icon(Heroicon::Eye)
                        ->color('primary')
                        ->slideOver()
                        ->modalWidth('7xl')
                        ->modalSubmitAction(false)
                        ->schema(fn($record): array=> $record->jobApplication
                                ? [
                                 ...JobApplicationInfolist::components(),

                                Tabs::make('Related job application details')
                                    ->tabs([
                                        Tab::make(__('cv-application-detail-modal.related.interviews'))
                                            ->schema([
                                                RepeatableEntry::make('interviews')
                                                    ->label(__('cv-application-detail-modal.related.interviews'))
                                                    ->placeholder(__('cv-application-detail-modal.related.no_interviews'))
                                                    ->schema([
                                                        TextEntry::make('interview_at')
                                                            ->label(__('cv-application-detail-modal.interviews.interview_at'))
                                                            ->dateTime('d/m/Y H:i')
                                                            ->placeholder('-'),

                                                        TextEntry::make('interview_type')
                                                            ->label(__('cv-application-detail-modal.interviews.interview_type'))
                                                            ->formatStateUsing(fn(InterviewType | string | null $state): ?string => $state instanceof InterviewType
                                                                    ? $state->label()
                                                                    : InterviewType::tryFrom($state ?? '')?->label() ?? $state)
                                                            ->badge()
                                                            ->placeholder('-'),

                                                        TextEntry::make('people')
                                                            ->label(__('cv-application-detail-modal.interviews.people'))
                                                            ->placeholder('-'),

                                                        TextEntry::make('result')
                                                            ->label(__('cv-application-detail-modal.interviews.result'))
                                                            ->formatStateUsing(fn(InterviewResult | string | null $state): ?string => $state instanceof InterviewResult
                                                                    ? $state->label()
                                                                    : InterviewResult::tryFrom($state ?? '')?->label() ?? $state)
                                                            ->badge()
                                                            ->placeholder('-'),

                                                        TextEntry::make('notes')
                                                            ->label(__('cv-application-detail-modal.interviews.notes'))
                                                            ->placeholder('-')
                                                            ->columnSpanFull(),
                                                    ])
                                                    ->columns(4)
                                                    ->grid(1),
                                            ]),

                                        Tab::make(__('cv-application-detail-modal.related.events'))
                                            ->schema([
                                                RepeatableEntry::make('events')
                                                    ->label(__('cv-application-detail-modal.related.events'))
                                                    ->placeholder(__('cv-application-detail-modal.related.no_events'))
                                                    ->schema([
                                                        TextEntry::make('occurred_at')
                                                            ->label(__('cv-application-detail-modal.events.occurred_at'))
                                                            ->dateTime('d/m/Y H:i')
                                                            ->placeholder('-'),

                                                        TextEntry::make('type')
                                                            ->label(__('cv-application-detail-modal.events.type'))
                                                            ->formatStateUsing(fn(JobApplicationEventType | string | null $state): ?string => $state instanceof JobApplicationEventType
                                                                    ? $state->label()
                                                                    : JobApplicationEventType::tryFrom((string) $state)?->label() ?? $state)
                                                            ->badge()
                                                            ->placeholder('-'),

                                                        TextEntry::make('title')
                                                            ->label(__('cv-application-detail-modal.events.title'))
                                                            ->placeholder('-'),

                                                        TextEntry::make('next_action_at')
                                                            ->label(__('cv-application-detail-modal.events.next_action_at'))
                                                            ->date('d/m/Y')
                                                            ->placeholder('-'),

                                                        TextEntry::make('body')
                                                            ->label(__('cv-application-detail-modal.events.body'))
                                                            ->placeholder('-')
                                                            ->columnSpanFull(),
                                                    ])
                                                    ->columns(4)
                                                    ->grid(1),
                                            ]),
                                    ])
                                    ->columnSpanFull(),
                            ]
                                :[])
                        ->mountUsing(function (Schema $schema, $record): void {
                            $schema->record($record->jobApplication);
                            $schema->fill();
                        })
                        ->visible(fn($record): bool => $record->jobApplication()->exists()),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
