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
        'is_reusable' => 'CV reutilizable',
    ],

    'helpers' => [
        'is_reusable' => 'Permite asociar este CV a varias postulaciones.',
        'is_reusable_locked' => 'Este CV está asociado a varias postulaciones y ya no se puede desactivar su reutilización.',
    ],

    'validation' => [
        'name_unique' => 'Ya existe un CV con este nombre interno.',
        'reusable_required_for_multiple_applications' => 'No puedes desactivar la reutilización porque este CV está asociado a varias postulaciones.',
    ],
];
