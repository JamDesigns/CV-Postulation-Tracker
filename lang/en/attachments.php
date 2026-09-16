<?php

return [
    'model_label' => 'Attachment',
    'plural_model_label' => 'Attachments',

    'fields' => [
        'name' => 'Name',
        'category' => 'Category',
        'category_other' => 'Other category',
        'direction' => 'Direction',
        'file_path' => 'File',
        'original_name' => 'Original name',
        'mime_type' => 'File type',
        'size' => 'Size',
        'document_date' => 'Received/sent date',
        'notes' => 'Notes',
        'created_at' => 'Created',
        'updated_at' => 'Updated',
    ],

    'values' => [
        'unknown_date' => 'Unknown',
    ],

    'validation' => [
        'category_other_required' => 'You must specify the category when "Other" is selected.',
    ],

    'actions' => [
        'open_file' => 'Open file',
        'download_file' => 'Download',
    ],
];
