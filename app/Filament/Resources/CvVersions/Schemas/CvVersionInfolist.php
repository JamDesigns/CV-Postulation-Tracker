<?php

namespace App\Filament\Resources\CvVersions\Schemas;

use App\Enums\BaseProfile;
use App\Enums\CvLanguage;
use Filament\Actions\Action;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class CvVersionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make(__('cv-versions.sections.details'))
                    ->tabs([
                        Tabs\Tab::make(__('cv-versions.sections.main'))
                            ->schema([
                                TextEntry::make('name')
                                    ->label(__('cv-versions.fields.name')),

                                TextEntry::make('language')
                                    ->label(__('cv-versions.fields.language'))
                                    ->formatStateUsing(fn (?string $state): ?string => CvLanguage::tryFrom($state)?->label() ?? $state)
                                    ->badge(),

                                TextEntry::make('base_profile')
                                    ->label(__('cv-versions.fields.base_profile'))
                                    ->formatStateUsing(fn (?string $state): ?string => BaseProfile::tryFrom($state)?->label() ?? $state)
                                    ->badge(),

                                IconEntry::make('is_reusable')
                                    ->label(__('cv-versions.fields.is_reusable'))
                                    ->boolean(),
                            ])
                            ->columns(4),

                        Tabs\Tab::make(__('cv-versions.sections.files'))
                            ->schema([
                                TextEntry::make('pdf_path')
                                    ->label(__('cv-versions.fields.pdf_path'))
                                    ->placeholder('-')
                                    ->formatStateUsing(fn (?string $state, $record): ?string => $state ? $record->pdfFriendlyName() : null)
                                    ->prefixAction(
                                        Action::make('openPdf')
                                            ->label(__('cv-versions.actions.open_pdf'))
                                            ->tooltip(__('cv-versions.actions.open_pdf'))
                                            ->icon(Heroicon::ArrowTopRightOnSquare)
                                            ->iconButton()
                                            ->color('primary')
                                            ->url(fn ($record): ?string => $record->pdf_path ? route('filament.admin.cv-versions.pdf', $record) : null)
                                            ->openUrlInNewTab()
                                            ->visible(fn ($record): bool => filled($record->pdf_path)),
                                    )
                                    ->limit(60),

                                TextEntry::make('docx_path')
                                    ->label(__('cv-versions.fields.docx_path'))
                                    ->placeholder('-')
                                    ->formatStateUsing(fn (?string $state, $record): ?string => $state ? $record->docxFriendlyName() : null)
                                    ->prefixAction(
                                        Action::make('downloadDocx')
                                            ->label(__('cv-versions.actions.download_docx'))
                                            ->tooltip(__('cv-versions.actions.download_docx'))
                                            ->icon(Heroicon::ArrowDownTray)
                                            ->iconButton()
                                            ->color('primary')
                                            ->url(fn ($record): ?string => $record->docx_path ? route('filament.admin.cv-versions.docx', $record) : null)
                                            ->visible(fn ($record): bool => filled($record->docx_path)),
                                    )
                                    ->limit(60),
                            ])
                            ->columns(2),

                        Tabs\Tab::make(__('cv-versions.sections.adaptation'))
                            ->schema([
                                TextEntry::make('highlighted_stack')
                                    ->label(__('cv-versions.fields.highlighted_stack'))
                                    ->placeholder('-'),

                                TextEntry::make('highlighted_experience')
                                    ->label(__('cv-versions.fields.highlighted_experience'))
                                    ->placeholder('-'),

                                TextEntry::make('adaptation_notes')
                                    ->label(__('cv-versions.fields.adaptation_notes'))
                                    ->placeholder('-')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),

                        Tabs\Tab::make(__('cv-versions.sections.metadata'))
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label(__('cv-versions.fields.created_at'))
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('-'),

                                TextEntry::make('updated_at')
                                    ->label(__('cv-versions.fields.updated_at'))
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('-'),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
