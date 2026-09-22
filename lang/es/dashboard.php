<?php

return [
    'stats' => [
        'active_applications' => 'Procesos activos',
        'active_applications_description' => 'Procesos abiertos o en movimiento',

        'sent_without_response' => 'Enviadas sin respuesta',
        'sent_without_response_description' => 'Candidaturas enviadas o con seguimiento',

        'in_process' => 'Entrevistas y pruebas técnicas',
        'in_process_description' => 'Entrevista o prueba técnica',

        'upcoming_interviews' => 'Entrevistas próximas',
        'upcoming_interviews_description' => 'Entrevistas pendientes con fecha futura',

        'adapted_cvs' => 'Versiones de CV',
        'adapted_cvs_description' => 'Versiones de CV disponibles',

        'rejected_applications' => 'Descartes',
        'rejected_applications_description' => 'Postulaciones descartadas',
    ],
    'widgets' => [
        'pending_next_steps' => [
            'heading' => 'Próximos pasos pendientes',
            'empty' => 'No hay próximos pasos pendientes.',
            'company' => 'Empresa',
            'position' => 'Puesto',
            'status' => 'Estado',
            'next_step' => 'Próximo paso',
            'sent_at' => 'Enviado',
            'next_action_at' => 'Próxima acción',
            'urgency' => 'Urgencia',
        ],
        'upcoming_interviews' => [
            'heading' => 'Próximas entrevistas',
            'empty' => 'No hay entrevistas próximas.',
            'application' => 'Postulación',
            'company' => 'Empresa',
            'date' => 'Fecha',
            'type' => 'Tipo',
            'result' => 'Resultado',
            'people' => 'Personas',
        ],
        'recent_applications' => [
            'heading' => 'Últimas postulaciones',
            'empty' => 'No hay postulaciones registradas.',
            'company' => 'Empresa',
            'position' => 'Puesto',
            'status' => 'Estado',
            'source' => 'Fuente',
            'work_mode' => 'Modalidad',
            'sent_at' => 'Enviado',
        ],
    ],
    'charts' => [
        'applications_by_status' => [
            'heading' => 'Procesos por estado | últimos 5 años',
            'current_year' => ':year (hasta hoy)',
        ],
    ],
];
