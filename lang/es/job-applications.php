<?php

return [
    'model_label' => 'Postulación',
    'plural_model_label' => 'Postulaciones',

    'sections' => [
        'main' => 'Información principal',
        'offer' => 'Oferta',
        'candidate_materials' => 'Material enviado',
        'follow_up' => 'Seguimiento',
        'notes' => 'Notas',
        'form' => 'Formulario de postulación',
        'details' => 'Detalles de la postulación',
        'metadata' => 'Metadatos',
    ],

    'fields' => [
        'company_name' => 'Empresa',
        'job_title' => 'Oferta',
        'job_url' => 'URL de la oferta',
        'source' => 'Fuente',
        'status' => 'Estado',
        'sent_at' => 'Fecha de envío',
        'location' => 'Ubicación',
        'work_mode' => 'Modalidad',
        'recruiter_name' => 'Recruiter',
        'recruiter_url' => 'URL del recruiter',
        'recruiter_email' => 'Email del recruiter',
        'main_stack' => 'Stack principal',
        'cv_version_id' => 'CV enviado',
        'dossier_sent' => 'Dosier',
        'technical_dossier_version_preview' => 'Dosier asignado',
        'no_active_dossier_for_cv_language' => 'No hay dosier activo para el idioma del CV seleccionado.',
        'message_sent' => 'Mensaje enviado',
        'adaptation_summary' => 'Resumen de adaptación',
        'notes' => 'Notas',
        'next_step' => 'Próximo paso',
        'next_action_at' => 'Fecha próxima acción',
        'technical_dossier_version_id' => 'Versión de dosier enviada',
        'next_action_urgency' => 'Urgencia',
        'created_at' => 'Creado',
        'updated_at' => 'Actualizado',
    ],

    'validation' => [
        'duplicate' => 'Ya existe una postulación para esta empresa, oferta y URL.',
    ],

    'actions' => [
        'mark_as_sent' => 'Marcar como enviada',
        'mark_as_responded' => 'Marcar como respondida',
        'mark_as_technical_test' => 'Marcar prueba técnica',
        'mark_follow_up_sent' => 'Seguimiento enviado',
        'pause_application' => 'Pausar candidatura',
        'reject_application' => 'Descartar candidatura',
        'mark_as_hired' => 'Marcar como contratada',
        'reopen_application' => 'Reabrir candidatura',
    ],

    'quick_actions' => [
        'fields' => [
            'next_step' => 'Próximo paso',
            'next_action_at' => 'Fecha próxima acción',
            'notes_to_append' => 'Notas a añadir',
            'status_to' => 'Nuevo estado',
        ],

        'next_steps' => [
            'send_follow_up' => 'Enviar seguimiento',
            'review_response' => 'Revisar respuesta / preparar siguiente paso',
            'complete_technical_test' => 'Realizar prueba técnica',
            'wait_after_follow_up' => 'Esperar respuesta tras seguimiento',
            'review_paused_application' => 'Revisar candidatura pausada',
            'reopened_application' => 'Retomar seguimiento de la candidatura',
        ],
    ],
];
