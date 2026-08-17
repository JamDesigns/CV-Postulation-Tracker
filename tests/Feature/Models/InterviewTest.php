<?php

use App\Enums\ApplicationStatus;
use App\Enums\InterviewType;
use App\Models\JobApplication;

test('it synchronizes the application when an interview is created', function () {
    $jobApplication = JobApplication::query()->create([
        'company_name' => 'Test Company',
        'job_title' => 'Full Stack Developer',
        'status' => ApplicationStatus::Responded,
        'next_step' => 'Review response',
        'next_action_at' => '2026-08-18',
    ]);

    $jobApplication->interviews()->create([
        'interview_at' => '2026-08-22 10:30:00',
        'interview_type' => InterviewType::Hr,
    ]);

    $jobApplication->refresh();

    expect($jobApplication->status)
        ->toBe(ApplicationStatus::Interview)
        ->and($jobApplication->next_step)
        ->toBe(ApplicationStatus::Interview->nextStep())
        ->and($jobApplication->next_action_at->toDateString())
        ->toBe('2026-08-22');
});

test('it does not synchronize the application when an historical interview is edited', function () {
    $jobApplication = JobApplication::query()->create([
        'company_name' => 'Test Company',
        'job_title' => 'Full Stack Developer',
        'status' => ApplicationStatus::Responded,
    ]);

    $interview = $jobApplication->interviews()->create([
        'interview_at' => '2026-08-22 10:30:00',
        'interview_type' => InterviewType::Technical,
    ]);

    $jobApplication->forceFill([
        'status' => ApplicationStatus::TechnicalTest,
        'next_step' => 'Complete technical test',
        'next_action_at' => '2026-08-25',
    ])->save();

    $interview->forceFill([
        'interview_at' => '2026-08-28 12:00:00',
    ])->save();

    $jobApplication->refresh();

    expect($jobApplication->status)
        ->toBe(ApplicationStatus::TechnicalTest)
        ->and($jobApplication->next_step)
        ->toBe('Complete technical test')
        ->and($jobApplication->next_action_at->toDateString())
        ->toBe('2026-08-25');
});

test('it stores translations for interview content fields', function () {
    $jobApplication = JobApplication::query()->create([
        'company_name' => 'Test Company',
        'job_title' => 'Full Stack Developer',
        'status' => ApplicationStatus::Responded,
    ]);

    $interview = $jobApplication->interviews()->create([
        'interview_at' => '2026-08-22 10:30:00',
        'interview_type' => InterviewType::Technical,
    ]);

    $interview
        ->setTranslations('people', [
            'es' => 'Ana García, Recruiter',
            'en' => 'Ana García, Recruiter',
            'fr' => 'Ana García, Recruiter',
        ])
        ->setTranslations('expected_questions', [
            'es' => 'Experiencia con Angular y Laravel',
            'en' => 'Experience with Angular and Laravel',
            'fr' => 'Expérience avec Angular et Laravel',
        ])
        ->setTranslations('strengths_to_defend', [
            'es' => 'Experiencia full stack y arquitectura',
            'en' => 'Full-stack and architecture experience',
            'fr' => 'Expérience full stack et architecture',
        ])
        ->setTranslations('risks_to_clarify', [
            'es' => 'Nivel de inglés hablado',
            'en' => 'Spoken English level',
            'fr' => 'Niveau d’anglais oral',
        ])
        ->setTranslations('notes', [
            'es' => 'Entrevista técnica de una hora',
            'en' => 'One-hour technical interview',
            'fr' => 'Entretien technique d’une heure',
        ])
        ->save();

    $interview->refresh();

    expect($interview->getTranslation('people', 'es', false))
        ->toBe('Ana García, Recruiter')
        ->and($interview->getTranslation('people', 'en', false))
        ->toBe('Ana García, Recruiter')
        ->and($interview->getTranslation('people', 'fr', false))
        ->toBe('Ana García, Recruiter')
        ->and($interview->getTranslation('expected_questions', 'es', false))
        ->toBe('Experiencia con Angular y Laravel')
        ->and($interview->getTranslation('expected_questions', 'en', false))
        ->toBe('Experience with Angular and Laravel')
        ->and($interview->getTranslation('expected_questions', 'fr', false))
        ->toBe('Expérience avec Angular et Laravel')
        ->and($interview->getTranslation('strengths_to_defend', 'es', false))
        ->toBe('Experiencia full stack y arquitectura')
        ->and($interview->getTranslation('strengths_to_defend', 'en', false))
        ->toBe('Full-stack and architecture experience')
        ->and($interview->getTranslation('strengths_to_defend', 'fr', false))
        ->toBe('Expérience full stack et architecture')
        ->and($interview->getTranslation('risks_to_clarify', 'es', false))
        ->toBe('Nivel de inglés hablado')
        ->and($interview->getTranslation('risks_to_clarify', 'en', false))
        ->toBe('Spoken English level')
        ->and($interview->getTranslation('risks_to_clarify', 'fr', false))
        ->toBe('Niveau d’anglais oral')
        ->and($interview->getTranslation('notes', 'es', false))
        ->toBe('Entrevista técnica de una hora')
        ->and($interview->getTranslation('notes', 'en', false))
        ->toBe('One-hour technical interview')
        ->and($interview->getTranslation('notes', 'fr', false))
        ->toBe('Entretien technique d’une heure');
});

test('it returns interview content for the selected locale', function () {
    $jobApplication = JobApplication::query()->create([
        'company_name' => 'Test Company',
        'job_title' => 'Full Stack Developer',
        'status' => ApplicationStatus::Responded,
    ]);

    $interview = $jobApplication->interviews()->create([
        'interview_at' => '2026-08-22 10:30:00',
        'interview_type' => InterviewType::Technical,
        'people' => [
            'es' => 'Equipo técnico',
            'en' => 'Technical team',
        ],
        'expected_questions' => [
            'es' => 'Preguntas sobre arquitectura',
            'en' => 'Architecture questions',
        ],
        'strengths_to_defend' => [
            'es' => 'Experiencia full stack',
            'en' => 'Full-stack experience',
        ],
        'risks_to_clarify' => [
            'es' => 'Nivel de inglés',
            'en' => 'English level',
        ],
        'notes' => [
            'es' => 'Preparar ejemplos',
            'en' => 'Prepare examples',
        ],
    ]);

    $interview->setLocale('es');

    expect($interview->people)
        ->toBe('Equipo técnico')
        ->and($interview->expected_questions)
        ->toBe('Preguntas sobre arquitectura')
        ->and($interview->strengths_to_defend)
        ->toBe('Experiencia full stack')
        ->and($interview->risks_to_clarify)
        ->toBe('Nivel de inglés')
        ->and($interview->notes)
        ->toBe('Preparar ejemplos');

    $interview->setLocale('en');

    expect($interview->people)
        ->toBe('Technical team')
        ->and($interview->expected_questions)
        ->toBe('Architecture questions')
        ->and($interview->strengths_to_defend)
        ->toBe('Full-stack experience')
        ->and($interview->risks_to_clarify)
        ->toBe('English level')
        ->and($interview->notes)
        ->toBe('Prepare examples');
});
