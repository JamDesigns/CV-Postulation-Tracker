<?php

return [
    'model_label' => 'Adapted CV',
    'plural_model_label' => 'Adapted CVs',

    'actions' => [
        'create' => 'New CV',
        'open_pdf' => 'Open PDF',
        'download_docx' => 'Download DOCX',
    ],

    'sections' => [
        'main' => 'Main information',
        'files' => 'Files',
        'adaptation' => 'Adaptation',
        'metadata' => 'Metadata',
        'form' => 'CV form',
        'details' => 'CV details',
    ],

    'fields' => [
        'name' => 'Internal name',
        'language' => 'Language',
        'base_profile' => 'Base profile',
        'pdf_path' => 'PDF file',
        'docx_path' => 'DOCX file',
        'highlighted_stack' => 'Highlighted stack',
        'highlighted_experience' => 'Highlighted experience',
        'adaptation_notes' => 'Adaptation notes',
        'created_at' => 'Created',
        'updated_at' => 'Updated',
        'is_reusable' => 'Reusable CV',
    ],

    'helpers' => [
        'is_reusable' => 'Allows this CV to be associated with multiple applications.',
        'is_reusable_locked' => 'This CV is associated with multiple applications, so reuse can no longer be disabled.',
    ],

    'validation' => [
        'name_unique' => 'A CV with this internal name already exists.',
        'reusable_required_for_multiple_applications' => 'You cannot disable reuse because this CV is associated with multiple applications.',
    ],
];
