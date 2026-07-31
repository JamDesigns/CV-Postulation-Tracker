<?php

return [
    'navigation' => [
        'label' => 'Dosieres técnicos',
        'group' => 'Documentos',
    ],

    'model' => [
        'label' => 'dosier técnico',
        'plural_label' => 'dosieres técnicos',
    ],

    'actions' => [
        'open_pdf' => 'Abrir PDF',
        'download_docx' => 'Descargar DOCX',
    ],

    'sections' => [
        'main' => 'Datos principales',
        'files' => 'Archivos',
        'content' => 'Contenido',
        'form' => 'Formulario de versión del dosier técnico',
        'details' => 'Detalles de la versión del dosier técnico',
    ],

    'fields' => [
        'name' => 'Nombre',
        'version_label' => 'Versión',
        'language' => 'Idioma',
        'pdf_path' => 'Archivo PDF',
        'docx_path' => 'Archivo DOCX',
        'summary' => 'Resumen',
        'content_snapshot' => 'Resumen de contenido',
        'is_active' => 'Versión activa',
        'published_at' => 'Fecha de publicación',
        'notes' => 'Notas',
        'created_at' => 'Creado',
        'updated_at' => 'Actualizado',
    ],
];
