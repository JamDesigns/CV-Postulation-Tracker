<?php

return [
    'model_label' => 'Candidature',
    'plural_model_label' => 'Candidatures',

    'sections' => [
        'main' => 'Informations principales',
        'offer' => 'Offre d’emploi',
        'candidate_materials' => 'Documents envoyés',
        'follow_up' => 'Suivi',
        'notes' => 'Notes',
        'form' => 'Formulaire de candidature',
        'details' => 'Détails de la candidature',
        'metadata' => 'Métadonnées',
    ],

    'fields' => [
        'company_name' => 'Entreprise',
        'job_title' => 'Poste',
        'job_url' => 'URL de l’offre',
        'source' => 'Source',
        'status' => 'Statut',
        'sent_at' => 'Date d’envoi',
        'location' => 'Localisation',
        'work_mode' => 'Mode de travail',
        'salary' => 'Salaire',
        'currency' => 'Devise',
        'language' => 'Langue',
        'main_stack' => 'Stack principale',
        'cv_version_id' => 'CV envoyé',
        'dossier_sent' => 'Dossier',
        'technical_dossier_version_preview' => 'Dossier attribué',
        'no_active_dossier_for_cv_language' => 'Aucun dossier actif n’existe pour la langue du CV sélectionné.',
        'message_sent' => 'Message envoyé',
        'adaptation_summary' => 'Résumé de l’adaptation',
        'notes' => 'Notes',
        'next_step' => 'Prochaine étape',
        'next_action_at' => 'Date de la prochaine action',
        'technical_dossier_version_id' => 'Version du dossier envoyée',
        'next_action_urgency' => 'Urgence',
        'created_at' => 'Créée le',
        'updated_at' => 'Mise à jour le',
        'salary_conversion' => 'Conversion',
    ],

    'salary_conversion' => [
        'equivalent' => 'Équivalent approximatif : :amount',
        'rate_unavailable' => 'Aucun taux de change enregistré n’est disponible pour cette devise.',
    ],

    'validation' => [
        'duplicate' => 'Une candidature existe déjà pour cette entreprise, ce poste et cette URL.',
    ],

    'actions' => [
        'mark_as_sent' => 'Marquer comme envoyée',
        'mark_as_responded' => 'Marquer comme ayant reçu une réponse',
        'mark_as_technical_test' => 'Marquer comme test technique',
        'mark_follow_up_sent' => 'Relance envoyée',
        'pause_application' => 'Mettre la candidature en pause',
        'reject_application' => 'Rejeter la candidature',
        'mark_as_hired' => 'Marquer comme embauché',
        'reopen_application' => 'Rouvrir la candidature',
        'view' => 'Voir',
    ],

    'quick_actions' => [
        'fields' => [
            'next_step' => 'Prochaine étape',
            'next_action_at' => 'Date de la prochaine action',
            'notes_to_append' => 'Notes à ajouter',
            'status_to' => 'Nouveau statut',
        ],

        'next_steps' => [
            'send_application' => 'Envoyer la candidature',
            'send_follow_up' => 'Envoyer une relance',
            'review_response' => 'Examiner la réponse / préparer la prochaine étape',
            'prepare_interview' => 'Préparer l’entretien',
            'complete_technical_test' => 'Réaliser le test technique',
            'wait_after_follow_up' => 'Attendre une réponse après la relance',
            'review_paused_application' => 'Examiner la candidature en pause',
            'reopened_application' => 'Reprendre le suivi de la candidature',
        ],
    ],
];
