<?php

namespace App\Filament\Resources\Interviews;

use App\Filament\Resources\Interviews\Pages\CreateInterview;
use App\Filament\Resources\Interviews\Pages\EditInterview;
use App\Filament\Resources\Interviews\Pages\ListInterviews;
use App\Filament\Resources\Interviews\Pages\ViewInterview;
use App\Filament\Resources\Interviews\Schemas\InterviewForm;
use App\Filament\Resources\Interviews\Schemas\InterviewInfolist;
use App\Filament\Resources\Interviews\Tables\InterviewsTable;
use App\Models\Interview;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InterviewResource extends Resource
{
    protected static ?string $model = Interview::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?int $navigationSort = 20;

    public static function getModelLabel(): string
    {
        return __('interviews.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('interviews.plural_model_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('navigation.items.interviews');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.interviews');
    }

    public static function form(Schema $schema): Schema
    {
        return InterviewForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InterviewInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InterviewsTable::configure($table);
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
            'index' => ListInterviews::route('/'),
            'create' => CreateInterview::route('/create'),
            'view' => ViewInterview::route('/{record}'),
            'edit' => EditInterview::route('/{record}/edit'),
        ];
    }
}
