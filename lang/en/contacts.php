<?php

return [
    'model_label' => 'Contact',
    'plural_model_label' => 'Contacts',

    'sections' => [
        'main' => 'Contact details',
        'relationship' => 'Application details',
    ],

    'actions' => [
        'create' => 'Create contact',
        'attach_existing' => 'Attach existing contact',
        'attach' => 'Attach',
        'attach_another' => 'Attach and attach another',
        'edit_relationship' => 'Edit relationship',
        'save' => 'Save',
    ],

    'fields' => [
        'name' => 'Name',
        'organization' => 'Organization',
        'contact_channel' => 'Contact',
        'role' => 'Role',
        'is_primary' => 'Primary',
        'source' => 'Source',
        'context' => 'Context',
        'url' => 'URL',
        'email' => 'Email',
        'created_at' => 'Created',
        'updated_at' => 'Updated',
    ],

    'validation' => [
        'at_least_one_detail' => 'You must provide at least a name, organization, URL, or email.',
        'not_attached_to_application' => 'The selected contact is not attached to this application.',
    ],
];
