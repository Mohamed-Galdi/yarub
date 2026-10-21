# Working on YARUB

YARUB is an existing production Arabic learning platform. ARRIB is a separate project for the same client; do not import its assumptions or configuration. Inspection baseline: `a0efd0b` (production synchronization), 2026-10-21.

## Architecture and navigation

Laravel 10 monolith: session authentication, Eloquent, controller-driven Blade pages, Livewire/PowerGrid admin tables, and Vite/Tailwind/Alpine assets. Students buy courses or monthly/annual lessons through Moyasar, watch S3/CloudFront video, take tests, receive certificates, and contact support.

- `routes/{web,auth,admin,student,api}.php`: public, authentication, dashboard, and minimal API routes.
- `app/Http/Controllers/{Admin,Student,Auth}` and root controllers: request handling and business logic; `app/Http/Middleware`: role gates and visit logging.
- `app/Models`, `database/migrations`: relationships and declared schema; `database/seeders`: demo/content writers, not production setup instructions.
- `resources/views/{home,admin,student,auth,layouts,components}`: Arabic UI; `app/Livewire`: tables; `app/Exports`: Excel exports.
- `resources/{js,css}`, `public/assets`, `vite.config.js`, `tailwind.config.js`: assets; build also depends on Composer vendor assets.
- [Project overview](docs/PROJECT_OVERVIEW.md): flows, locked versions, integrations, and open questions.
- [Deployment](docs/DEPLOYMENT.md): confirmed manual pull and conditional steps requiring verification.

## Conventions

Follow nearby code: PSR-4 `App\` classes, four-space PHP indentation, Eloquent relationships/scopes, named routes, inline request validation (auth also uses FormRequests), Blade components and Tailwind utilities. Preserve Arabic text, RTL layout, existing route names, table/pivot names, and JSON/redirect response contracts. SweetAlert supplies user feedback. Eager-load relationships: `AppServiceProvider` prevents lazy loading outside production. Keep new features within this architecture and make minimal, focused changes; avoid broad formatting or refactoring.

## Local commands and verification limits

Use a deliberately isolated local environment with a disposable database and non-live integration settings before booting Laravel. Start from `.env.example`, which omits project-specific settings listed in the overview. Do not reuse production credentials. No complete clean-install recipe has been verified.

| Command | Evidence / conditions |
| --- | --- |
| `npm run dev` | Defined as `vite` in `package.json`; not run during inspection. |
| `npm run build` | Defined as `vite build`; not run. Requires existing Node dependencies and Composer vendor assets; writes `public/build` and can load environment files. |
| `php artisan serve` | Laravel local server entry point; not run or verified end to end here. Requires isolated configuration first. |
| `php vendor/phpunit/phpunit/phpunit --no-configuration --bootstrap vendor/autoload.php --do-not-cache-result tests/Unit` | Executed successfully: one placeholder test, one assertion; does not bootstrap Laravel. |
| `php artisan test` | Conventional suite entry point, not run. Feature tests use `RefreshDatabase`; current `phpunit.xml` does not isolate the DB. Fix test environment isolation before using it. |
| `php -l path/to/file.php` | Syntax-only check for a changed PHP file; does not validate behavior. |

Observed tools: PHP CLI 8.4.5, Node 22.14.0, npm 10.9.2. These are local observations, not production requirements or proof of compatibility: locked OpenSpout excludes PHP 8.4. Tests are mostly stale Breeze scaffolding; a passing unit placeholder gives no journey coverage.

## Production safety

- Inspect `git status` first and preserve unrelated changes. Never commit runtime/editor files or secrets. `.env` and `.env.save` must remain outside version control; both are ignored at baseline. Do not read real environment files or credential backups for routine discovery.
- Never assume a local database is disposable. Do not run migrations, seeders, `message:send-demo`, destructive tests, uploads, payment calls, or live mail without an explicitly appropriate environment and task scope. Even browsing pages can write visitor records.
- Do not access production, deploy, or push unless the task authorizes it. Follow the deployment document; do not invent a standard cache/migration/restart sequence.
- Do not upgrade dependencies incidentally. Security/advisory review and remediation are separate work, with compatibility testing. Do not silently fix the behavioral concerns listed in the overview while implementing unrelated features.
- Review access checks, payment/subscription integrity, data retention, and integration side effects for changes in those areas. Add focused behavior tests against isolated data when warranted.
