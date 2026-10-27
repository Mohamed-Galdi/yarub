# Video library — Phase 1 foundation

This document retains the Phase 1 discovery record. Phase 2 implementation and current backend/setup details are in [VIDEO_LIBRARY_MEDIA.md](VIDEO_LIBRARY_MEDIA.md).

Discovery and development verification: 2026-10-26. Scope: Phase 1 of `YARUB_VIDEO_LIBRARY_IMPLEMENTATION.md` only. No routes, controllers, navigation, uploads or player UI are enabled. No production access or real environment-file inspection was used.

## Repository findings

- Laravel 10.48.22, Eloquent, Blade, Livewire 3.5.2/PowerGrid 5.9.0, Vite 5.4.10, Tailwind 3.4.10 and Alpine 3.14.1 (lockfiles). Keep this monolith; no new framework or dependencies.
- One session/web guard, `User.role` (`student`/`admin`), soft-deleted users, `auth`, `verified`, and role middleware. `User` does not implement `MustVerifyEmail`; do not treat `verified` as a new requirement. Existing policies are registered in `AuthServiceProvider`.
- Student pages use `/student-dashboard`, `student.*` names and `layouts.student`; admins use `/admin-dashboard`, `admin.*` and `layouts.admin`. Their middleware also logs visits. Media requests should not inherit visit logging on every range request.
- Student login deliberately accepts only exact Qudrat/Arabic Skills index destinations. Phase 5 must extend this safely for the library, preserving query/type/page/video state with a validated same-origin internal path; never accept an arbitrary return URL. Preserve cart/default login behavior. Page guests redirect to login; media/JSON must return errors rather than login HTML.
- Existing purchase/subscription, training sessions, answers and progress are separate. Videos have no links to those records and add no subscription checks.
- Actual types are `QudratCategory` (`qudrat_categories`, title/slug/sort_order/is_published) and `ArabicSkillBank` (`arabic_skill_banks`, title/slug/sort_order/is_active). ArabicSkillModel is a question set within a bank, not the video type. Fetch dropdown choices from these tables, ordered by sort_order/id, grouped by the two programs. No copied slug/title list or taxonomy seed is introduced.
- Qudrat admin has category editing but no category delete endpoint; its current update forces publication. Arabic Skills has bank activation/deactivation, not deletion. Existing content FKs already restrict deletion. New video FKs also restrict it; a future deletion interface must translate this into `هذا النوع مرتبط بفيديوهات. أعد تصنيفها أو احذفها أولًا.` rather than cascading. Deactivation is allowed and immediately excludes associated videos through eligibility.
- Existing filesystem disks are local, public and S3. Old course/lesson uploads use public temporary storage then S3/CloudFront; do not reuse that lifecycle for this feature. Queue defaults to sync; no jobs-table migration/custom worker setup or container/web-server configuration is tracked. Deployment documentation identifies `yarub-php`, but does not establish its mounts or actual HTTP limits.
- Visual reference inspected in source: student Qudrat index/header, admin Qudrat index, public `home/learning/qudrat`, `qudrat.css`, `learning-public.css`, and shared layouts. Retain RTL, Arabic fonts from app.css, indigo `#292a65`, readable slate text, restrained bordered white surfaces, consistent spacing and responsive controls. No rendered/UI verification is claimed in this phase.
- Impeccable is available at `.agents/skills/impeccable/SKILL.md`, read completely; its Windows `context --target routes/student.php` completed. It found no PRODUCT.md or DESIGN.md and only unrelated admin-student surface briefs. Before new UI work, follow its context/init/new-work instructions and read craft-floor immediately before UI edits. No design artifacts were invented in Phase 1.

## Data and authorization decisions

`LibraryVideo` / `library_videos` uses a numeric stable primary key, independent of title. Two nullable foreign keys target the existing category tables, with a DB CHECK enforcing exactly one non-null key. Each FK rejects nonexistent types and restricts deletion. This provides stronger referential integrity than a polymorphic type/id pair without changing either program. MySQL **8.0.16+ with enforced CHECK constraints** is required; verify the actual production engine/version before migration. There is no stored program column: `program` derives from the chosen FK (`qudrat` or `arabic_skills`).

