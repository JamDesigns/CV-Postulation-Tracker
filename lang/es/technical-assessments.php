<?php

return [
    'model_label' => 'Prueba técnica',
    'plural_model_label' => 'Pruebas técnicas',

    'actions' => [
        'create' => 'Crear prueba técnica',
        'view' => 'Ver prueba técnica',
        'open_details' => 'Abrir detalle completo',
    ],

    'empty' => [
        'heading' => 'No hay pruebas técnicas registradas',
        'description' => 'Crea una prueba técnica para empezar.',
    ],

    'sections' => [
        'main' => 'Información principal',
        'dates' => 'Fechas',
        'documentation' => 'Documentación',
        'details' => 'Detalles de la prueba técnica',
        'metadata' => 'Metadatos',
        'form' => 'Formulario de prueba técnica',
    ],

    'fields' => [
        'job_application_id' => 'Postulación',
        'test_type' => 'Tipo de prueba',
        'modality' => 'Modalidad',
        'direction' => 'Dirección',
        'status' => 'Estado',
        'result' => 'Resultado',
        'received_at' => 'Fecha de recepción',
        'scheduled_at' => 'Fecha programada',
        'deadline_at' => 'Fecha límite de entrega',
        'finished_at' => 'Fecha de finalización',
        'result_communicated_at' => 'Fecha de comunicación del resultado',
        'source_url' => 'Enlace al enunciado',
        'delivery_url' => 'Enlace a la entrega',
        'notes' => 'Notas',
        'created_at' => 'Creado',
        'updated_at' => 'Actualizado',
    ],
    'validation' => [
        'finished_before_started' => 'La fecha de finalización no puede ser anterior a la fecha de inicio de la prueba técnica.',
    ],
];
