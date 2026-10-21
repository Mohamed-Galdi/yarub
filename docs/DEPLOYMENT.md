# YARUB manual deployment

## Confirmed setup

Confirmed by the project owner, not by server access during this inspection:

- Existing production Laravel application at `/var/www/yarub`, owned by `www-data`.
- Production changes synchronized into GitHub and local `main` at `a0efd0b`.
- Deployment is manual; no CI/CD.
- From `/var/www/yarub`, the established update command is:

```sh
sudo -u www-data git pull --ff-only origin main
```

Do not replace fast-forward-only pulls with forced resets/merges. Stop and investigate divergence or conflicting server changes. This document authorizes no deployment; no server command was run for its preparation.

## Release checklist

Before an authorized release, review the exact diff/target commit and affected journeys; check the server checkout for local changes and confirm the current branch. Preserve runtime data, uploads and `.env`/`.env.save` outside Git. Verify a restorable backup and recovery plan before schema/data changes. Run relevant checks in an isolated local/staging environment and agree the conditional steps below before pulling code that depends on them.

Only the pull above is an established production command. The following table is a decision checklist, **not an executable production runbook**. Candidate commands are unverified and must be checked for the actual runtime, execution user, paths, order and side effects.

| Change | Deployment work to determine | Unverified candidates / caveats |
| --- | --- | --- |
| Documentation only | Pull approved commit; no app-specific step indicated by source. | No migration/build/cache action justified solely by docs. |
| PHP controllers/models/middleware | Pull; determine opcode/process refresh needs and any related caches. | PHP-FPM/web-server service names, OPcache policy and reload commands unknown. Do not assume restarts are required. |
| Routes | Determine whether route cache is enabled and refresh it if needed. | `php artisan route:clear` / `route:cache`; current duplicate route names and missing controller action need checking before caching. |
| Blade templates/components | Determine compiled-view handling; include asset build if Tailwind classes changed. | `php artisan view:clear` / `view:cache`; no confirmed production cache policy. |
| JS/CSS, Tailwind classes/config, npm lock | Build and publish matching Vite assets; confirm whether built on server or elsewhere. | `npm ci`, `npm run build` are candidates for an authorized future release, not run here. `public/build` is ignored, so pulling alone does not ship a rebuilt bundle. Build needs Composer vendor assets too. |
| Composer manifest/lock | Install locked dependencies using the confirmed compatible PHP/extensions and review hooks. | `composer install --no-dev --prefer-dist --optimize-autoloader` is a candidate only. Never substitute `composer update`. Hooks run package discovery; update hooks publish assets. |
| Configuration/environment | Owner provisions secrets outside Git; determine cache strategy and direct `env()` dependencies first. | `php artisan config:clear` / `config:cache`; CloudFront domain is read with `env()` in controllers, which needs review before enabling config caching. Never regenerate production `APP_KEY` as a routine deployment step. |
| New/changed migration | Review compatibility, backup/recovery, DB state and sequencing before release. Existing migration edits may not run on an already migrated DB. | `php artisan migrate --force` is an unverified candidate, not a default step. No migrations/seeds were run. Do not use `migrate:fresh`, `refresh`, demo seeders or automatic rollback as deployment procedures. |
| Storage/upload paths | Verify existing public link, persistent files, S3 configuration and least-required write permissions. | `php artisan storage:link` only if verified necessary. No recursive ownership/permission changes are prescribed. Video writes/deletes may hit S3; use test storage before release. |
| Queued work | Establish whether any workers actually run and how managed. | `php artisan queue:restart` is conditional/unverified; source has no custom queued jobs and example uses sync. Do not create workers as a routine step. |
| Scheduler | Establish server cron/timers and whether new tasks need setup. | No active application schedule found. Do not add cron based on framework defaults. |

After release, check the target commit and the affected public/student/admin flows, assets and logs without exposing secrets. Page requests can write visitor records; uploads, messages, tests and payments have additional side effects. Use planned test accounts/data and provider test mode for transactional checks; do not trigger a live purchase or broadcast message casually.

If verification fails, stop further changes and use the agreed recovery plan. A Git code reversal alone does not restore database changes, uploaded/deleted objects, payments or cached state. There is no verified rollback command sequence yet.

## Details still requiring confirmation

Production PHP/Composer/Node versions and extensions; web server and document root; database engine/version and migration history; asset build/publish location; environment provisioning; cache/OPcache policy; writable paths/public storage link; backup locations and tested restores; worker/cron/process-manager setup; service reload procedure; staging availability and post-release checks. Do not infer these from local tools or `.env.example`.

See [project overview](PROJECT_OVERVIEW.md) for integration variables and known source inconsistencies, and [AGENTS.md](../AGENTS.md) for coding and local test safeguards.