The migration preflight rejects unverified engines/versions before table creation, including older MySQL versions that can ignore CHECK constraints. The additive migration also stores private disk/relative asset paths, technical metadata, cover origin, processing/error state, independent visibility intent, first publication time and optional uploading admin. Pending/processing/failed records are technical states, not editorial drafts. Source paths are nullable until processing succeeds. `uploaded_by` becomes null on hard deletion; the relationship includes soft-deleted administrators for audit. Processing fields are deliberately excluded from mass assignment; Phase 2 must assign them from inspected server results only.

`LibraryVideo::metadataRules()` validates required title (255), optional plain description (10,000), exactly one existing category, and prohibits client program assignment; the saving hook enforces the category/title rules for model writes too. Inactive types may remain attached for administration but never make content eligible. Phase 2 should reject newly selected inactive classifications with an actionable field error, while allowing edits to existing inactive records. Escape description text in future Blade views. Upload/file validation is not implemented yet.

`eligible()` is the one reusable query: ready + visible + published Qudrat category OR active Arabic Skills bank (the OR is nested under readiness/visibility). `isEligible()` re-queries persisted state to avoid stale authorization after visibility/category changes. Student ordering uses published_at DESC/id DESC; admin ordering uses created_at DESC/id DESC. Phase 2 must set published_at only at the first successful publication; hide/show/edit/replacement must preserve it, including when processing finishes after an admin hide.

`LibraryVideoPolicy` distinguishes:

| Ability | Authorization | Apply later to |
| --- | --- | --- |
| viewAny | Active authenticated student role | Library page, listing/search/related endpoints |
| view | Student + current eligibility | Direct metadata, media and cover requests |
| manage/create/update/delete | Active administrator role | Every management mutation; existing web CSRF/session conventions |
| preview | Administrator only, independently of eligibility | Management preview metadata/cover/media |
| viewPublicExample | Selected singleton ID + current eligibility; allows guests | Public example metadata/cover/media only |

No blanket admin `before` override is used. An admin does not obtain student catalog access through `view`; preview is explicit. Soft-deleted users cannot access student/management abilities. Generic model serialization hides asset paths and diagnostics, but future endpoints must still build explicit authorized payloads; hidden fields alone are not access control.

`VideoLibrarySetting` is one DB-constrained row (id=1) with a nullable FK, initially empty. `PublicExample::select()` requires an administrator, locks the singleton and validates eligibility in a transaction; a new choice atomically replaces the previous selection. Null clears it. Deletion clears the FK. `eligiblePublicExample()` resolves only this reference and re-applies eligibility without fallback. A hidden/inactive selected example may reappear when eligible again. Every later guest asset request must resolve the singleton afresh; selection never grants access to other IDs. Library shares remain protected even for the selected example.

## Proposed routes (not registered)

| Surface | Proposed route/name | Guard |
| --- | --- | --- |
| Student library/overlay | GET `/student-dashboard/videos?video=<id>&query=...&type=...&page=...`, `student.videos.index` | auth + student + viewAny; selected video also view |
| Student metadata/related | GET `/student-dashboard/videos/{video}` and `/{video}/related` | Explicit student authorization and eligibility; bounded related list |
| Student bytes/covers | GET `/student-dashboard/videos/{video}/media`, `/{video}/cover` | view on every request; 401/403/404, never redirect HTML |
| Management | `/admin-dashboard/videos`, `admin.videos.*` | auth + admin + policy; CSRF on mutations |
| Admin preview assets | GET `/admin-dashboard/videos/{video}/preview/{media-or-cover}` | preview policy only |
| Public showcase | GET `/videos`, `videos` | Introduction + selected eligible example + login/library CTA |
| Public example assets | GET `/videos/example/media`, `/videos/example/cover` | Resolve singleton; no client-chosen library ID |

No public catalog/search/filter/related route is planned. Add admin navigation only in Phase 3 and student/public navigation only after Phase 5's complete player. Ensure unauthorized IDs reveal no protected metadata, file paths or distinction between hidden/failed/deleted records.

