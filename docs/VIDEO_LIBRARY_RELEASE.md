# Video library release and recovery handoff

Phase 6, 2026-10-27. This is a development verification and future operator checklist. No production commands, uploads, migrations, deployment or infrastructure changes were performed. Read [deployment facts](DEPLOYMENT.md), [foundation](VIDEO_LIBRARY.md), [media lifecycle](VIDEO_LIBRARY_MEDIA.md), [admin](VIDEO_LIBRARY_ADMIN.md), [catalog](VIDEO_LIBRARY_STUDENT.md), and [player](VIDEO_LIBRARY_PLAYER.md) together.

## Development reproduction

Requirements observed locally: Herd PHP 8.3.11, MySQL 8.0.43, Node 22/npm 10, installed locked Composer/Node dependencies, FFmpeg and ffprobe 8.0.1 on PATH. PHP needs PDO MySQL, fileinfo, GD JPEG/PNG/WebP, mbstring and the existing application extensions. Keep PHP 8.2/8.3 for the existing lockfile; do not incidentally upgrade dependencies. The native harness creates a dedicated loopback MySQL instance on port 33317 and guarded disposable schemas. It never reads root environment files.

From the repository in PowerShell:

```powershell
./tools/qudrat-local.ps1 start
$videoPhp = "$env:USERPROFILE/.config/herd/bin/php83/php.exe"
& $videoPhp -c storage/app/qudrat-local/php.ini vendor/phpunit/phpunit/phpunit -c phpunit.video-library.xml
& $videoPhp -c storage/app/qudrat-local/php.ini vendor/phpunit/phpunit/phpunit -c phpunit.qudrat.xml --filter 'AdminContentTest|PracticeTest'
& $videoPhp -c storage/app/qudrat-local/php.ini vendor/phpunit/phpunit/phpunit -c phpunit.arabic-skills.xml --filter 'FoundationTest|AdminPagesTest|StudentPagesTest'
& $videoPhp -c storage/app/qudrat-local/php.ini vendor/phpunit/phpunit/phpunit -c phpunit.learning-public.xml
& $videoPhp -c storage/app/qudrat-local/php.ini vendor/phpunit/phpunit/phpunit -c phpunit.admin-tracking.xml --filter UsersListTest
node --test tests/Frontend/*.test.mjs
node tools/qudrat-build.mjs
```

Run database suites sequentially: they share the exact disposable `yarub_qudrat_test_local` schema. Do not run them while browser review fixtures exist. Ordinary root `php artisan test` remains inappropriate because its environment is not isolated.

For an optional browser review, after the suites:

```powershell
& $videoPhp -c storage/app/qudrat-local/php.ini tools/video-library-review.php prepare-qa
& $videoPhp -c storage/app/qudrat-local/php.ini -d upload_max_filesize=250M -d post_max_size=260M -d max_execution_time=120 -S 127.0.0.1:8026 -t public tools/video-library-router.php
```

The second command remains in the foreground; use a second terminal for helper commands. `prepare-qa` generates a labeled four-second synthetic Arabic teaching clip, poster/custom cover, synthetic accounts and contact fields. Windows uses installed Arial for readable fixture text; FFmpeg must support drawtext and find the chosen font. This content is review data, not client teaching material. The helper initially uses synthetic student authentication; `review-admin`, `review-student`, and `review-session` select synthetic admin, synthetic student, or the normal session/login flow. These overrides exist only in the guarded loopback review router. They do not alter production authentication. Root environment files and live integrations remain unused.

Stop the review server with Ctrl+C, then clean up and stop the dedicated instance:

```powershell
& $videoPhp -c storage/app/qudrat-local/php.ini tools/video-library-review.php cleanup
./tools/qudrat-local.ps1 stop
```

Review/runtime files and screenshots stay Git-excluded. The helper relaxes free-space reserves only for its isolated review disk. The development drive had about 78.5 GB free of 510.8 GB during QA, below the default 20% reserve; this is not evidence to lower the production reserve.

## Required infrastructure evidence before release

An operator must confirm these against the real environment. The repository has no container/compose, volume or reverse-proxy configuration establishing them.

