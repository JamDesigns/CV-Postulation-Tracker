<?php

return [
    'navigation' => [
        'label' => 'Technical dossiers',
        'group' => 'Documents',
    ],

    'model' => [
        'label' => 'technical dossier',
        'plural_label' => 'technical dossiers',
    ],

    'actions' => [
        'open_pdf' => 'Open PDF',
        'download_docx' => 'Download DOCX',
    ],

    'sections' => [
        'main' => 'Main details',
        'files' => 'Files',
        'content' => 'Content',
        'form' => 'Technical dossier version form',
        'details' => 'Technical dossier version details',
    ],

    'fields' => [
        'name' => 'Name',
        'version_label' => 'Version',
        'language' => 'Language',
        'pdf_path' => 'PDF file',
        'docx_path' => 'DOCX file',
        'content_snapshot' => 'Content summary',
        'is_active' => 'Active version',
        'published_at' => 'Publication date',
        'notes' => 'Notes',
        'created_at' => 'Created',
        'updated_at' => 'Updated',
    ],
];
