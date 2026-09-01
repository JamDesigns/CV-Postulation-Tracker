<?php

return [
    'model_label' => 'Contact',
    'plural_model_label' => 'Contacts',

    'sections' => [
        'main' => 'Coordonnées du contact',
        'relationship' => 'Détails dans la candidature',
    ],

    'actions' => [
        'create' => 'Créer un contact',
        'attach_existing' => 'Joindre un contact existant',
        'attach' => 'Joindre',
        'attach_another' => 'Joindre et en joindre un autre',
        'edit_relationship' => 'Modifier la relation',
        'save' => 'Enregistrer',
    ],

    'fields' => [
        'name' => 'Nom',
        'organization' => 'Organisation',
        'contact_channel' => 'Contact',
        'role' => 'Rôle',
        'is_primary' => 'Principal',
        'source' => 'Origine',
        'context' => 'Contexte',
        'url' => 'URL',
        'email' => 'E-mail',
        'created_at' => 'Créé',
        'updated_at' => 'Mis à jour',
    ],

    'validation' => [
        'at_least_one_detail' => 'Vous devez renseigner au moins un nom, une organisation, une URL ou un e-mail.',
        'not_attached_to_application' => 'Le contact sélectionné n’est pas associé à cette candidature.',
    ],
];