| Area | Required evidence |
| --- | --- |
| Database | MySQL 8.0.16+ enforcing CHECK constraints, reviewed users/Qudrat/Arabic Skills parent migrations already applied, backup and tested restore available. MariaDB compatibility has not been established. |
| Private media | Persistent, backed-up storage mounted at the configured private disk root. Default container location is `/var/www/yarub/storage/app/video-library`. Confirm host/volume identity, write/read permissions for `www-data`, capacity and survival across container replacement. |
| Exposure | Root stays outside `public`; no web-server alias, public symlink, object/CDN URL or static fallback serves video files or covers. Do not run `storage:link` for this disk. Test bypass attempts against the actual front server. |
| Media runtime | FFmpeg/ffprobe executable by the PHP runtime, required H.264/AAC decoding and poster extraction, GD sanitization, process execution permitted, writable PHP temporary directory and private media root. |
| HTTP settings | Verify the effective PHP-FPM and proxy settings, body/time/memory limits, buffering/temp storage and range delivery through the real HTTPS front server. CLI ini output alone does not prove FPM settings. |
| Recovery | Confirm backup location, consistent snapshot method, tested restore, existing maintenance/drain procedure, OPcache refresh and scheduler ownership. These production procedures remain unknown. |
| Clients | Real iOS/Safari and Android playback, full screen, native share, HTTPS clipboard/fallback, keyboard/screen-reader checks on supported clients. Local responsive Chromium is not physical-device coverage. |

Known container/working directory from deployment inspection: `yarub-php`, `/var/www/yarub`, user `www-data`. Future read-only preflight examples (not executed here):

```bash
docker inspect --format '{{json .Mounts}}' yarub-php
docker exec --user www-data --workdir /var/www/yarub yarub-php php -v
docker exec --user www-data --workdir /var/www/yarub yarub-php php -m
docker exec --user www-data --workdir /var/www/yarub yarub-php php --ini
docker exec --user www-data --workdir /var/www/yarub yarub-php ffmpeg -version
docker exec --user www-data --workdir /var/www/yarub yarub-php ffprobe -version
docker exec --user www-data --workdir /var/www/yarub yarub-php df -h /var/www/yarub/storage/app
```

Keep infrastructure output private. Adjust tool paths only after confirming them; use `VIDEO_LIBRARY_FFMPEG` and `VIDEO_LIBRARY_FFPROBE` when necessary. Prove persistence with a planned synthetic asset, recorded hash and stable video ID across the operator's verified container replacement procedure. No restart/recreation command is inferred here.

## Limits and capacity

Application defaults: 250 MiB video (`VIDEO_LIBRARY_MAX_VIDEO_KIB=256000`), 5 MiB cover, video dimension at most 3840, cover dimension at most 4096 and eight million pixels. These are operational limits, not a three-minute duration restriction. Inspect actual client files before launch; MP4 H.264/AAC compatibility and full decode validation remain enforced.

Coordinate PHP `upload_max_filesize=250M`, `post_max_size=260M` and a proxy body limit of at least 260 MiB, accounting for multipart overhead. A suggested starting budget is 300 seconds for body transfer and 120 seconds for bounded synchronous processing, with suitable FPM/proxy/input settings and at least 256 MiB PHP memory (512 MiB used in local QA). These are proposed values requiring an actual maximum-size HTTP upload trial, not measured production capacity. Video inspection/decode/poster default budgets are 15/45/30 seconds; do not increase one timeout without checking the complete request budget.

`VIDEO_LIBRARY_REQUEST_TIMEOUT` now defaults to 450 seconds for media uploads; the form bounds it to 150–1800 seconds. It includes transfer, processing and margin. Metadata-only requests retain 150 seconds. Align the client setting with the effective server/transfer budgets; a browser timeout may occur while the server continues, so retry/reconciliation must retain the request key. HTTP 413 now explains the server size limit and preserves entered form data.

