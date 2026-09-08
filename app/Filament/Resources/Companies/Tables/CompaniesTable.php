<?php

namespace App\Filament\Resources\Companies\Tables;

use App\Models\Company;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CompaniesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('companies.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('website')
                    ->label(__('companies.fields.website'))
                    ->placeholder('-')
                    ->url(fn (?string $state): ?string => $state)
                    ->openUrlInNewTab()
                    ->copyable()
                    ->limit(50),

                TextColumn::make('linkedin_url')
                    ->label(__('companies.fields.linkedin_url'))
                    ->placeholder('-')
                    ->url(fn (?string $state): ?string => $state)
                    ->openUrlInNewTab()
                    ->copyable()
                    ->limit(50),

                TextColumn::make('job_applications_count')
                    ->label(__('job-applications.plural_model_label'))
                    ->counts('jobApplications')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label(__('companies.fields.updated_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
            ])
            ->defaultSort('name')
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make()
                        ->hidden(
                            fn (Company $record): bool => $record
                                ->jobApplications()
                                ->exists(),
                        ),
                ]),
            ]);
    }
}
