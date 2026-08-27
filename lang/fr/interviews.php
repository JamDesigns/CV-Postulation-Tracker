<?php

return [
    'model_label' => 'Entretien',
    'plural_model_label' => 'Entretiens',

    'actions' => [
        'create' => 'Créer un entretien',
        'view' => 'Voir l’entretien',
    ],

    'empty' => [
        'heading' => 'Aucun entretien enregistré',
        'description' => 'Créez un entretien pour commencer.',
    ],

    'sections' => [
        'main' => 'Informations principales',
        'preparation' => 'Préparation',
        'result' => 'Résultat',
        'details' => 'Détails de l’entretien',
        'metadata' => 'Métadonnées',
        'form' => 'Formulaire de l’entretien',
    ],

    'fields' => [
        'job_application_id' => 'Candidature',
        'interview_at' => 'Date de l’entretien',
        'interview_type' => 'Type d’entretien',
        'people' => 'Participants',
        'expected_questions' => 'Questions attendues',
        'strengths_to_defend' => 'Points forts à défendre',
        'risks_to_clarify' => 'Risques à clarifier',
        'result' => 'Résultat',
        'notes' => 'Notes',
        'created_at' => 'Créé le',
        'updated_at' => 'Mis à jour le',
        'interview_from' => 'Du',
        'interview_until' => 'Au',
    ],

    'validation' => [
        'duplicate' => 'Un entretien existe déjà pour cette candidature avec la même date et le même type.',
    ],
];