Disk admission requires incoming bytes plus working allowance and the greater of 1 GiB or 20% of the disk capacity as reserve. Keep these defaults unless a reviewed capacity plan justifies otherwise. Fifty maximum-size source files alone are about 12.2 GiB, excluding covers, temporary files and retained replacements. Budget simultaneous PHP upload temp plus application staging, decode/poster working space, old ready versions during replacement and cleanup backlog. Check both PHP temp and private-media volumes; measuring one does not establish capacity on the other.

## Future authorized migration and release sequence

1. Resolve the infrastructure evidence above, record the reviewed commit, stop video mutations using the operator's confirmed procedure, drain in-flight processors and pause cleanup. Take and verify the consistent backup below. Do not casually disable unrelated learning or payment flows.
2. Follow the confirmed pull/dependency procedure in [DEPLOYMENT.md](DEPLOYMENT.md). Inspect migration status. Apply only these missing, reviewed video migrations after their existing parent tables are present:

```bash
docker exec --user www-data --workdir /var/www/yarub yarub-php php artisan migrate:status
docker exec --user www-data --workdir /var/www/yarub yarub-php php artisan migrate --path=database/migrations/2026_10_26_000001_create_library_videos_tables.php --force
docker exec --user www-data --workdir /var/www/yarub yarub-php php artisan migrate --path=database/migrations/2026_10_26_000002_add_video_media_lifecycle.php --force
```

3. Build on the host with the known `www-data`/npm-cache procedure in DEPLOYMENT.md; require a matching manifest and assets including all video entries. Apply only the documented route/view clear and established configuration/OPcache policy. Do not invent restart or cache commands. No seed/import, queue worker, new package or new schema migration is introduced by Phase 6.
4. Arrange this command hourly using the operator's verified scheduler (not installed by this feature):

```bash
docker exec --user www-data --workdir /var/www/yarub yarub-php php artisan video-library:cleanup
```

Processing is synchronous; no queue worker is required. Cleanup recovers operations older than one hour and handles up to 100 due cleanup candidates per invocation. Failed deletions remain for retry after ten minutes; monitor nonzero exits and repeat when a backlog remains. Preserve operation tombstones and cleanup ledgers.

5. Use planned test accounts and synthetic media to verify the entire acceptance journey in [QA evidence](VIDEO_LIBRARY_QA.md), actual HTTP size rejection, range bytes/HEAD/416, static bypass denial and persistence. Confirm existing student programs/login/cart behavior and public/student navigation. Reopen video mutations and resume cleanup only after acceptance; record evidence and follow-ups.

## Consistent backup, restore and rollback

Back up the full application database and private-media tree as one consistent recovery point, after draining video mutations and pausing cleanup. Include `library_videos`, `video_library_settings`, `video_media_operations`, `video_asset_cleanups`, their existing user/classification parents, all current and pending private objects, stable IDs/paths, and a hash manifest. Retain the reviewed code/build manifest/assets and operational configuration securely outside Git. Backing up rows without bytes, or bytes without selection/lifecycle rows, is insufficient. The production snapshot tooling and credentials must come from the operator; no unverified dump/restore commands are supplied.

Restore first into an isolated environment: preserve IDs and relative paths, restore ownership/mount configuration, compare hashes, then check student eligibility, selected-example identity, exact range bytes, hide/remove revocation and cleanup protection for restored live assets. The local JourneyTest performs this paired synthetic row/file restoration and advances time before cleanup; it does not prove a full production database restore, cloud backup or actual container mount persistence.

If application verification fails, close access through the established maintenance procedure and retain evidence. Prefer the reviewed previous code with its matching built assets while keeping the additive feature tables and private media intact. Do not run `migrate:rollback`, migration `down`, `fresh`, blanket file deletion or ledger truncation as a rollback recipe. Suspend video mutation/cleanup and classification deletion until the restored code's compatibility with feature references is assessed. Old code may not understand new references; retaining data alone does not establish behavioral compatibility.

A database restore is a separate recovery decision: restoring an earlier whole database can discard later purchases, progress and support changes. Reconcile intervening writes under the operator's approved recovery plan. Git rollback does not recover data or files. Confirm and rehearse the real backup/restore, maintenance and OPcache procedures before release.
