<?php

namespace App\Filament\Resources\CvVersions;

use App\Filament\Resources\CvVersions\Pages\CreateCvVersion;
use App\Filament\Resources\CvVersions\Pages\EditCvVersion;
use App\Filament\Resources\CvVersions\Pages\ListCvVersions;
use App\Filament\Resources\CvVersions\Pages\ViewCvVersion;
use App\Filament\Resources\CvVersions\Schemas\CvVersionForm;
use App\Filament\Resources\CvVersions\Schemas\CvVersionInfolist;
use App\Filament\Resources\CvVersions\Tables\CvVersionsTable;
use App\Models\CvVersion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable;

class CvVersionResource extends Resource
{
    use Translatable;

    protected static ?string $model = CvVersion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getModelLabel(): string
    {
        return __('cv-versions.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('cv-versions.plural_model_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('navigation.items.cv_versions');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.documents');
    }

    public static function form(Schema $schema): Schema
    {
        return CvVersionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CvVersionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CvVersionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCvVersions::route('/'),
            'create' => CreateCvVersion::route('/create'),
            'view' => ViewCvVersion::route('/{record}'),
            'edit' => EditCvVersion::route('/{record}/edit'),
        ];
    }
}
