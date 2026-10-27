# Video library — Phase 2 media lifecycle and protected delivery

Phase 3 admin UI implementation and verification are recorded in [VIDEO_LIBRARY_ADMIN.md](VIDEO_LIBRARY_ADMIN.md).

Development work: 2026-10-26. Implements Phase 2 only; Phase 1 decisions remain in [VIDEO_LIBRARY.md](VIDEO_LIBRARY.md). No admin/student/public page UI or navigation is added. The student library and public showcase remain future phases. Production has not been accessed or changed.

## Implemented backend

- `MediaInspector`: actual MIME/container checks, ffprobe metadata, full video/audio decode validation, generated JPEG posters and sanitized raster covers. Commands use Symfony Process argument arrays, generated paths, file-only protocols, MOV/MP4 demuxing, disabled external data references and bounded runtime/output. Upload filenames never become shell commands or managed filenames.
- `VideoManager`: upload, metadata edits, hide/show, video/cover replacement, generated-cover reset, retries and deletion. Required prepared assets must exist and be nonempty before the ready-state transaction. First success sets published_at once. Visibility is independently preserved, including hide during processing. Failed initial upload stays failed; failed replacement keeps the previous source/cover/readiness/publication intact with an admin error.
- A second additive migration adds processing token/time, `video_media_operations` (request UUID and lifecycle status), and `video_asset_cleanups` (persistent write-ahead/retry ledger). Use a stable UUID request_key for each logical upload/replacement. In-flight/completed duplicate requests replay the same record; failed requests may retry with the same UUID and fresh files. Deletion leaves a canceled request tombstone, preventing late/repeated requests from recreating the video. A competing different media request returns 409. Metadata/hide/delete can proceed while processing; finalization re-locks the current record and validates its token.
- Files live under the dedicated private local disk as `objects/<server-generated-uuid>/video.mp4` and `cover.jpg`. No asset URL is persisted. Managed paths/ancestors are checked for traversal and escaping symlinks. Storage under either public/ or storage/app/public is rejected. The existing course/lesson S3 and temporary-file handling is unchanged.
- Files are prepared before swapping pointers. Generated covers refresh with video replacement; custom covers persist unless replaced/reset. Metadata does not require media upload. Source identity, hidden intent and publication time survive replacement. Delete requires the exact current title in `confirmation`, clears the selected public example via FK, and cancels operations.

## Accepted media and bounds

| Setting | Current behavior/default |
| --- | --- |
| Video file | `.mp4`, actual MP4 MIME, accepted ISO MP4 brand, intact top-level atoms, moov before mdat (Fast Start/Web Optimized). No automatic remux/transcode. |
| Video track | Exactly one H.264 track; Baseline/Constrained Baseline/Main/High, positive level up to 4.2, yuv420p 8-bit, progressive/unknown field order, positive average frame rate up to 60 fps. Full decode must succeed. |
| Orientation | Physically portrait (height > width), square pixels, no nonzero rotation metadata. Preserve source aspect ratio; 9:16 is a presentation target, not a forced crop. |
| Audio | None or one AAC-LC track, 1–2 channels, at most 48 kHz. Other/extra streams rejected. |
| Duration | Positive and finite. No hard three-minute limit. Decode time budget can reject unusually costly files with a processing error. |
| Video size/dimension | 256,000 KiB (250 MiB), no dimension above 3840. `VIDEO_LIBRARY_MAX_VIDEO_KIB`, `VIDEO_LIBRARY_MAX_VIDEO_DIMENSION`. Codec level restrictions still apply. |
| Custom cover | Actual decodable JPEG/PNG/static WebP, max 5120 KiB, dimension <=4096, <=8,000,000 pixels, memory preflight. SVG/animated inputs rejected. Decode/re-encode to JPEG strips appended content/metadata. Configurable with `VIDEO_LIBRARY_MAX_COVER_KIB`, `VIDEO_LIBRARY_MAX_COVER_DIMENSION`, `VIDEO_LIBRARY_MAX_COVER_PIXELS`. |
| Poster | Representative frame from a bounded 20-frame group after a short input seek; max width 720, no upscaling/cropping, square pixels, explicit full-range JPEG output. A custom cover remains the fallback for an unhelpful frame. |
| Tool budgets | Probe 15s, full decode 45s, poster 30s. Corresponding `VIDEO_LIBRARY_PROBE_TIMEOUT`, `VIDEO_LIBRARY_DECODE_TIMEOUT`, `VIDEO_LIBRARY_POSTER_TIMEOUT`; each is clamped to 1–300 seconds. Decoder threads limited to two, JPEG encoder to one. Captured probe JSON <=64 KiB; private stderr diagnostic tail <=8 KiB, process buffers cleared continuously. |
| Disk reserve | Greater of 1 GiB and 20% of volume size, plus incoming source/cover and 16 MiB working allowance. `VIDEO_LIBRARY_MINIMUM_FREE_BYTES`, `VIDEO_LIBRARY_FREE_SPACE_RESERVE_RATIO`. Application private disk is checked before copying; PHP upload-temp space must also be provisioned. |

