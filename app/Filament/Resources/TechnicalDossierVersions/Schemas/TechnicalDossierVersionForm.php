<?php

namespace App\Filament\Resources\TechnicalDossierVersions\Schemas;

use App\Enums\CvLanguage;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;

class TechnicalDossierVersionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make(__('technical-dossier-versions.sections.form'))
                    ->tabs([
                        Tabs\Tab::make(__('technical-dossier-versions.sections.main'))
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('technical-dossier-versions.fields.name'))
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                TextInput::make('version_label')
                                    ->label(__('technical-dossier-versions.fields.version_label'))
                                    ->required()
                                    ->maxLength(50)
                                    ->rule(fn (Get $get, $record) => Rule::unique('technical_dossier_versions', 'version_label')
                                        ->where(fn (Builder $query): Builder => $query
                                            ->where('name', $get('name'))
                                            ->where('language', $get('language')))
                                        ->ignore($record))
                                    ->validationMessages([
                                        'unique' => __('technical-dossier-versions.validation.duplicate'),
                                    ]),

                                Select::make('language')
                                    ->label(__('technical-dossier-versions.fields.language'))
                                    ->options(CvLanguage::options())
                                    ->required()
                                    ->default(CvLanguage::Spanish->value),

                                Toggle::make('is_active')
                                    ->label(__('technical-dossier-versions.fields.is_active'))
                                    ->required(),

                                DatePicker::make('published_at')
                                    ->label(__('technical-dossier-versions.fields.published_at')),
                            ])
                            ->columns(2),

                        Tabs\Tab::make(__('technical-dossier-versions.sections.files'))
                            ->schema([
                                FileUpload::make('pdf_path')
                                    ->label(__('technical-dossier-versions.fields.pdf_path'))
                                    ->extraAttributes(['class' => 'compact-file-upload'])
                                    ->disk('local')
                                    ->directory('technical-dossier-versions/pdf')
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->getUploadedFileUsing(function (FileUpload $component, string $file, string|array|null $storedFileNames): ?array {
                                        $uploadedFile = $component->getUploadedFile($file, $storedFileNames);

                                        if (! $uploadedFile) {
                                            return null;
                                        }

                                        $record = $component->getRecord();

                                        if ($record?->pdf_path) {
                                            $uploadedFile['name'] = $record->pdfFriendlyName();
                                        }

                                        return $uploadedFile;
                                    })
                                    ->downloadable()
                                    ->openable(),

                                FileUpload::make('docx_path')
                                    ->label(__('technical-dossier-versions.fields.docx_path'))
                                    ->extraAttributes(['class' => 'compact-file-upload'])
                                    ->disk('local')
                                    ->directory('technical-dossier-versions/docx')
                                    ->acceptedFileTypes([
                                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                        'application/zip',
                                        'application/x-zip',
                                        'application/x-zip-compressed',
                                        'application/octet-stream',
                                        '.docx',
                                    ])
                                    ->mimeTypeMap([
                                        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                    ])
                                    ->rules(['extensions:docx'])
                                    ->getUploadedFileUsing(function (FileUpload $component, string $file, string|array|null $storedFileNames): ?array {
                                        $uploadedFile = $component->getUploadedFile($file, $storedFileNames);

                                        if (! $uploadedFile) {
                                            return null;
                                        }

                                        $record = $component->getRecord();

                                        if ($record?->docx_path) {
                                            $uploadedFile['name'] = $record->docxFriendlyName();
                                        }

                                        return $uploadedFile;
                                    })
                                    ->downloadable(),
                            ])
                            ->columns(2),

                        Tabs\Tab::make(__('technical-dossier-versions.sections.content'))
                            ->schema([
                                Textarea::make('content_snapshot')
                                    ->label(__('technical-dossier-versions.fields.content_snapshot'))
                                    ->rows(5)
                                    ->autosize()
                                    ->columnSpanFull(),

                                Textarea::make('notes')
                                    ->label(__('technical-dossier-versions.fields.notes'))
                                    ->rows(3)
                                    ->autosize()
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