## Storage, processing and delivery plan for Phase 2

- Dedicated `video_library` disk roots at `storage/app/video-library`, outside `storage/app/public`. No URL, symlink or public route is configured. Store generated relative names in separate managed video/cover/temp directories. Reject path traversal/symlink escapes; never trust upload names or accept disk/path values from clients. Web server document root must be `public/`, with no alias to this private root.
- Start with bounded synchronous inspection/poster generation: no transcoding, worker or new queue infrastructure at this scale. Use Symfony Process argument arrays, trusted binary paths, local-only inputs/protocol restrictions and process timeouts. ffprobe must inspect actual container, H.264 profile/pixel format/audio, dimensions, duration and file integrity; MP4 extension/MIME alone is insufficient. A nonblank frame candidate can be selected for a generated JPEG poster; constrain width to 720, preserve aspect ratio, no source crop. Reassess queued processing only if actual fixtures exceed bounds; keep the same retry-safe lifecycle.
- Operational defaults in `config/video_library.php` are proposals, not client duration requirements or active upload validation: max video **250 MiB** (256,000 KiB), portrait height > width, no dimension above 3840, positive finite duration with no arbitrary 3-minute cap. Browser baseline: MP4/H.264 Baseline/Main/High, 8-bit yuv420p up to level 4.2, max 60 fps; optional AAC-LC audio, no unsupported extra streams, fast-start metadata required or safely remuxed without transcoding in Phase 2. Reject HEVC, 10-bit, interlaced or unsupported tracks with export instructions; verify real Safari/Chrome/Android fixtures later. JPEG/PNG/WebP custom covers up to 5 MiB and 4096 px per dimension; reject SVG/animation and re-encode decoded raster data. Probe timeout 15s, poster timeout 30s. These values can be configured via the named environment settings; exact fps/codec enforcement belongs to Phase 2.
- Align proposed application 250 MiB video + 5 MiB cover with PHP upload_max_filesize=250M, post_max_size=260M, proxy body limit at least 260 MiB, upload/input time budget 300s and post-upload processing/FPM/proxy response budget at least 90s. Validate on actual server; CLI limits do not establish FPM settings. Current isolated PHP CLI defaults are only 2M upload/8M post, so uploads would require configuration before browser testing. Never silently change production limits.
- Prefer an operator-configured internal Nginx X-Accel-Redirect location if available: PHP authorizes every request and returns a safe internal file path; internal location must not be externally addressable. Otherwise Symfony BinaryFileResponse for verified local paths, not Storage::get/full-file buffering. Verify 200/206/416, GET/HEAD, Content-Range, Content-Length, Content-Type, seek and invalid ranges with fixtures. No handoff capability is assumed today. Use `Cache-Control: private, no-store` on all asset/metadata responses, including the example, and ensure proxies/CDNs never cache an authorization bypass. Implement and test this in Phase 2.
- Validate replacements into new generated paths before the DB swap. Preserve ID/hidden intent/publication time/custom cover; failed replacement leaves existing playable version. Clean replaced files only after commit. Deletion/failed cleanup needs a safe retry ledger/log and scoped reconciliation; no job can resurrect a deleted record. These lifecycle operations are intentionally deferred.

## Environment and deployment prerequisites

Observed locally: compatible test PHP 8.3.11 via Herd, MySQL 8.0.43 on the isolated loopback port 33317, FFmpeg/ffprobe 8.0.1 under `C:/tools`, roughly 93 GB free on C: at discovery. Default shell PHP is a different installation; use the explicit PHP 8.3 path below. Media tools were located/version checked only, not executed on uploads. No production mount/capacity/tool availability is established.

Before a later authorized release, operators must confirm:

