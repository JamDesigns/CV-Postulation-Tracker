<?php

return [
    'model_label' => 'Contacto',
    'plural_model_label' => 'Contactos',

    'sections' => [
        'main' => 'Datos del contacto',
        'relationship' => 'Datos en la candidatura',
    ],

    'actions' => [
        'create' => 'Crear contacto',
        'attach_existing' => 'Adjuntar contacto existente',
        'attach' => 'Adjuntar',
        'attach_another' => 'Adjuntar y adjuntar otro',
        'edit_relationship' => 'Editar relación',
        'save' => 'Guardar',
    ],

    'fields' => [
        'name' => 'Nombre',
        'organization' => 'Organización',
        'contact_channel' => 'Contacto',
        'role' => 'Rol',
        'is_primary' => 'Principal',
        'source' => 'Origen',
        'context' => 'Contexto',
        'url' => 'URL',
        'email' => 'Email',
        'created_at' => 'Creado',
        'updated_at' => 'Actualizado',
    ],

    'validation' => [
        'at_least_one_detail' => 'Debes indicar al menos un nombre, organización, URL o email.',
        'not_attached_to_application' => 'El contacto seleccionado no está asociado a esta candidatura.',
    ],
];
