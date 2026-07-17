<?php
namespace App\Filament\Resources\TechnicalDossierVersions\Tables;

use App\Enums\CvLanguage;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TechnicalDossierVersionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('technical-dossier-versions.fields.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('version_label')
                    ->label(__('technical-dossier-versions.fields.version_label'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('language')
                    ->label(__('technical-dossier-versions.fields.language'))
                    ->formatStateUsing(fn(CvLanguage | string | null $state): ?string => $state instanceof CvLanguage
                            ? $state->label()
                            : CvLanguage::tryFrom((string) $state)?->label() ?? $state)
                    ->badge()
                    ->searchable()
                    ->sortable(),

                IconColumn::make('pdf_path')
                    ->label(__('technical-dossier-versions.fields.pdf_path'))
                    ->boolean()
                    ->state(fn($record): bool => filled($record->pdf_path))
                    ->alignCenter()
                    ->wrapHeader(),

                IconColumn::make('docx_path')
                    ->label(__('technical-dossier-versions.fields.docx_path'))
                    ->boolean()
                    ->state(fn($record): bool => filled($record->docx_path))
                    ->alignCenter()
                    ->wrapHeader(),

                IconColumn::make('is_active')
                    ->label(__('technical-dossier-versions.fields.is_active'))
                    ->boolean()
                    ->sortable()
                    ->alignCenter()
                    ->wrapHeader(),

                TextColumn::make('published_at')
                    ->label(__('technical-dossier-versions.fields.published_at'))
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('technical-dossier-versions.fields.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('technical-dossier-versions.fields.updated_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
