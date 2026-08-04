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
    ],

    'validation' => [
        'name_unique' => 'A CV with this internal name already exists.',
    ],
];
