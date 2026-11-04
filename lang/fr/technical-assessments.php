<?php

return [
    'model_label' => 'Test technique',
    'plural_model_label' => 'Tests techniques',

    'actions' => [
        'create' => 'Créer un test technique',
        'view' => 'Voir le test technique',
        'open_details' => 'Ouvrir la fiche complète',
    ],

    'empty' => [
        'heading' => 'Aucun test technique enregistré',
        'description' => 'Créez un test technique pour commencer.',
    ],

    'sections' => [
        'main' => 'Informations principales',
        'dates' => 'Dates',
        'documentation' => 'Documentation',
        'details' => 'Détails du test technique',
        'metadata' => 'Métadonnées',
        'form' => 'Formulaire du test technique',
    ],

    'fields' => [
        'job_application_id' => 'Candidature',
        'test_type' => 'Type de test',
        'modality' => 'Modalité',
        'direction' => 'Sens',
        'status' => 'Statut',
        'result' => 'Résultat',
        'received_at' => 'Date de réception',
        'scheduled_at' => 'Date prévue',
        'deadline_at' => 'Date limite de remise',
        'finished_at' => 'Date de fin',
        'result_communicated_at' => 'Date de communication du résultat',
        'source_url' => 'Lien vers le sujet',
        'delivery_url' => 'Lien vers la remise',
        'notes' => 'Notes',
        'created_at' => 'Créé le',
        'updated_at' => 'Mis à jour le',
    ],
    'validation' => [
        'finished_before_started' => 'La date de fin ne peut pas être antérieure à la date de début du test technique.',
    ],
];
