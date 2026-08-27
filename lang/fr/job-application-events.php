<?php

return [
    'navigation' => [
        'label' => 'Chronologie',
    ],

    'model' => [
        'label' => 'événement',
        'plural_label' => 'événements',
    ],

    'fields' => [
        'type' => 'Type',
        'occurred_at' => 'Date de l’événement',
        'title' => 'Titre',
        'body' => 'Détails',
        'status_from' => 'Statut précédent',
        'status_to' => 'Nouveau statut',
        'next_action_at' => 'Date de la prochaine action',
        'created_at' => 'Créé le',
        'updated_at' => 'Mis à jour le',
    ],

    'types' => [
        'status_changed' => 'Statut modifié',
        'note_added' => 'Note ajoutée',
        'application_sent' => 'Candidature envoyée',
        'response_received' => 'Réponse reçue',
        'technical_test' => 'Test technique',
        'follow_up_sent' => 'Relance envoyée',
        'paused' => 'Candidature mise en pause',
        'rejected' => 'Candidature rejetée',
        'reopened' => 'Candidature rouverte',
        'hired' => 'Embauché',
        'manual_note' => 'Note manuelle',
    ],

    'actions' => [
        'create' => 'Créer un événement',
        'view' => 'Voir l’événement',
    ],

    'empty' => 'Aucun événement n’est encore enregistré.',
];
