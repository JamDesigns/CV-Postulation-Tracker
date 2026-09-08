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
        'is_reusable' => 'CV réutilisable',
    ],

    'helpers' => [
        'is_reusable' => 'Permet d’associer ce CV à plusieurs candidatures.',
        'is_reusable_locked' => 'This CV is associated with multiple applications, so reuse can no longer be disabled.',
    ],

    'validation' => [
        'name_unique' => 'Un CV portant ce nom interne existe déjà.',
        'reusable_required_for_multiple_applications' => 'Vous ne pouvez pas désactiver la réutilisation car ce CV est associé à plusieurs candidatures.',
    ],
];
