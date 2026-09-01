<?php

return [
    'providers' => [
        'gmail' => [
            'label' => 'Gmail',
            'compose_url' => 'https://mail.google.com/mail/?view=cm&fs=1&to={email}',
        ],

        'yahoo' => [
            'label' => 'Yahoo Mail',
            'compose_url' => 'https://compose.mail.yahoo.com/?to={email}',
        ],

        'outlook' => [
            'label' => 'Outlook',
            'compose_url' => 'https://outlook.office.com/mail/deeplink/compose?to={email}',
        ],
        'yandex' => [
            'label' => 'Yandex Mail',
            'compose_url' => 'https://mail.yandex.com/compose?mailto=mailto%3A{email}',
        ],
    ],
];
