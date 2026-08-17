<?php

namespace App\Filament\Resources\TechnicalDossierVersions;

use App\Filament\Resources\TechnicalDossierVersions\Pages\CreateTechnicalDossierVersion;
use App\Filament\Resources\TechnicalDossierVersions\Pages\EditTechnicalDossierVersion;
use App\Filament\Resources\TechnicalDossierVersions\Pages\ListTechnicalDossierVersions;
use App\Filament\Resources\TechnicalDossierVersions\Pages\ViewTechnicalDossierVersion;
use App\Filament\Resources\TechnicalDossierVersions\Schemas\TechnicalDossierVersionForm;
use App\Filament\Resources\TechnicalDossierVersions\Schemas\TechnicalDossierVersionInfolist;
use App\Filament\Resources\TechnicalDossierVersions\Tables\TechnicalDossierVersionsTable;
use App\Models\TechnicalDossierVersion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable;

class TechnicalDossierVersionResource extends Resource
{
    use Translatable;

    protected static ?string $model = TechnicalDossierVersion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationLabel(): string
    {
        return __('technical-dossier-versions.navigation.label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('technical-dossier-versions.navigation.group');
    }

    public static function getModelLabel(): string
    {
        return __('technical-dossier-versions.model.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('technical-dossier-versions.model.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return TechnicalDossierVersionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TechnicalDossierVersionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TechnicalDossierVersionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTechnicalDossierVersions::route('/'),
            'create' => CreateTechnicalDossierVersion::route('/create'),
            'view' => ViewTechnicalDossierVersion::route('/{record}'),
            'edit' => EditTechnicalDossierVersion::route('/{record}/edit'),
        ];
    }
}
