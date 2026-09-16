<?php

return [
    'model_label' => 'Adjunto',
    'plural_model_label' => 'Adjuntos',

    'fields' => [
        'name' => 'Nombre',
        'category' => 'Categoría',
        'category_other' => 'Otra categoría',
        'direction' => 'Dirección',
        'file_path' => 'Archivo',
        'original_name' => 'Nombre original',
        'mime_type' => 'Tipo de archivo',
        'size' => 'Tamaño',
        'document_date' => 'Fecha de recepción/envío',
        'notes' => 'Notas',
        'created_at' => 'Creado',
        'updated_at' => 'Actualizado',
    ],

    'values' => [
        'unknown_date' => 'Desconocida',
    ],

    'validation' => [
        'category_other_required' => 'Debes indicar la categoría cuando seleccionas "Otro".',
    ],

    'actions' => [
        'open_file' => 'Abrir archivo',
        'download_file' => 'Descargar',
    ],
];
