# YARUB deployment runbook

Use this guide for every manual deployment. Updated 2026-10-24 using the owner's recorded server commands, repository source, and the earlier “Plan Qudrat Phase 1” chat. No production operation was performed to prepare this guide.

## Recorded server setup

| Item | Value |
| --- | --- |
| Host checkout | `/var/www/yarub` |
| Branch / remote | `main` / `origin` |
| Application owner | `www-data` |
| PHP container | `yarub-php` |
| Container working directory | `/var/www/yarub` |
| Artisan execution user | `www-data` |
| Database target | `db:3306/yarubapp` |
| Node/npm | On the host; Node 22 was used for the earlier build |
| Writable npm cache | `/var/cache/yarub-npm` |

Run Git and npm on the **host**, and Artisan in the **PHP container**. Reconfirm these values if infrastructure changes. Keep production credentials outside Git and this document.

## 1. Decide which steps apply

Review every commit between the deployed version and the release, and run relevant checks locally or in staging.

| Change | Deployment work |
| --- | --- |
| Documentation only | Pull only. |
| PHP code | Pull; use the existing OPcache refresh procedure if needed. |
| Routes | Clear route cache after prerequisites are ready. |
| Blade views | Clear compiled views; build if Tailwind classes changed. |
| JS, CSS, Tailwind, Vite | Build assets; `public/build` is ignored, so pulling alone does not publish them. |
| npm lockfile or missing Node dependencies | Run `npm ci`, then build. |
| Composer lockfile/configuration | Review PHP/extensions, hooks and the actual Composer installation procedure before release. Install dependencies before dependent migrations/builds. |
| New migrations | Backup, inspect history, apply only reviewed missing migrations. |
| Initial question banks or reviewed source content update | Run the relevant guarded importer after its migrations. Ordinary code/UI changes do not need imports. |
| Environment/configuration | Provision securely and follow the established config-cache policy. |
| Docker configuration/image | Review the actual infrastructure procedure; ordinary code changes do not imply an image rebuild. |

Before schema/content changes, retain a restorable database backup and the previous code/assets. Use the established maintenance procedure while schema, assets or content are incomplete. Production backup/restore and maintenance commands have not yet been recorded here; resolve them before a release that needs them.

Run commands separately and stop on failure. Do not continue after an unsuccessful pull, install, migration, validation or build.

## 2. Inspect and pull

After SSH:

```bash
cd /var/www/yarub
sudo -u www-data git status --short
sudo -u www-data git branch --show-current
sudo -u www-data git log -1 --oneline
sudo -u www-data git fetch origin
sudo -u www-data git diff --stat HEAD..origin/main
```

Confirm `main`, inspect the release diff and investigate unexpected server changes. Preserve uploads, runtime data and `.env`/`.env.save`.

```bash
sudo -u www-data git pull --ff-only origin main
sudo -u www-data git log -1 --oneline
```

Stop on divergence or conflicts; do not substitute forced resets or unreviewed merges.

## 3. Install dependencies only when needed

For a changed npm lockfile or missing dependencies, run on the host:

```bash
sudo -u www-data npm ci --cache=/var/cache/yarub-npm --include=dev
```

Development dependencies are required for building. The dedicated cache avoids the earlier `/var/www/.npm` permission failure. If this directory is missing, an operator with permission can recreate just the cache:

```bash
install -d -o www-data -g www-data -m 0750 /var/cache/yarub-npm
```

Do not run dependency upgrades, `npm audit fix`, or `composer update` during routine deployment. A production Composer executable/install command has not yet been verified; establish it in the actual PHP runtime if Composer dependencies change. Vite also requires existing Composer vendor assets.

## 4. Apply reviewed missing migrations

Skip when there are no new migrations. First inspect history:

```bash
docker exec --user www-data --workdir /var/www/yarub yarub-php \
  php artisan migrate:status
```

Replace the placeholder below with each exact reviewed pending migration filename:

```bash
docker exec --user www-data --workdir /var/www/yarub yarub-php \
  php artisan migrate --path=database/migrations/REVIEWED_MIGRATION.php --force
```

Do not execute the placeholder literally. Current feature migrations:

| Feature | Migration path |
| --- | --- |
| Qudrat tables | `database/migrations/2026_10_21_000001_create_qudrat_tables.php` |
| Qudrat automatic publication | `database/migrations/2026_10_21_000002_publish_qudrat_content_by_default.php` |
| Arabic Skills tables | `database/migrations/2026_10_23_000001_create_arabic_skill_tables.php` |
| Video library foundation | `database/migrations/2026_10_26_000001_create_library_videos_tables.php` |
| Video media lifecycle | `database/migrations/2026_10_26_000002_add_video_media_lifecycle.php` |
| Video seed import identities | `database/migrations/2026_10_27_000001_create_video_seed_imports_table.php` |
| Qudrat practice set identity | `database/migrations/2026_10_29_000001_add_set_identity_to_qudrat_sessions.php` |
| Arabic Skills practice set identity | `database/migrations/2026_10_29_000002_add_set_identity_to_arabic_skill_sessions.php` |

Before deploying video media endpoints, complete the private storage, media-tool, upload-limit and cleanup prerequisites in [VIDEO_LIBRARY_MEDIA.md](VIDEO_LIBRARY_MEDIA.md), then follow the [Phase 6 release and recovery handoff](VIDEO_LIBRARY_RELEASE.md) and [QA evidence](VIDEO_LIBRARY_QA.md). These migrations were verified only on isolated development MySQL; no video deployment has been performed.