Validation errors are Arabic, field-specific JSON errors (422); inspection failures explain required export settings where possible. There is no mandatory conversion pipeline. Ready-to-upload exports must use Fast Start, physically portrait H.264/AAC with the bounds above. Files with missing/corrupt packets are rejected even when their headers parse.

## Endpoints and response contract

All routes are in `routes/videos.php`, included by web.php, with existing session/CSRF middleware. Dedicated role middleware returns JSON 401/403, never login HTML as media bytes, and avoids visit logging. Every management service also checks its policy. Asset/JSON responses use `Cache-Control: private, no-store` and nosniff; the web server/CDN must preserve this.

| Method/path | Purpose |
| --- | --- |
| POST `/admin-dashboard/videos` | Multipart upload: title, one category FK, optional description/cover, video, request_key UUID. |
| GET `/admin-dashboard/videos/operations/{key}` | Owning admin's request status for post-upload processing polling. 404 until initialized or after video deletion. |
| GET `/admin-dashboard/videos/{video}` | Admin-only metadata/status; no storage paths or raw tool diagnostics. |
| PATCH `/admin-dashboard/videos/{video}` | Title/description/category edit; exactly one FK, no client program. New classification must be active; retaining an existing inactive classification is permitted for admin editing. |
| POST `/admin-dashboard/videos/{video}/media` | Multipart source and/or cover replacement, or reset_cover=true, with request_key. Cover and reset cannot be submitted together. |
| PATCH `/admin-dashboard/videos/{video}/visibility` | Set explicit is_visible boolean; does not bump published_at or force readiness. |
| DELETE `/admin-dashboard/videos/{video}` | Exact title confirmation; transactional cancellation/deletion and subsequent file cleanup. |
| PUT `/admin-dashboard/videos/public-example` | Explicit nullable video_id; eligible videos only. Existing singleton selection, no automatic fallback. |
| GET/HEAD `/admin-dashboard/videos/{video}/preview/{media\|cover}` | Separately authorized administrator preview, including hidden content with existing assets. |
| GET/HEAD `/student-dashboard/videos/{video}/{media\|cover}` | Student role + shared eligibility on each request, including the selected public-example video. |
| GET/HEAD `/videos/example/{media\|cover}` | Resolve only the current eligible singleton. No client-selected video ID; query parameters cannot choose another video. |

Upload/replacement/status responses include explicit metadata, `processing`, safe `processing_error`, and `is_public_example`; HTTP 202 while processing, 200 otherwise. Initial synchronous failure returns 422 while retaining the record/request association for retry. Request polling distinguishes processing from body-upload progress; Phase 3 must use real XHR upload progress, then this status API. No fake percentage or premature upload form is shipped. JSON controls require normal session CSRF tokens and Accept: application/json. Backend GETs are not an admin list UI; that is Phase 3.

Student/guest hidden, deleted, failed, missing-file or ineligible assets return 404 without metadata. Guests cannot access admin metadata or any student media/cover, even for the selected example; their only exception uses the separate public-example route. Unready replacements do not interrupt a previously ready version; new guest/student requests continue using the old version until the atomic switch succeeds. Revocation applies to subsequent requests, not bytes already downloaded.

Delivery uses the pinned Symfony `BinaryFileResponse` with bounded chunked reads, not Storage::get/full-file buffering. Verified GET/HEAD, 200/206/416, open/suffix ranges, exact response bytes and media headers. Malformed/multipart ranges are ignored with a full 200 (no multipart implementation); unsatisfiable single ranges return an empty 416 with Content-Range. If-Range is conservative: no cache validators are published, so mismatches get the complete current response. Client X-Sendfile/X-Accel headers are not trusted and no unverified web-server handoff is enabled.

## Cleanup and interrupted requests

Before each prepared-file write, a cleanup candidate is persisted with a one-hour grace period. Committed asset references are protected during reconciliation. Replaced/deleted files become due inside the same DB transaction; deletion is attempted only after commit. Failed removal logs a private diagnostic, increments attempts and retains the ledger item for retry after ten minutes. Failed processing similarly schedules its candidate files for cleanup without deleting a working version. No shared temp directory is cleaned.

`php artisan video-library:cleanup` recovers processing operations older than one hour and retries up to 100 due cleanup items. Recovery invalidates the old token and marks initial uploads failed; an existing ready version stays playable. A late processor cannot finalize after token invalidation/deletion. Retry uses a fresh processing token and directory. The grace period exceeds the maximum allowed tool budgets. At normal volume, run this hourly through the operator's verified scheduler, and repeat if a backlog remains. A nonzero exit means cleanup failures remain recorded; inspect private logs and disk ownership, then retry. No queue worker is required or scheduler installed by this phase. Keep request tombstones; deleting them changes idempotency guarantees.

## Local setup and deployment handoff

