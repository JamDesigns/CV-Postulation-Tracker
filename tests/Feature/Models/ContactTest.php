<?php

use App\Models\Contact;
use Illuminate\Validation\ValidationException;

test('it rejects a completely empty contact', function () {
    expect(fn () => Contact::query()->create())
        ->toThrow(ValidationException::class);

    expect(Contact::query()->count())->toBe(0);
});

test('it allows a contact with only one identifying detail', function () {
    $nameContact = Contact::query()->create([
        'name' => 'Name Only',
    ]);

    $organizationContact = Contact::query()->create([
        'organization' => 'Organization Only',
    ]);

    $emailContact = Contact::query()->create([
        'email' => 'contact@example.com',
    ]);

    $urlContact = Contact::query()->create([
        'url' => 'https://example.com/contact',
    ]);

    expect($nameContact->exists)
        ->toBeTrue()
        ->and($organizationContact->exists)
        ->toBeTrue()
        ->and($emailContact->exists)
        ->toBeTrue()
        ->and($urlContact->exists)
        ->toBeTrue();
});

test('it uses the name as the display name when available', function () {
    $contact = Contact::query()->create([
        'name' => 'Jane Doe',
        'organization' => 'Example Company',
        'email' => 'jane@example.com',
        'url' => 'https://example.com/jane',
    ]);

    expect($contact->display_name)->toBe('Jane Doe');
});

test('it falls back to email for the display name when the name is missing', function () {
    $contact = Contact::query()->create([
        'organization' => 'Example Company',
        'email' => 'jane@example.com',
        'url' => 'https://example.com/jane',
    ]);

    expect($contact->display_name)->toBe('jane@example.com');
});

test('it falls back to organization for the display name when name and email are missing', function () {
    $contact = Contact::query()->create([
        'organization' => 'Example Company',
        'url' => 'https://example.com/contact',
    ]);

    expect($contact->display_name)->toBe('Example Company');
});

test('it falls back to url for the display name when no other detail is available', function () {
    $contact = Contact::query()->create([
        'url' => 'https://example.com/contact',
    ]);

    expect($contact->display_name)->toBe('https://example.com/contact');
});
