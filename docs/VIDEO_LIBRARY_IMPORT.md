# Import the supplied video collection

The root `vids` folder contains 38 MP4/JPG pairs, 131,538,337 bytes before preparation: 10 برهان, 11 رسم, 7 مداد and 10 نسق. Files are paired by the complete basename. Only the first ` - ` separates the type from the title; for example `برهان - قلم يعرب - افتتاحية تجذب القارئ.mp4` becomes title `قلم يعرب - افتتاحية تجذب القارئ` in existing Arabic Skills bank `burhan` (بُرهان). Other mappings are `rasm`, `midad`, `nasaq`. No new classifications or course questions are created by the production importer.

All files need Fast Start container preparation. Twenty-eight are remuxed with original encoded tracks; ten نسق files have anamorphic pixels and are re-encoded with square pixels while preserving display proportions, without cropping. Audio is copied. Two نسق sources have square stored frame dimensions but portrait display proportions; padding alone would not correctly normalize their pixel aspect. The original videos and thumbnails remain untouched. Custom thumbnails are passed through the existing cover sanitizer on import.

## Local test and package

Use the dedicated native harness from [release prerequisites](VIDEO_LIBRARY_RELEASE.md), with PHP 8.3, isolated MySQL, FFmpeg/ffprobe on PATH and installed locked dependencies. No real environment files or production data are required.

```powershell
# Start only when the dedicated instance is stopped.
./tools/qudrat-local.ps1 start
$videoPhp = "$env:USERPROFILE/.config/herd/bin/php83/php.exe"
& $videoPhp -c storage/app/qudrat-local/php.ini tools/video-seed-local.php setup
# Preparation refuses an existing output directory. Reuse the already prepared bundle for repeat imports.
& $videoPhp -c storage/app/qudrat-local/php.ini tools/prepare-video-seed.php vids storage/app/qudrat-local/video-seed/bundle
& $videoPhp -c storage/app/qudrat-local/php.ini tools/video-seed-local.php video-library:import --path=storage/app/qudrat-local/video-seed/bundle --dry-run
& $videoPhp -c storage/app/qudrat-local/php.ini tools/video-seed-local.php video-library:import --path=storage/app/qudrat-local/video-seed/bundle --admin=video-seed-admin@example.invalid
& $videoPhp -c storage/app/qudrat-local/php.ini -S 127.0.0.1:8027 -t public tools/video-seed-router.php
```

Open `http://127.0.0.1:8027/student-dashboard/videos`. Synthetic student: `video-seed-student@example.invalid`; administrator: `video-seed-admin@example.invalid`; both use `local-video-seed`. The local helper explicitly selects only `127.0.0.1:33317/yarub_qudrat_dev_local`, with private assets in `storage/app/qudrat-local/video-seed/private`. It does not attach to a previously selected review database. It creates missing classification headers from the existing content package solely in this disposable development database, preserving existing rows. It does not import the question banks. Normal session authentication applies; there is no automatic login. These development credentials are never imported into production.

Reserve checks are relaxed only in this dedicated local helper; production retains the configured 1 GiB/20% reserve. Stop the server with Ctrl+C and the dedicated database with `./tools/qudrat-local.ps1 stop` when finished; imported local records and files persist for the next test session.

The database-free `--dry-run` validates names, complete pairs, recognized types and SHA-256 hashes and prints the package digest. It does not decode media or check database classifications; the actual import does. The package root must contain only the paired MP4/JPG files. No metadata file belongs inside it.

Create a transfer archive after the prepared bundle passes local import:

```powershell
tar -czf storage/app/qudrat-local/yarub-video-seed.tar.gz -C storage/app/qudrat-local/video-seed bundle
Get-FileHash storage/app/qudrat-local/yarub-video-seed.tar.gz -Algorithm SHA256
```

Keep the archive and its SHA-256 with the release evidence. Prepared media is runtime content; `vids` and the local runtime directory are ignored by Git. Commit/deploy the importer code and migration normally, and transfer the archive privately. GitHub media publication is not needed for this route. Do not place the archive or extracted media under `public` or `storage/app/public`.

## Server deployment

These are operator instructions, **not actions executed by this task**. Replace `USER@HOST` and `ADMIN_ID` with the SSH destination and an existing production administrator ID/email. Start with the [release checklist](VIDEO_LIBRARY_RELEASE.md): backup, persistent private-media mount, tools/extensions, disk capacity, application release/build and the established maintenance/cache policy. Keep the current `APP_ENV=production`. Confirm the actual database is still `db:3306/yarubapp`; change the explicit target deliberately if infrastructure differs.

1. Deploy the reviewed application code using [DEPLOYMENT.md](DEPLOYMENT.md). The complete video feature must be present, with built Vite assets. Inspect migration status. If the two earlier video migrations are missing, apply them in order using the release guide, after existing user/Qudrat/Arabic Skills parents. Then apply the new import ledger migration:

```bash
docker exec --user www-data --workdir /var/www/yarub yarub-php php artisan migrate:status
docker exec --user www-data --workdir /var/www/yarub yarub-php php artisan migrate --path=database/migrations/2026_10_27_000001_create_video_seed_imports_table.php --force
```

The existing Arabic Skills banks `burhan`, `rasm`, `midad`, `nasaq` must exist and be active. If missing, complete the separately documented/approved Arabic Skills content import first. The video importer fails before writes when a required bank is missing/inactive; it never creates or republishes those banks.

2. Transfer the archive from the development machine:

