<?php

return [
    'model_label' => 'Pièce jointe',
    'plural_model_label' => 'Pièces jointes',

    'fields' => [
        'name' => 'Nom',
        'category' => 'Catégorie',
        'category_other' => 'Autre catégorie',
        'direction' => 'Direction',
        'file_path' => 'Fichier',
        'original_name' => 'Nom d’origine',
        'mime_type' => 'Type de fichier',
        'size' => 'Taille',
        'document_date' => 'Date de réception/envoi',
        'notes' => 'Notes',
        'created_at' => 'Créée le',
        'updated_at' => 'Mise à jour le',
    ],

    'values' => [
        'unknown_date' => 'Inconnue',
    ],

    'validation' => [
        'category_other_required' => 'Vous devez préciser la catégorie lorsque « Autre » est sélectionné.',
    ],

    'actions' => [
        'open_file' => 'Ouvrir le fichier',
        'download_file' => 'Télécharger',
    ],
];