For the supplied paired video/thumbnail collection, use the [private archive transfer and idempotent import procedure](VIDEO_LIBRARY_IMPORT.md). Importing media is a separate explicit release step, not part of ordinary migrations or seeding.

Both Qudrat migrations succeeded in the earlier recorded deployment. Check current history rather than assuming another environment has them. Stop if existing tables disagree with migration history. Editing applied migrations does not apply those edits on production.

Avoid unrelated migration batches. Never use `migrate:fresh`, `migrate:refresh`, demo seeders or automatic rollback for deployment. MySQL schema changes are not an all-or-nothing recovery mechanism.

## 5. Import content only when needed

Import for initial feature setup or an explicitly reviewed source-content update, not after every pull. Run only the relevant validation:

```bash
docker exec --user www-data --workdir /var/www/yarub yarub-php \
  php artisan qudrat:import --dry-run

docker exec --user www-data --workdir /var/www/yarub yarub-php \
  php artisan arabic-skills:import --dry-run
```

Dry-runs do not connect to the database. Compare the digest with the locally reviewed package:

| Package | Original inventory | Reviewed SHA-256 as of 2026-10-24 |
| --- | --- | --- |
| `qudrat_content/` | 6 categories, 1,200 questions, 4,800 options, 40 passages | `53630957f5c66ad73874feb7adad614cdd536f016a3ae049b5f91cbeac8446da` |
| `arabic_skills_content/` | 5 banks, 50 models, 1,500 questions, 3,000 points | `bd92f3d973ac169cfb04d28a162cee238d7b9d7c5f89b89b7e017bd40b4b6b33` |

These describe the original packages, not live administrator-managed totals. If source files change, review and validate them locally and update the approved digest. Do not approve unexpected server content simply by copying its digest.

For the matching reviewed Qudrat package:

```bash
docker exec -it \
  --user www-data \
  --workdir /var/www/yarub \
  --env QUDRAT_ALLOW_PRODUCTION_IMPORT=true \
  yarub-php php artisan qudrat:import \
  --production --database=yarubapp \
  --sha256=53630957f5c66ad73874feb7adad614cdd536f016a3ae049b5f91cbeac8446da
```

For the matching reviewed Arabic Skills package:

```bash
docker exec -it \
  --user www-data \
  --workdir /var/www/yarub \
  --env ARABIC_SKILLS_ALLOW_PRODUCTION_IMPORT=true \
  yarub-php php artisan arabic-skills:import \
  --production --database=yarubapp \
  --sha256=bd92f3d973ac169cfb04d28a162cee238d7b9d7c5f89b89b7e017bd40b4b6b33
```

Confirm the intended target `db:3306/yarubapp`, then answer **yes**. Use the actual reviewed database name if infrastructure changed. Keep `APP_ENV=production`; the process-only opt-in works with cached config and should not be added permanently to `.env`.

Both importers preserve administrator edits, additions, archives and learner history/snapshots, and reject production overwrite flags. Exit code **0** means success; **1** means validation, authorization, cancellation or runtime failure; **2** means non-conflicting records committed while conflicts were skipped. Save and inspect the report, and resolve conflicts deliberately. Repeat the same approved import to verify unchanged records without duplicates. For separately approved automation only, `--force --no-interaction` replaces the prompt while retaining the other guards.

## 6. Build assets when needed

After required dependency installation, run on the host:

```bash
sudo -u www-data npm --cache=/var/cache/yarub-npm run build
```

Require a successful build and matching Vite manifest/assets. Do not use `npm run dev` in production. Existing Browserslist and Tailwind safelist notices were non-blocking in earlier builds; actual errors must be resolved.

## 7. Refresh affected caches

For route changes:

```bash
docker exec --user www-data --workdir /var/www/yarub yarub-php \
  php artisan route:clear
```

For Blade changes:

```bash
docker exec --user www-data --workdir /var/www/yarub yarub-php \
  php artisan view:clear
```

Do not introduce `route:cache` or `config:cache` routinely: known duplicate route names and direct `env()` integration reads require separate verification. Follow the established configuration-cache policy for environment changes. Follow the existing OPcache refresh procedure if PHP retains old code; service/container restart commands have not yet been verified here. Avoid blanket cache clearing and container restarts for every release.

## 8. Verify and record

- Confirm the deployed commit and successful build/migration/import reports.
- Check affected public, student and admin journeys with planned test accounts, including mobile layouts and asset loading.
- For training changes, check banks, starting/resuming practice, feedback and results. For discovery changes, check `/qudrat`, `/arabic-skills`, their previews/login destinations and the `/lessons` redirect.
- Inspect relevant logs without sharing secrets. Avoid live purchases, broadcasts or uploads as casual checks.
- Reopen access after required checks pass; record the commit, commands, reports and follow-up work.

If verification fails, stop and use the agreed recovery plan. A Git reversal alone does not restore database changes, uploads, payments or cached state. Record backup locations/tested production restores, maintenance handling, OPcache refresh, service management, Composer runtime and worker/scheduler procedures when confirmed. Do not invent rollback, restart, storage-link or permission-reset commands.

See [project overview](PROJECT_OVERVIEW.md), [Qudrat](QUDRAT.md), [Arabic Skills](ARABIC_SKILLS.md), and [repository safeguards](../AGENTS.md).
