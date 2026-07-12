<?php

namespace App\Filament\Resources\JobApplications\RelationManagers;

use App\Enums\InterviewResult;
use App\Enums\InterviewType;
use App\Filament\Resources\Interviews\InterviewResource;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InterviewsRelationManager extends RelationManager
{
    protected static string $relationship = 'interviews';

    protected static ?string $relatedResource = InterviewResource::class;

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('interview_at')
                    ->label(__('interviews.fields.interview_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('interview_type')
                    ->label(__('interviews.fields.interview_type'))
                    ->formatStateUsing(fn(InterviewType|string|null $state): ?string => $state instanceof InterviewType
                        ? $state->label()
                        : InterviewType::tryFrom($state ?? '')?->label() ?? $state)
                    ->badge()
                    ->sortable(),

                TextColumn::make('result')
                    ->label(__('interviews.fields.result'))
                    ->formatStateUsing(fn(InterviewResult|string|null $state): ?string => $state instanceof InterviewResult
                        ? $state->label()
                        : InterviewResult::tryFrom($state ?? '')?->label() ?? $state)
                    ->badge()
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
