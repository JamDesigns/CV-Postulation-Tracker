<?php

return [
    'model_label' => 'CV adaptado',
    'plural_model_label' => 'CVs adaptados',

    'actions' => [
        'create' => 'Nuevo CV',
        'open_pdf' => 'Abrir PDF',
        'download_docx' => 'Descargar DOCX',
    ],

    'sections' => [
        'main' => 'Información principal',
        'files' => 'Archivos',
        'adaptation' => 'Adaptación',
        'metadata' => 'Metadatos',
        'form' => 'Formulario de CV',
        'details' => 'Detalles del CV',
    ],

    'fields' => [
        'name' => 'Nombre interno',
        'language' => 'Idioma',
        'base_profile' => 'Perfil base',
        'pdf_path' => 'Archivo PDF',
        'docx_path' => 'Archivo DOCX',
        'highlighted_stack' => 'Stack destacado',
        'highlighted_experience' => 'Experiencia destacada',
        'adaptation_notes' => 'Notas de adaptación',
        'created_at' => 'Creado',
        'updated_at' => 'Actualizado',
    ],
];
