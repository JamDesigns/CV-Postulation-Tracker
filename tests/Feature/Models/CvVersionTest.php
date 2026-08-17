<?php

use App\Models\CvVersion;

test('it stores translations for cv version content fields', function () {
    $cvVersion = CvVersion::query()->create([
        'name' => 'Full Stack CV',
    ]);

    $cvVersion
        ->setTranslations('highlighted_stack', [
            'es' => 'Angular, Laravel y TypeScript',
            'en' => 'Angular, Laravel and TypeScript',
            'fr' => 'Angular, Laravel et TypeScript',
        ])
        ->setTranslations('highlighted_experience', [
            'es' => 'Experiencia destacada en desarrollo full stack',
            'en' => 'Highlighted full-stack development experience',
            'fr' => 'Expérience mise en avant en développement full stack',
        ])
        ->setTranslations('adaptation_notes', [
            'es' => 'CV adaptado para la oferta',
            'en' => 'CV adapted for the job offer',
            'fr' => 'CV adapté à l’offre d’emploi',
        ])
        ->save();

    $cvVersion->refresh();

    expect($cvVersion->getTranslation('highlighted_stack', 'es', false))
        ->toBe('Angular, Laravel y TypeScript')
        ->and($cvVersion->getTranslation('highlighted_stack', 'en', false))
        ->toBe('Angular, Laravel and TypeScript')
        ->and($cvVersion->getTranslation('highlighted_stack', 'fr', false))
        ->toBe('Angular, Laravel et TypeScript')
        ->and($cvVersion->getTranslation('highlighted_experience', 'es', false))
        ->toBe('Experiencia destacada en desarrollo full stack')
        ->and($cvVersion->getTranslation('highlighted_experience', 'en', false))
        ->toBe('Highlighted full-stack development experience')
        ->and($cvVersion->getTranslation('highlighted_experience', 'fr', false))
        ->toBe('Expérience mise en avant en développement full stack')
        ->and($cvVersion->getTranslation('adaptation_notes', 'es', false))
        ->toBe('CV adaptado para la oferta')
        ->and($cvVersion->getTranslation('adaptation_notes', 'en', false))
        ->toBe('CV adapted for the job offer')
        ->and($cvVersion->getTranslation('adaptation_notes', 'fr', false))
        ->toBe('CV adapté à l’offre d’emploi');
});

test('it returns cv version content for the selected locale', function () {
    $cvVersion = CvVersion::query()->create([
        'name' => 'Frontend CV',
        'highlighted_stack' => [
            'es' => 'Angular y TypeScript',
            'en' => 'Angular and TypeScript',
        ],
        'highlighted_experience' => [
            'es' => 'Experiencia destacada en frontend',
            'en' => 'Highlighted frontend experience',
        ],
        'adaptation_notes' => [
            'es' => 'Adaptado para un puesto frontend',
            'en' => 'Adapted for a frontend role',
        ],
    ]);

    $cvVersion->setLocale('es');

    expect($cvVersion->highlighted_stack)
        ->toBe('Angular y TypeScript')
        ->and($cvVersion->highlighted_experience)
        ->toBe('Experiencia destacada en frontend')
        ->and($cvVersion->adaptation_notes)
        ->toBe('Adaptado para un puesto frontend');

    $cvVersion->setLocale('en');

    expect($cvVersion->highlighted_stack)
        ->toBe('Angular and TypeScript')
        ->and($cvVersion->highlighted_experience)
        ->toBe('Highlighted frontend experience')
        ->and($cvVersion->adaptation_notes)
        ->toBe('Adapted for a frontend role');
});
