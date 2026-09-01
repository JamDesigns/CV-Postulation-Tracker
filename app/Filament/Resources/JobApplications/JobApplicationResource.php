<?php

namespace App\Filament\Resources\JobApplications;

use App\Filament\Resources\JobApplications\Pages\CreateJobApplication;
use App\Filament\Resources\JobApplications\Pages\EditJobApplication;
use App\Filament\Resources\JobApplications\Pages\ListJobApplications;
use App\Filament\Resources\JobApplications\Pages\ViewJobApplication;
use App\Filament\Resources\JobApplications\RelationManagers\ContactsRelationManager;
use App\Filament\Resources\JobApplications\RelationManagers\EventsRelationManager;
use App\Filament\Resources\JobApplications\RelationManagers\InterviewsRelationManager;
use App\Filament\Resources\JobApplications\Schemas\JobApplicationForm;
use App\Filament\Resources\JobApplications\Schemas\JobApplicationInfolist;
use App\Filament\Resources\JobApplications\Tables\JobApplicationsTable;
use App\Models\JobApplication;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class JobApplicationResource extends Resource
{
    protected static ?string $model = JobApplication::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'job_title';

    public static function getModelLabel(): string
    {
        return __('job-applications.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('job-applications.plural_model_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('navigation.items.job_applications');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.applications');
    }

    public static function form(Schema $schema): Schema
    {
        return JobApplicationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return JobApplicationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JobApplicationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            EventsRelationManager::class,
            InterviewsRelationManager::class,
            ContactsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListJobApplications::route('/'),
            'create' => CreateJobApplication::route('/create'),
            'view' => ViewJobApplication::route('/{record}'),
            'edit' => EditJobApplication::route('/{record}/edit'),
        ];
    }
}
