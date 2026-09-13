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
        'snapshot' => 'Snapshot de la candidature',
        'offer_snapshot' => 'Offre archivée',
        'application_form_snapshot' => 'Formulaire envoyé',
    ],

    'fields' => [
        'job_title' => 'Poste',
        'job_url' => 'URL de l’offre',
        'source' => 'Source',
        'status' => 'Statut',
        'rejection_reason' => 'Motif du rejet',
        'sent_at' => 'Date d’envoi',
        'location' => 'Localisation',
        'work_mode' => 'Mode de travail',
        'salary' => 'Salaire',
        'salary_min' => 'Salaire minimum',
        'salary_max' => 'Salaire maximum',
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
        'offer_snapshot' => 'Contenu complet de l’offre',
        'offer_snapshot_at' => 'Offre archivée le',
        'application_form_import' => 'Importer le formulaire depuis JSON',
        'application_form_snapshot' => 'Questions et réponses',
        'application_form_question' => 'Question',
        'application_form_answer' => 'Réponse',
    ],

    'salary_conversion' => [
        'equivalent' => 'Équivalent approximatif : :amount',
        'rate_unavailable' => 'Aucun taux de change enregistré n’est disponible pour cette devise.',
    ],

    'validation' => [
        'duplicate' => 'Une candidature existe déjà pour cette entreprise, ce poste et cette URL.',
        'cv_version_unique' => 'Ce CV est déjà associé à une autre candidature.',
        'salary_max_requires_min' => 'Vous ne pouvez pas indiquer un salaire maximum sans indiquer un salaire minimum.',
        'salary_max_gte_min' => 'Le salaire maximum doit être supérieur ou égal au salaire minimum.',
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
        'import_application_form' => 'Importer le JSON',
        'add_application_form_item' => 'Ajouter une question et une réponse',
        'inquiry_sent' => 'Demande d’information envoyée',
    ],

    'quick_actions' => [
        'fields' => [
            'next_step' => 'Prochaine étape',
            'next_action_at' => 'Date de la prochaine action',
            'notes_to_append' => 'Notes à ajouter',
            'status_to' => 'Nouveau statut',
            'inquiry_message' => 'Contenu de la demande',
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
            'wait_for_inquiry_response' => 'Attendre une réponse à la demande',
        ],
    ],

    'application_form_import' => [
        'helper' => 'Collez ici le JSON généré par l’agent pour le convertir en questions et réponses modifiables.',
        'success' => 'Formulaire importé correctement.',
        'invalid_json' => 'Le contenu collé n’est pas un JSON valide.',
        'invalid_structure' => 'Le JSON doit être une liste d’objets contenant les champs "question" et "answer".',
    ],

    'reminders' => [
        'next_action' => [
            'title' => ':company · :job',
            'view' => 'Voir la candidature',
        ],
    ],
];
