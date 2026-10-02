# CV Postulation Tracker

CV Postulation Tracker is a local-first web application for managing and following a job search from a single place.

It covers the lifecycle of a job application: companies and contacts, applications, CV versions, technical dossiers, communications and events, interviews, attachments, next actions, reminders, salary information, and job-search statistics.

> **Local application:** CV Postulation Tracker is designed to run locally on your own computer or local network environment. It is not intended to be deployed as a public-facing web application.

## Main features

- Job application tracking with status, source, work mode, salary range, salary expectations, next steps, rejection reasons, offer snapshots, and application-form snapshots.
- Company and contact management, including contacts associated with individual applications.
- Application event history and communication tracking.
- Interview management and follow-up.
- CV version and technical dossier management.
- File attachments associated with applications.
- Dashboard with summaries, pending next steps, recent applications, and upcoming interviews.
- Job-search statistics and charts for status, source, work mode, evolution, funnel, and rejection reasons.
- Database notifications and scheduled reminders for pending application actions.
- Configurable UI color palettes.
- Multilingual interface: Spanish, English, and French.
- Webmail shortcuts for Gmail, Outlook, Yahoo Mail, and Yandex Mail.

## Technology stack

- PHP 8.3+
- Laravel 13.8+
- Filament 4
- Livewire
- SQLite by default
- Vite 8
- Tailwind CSS 4
- Node.js / npm
- Pest 4
- Laravel Pint
- Filament Language Switch
- Laravel Lang

## Requirements

- PHP 8.3 or later, with the extensions required by Laravel and SQLite.
- Composer.
- Node.js and npm.
- Git.
- SQLite, the default database for the project.

A local development environment such as Laragon can provide most of these dependencies on Windows.

## Installation

There are two recommended ways to run the project locally: Laragon on Windows or any compatible local PHP environment.

### Option A: Windows with Laragon

Clone the repository inside Laragon's web root:

```bash
cd C:/laragon/www
git clone https://github.com/JamDesigns/CV-Postulation-Tracker.git
cd CV-Postulation-Tracker
```

Install PHP dependencies and create the environment file:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

The project uses SQLite by default. Create the database file if it does not already exist:

```bash
touch database/database.sqlite
```

On Windows Command Prompt, the equivalent is:

```cmd
type nul > database\database.sqlite
```

Run the migrations and install/build frontend dependencies:

```bash
php artisan migrate
npm install
npm run build
```

Create the first Filament user:

```bash
php artisan make:filament-user
```

Follow the interactive prompts to set the name, email, and password.

Laragon can expose projects through its local virtual-host system. Make sure the web server uses the application's `public` directory as the document root. Alternatively, start Laravel's development server:

```bash
php artisan serve
```

Open the URL displayed by Artisan. The root route redirects to the Filament panel at `/admin`.

### Option B: Generic local PHP environment

Clone the repository and enter the project directory:

```bash
git clone https://github.com/JamDesigns/CV-Postulation-Tracker.git
cd CV-Postulation-Tracker
```

Install and configure the application:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Create `database/database.sqlite`, then run:

```bash
php artisan migrate
npm install
npm run build
php artisan make:filament-user
```

For a simple local installation:

```bash
php artisan serve
```

For Apache, Nginx, Caddy, or another installed local web server, configure its virtual host/site so the **document root points to the project's `public/` directory**. Do not expose the project root itself.

Update `APP_URL` in `.env` when necessary to match the local hostname.

## Development mode

The project provides a Composer script that starts Laravel's development server, the queue listener, and Vite together:

```bash
composer run dev
```

## Environment configuration

The supplied `.env.example` is configured for local development. Important defaults are:

```dotenv
APP_ENV=local
APP_DEBUG=true
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_TIMEZONE=Europe/Madrid

DB_CONNECTION=sqlite
SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

Set `APP_NAME` and `APP_URL` as appropriate for the local installation.

Because sessions, queues, cache, notifications, and application data use database-backed functionality, run the migrations before using the application.

## Background processes and reminders

Application reminders use Laravel's scheduler. The project schedules the reminder command every five minutes.

To process scheduled tasks continuously during local use:

```bash
php artisan schedule:work
```

If queued jobs are used, run the queue listener as well:

```bash
php artisan queue:listen --tries=1
```

`composer run dev` already starts the queue listener, but the scheduler is a separate process.

## Languages

The application currently supports:

- Spanish (`es`)
- English (`en`)
- French (`fr`)

Available locales are defined in:

```text
config/locales.php
```

The Filament language selector and flags are configured in:

```text
app/Providers/AppServiceProvider.php
```

Translations are stored in both JSON files and locale directories:

```text
lang/es.json
lang/en.json
lang/fr.json

lang/es/
lang/en/
lang/fr/
```

### Adding a new language

For example, to add German (`de`):

1. Add `de` to `config/locales.php`.

```php
return [
    'es',
    'en',
    'fr',
    'de',
];
```

2. Add the locale and its flag to the language-switch configuration in `app/Providers/AppServiceProvider.php`.

```php
->flags([
    'es' => asset('flags/es.svg'),
    'en' => asset('flags/gb.svg'),
    'fr' => asset('flags/fr.svg'),
    'de' => asset('flags/de.svg'),
])
```

3. Add the flag as `public/flags/de.svg`.
4. Create `lang/de.json`.
5. Create `lang/de/`.
6. Add translated versions of the files present in the other locale directories, keeping translation keys synchronized.
7. Clear cached configuration:

```bash
php artisan optimize:clear
```

The new locale can then be exposed by the Filament language switcher.

## Database

SQLite is the default database:

```dotenv
DB_CONNECTION=sqlite
```

The default local database file is:

```text
database/database.sqlite
```

Laravel supports other database engines, but using one requires configuring the corresponding Laravel database connection and credentials in `.env`. The repository's default setup uses SQLite.

## Authentication

The Filament panel requires authentication. After a fresh installation, create a user with:

```bash
php artisan make:filament-user
```

The application panel is available at `/admin`, and the root URL redirects to it automatically.

## File storage

CV files, technical dossiers, and application attachments are managed by the application and served through authenticated routes where appropriate.

Keep user-generated files and local application data out of version control. When moving an existing installation to another computer, back up the SQLite database and any required files under `storage/`.

## Tests

Run the automated test suite:

```bash
php artisan test
```

Or use the Composer script:

```bash
composer test
```

## Code style

Check formatting with Laravel Pint:

```bash
./vendor/bin/pint --test
```

Apply formatting:

```bash
./vendor/bin/pint
```

## Frontend assets

Development:

```bash
npm run dev
```

Build:

```bash
npm run build
```

## Updating an existing local installation

After pulling repository changes, the usual update sequence is:

```bash
git pull
composer install
php artisan migrate
npm install
npm run build
php artisan optimize:clear
```

Back up local application data before significant updates when appropriate.

## License

The project currently declares the MIT license in its Composer configuration.
