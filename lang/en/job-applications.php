<?php

return [
    'model_label' => 'Job application',
    'plural_model_label' => 'Job applications',

    'sections' => [
        'main' => 'Main information',
        'offer' => 'Job offer',
        'candidate_materials' => 'Submitted materials',
        'follow_up' => 'Follow-up',
        'notes' => 'Notes',
        'form' => 'Job application form',
        'details' => 'Job application details',
        'metadata' => 'Metadata',
    ],

    'fields' => [
        'company_name' => 'Company',
        'job_title' => 'Job',
        'job_url' => 'Job URL',
        'source' => 'Source',
        'status' => 'Status',
        'sent_at' => 'Sent date',
        'location' => 'Location',
        'work_mode' => 'Work mode',
        'salary' => 'Salary',
        'currency' => 'Currency',
        'language' => 'Language',
        'main_stack' => 'Main stack',
        'cv_version_id' => 'Submitted CV',
        'dossier_sent' => 'Dossier',
        'technical_dossier_version_preview' => 'Assigned dossier',
        'no_active_dossier_for_cv_language' => 'There is no active dossier for the selected CV language.',
        'message_sent' => 'Sent message',
        'adaptation_summary' => 'Adaptation summary',
        'notes' => 'Notes',
        'next_step' => 'Next step',
        'next_action_at' => 'Next action date',
        'technical_dossier_version_id' => 'Sent dossier version',
        'next_action_urgency' => 'Urgency',
        'created_at' => 'Created',
        'updated_at' => 'Updated',
        'salary_conversion' => 'Conversion',
    ],

    'salary_conversion' => [
        'equivalent' => 'Approximate equivalent: :amount',
        'rate_unavailable' => 'No saved exchange rate is available for this currency.',
    ],

    'validation' => [
        'duplicate' => 'A job application already exists for this company, job and URL.',
    ],

    'actions' => [
        'mark_as_sent' => 'Mark as sent',
        'mark_as_responded' => 'Mark as responded',
        'mark_as_technical_test' => 'Mark as technical test',
        'mark_follow_up_sent' => 'Follow-up sent',
        'pause_application' => 'Pause application',
        'reject_application' => 'Reject application',
        'mark_as_hired' => 'Mark as hired',
        'reopen_application' => 'Reopen application',
        'view' => 'View',
    ],

    'quick_actions' => [
        'fields' => [
            'next_step' => 'Next step',
            'next_action_at' => 'Next action date',
            'notes_to_append' => 'Notes to append',
            'status_to' => 'New status',
        ],

        'next_steps' => [
            'send_application' => 'Submit application',
            'send_follow_up' => 'Send follow-up',
            'review_response' => 'Review response / prepare next step',
            'prepare_interview' => 'Prepare for interview',
            'complete_technical_test' => 'Complete technical test',
            'wait_after_follow_up' => 'Wait for response after follow-up',
            'review_paused_application' => 'Review paused application',
            'reopened_application' => 'Resume application follow-up',
        ],
    ],
];