1. MySQL CHECK support, existing prerequisite migrations/tables and reviewed backup. The new migration changes no existing table/data; MySQL DDL is not atomic. On failure stop and inspect partial schema/history rather than rerunning or dropping blindly.
2. Private root is persistent across `yarub-php` replacement/deployment, mounted at the app's storage path, writable by its PHP user and never publicly aliased. No Docker/compose mount definition exists in the repository. Confirm actual mounts and ownership; do not invent a docker volume command.
3. FFmpeg/ffprobe are available inside the actual PHP application container, with bounded process execution allowed; host binaries alone are insufficient. Confirm FPM/proxy limits, free space and process budgets using representative fixtures. No worker is required for the chosen synchronous plan.
4. Reserve up to about 12.5 GiB for 50 maximum-size videos plus covers, another source-size copy per concurrent replacement/upload and temp workspace; allow at least 20% filesystem headroom. Actual typical sizes remain unknown. Check free bytes before accepting files; include temp disk in the calculation. Reject safely when insufficient. Operators should check disk usage periodically and reconcile only unreferenced managed paths after a grace period, with failed cleanup logged/retried. Do not clean a shared temp directory or delete active replacement files.
5. Back up DB and private media together; test restore consistency and storage persistence. A code rollback must retain videos and storage. Never roll back this table after real uploads without a reviewed data/media backup plan.

For a later authorized deployment only, after the above checks and following `DEPLOYMENT.md`, inspect migration status and run this exact reviewed path inside the established container:

```bash
docker exec --user www-data --workdir /var/www/yarub yarub-php php artisan migrate:status
docker exec --user www-data --workdir /var/www/yarub yarub-php php artisan migrate --path=database/migrations/2026_10_26_000001_create_library_videos_tables.php --force
```

No seed/import, frontend build or route/view cache changes are required by Phase 1. Follow the actual configuration/OPcache policy for new PHP config; do not invent blanket cache/restart steps. No deployment commands were run.

## Development acceptance

Use the existing guarded local harness, never default `php artisan test`/root `.env`. `tools/qudrat-local.ps1 start` starts its dedicated instance/data directory and non-live config; it refuses an occupied port. `QudratTestCase::createApplication()` restricts database access to the loopback disposable test schema and config-cache path. Video tests migrate only users, both program schemas and this feature, then wrap fixture writes in rollback transactions. No source-content import or production connection is used.

```powershell
./tools/qudrat-local.ps1 start
& "$env:USERPROFILE/.config/herd/bin/php83/php.exe" -c storage/app/qudrat-local/php.ini vendor/phpunit/phpunit/phpunit -c phpunit.video-library.xml
```

The test suite covers model validation and raw-DB FK/XOR enforcement, derived programs, restricted category deletion, eligible states and category deactivation, role/guest permissions (including soft-deleted users and stale model instances), explicit example replacement/removal/revocation, deletion nullification, deterministic ordering, preserved publication timestamp and the private disk boundary. It tests authorization foundation, not HTTP/media delivery (Phase 2) or login/player behavior (Phase 5).

Actual final results on PHP 8.3.11 / isolated MySQL 8.0.43:

| Check | Result |
| --- | --- |
| New migration via targeted test setup, plus direct down/up roundtrip guarded by testing environment, exact disposable schema and empty video tables | Passed; initial example null |
| `phpunit -c phpunit.video-library.xml` | 6 tests, 86 assertions, passed |
| `phpunit -c phpunit.qudrat.xml --filter 'AdminContentTest\|PracticeTest'` | 22 tests, 234 assertions, passed |
| `phpunit -c phpunit.arabic-skills.xml --filter 'FoundationTest\|AdminPagesTest\|StudentPagesTest'` | 17 tests, 721 assertions, passed |
| `php -c storage/app/qudrat-local/php.ini -l <each changed/new PHP file>` | All 11 files passed |
| `git diff --check` and focused diff/status review | Passed; existing deleted admin roadmap and untracked implementation contract preserved |

All PHPUnit commands used the explicit Herd PHP executable and isolated ini shown above. The initial video fixture omitted the bank's required source_metadata; it was corrected and the final suite passed. No frontend build or browser/media check was needed for this foundation-only phase. Production access, external integration calls, deployment and later phases were not performed. The dedicated local MySQL instance started for verification was stopped afterward.

Next phase: **Phase 2 — Media upload, processing, and protected delivery**. Stop here until requested.