```powershell
scp storage/app/qudrat-local/yarub-video-seed.tar.gz USER@HOST:/tmp/yarub-video-seed.tar.gz
```

On the server, compare `sha256sum` to the recorded archive hash, then copy and extract privately. These examples require `tar` inside the PHP container; verify availability first. The fixed directory is a staging location, separate from the managed media root.

```bash
sha256sum /tmp/yarub-video-seed.tar.gz
docker cp /tmp/yarub-video-seed.tar.gz yarub-php:/tmp/yarub-video-seed.tar.gz
docker exec --user www-data --workdir /var/www/yarub yarub-php mkdir -p storage/app/video-seed-source
docker exec --user www-data --workdir /var/www/yarub yarub-php tar -xzf /tmp/yarub-video-seed.tar.gz -C storage/app/video-seed-source
docker exec --user www-data --workdir /var/www/yarub yarub-php chmod -R u=rwX,g=rX,o= storage/app/video-seed-source
docker exec --user www-data --workdir /var/www/yarub yarub-php php artisan video-library:import --path=storage/app/video-seed-source/bundle --dry-run
```

Require its package digest to equal `d30652882ebbadd473e30712f46fd4dbe29a9199279c814394d079dab36781b7`. The prepared archive SHA-256 is `e3c33d09855ca073980128da542879b22f530bf7168e97f3e8a0f62a48c086b4`. An archive hash and package digest serve different purposes; do not interchange them. Do not extract a changed package on top of an old one; use a fresh private staging directory and dry-run it again.

3. Run the actual import with the locally reviewed package digest:

```bash
docker exec -it --user www-data --workdir /var/www/yarub \
  --env VIDEO_LIBRARY_ALLOW_PRODUCTION_IMPORT=true \
  yarub-php php artisan video-library:import \
  --path=storage/app/video-seed-source/bundle \
  --admin=ADMIN_ID --production --database=yarubapp \
  --sha256=d30652882ebbadd473e30712f46fd4dbe29a9199279c814394d079dab36781b7
```

Read the displayed target and answer yes. Production imports require the process-only opt-in, exact database, reviewed digest and interactive confirmation; there is no implicit production mode or unattended force switch. No permanent environment change is needed. CLI import avoids HTTP body/transfer limits but still runs disk-space checks, complete decoding, compatibility validation and cover sanitization through the same media service as admin uploads. Allow time for all 38 files to finish.

4. Verify the result count, titles and type filters as an authenticated student, cover loading, playback/seek and a shared link through login. Expected type counts are 10/11/7/10. Guests still cannot browse or retrieve library files/covers. The public example selection stays unchanged; select one explicitly through the admin UI if desired. Keep the media root persistent and backed up, and maintain the cleanup schedule in the release guide.

## Repeat, failure and recovery

`video_seed_imports` records stable filename identities, source checksums and request keys; existing media-operation tombstones retain upload/deletion state. Rerunning the identical package skips completed imports, preserving titles, classification edits, visibility, covers and public-example choice made by administrators afterward. It does not recreate deleted imports. Changing a source pair under the same filename is an explicit conflict, not an automatic overwrite or duplicate. New filenames are new identities, so do not rename files to force a retry.

An import stops on failure; prior successful videos remain usable. Resolve the reported problem and rerun the exact same package, using the original administrator for unfinished items. In-flight operations require inspection or interrupted-operation cleanup before retry. Do not delete ledger rows/tombstones to bypass conflicts. Changed media for an existing video should use the admin replacement workflow after review; missing managed assets require restoration, not reseeding by force.

Include `video_seed_imports` in the consistent full database/private-media backup. Staged source files are separate from the managed copies; retain the tested transfer package securely for recovery. Never run rollback/down/fresh or delete managed media as a deployment rollback. Follow the existing release recovery guidance, preserving learning/payment data.

## Verification record

The isolated video suite passes **46 tests / 714 assertions**, including six import tests for title/type mapping, custom covers, repeat safety/admin edits, changed-source conflicts, deletion tombstones, preflight dependencies, missing pairs, read-only dry-run and local rejection of production override. PHP syntax checks pass for the new command/service/migration/helpers/tests; diff and new-file whitespace checks pass. No frontend code changed, so no new frontend build was needed.

All 38 real pairs imported successfully into the dedicated development database. A subsequent identical import reports **0 imported / 38 skipped**. A read-only verification checked each imported title, bank slug, ready/eligible state, sanitized custom JPEG cover and exact managed-video SHA-256 against the prepared source. Counts: burhan 10, rasm 11, midad 7, nasaq 10. No public example was selected. Initial validation correctly rejected non-Fast-Start and anamorphic inputs; only failed disposable attempts were reset during preparation, with completed imports and operation tombstones preserved.

Browser verification used the actual synthetic student login and showed 38 videos, the requested stripped title and matching cover through search. The normalized نسق video decoded successfully (750×1080, duration 110.178 seconds, readyState 4, no media error); closing returned to the catalog. Screenshot evidence: local `storage/app/qudrat-local/video-seed/seeded-title.jpg`. The local server and dedicated MySQL were intentionally left running for the user's test session. No production import, deployment, Git commit or push was performed.

The prepared bundle contains 76 files totaling 150,637,043 bytes. Its private transfer archive `storage/app/qudrat-local/yarub-video-seed.tar.gz` is 145,385,150 bytes (about 139 MiB). Use the archive already prepared here; rerunning FFmpeg preparation on another tool version can produce a different digest and requires another review/import test.
