# Public learning pages: Docker release commands

Prepared 2026-10-24. These commands are for the owner to execute; no production operation was performed while preparing them.

The earlier “Plan Qudrat Phase 1” deployment chat records successful PHP commands in `yarub-php`, working directory `/var/www/yarub`, as `www-data`. Node/npm builds ran on the host using `/var/cache/yarub-npm` after the default npm cache caused a permission error. The owner identified the production database as `yarubapp`, at `db:3306`. Confirm these still describe the deployment.

Run commands separately and stop on failure. Before any first-time Arabic Skills schema/content work, retain a restorable database backup and the previous code/assets, and use the site's established maintenance procedure while the feature is incomplete.

## Pull and build

```bash
cd /var/www/yarub
sudo -u www-data git status --short
sudo -u www-data git pull --ff-only origin main
sudo -u www-data npm --cache=/var/cache/yarub-npm run build
```

Investigate unexpected tracked server changes before pulling. This release changes no dependency lockfiles, so an existing working dependency installation does not need `npm ci` or Composer installation. If Node dependencies are absent, the previously successful installation command is:

```bash
sudo -u www-data npm ci --cache=/var/cache/yarub-npm --include=dev
```

Then rerun the build. Build on the host, and run Artisan in the PHP container.

## Only if Arabic Skills is not deployed yet

The public-page commit adds no migration or importer changes. The preceding Arabic Skills feature commit (`5ec63bc`) does add one migration and its content importer. Skip this section if that feature has already been migrated and imported successfully.

Inspect migration state:

```bash
docker exec --user www-data --workdir /var/www/yarub yarub-php \
  php artisan migrate:status
```

Apply only the missing Arabic Skills migration; stop if tables already exist but migration history disagrees:

```bash
docker exec --user www-data --workdir /var/www/yarub yarub-php \
  php artisan migrate \
  --path=database/migrations/2026_10_23_000001_create_arabic_skill_tables.php --force
```

Validate the deployed content:

```bash
docker exec --user www-data --workdir /var/www/yarub yarub-php \
  php artisan arabic-skills:import --dry-run
```

Expect **5 banks, 50 models, 1,500 questions, 3,000 points**, and the reviewed SHA-256:

```text
bd92f3d973ac169cfb04d28a162cee238d7b9d7c5f89b89b7e017bd40b4b6b33
```

If the digest matches, import with the process-only opt-in:

```bash
docker exec -it \
  --user www-data \
  --workdir /var/www/yarub \
  --env ARABIC_SKILLS_ALLOW_PRODUCTION_IMPORT=true \
  yarub-php php artisan arabic-skills:import \
  --production \
  --database=yarubapp \
  --sha256=bd92f3d973ac169cfb04d28a162cee238d7b9d7c5f89b89b7e017bd40b4b6b33
```

Confirm the displayed target is `db:3306/yarubapp`, then answer **yes**. If the database has changed, use its actual name instead. Do not change `APP_ENV` or add the opt-in permanently to `.env`. The importer requires production console mode, preserves existing administrator changes and history, and rejects production overwrite flags. Exit code 2 means non-conflicting records were committed while conflicts were skipped: inspect the report rather than forcing an overwrite. Rerunning the same guarded command should report unchanged source records without duplicates.

The earlier chat confirms both Qudrat migrations already succeeded; this release does not require rerunning them or importing Qudrat again.

## Refresh and check

After the build and any required Arabic Skills setup succeed:

```bash
docker exec --user www-data --workdir /var/www/yarub yarub-php \
  php artisan route:clear

docker exec --user www-data --workdir /var/www/yarub yarub-php \
  php artisan view:clear
```

No configuration change, Docker image rebuild, or blanket container restart is introduced by this release. Follow the existing OPcache refresh procedure if the PHP runtime retains old code. Do not newly enable route/config caching as part of this update; the repository's deployment notes list existing compatibility concerns.

Check `/qudrat`, `/arabic-skills`, both sample interactions, mobile navigation, and the `/lessons` redirect. Check that a guest's training button opens student login and then returns to the selected feature. If Arabic Skills was newly imported, check its five banks in the student dashboard and review importer counts before reopening access.

See [public page implementation and checks](PUBLIC_LEARNING_PAGES.md), [Arabic Skills implementation](ARABIC_SKILLS.md), and [deployment constraints](DEPLOYMENT.md).
