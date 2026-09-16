<?php

namespace App\Filament\Resources\JobApplications\RelationManagers;

use App\Enums\AttachmentCategory;
use App\Enums\AttachmentDirection;
use App\Models\Attachment;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class AttachmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'attachments';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('attachments.plural_model_label');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('file_path')
                    ->label(__('attachments.fields.file_path'))
                    ->disk('local')
                    ->directory(
                        fn (): string => 'attachments/job-applications/'.$this->getOwnerRecord()->getKey(),
                    )
                    ->visibility('private')
                    ->storeFileNamesIn('original_name')
                    ->acceptedFileTypes([
                        'application/pdf',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/zip',
                        'application/x-zip-compressed',
                        'application/octet-stream',
                        'text/csv',
                        'text/plain',
                        'application/vnd.ms-excel',
                        'image/png',
                        'image/jpeg',
                        'message/rfc822',
                    ])
                    ->rules([
                        'extensions:pdf,docx,xlsx,csv,txt,png,jpg,jpeg,eml',
                    ])
                    ->maxSize(20480)
                    ->required()
                    ->downloadable()
                    ->openable()
                    ->preventFilePathTampering()
                    ->live()
                    ->afterStateUpdated(function (Set $set, mixed $state): void {
                        $files = is_array($state) ? $state : [$state];

                        $file = collect($files)
                            ->first(
                                fn (mixed $file): bool => $file instanceof TemporaryUploadedFile,
                            );

                        if (! $file) {
                            return;
                        }

                        $set(
                            'name',
                            Attachment::suggestNameFromOriginalFilename(
                                $file->getClientOriginalName(),
                            ),
                        );
                    })
                    ->columnSpanFull(),

                TextInput::make('name')
                    ->label(__('attachments.fields.name'))
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Grid::make(2)
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                Select::make('direction')
                                    ->label(__('attachments.fields.direction'))
                                    ->options(AttachmentDirection::options())
                                    ->default(AttachmentDirection::NotSpecified->value)
                                    ->required(),

                                DatePicker::make('document_date')
                                    ->label(__('attachments.fields.document_date')),
                            ]),

                        Grid::make(1)
                            ->schema([
                                Select::make('category')
                                    ->label(__('attachments.fields.category'))
                                    ->options(AttachmentCategory::options())
                                    ->required()
                                    ->live(),

                                TextInput::make('category_other')
                                    ->label(__('attachments.fields.category_other'))
                                    ->maxLength(255)
                                    ->visible(
                                        fn (Get $get): bool => $get('category') === AttachmentCategory::Other->value,
                                    )
                                    ->required(
                                        fn (Get $get): bool => $get('category') === AttachmentCategory::Other->value,
                                    ),
                            ]),
                    ])
                    ->columnSpanFull(),

                Textarea::make('notes')
                    ->label(__('attachments.fields.notes'))
                    ->rows(4)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label(__('attachments.fields.name'))
                    ->columnSpanFull(),

                Grid::make(3)
                    ->schema([
                        TextEntry::make('original_name')
                            ->label(__('attachments.fields.original_name')),

                        TextEntry::make('mime_type')
                            ->label(__('attachments.fields.mime_type')),

                        TextEntry::make('size')
                            ->label(__('attachments.fields.size'))
                            ->formatStateUsing(function (int|string|null $state): ?string {
                                if ($state === null) {
                                    return null;
                                }

                                $bytes = (int) $state;

                                if ($bytes >= 1024 * 1024) {
                                    return number_format($bytes / (1024 * 1024), 2).' MB';
                                }

                                if ($bytes >= 1024) {
                                    return number_format($bytes / 1024, 2).' KB';
                                }

                                return $bytes.' B';
                            }),
                    ])
                    ->columnSpanFull(),

                Grid::make(2)
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                TextEntry::make('direction')
                                    ->label(__('attachments.fields.direction'))
                                    ->formatStateUsing(
                                        fn (AttachmentDirection|string|null $state): ?string => $state instanceof AttachmentDirection
                                            ? $state->label()
                                            : AttachmentDirection::tryFrom($state ?? '')?->label() ?? $state,
                                    )
                                    ->badge(),

                                TextEntry::make('document_date')
                                    ->label(__('attachments.fields.document_date'))
                                    ->date('d/m/Y')
                                    ->placeholder(__('attachments.values.unknown_date')),
                            ]),

                        Grid::make(1)
                            ->schema([
                                TextEntry::make('category')
                                    ->label(__('attachments.fields.category'))
                                    ->formatStateUsing(
                                        fn (AttachmentCategory|string|null $state): ?string => $state instanceof AttachmentCategory
                                            ? $state->label()
                                            : AttachmentCategory::tryFrom($state ?? '')?->label() ?? $state,
                                    )
                                    ->badge(),

                                TextEntry::make('category_other')
                                    ->label(__('attachments.fields.category_other'))
                                    ->visible(
                                        fn (Attachment $record): bool => $record->category === AttachmentCategory::Other,
                                    ),
                            ]),
                    ])
                    ->columnSpanFull(),

                TextEntry::make('notes')
                    ->label(__('attachments.fields.notes'))
                    ->placeholder('-')
                    ->columnSpanFull(),

                TextEntry::make('created_at')
                    ->label(__('attachments.fields.created_at'))
                    ->dateTime('d/m/Y'),

                TextEntry::make('updated_at')
                    ->label(__('attachments.fields.updated_at'))
                    ->dateTime('d/m/Y'),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->modelLabel(__('attachments.model_label'))
            ->pluralModelLabel(__('attachments.plural_model_label'))
            ->heading(__('attachments.plural_model_label'))
            ->columns([
                TextColumn::make('name')
                    ->label(__('attachments.fields.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category')
                    ->label(__('attachments.fields.category'))
                    ->formatStateUsing(
                        function (
                            AttachmentCategory|string|null $state,
                            Attachment $record,
                        ): ?string {
                            $category = $state instanceof AttachmentCategory
                                ? $state
                                : AttachmentCategory::tryFrom($state ?? '');

                            if (! $category) {
                                return $state;
                            }

                            if (
                                $category === AttachmentCategory::Other
                                && filled($record->category_other)
                            ) {
                                return $category->label().' — '.$record->category_other;
                            }

                            return $category->label();
                        },
                    )
                    ->badge()
                    ->sortable(),

                TextColumn::make('direction')
                    ->label(__('attachments.fields.direction'))
                    ->formatStateUsing(
                        fn (AttachmentDirection|string|null $state): ?string => $state instanceof AttachmentDirection
                            ? $state->label()
                            : AttachmentDirection::tryFrom($state ?? '')?->label() ?? $state,
                    )
                    ->badge()
                    ->sortable(),

                TextColumn::make('document_date')
                    ->label(__('attachments.fields.document_date'))
                    ->date('d/m/Y')
                    ->placeholder(__('attachments.values.unknown_date'))
                    ->sortable(),

                TextColumn::make('size')
                    ->label(__('attachments.fields.size'))
                    ->formatStateUsing(function (int|string|null $state): ?string {
                        if ($state === null) {
                            return null;
                        }

                        $bytes = (int) $state;

                        if ($bytes >= 1024 * 1024) {
                            return number_format($bytes / (1024 * 1024), 2).' MB';
                        }

                        if ($bytes >= 1024) {
                            return number_format($bytes / 1024, 2).' KB';
                        }

                        return $bytes.' B';
                    })
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),

                    Action::make('openFile')
                        ->label(__('attachments.actions.open_file'))
                        ->icon(Heroicon::ArrowTopRightOnSquare)
                        ->url(
                            fn (Attachment $record): string => route(
                                'filament.admin.attachments.file',
                                $record,
                            ),
                        )
                        ->openUrlInNewTab(),

                    Action::make('downloadFile')
                        ->label(__('attachments.actions.download_file'))
                        ->icon(Heroicon::ArrowDownTray)
                        ->url(
                            fn (Attachment $record): string => route(
                                'filament.admin.attachments.download',
                                $record,
                            ),
                        ),

                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
