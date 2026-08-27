<?php

return [
    'model_label' => 'Interview',
    'plural_model_label' => 'Interviews',

    'actions' => [
        'create' => 'Create interview',
        'view' => 'View interview',
    ],

    'empty' => [
        'heading' => 'No interviews registered',
        'description' => 'Create an interview to get started.',
    ],

    'sections' => [
        'main' => 'Main information',
        'preparation' => 'Preparation',
        'result' => 'Result',
        'details' => 'Interview details',
        'metadata' => 'Metadata',
        'form' => 'Interview form',
    ],

    'fields' => [
        'job_application_id' => 'Job application',
        'interview_at' => 'Interview date',
        'interview_type' => 'Interview type',
        'people' => 'People',
        'expected_questions' => 'Expected questions',
        'strengths_to_defend' => 'Strengths to defend',
        'risks_to_clarify' => 'Risks to clarify',
        'result' => 'Result',
        'notes' => 'Notes',
        'created_at' => 'Created',
        'updated_at' => 'Updated',
        'interview_from' => 'From',
        'interview_until' => 'Until',
    ],

    'validation' => [
        'duplicate' => 'An interview already exists for this application with the same date and type.',
    ],
];
