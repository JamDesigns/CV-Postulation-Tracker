<?php

namespace App\Filament\Resources\TechnicalAssessments;

use App\Filament\Resources\TechnicalAssessments\Pages\CreateTechnicalAssessment;
use App\Filament\Resources\TechnicalAssessments\Pages\EditTechnicalAssessment;
use App\Filament\Resources\TechnicalAssessments\Pages\ListTechnicalAssessments;
use App\Filament\Resources\TechnicalAssessments\Pages\ViewTechnicalAssessment;
use App\Filament\Resources\TechnicalAssessments\RelationManagers\AttachmentsRelationManager;
use App\Filament\Resources\TechnicalAssessments\Schemas\TechnicalAssessmentForm;
use App\Filament\Resources\TechnicalAssessments\Schemas\TechnicalAssessmentInfolist;
use App\Filament\Resources\TechnicalAssessments\Tables\TechnicalAssessmentsTable;
use App\Models\TechnicalAssessment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TechnicalAssessmentResource extends Resource
{
    protected static ?string $model = TechnicalAssessment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?int $navigationSort = 30;

    public static function getModelLabel(): string
    {
        return __('technical-assessments.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('technical-assessments.plural_model_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('navigation.items.technical_assessments');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.interviews');
    }

    public static function form(Schema $schema): Schema
    {
        return TechnicalAssessmentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TechnicalAssessmentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TechnicalAssessmentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            AttachmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTechnicalAssessments::route('/'),
            'create' => CreateTechnicalAssessment::route('/create'),
            'view' => ViewTechnicalAssessment::route('/{record}'),
            'edit' => EditTechnicalAssessment::route('/{record}/edit'),
        ];
    }
}
