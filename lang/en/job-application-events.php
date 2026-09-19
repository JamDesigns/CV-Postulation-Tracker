<?php

return [
    'navigation' => [
        'label' => 'Timeline',
    ],

    'model' => [
        'label' => 'event',
        'plural_label' => 'events',
    ],

    'fields' => [
        'type' => 'Type',
        'communication_channel' => 'Communication channel',
        'occurred_at' => 'Event date',
        'title' => 'Title',
        'body' => 'Details',
        'status_from' => 'Previous status',
        'status_to' => 'New status',
        'next_action_at' => 'Next action date',
        'created_at' => 'Created',
        'updated_at' => 'Updated',
    ],

    'types' => [
        'status_changed' => 'Status changed',
        'application_sent' => 'Application sent',
        'communication_sent' => 'Communication sent',
        'communication_received' => 'Communication received',
        'inquiry_sent' => 'Inquiry sent',
        'response_received' => 'Response received',
        'technical_test' => 'Technical test',
        'follow_up_sent' => 'Follow-up sent',
        'paused' => 'Application paused',
        'rejected' => 'Application rejected',
        'reopened' => 'Application reopened',
        'hired' => 'Hired',
        'manual_note' => 'Manual note',
    ],

    'actions' => [
        'create' => 'Create event',
        'view' => 'View event',
    ],

    'empty' => 'There are no events registered yet.',
];
