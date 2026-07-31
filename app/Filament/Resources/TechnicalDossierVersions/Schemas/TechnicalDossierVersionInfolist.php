<?php

namespace App\Filament\Resources\TechnicalDossierVersions\Schemas;

use App\Enums\CvLanguage;
use Filament\Actions\Action;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class TechnicalDossierVersionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make(__('technical-dossier-versions.sections.details'))
                    ->tabs([
                        Tabs\Tab::make(__('technical-dossier-versions.sections.main'))
                            ->schema([
                                TextEntry::make('name')
                                    ->label(__('technical-dossier-versions.fields.name')),

                                TextEntry::make('version_label')
                                    ->label(__('technical-dossier-versions.fields.version_label')),

                                TextEntry::make('language')
                                    ->label(__('technical-dossier-versions.fields.language'))
                                    ->formatStateUsing(fn (CvLanguage|string|null $state): ?string => $state instanceof CvLanguage
                                            ? $state->label()
                                            : CvLanguage::tryFrom((string) $state)?->label() ?? $state)
                                    ->badge(),

                                IconEntry::make('is_active')
                                    ->label(__('technical-dossier-versions.fields.is_active'))
                                    ->boolean(),

                                TextEntry::make('published_at')
                                    ->label(__('technical-dossier-versions.fields.published_at'))
                                    ->date('d/m/Y')
                                    ->placeholder('-'),

                                TextEntry::make('created_at')
                                    ->label(__('technical-dossier-versions.fields.created_at'))
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('-'),

                                TextEntry::make('updated_at')
                                    ->label(__('technical-dossier-versions.fields.updated_at'))
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('-'),
                            ])
                            ->columns(3),

                        Tabs\Tab::make(__('technical-dossier-versions.sections.files'))
                            ->schema([
                                TextEntry::make('pdf_path')
                                    ->label(__('technical-dossier-versions.fields.pdf_path'))
                                    ->formatStateUsing(fn ($record): string => $record->pdfFriendlyName())
                                    ->placeholder('-')
                                    ->prefixAction(
                                        Action::make('openPdf')
                                            ->icon(Heroicon::ArrowTopRightOnSquare)
                                            ->color('primary')
                                            ->url(fn ($record): ?string => $record->pdf_path
                                                    ? route('filament.admin.technical-dossier-versions.pdf', [
                                                        'technicalDossierVersion' => $record,
                                                    ])
                                                    : null)
                                            ->openUrlInNewTab()
                                            ->visible(fn ($record): bool => filled($record->pdf_path)),
                                    ),

                                TextEntry::make('docx_path')
                                    ->label(__('technical-dossier-versions.fields.docx_path'))
                                    ->formatStateUsing(fn ($record): string => $record->docxFriendlyName())
                                    ->placeholder('-')
                                    ->prefixAction(
                                        Action::make('downloadDocx')
                                            ->icon(Heroicon::ArrowDownTray)
                                            ->color('primary')
                                            ->url(fn ($record): ?string => $record->docx_path
                                                    ? route('filament.admin.technical-dossier-versions.docx', [
                                                        'technicalDossierVersion' => $record,
                                                    ])
                                                    : null)
                                            ->visible(fn ($record): bool => filled($record->docx_path)),
                                    ),
                            ])
                            ->columns(2),

                        Tabs\Tab::make(__('technical-dossier-versions.sections.content'))
                            ->schema([
                                TextEntry::make('summary')
                                    ->label(__('technical-dossier-versions.fields.summary'))
                                    ->placeholder('-')
                                    ->columnSpanFull(),

                                TextEntry::make('content_snapshot')
                                    ->label(__('technical-dossier-versions.fields.content_snapshot'))
                                    ->placeholder('-')
                                    ->columnSpanFull(),

                                TextEntry::make('notes')
                                    ->label(__('technical-dossier-versions.fields.notes'))
                                    ->placeholder('-')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
