<?php

use Filament\Support\Colors\Color;

return [
    'default' => 'amber',

    'palettes' => [
        'amber' => [
            'preview' => '#f59e0b',
            'colors' => [
                'primary' => Color::Amber,
                'gray' => Color::Zinc,
                'info' => Color::Blue,
                'success' => Color::Green,
                'warning' => Color::Amber,
                'danger' => Color::Red,
            ],
        ],

        'blue' => [
            'preview' => '#3b82f6',
            'colors' => [
                'primary' => Color::Blue,
                'gray' => Color::Slate,
                'info' => Color::Sky,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'danger' => Color::Rose,
            ],
        ],

        'violet' => [
            'preview' => '#8b5cf6',
            'colors' => [
                'primary' => Color::Violet,
                'gray' => Color::Slate,
                'info' => Color::Sky,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'danger' => Color::Rose,
            ],
        ],
    ],
];
