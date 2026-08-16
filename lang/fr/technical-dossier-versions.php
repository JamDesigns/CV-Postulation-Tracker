<?php

return [
    'navigation' => [
        'label' => 'Dossiers techniques',
        'group' => 'Documents',
    ],

    'model' => [
        'label' => 'dossier technique',
        'plural_label' => 'dossiers techniques',
    ],

    'actions' => [
        'open_pdf' => 'Ouvrir le PDF',
        'download_docx' => 'Télécharger le DOCX',
    ],

    'sections' => [
        'main' => 'Informations principales',
        'files' => 'Fichiers',
        'content' => 'Contenu',
        'form' => 'Formulaire de version du dossier technique',
        'details' => 'Détails de la version du dossier technique',
    ],

    'fields' => [
        'name' => 'Nom',
        'version_label' => 'Version',
        'language' => 'Langue',
        'pdf_path' => 'Fichier PDF',
        'docx_path' => 'Fichier DOCX',
        'content_snapshot' => 'Résumé du contenu',
        'is_active' => 'Version active',
        'published_at' => 'Date de publication',
        'notes' => 'Notes',
        'created_at' => 'Créé le',
        'updated_at' => 'Mis à jour le',
    ],
];
