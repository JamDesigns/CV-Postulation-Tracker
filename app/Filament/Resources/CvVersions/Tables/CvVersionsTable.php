<?php

namespace App\Filament\Resources\CvVersions\Tables;

use App\Enums\BaseProfile;
use App\Enums\CvLanguage;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
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
