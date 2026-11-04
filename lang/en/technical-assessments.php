<?php

return [
    'model_label' => 'Technical assessment',
    'plural_model_label' => 'Technical assessments',

    'actions' => [
        'create' => 'Create technical assessment',
        'view' => 'View technical assessment',
        'open_details' => 'Open full details',
    ],

    'empty' => [
        'heading' => 'No technical assessments registered',
        'description' => 'Create a technical assessment to get started.',
    ],

    'sections' => [
        'main' => 'Main information',
        'dates' => 'Dates',
        'documentation' => 'Documentation',
        'details' => 'Technical assessment details',
        'metadata' => 'Metadata',
        'form' => 'Technical assessment form',
    ],

    'fields' => [
        'job_application_id' => 'Job application',
        'test_type' => 'Assessment type',
        'modality' => 'Modality',
        'direction' => 'Direction',
        'status' => 'Status',
        'result' => 'Result',
        'received_at' => 'Received at',
        'scheduled_at' => 'Scheduled at',
        'deadline_at' => 'Submission deadline',
        'finished_at' => 'Completed at',
        'result_communicated_at' => 'Result communicated at',
        'source_url' => 'Assignment URL',
        'delivery_url' => 'Submission URL',
        'notes' => 'Notes',
        'created_at' => 'Created',
        'updated_at' => 'Updated',
    ],
    'validation' => [
        'finished_before_started' => 'The completion date cannot be earlier than the technical assessment start date.',
    ],
];
