<?php

return [
    'model_label' => 'CV adapté',
    'plural_model_label' => 'CV adaptés',

    'actions' => [
        'create' => 'Nouveau CV',
        'open_pdf' => 'Ouvrir le PDF',
        'download_docx' => 'Télécharger le DOCX',
    ],

    'sections' => [
        'main' => 'Informations principales',
        'files' => 'Fichiers',
        'adaptation' => 'Adaptation',
        'metadata' => 'Métadonnées',
        'form' => 'Formulaire du CV',
        'details' => 'Détails du CV',
    ],

    'fields' => [
        'name' => 'Nom interne',
        'language' => 'Langue',
        'base_profile' => 'Profil de base',
        'pdf_path' => 'Fichier PDF',
        'docx_path' => 'Fichier DOCX',
        'highlighted_stack' => 'Stack mise en avant',
        'highlighted_experience' => 'Expérience mise en avant',
        'adaptation_notes' => 'Notes d’adaptation',
        'created_at' => 'Créé le',
        'updated_at' => 'Mis à jour le',
    ],

    'validation' => [
        'name_unique' => 'Un CV portant ce nom interne existe déjà.',
    ],
];
