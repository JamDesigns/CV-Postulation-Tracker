<?php

return [
    'navigation' => [
        'label' => 'Histórico',
    ],

    'model' => [
        'label' => 'evento',
        'plural_label' => 'eventos',
    ],

    'fields' => [
        'type' => 'Tipo',
        'occurred_at' => 'Fecha del evento',
        'title' => 'Título',
        'body' => 'Detalle',
        'status_from' => 'Estado anterior',
        'status_to' => 'Estado nuevo',
        'next_action_at' => 'Fecha próxima acción',
        'created_at' => 'Creado',
        'updated_at' => 'Actualizado',
    ],

    'types' => [
        'status_changed' => 'Cambio de estado',
        'application_sent' => 'Candidatura enviada',
        'inquiry_sent' => 'Consulta enviada',
        'response_received' => 'Respuesta recibida',
        'technical_test' => 'Prueba técnica',
        'follow_up_sent' => 'Seguimiento enviado',
        'paused' => 'Candidatura pausada',
        'rejected' => 'Candidatura descartada',
        'reopened' => 'Candidatura reabierta',
        'hired' => 'Contratado',
        'manual_note' => 'Nota manual',
    ],

    'actions' => [
        'create' => 'Crear evento',
        'view' => 'Vista del evento',
    ],

    'empty' => 'Todavía no hay eventos registrados.',
];
