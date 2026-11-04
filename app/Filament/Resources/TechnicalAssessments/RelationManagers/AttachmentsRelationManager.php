<?php

namespace App\Filament\Resources\TechnicalAssessments\RelationManagers;

use App\Filament\Resources\JobApplications\RelationManagers\AttachmentsRelationManager as JobApplicationAttachmentsRelationManager;
use Filament\Actions\CreateAction;
use Filament\Tables\Table;

class AttachmentsRelationManager extends JobApplicationAttachmentsRelationManager
{
    protected static string $relationship = 'attachments';

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(function (array $data): array {
                        $data['job_application_id'] = $this->getOwnerRecord()->job_application_id;

                        return $data;
                    }),
            ]);
    }
}