Use the existing isolated harness only. Tests generate tiny H.264/AAC fixture files with FFmpeg into UUID directories under storage/app/qudrat-local/video-tests and use a separate private test disk there. Those files are removed after tests. No production files, real environment files, payments, mail or existing learning data are used. File-space reserve settings are relaxed only inside this disposable test disk; defaults remain operationally conservative. The Windows development drive currently has less than 20% free, so a later local browser upload needs more capacity or an explicitly appropriate development-only reserve setting.

Observed tools: Herd PHP 8.3.11, isolated MySQL 8.0.43 on loopback:33317, FFmpeg/ffprobe 8.0.1 on PATH. PHP requires fileinfo, GD (JPEG/PNG/WebP), PDO MySQL and process execution. `VIDEO_LIBRARY_FFMPEG` and `VIDEO_LIBRARY_FFPROBE` may name trusted absolute binary paths. The tools must be installed in the actual PHP runtime/container, not only its host. No packages were installed or upgraded.

```powershell
./tools/qudrat-local.ps1 start
& "$env:USERPROFILE/.config/herd/bin/php83/php.exe" -c storage/app/qudrat-local/php.ini vendor/phpunit/phpunit/phpunit -c phpunit.video-library.xml
./tools/qudrat-local.ps1 stop
```

Before a separately authorized deployment, follow [DEPLOYMENT.md](DEPLOYMENT.md) and confirm the still-unknown persistent private mount, owner permissions, private document-root boundaries, backups/restores, actual MySQL CHECK support, FFmpeg build/codec support inside yarub-php, and web/FPM limits. Back up records and private files together. Do not roll back these tables after uploads without a reviewed data-preservation plan.

For defaults, align PHP upload_max_filesize=250M, post_max_size=260M, proxy request allowance >=260 MiB, upload/input budget appropriate to connection speed (proposed 300s), post-upload response/FPM budget >=120s and PHP memory >=256 MiB (512 MiB used in development). If tool budgets increase, raise the processing response budget accordingly; maximum configurable tool budgets total 900s, below recovery grace. Verify these in HTTP/FPM, not CLI. Current isolated CLI upload/post defaults remain 2M/8M: PHPUnit test uploads bypass PHP multipart transport limits and do not prove server upload configuration. No live configuration was changed.

After backups/preflight and only when deployment is authorized, inspect migration status, apply only missing reviewed paths in this order, then verify the release:

```bash
docker exec --user www-data --workdir /var/www/yarub yarub-php php artisan migrate:status
docker exec --user www-data --workdir /var/www/yarub yarub-php php artisan migrate --path=database/migrations/2026_10_26_000001_create_library_videos_tables.php --force
docker exec --user www-data --workdir /var/www/yarub yarub-php php artisan migrate --path=database/migrations/2026_10_26_000002_add_video_media_lifecycle.php --force
```

Run `video-library:cleanup` in the same verified application environment when due; use the existing route-clear/config/OPcache policy. No frontend build, content import or queue worker is introduced. Real HTTP upload-size handling, proxy/static-alias behavior, actual container persistence, production tools, and real Safari/Chrome/Android playback remain deployment/browser checks; they are not established by local kernel tests.

## Acceptance evidence

The video test suite uses actual FFmpeg/ffprobe fixtures and exact emitted response bytes. It checks compatible/incompatible/truncated/corrupt media, both cover paths, publication, metadata/category/size/space validation, failed initial retry, failed replacement preservation, idempotent replay and real two-process duplicate submission, hide/delete interleavings, canceled request replay, interrupted recovery, cleanup failure/retry, roles, public-example switching/replacement/revocation, missing/escaping paths, range semantics, and explicitly enabled CSRF verification. No rendered UI or real-device compatibility is claimed.

Final development results (explicit PHP 8.3 executable and isolated ini as above):

| Check | Actual result |
| --- | --- |
| `phpunit -c phpunit.video-library.xml` | Passed: 22 tests, 360 assertions, including real concurrent workers. |
| `phpunit -c phpunit.qudrat.xml --filter 'AdminContentTest\|PracticeTest'` | Passed: 22 tests, 234 assertions. |
| `phpunit -c phpunit.arabic-skills.xml --filter 'FoundationTest\|AdminPagesTest\|StudentPagesTest'` | Passed: 17 tests, 721 assertions. |
| `phpunit -c phpunit.learning-public.xml` | Passed: 5 tests, 57 assertions, including existing login destinations. |
| New lifecycle migration application and direct down/up roundtrip | Passed on the exact disposable test schema, with empty feature-table guards. |
| PHP syntax checks | Passed for all 17 inspected feature/support/route PHP files. |
| `git diff --check`, new-file trailing whitespace scan, focused diff/status review | Passed; pre-existing roadmap deletion and untracked implementation contract preserved. |

The full final media suite passed after correcting poster full-range output and input-seek placement found by a valid colored-video fixture. Expected validation failures, injected cleanup failure and injected processing failure are test scenarios. Tests used no live services. The isolated database instance started for this verification was stopped after checks. No deployment, frontend build or navigation/UI changes were performed.

Next phase: **Phase 3 — Admin video management UI**. Stop here until requested.
