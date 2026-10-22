# Qudrat (القدرات)

Add verbal aptitude practice to the existing Yarub application. Students practice six question categories; admins review and edit content that publishes automatically.

**Current status: Phases 1–5 are complete locally, including verification and a proposed release/recovery plan. Every logged-in student can practice all six categories; admins manage automatically published content. Production deployment/import has not been performed. Production release still requires the prerequisites listed in Phase 5.**

This is the single Qudrat requirements and roadmap document. Content lives in `qudrat_content/` at the repository root. Follow `AGENTS.md` for project conventions and environment safety.

## Roadmap

Implement one phase at a time, only when requested. At the end of each phase, update its status here and report what changed, what was tested, any blockers and the next step. Do not mark a phase complete until its acceptance checks pass.

| Phase | Deliverable | Status |
| --- | --- | --- |
| 1. Inspect and plan | Inspect Yarub, validate JSON, choose integration approach | Complete — discovery only |
| 2. Database and import | Create Qudrat storage and safely import development content | Complete — verified on isolated native MySQL |
| 3. Admin management | Review, edit and preview automatically published content locally | Complete — verified locally |
| 4. Student practice | Practice sessions, hints, grading, resume and progress | Complete — verified locally |
| 5. Verification and release preparation | Verify the complete feature and document rollout | Complete locally — production prerequisites remain |

### Phase 1 — Inspect and plan (complete)

Completed on 2026-10-21 against checkout `799df14`:

- Inspected authentication, roles, routes, models, layouts, assets, database configuration and tests.
- Independently parsed and structurally validated all six JSON files; confirmed the inventory below.
- Chose separate Qudrat tables while reusing Yarub authentication, layouts and admin UI conventions.
- Identified the unresolved access rule and local environment requirements below.

**Result:** a verified content inventory and implementation plan. No feature was built, Laravel was not booted, and no database or production system was accessed.

### Phase 2 — Database and development import (complete)

**Build:**

- Establish an isolated development/test environment with a disposable database and non-live integrations before booting Laravel.
- Add Qudrat migrations, models, relationships, validation and authorization foundations using the table plan below.
- Add an explicit `qudrat:import` command with validation-only mode and default input `qudrat_content/`.
- Import the supplied content into the development database with automatic publication. Do not add it to automatic boot/deployment or the broad demo seeder.
- Add focused validator/import tests, including reruns, admin-edit conflicts and rollback on failure.

**Done when:** the development database contains 6 categories, 1,200 questions, 4,800 options and 40 passages; a second import creates no duplicates; invalid input causes no partial writes; admin edits survive reruns; new content publishes automatically and existing records/progress and archived states are preserved.

**Start with:** `Read docs/QUDRAT.md and implement Phase 2 only. Update this document with results and verified local commands.`

**Delivered on 2026-10-21:** seven Qudrat tables/models, user relationships, admin-content and owned-session policies, a standalone content validator, and the guarded `qudrat:import` command. No student/admin Qudrat routes or screens were added in this phase. Session content snapshots and submitted answers are protected against normal model edits; database constraints bind answers to their session owner and question, and passages to the question's category.

The development import contains **6 categories, 1,200 questions, 4,800 options and 40 passages**, now published automatically, with zero invalid reading links. The second import reported all records unchanged, with no new records. Focused tests cover source updates, stable option identity, publication/history preservation, admin-edited questions/options/categories/passages, explicit overwrites, missing-source retention, malformed input before writes, rollback, database constraints, ownership and environment guards. Content validation remains fixed to this complete six-file input bundle; incomplete bundles are rejected without deleting existing records.

**Verification:** PHP 8.3.11 and MySQL 8.0.43; Composer lockfile platform requirements passed; changed PHP files passed syntax checks; new PHP files passed Pint; the Qudrat suite passed 28 tests and 109 assertions. Exact local commands are below. The full legacy suite and asset build were not run: existing Breeze drift is unrelated and this phase adds no frontend assets.

**Resolved setup incident:** an initial positional-argument bug in the new PowerShell helper invoked the existing profile's `migrate` shortcut (`php artisan migrate:fresh --seed`) against the normal Artisan connection. The owner confirmed that connection held disposable local/demo data. Parameter binding is now explicit, the helper validates the PHP executable, and the isolated PHP entry point rejects reset/demo-seed commands. Subsequent import/test work used the dedicated databases below. No real environment file was read or edited, and no deployment was performed.

### Verified native commands for Phase 2

**Current local database (owner requested):** the normal development app and review server now use `127.0.0.1:3306`, database `yarub`, username `root`, empty password. Existing data is retained. Before applying the Qudrat-only publication migration/import, a full SQL backup was saved under ignored `storage/app/qudrat-local/backups/`; checksums of all 29 existing non-Qudrat application tables were unchanged afterward. The previously empty Qudrat tables now contain 6 published categories, 1,200 published questions, 4,800 options and 40 passages. No reset or demo seeding was run.

The review server reads `storage/app/qudrat-local/.env`; tests independently read `.env.testing` in that directory and still use the restricted account on port 33317 and `yarub_qudrat_test_local`. Review sessions are separated by database identity; sign in with an existing local admin account after switching databases. The review-fixture command remains restricted to the dedicated development database and cannot create or modify users in `yarub`.

Imports into existing `yarub` require both `QUDRAT_ALLOW_DEVELOPMENT_IMPORT=true` and `QUDRAT_EXISTING_LOCAL_DATABASE=yarub`, a loopback MySQL connection, and local/development mode. This exception is rejected in testing and production. Tests, backup files and credentials remain outside version control. Use only the named Qudrat migration on an existing application database:

```powershell
./tools/qudrat-local.ps1 artisan migrate --path=database/migrations/2026_10_21_000002_publish_qudrat_content_by_default.php
./tools/qudrat-local.ps1 artisan qudrat:import
./tools/qudrat-local.ps1 test  # separate disposable test database
```

The following dedicated-database setup documents the initial Phase 2 verification; its import connection has since been replaced by the owner-approved local database above.

The owner uses native PHP plus `npm run dev`, not Docker. `tools/qudrat-local.ps1` provides a separate native MySQL instance solely for Qudrat development/testing; it does not replace the normal serve/Vite workflow. It uses installed binaries, never downloads/upgrades dependencies, and stores its configuration/databases/cache under ignored `storage/app/qudrat-local/`.

Default binaries on this machine: PHP at `$env:USERPROFILE/.config/herd/bin/php83/php.exe`, MySQL at `C:/Program Files/MySQL/MySQL Server 8.0/bin`. Override the named `-Php` and `-MySqlBin` arguments if needed. The helper creates its own datadir and refuses an already occupied port; it does not reuse an existing MySQL server. The databases are `yarub_qudrat_dev_local` and `yarub_qudrat_test_local` on `127.0.0.1:33317`. Its restricted local account can access only these two databases.

Run from the repository root in PowerShell:

```powershell
# Start the separate local instance (do not repeat while it is already running).
./tools/qudrat-local.ps1 start

# Only the existing users prerequisite and the new Qudrat migration.
./tools/qudrat-local.ps1 artisan migrate --path=database/migrations/2014_10_12_000000_create_users_table.php --path=database/migrations/2026_10_21_000001_create_qudrat_tables.php --path=database/migrations/2026_10_21_000002_publish_qudrat_content_by_default.php

./tools/qudrat-local.ps1 artisan qudrat:import --dry-run
./tools/qudrat-local.ps1 artisan qudrat:import
./tools/qudrat-local.ps1 artisan qudrat:import  # rerun: unchanged, no duplicates

# Only after reviewing a named conflict; can repeat --overwrite-id for other named IDs.
./tools/qudrat-local.ps1 artisan qudrat:import --overwrite-id=verbal_analogies_001

./tools/qudrat-local.ps1 test
./tools/qudrat-local.ps1 stop
```

The PHP entry point is `tools/qudrat-local.php`; it loads only its own ignored configuration and caches, checks the development import guard, and permits imports, migrations and the explicit local review-fixture command. The test suite uses `phpunit.qudrat.xml` and `Tests\QudratTestCase`: it overrides environment/cache paths, verifies the dedicated test database before migrations, and rolls back each test. Do not substitute the project's default `php artisan test` while its database isolation remains unresolved.

For an independently configured isolated native environment, the standard `php artisan qudrat:import` command is also available. Mutating imports require local/development/testing mode, `QUDRAT_ALLOW_DEVELOPMENT_IMPORT=true`, and loopback MySQL with a `yarub_qudrat_dev_*` or `yarub_qudrat_test_*` database name. This opt-in is not permission to use live credentials/data. Dry-run validates files without database queries. Exit codes: 0 success, 1 invalid input/environment/runtime failure, 2 committed non-conflicting records with conflicts skipped and reported. `Conflicting` counts describe skipped records/dependencies; they are not additional imported records.

### Phase 3 — Admin management and content review (complete)

**Build:**

- Add القدرات to the existing admin sidebar and show category total/published/archive counts.
- Add a paginated question table with prompt/option search and category/archive filters, sorted by category and source order.
- Add question create/edit with automatic publication, preview and archive actions. Keep the initial category set fixed.
- Add shared-passage editing with linked-question navigation and a clear warning that one edit affects multiple questions.
- Build shared question-rendering components now so admin preview and the later student player use the same markup.
- Present imported content for owner review, including educational correctness and reading paragraph formatting.

**Done when:** ordinary admins can manage and preview content; students/guests cannot access admin pages or Livewire actions; incomplete questions cannot be saved; shared-passage previews update correctly; archived questions remain inaccessible to students. Browser file upload is not required.

**Start with:** `Read docs/QUDRAT.md and implement Phase 3 only. Update its status after verification.`

**Delivered on 2026-10-21:** the admin sidebar now links to `/admin-dashboard/qudrat`. The six-category overview shows total/published/archive counts and category settings. A PowerGrid table searches prompts and option text, filters category/status, paginates, and links to editing and preview. The question editor supports four stable options, one correct key, automatic publication on save, linked reading passages and reversible archiving. Passage management shows linked questions and warns that edits affect shared content. Question/passage revision checks reject stale saves.

Shared Blade player/options/passage/feedback components render the preview with native keyboard-accessible radios, escaped text and preserved whitespace. Reading layout is side-by-side on desktop and stacked on mobile. Admin-only preview can reveal hints and feedback without recording an attempt. A dedicated Qudrat Vite entry starts one Livewire/Alpine instance; unrelated pages keep their existing script behavior. The new screens inherit Yarub's indigo/gray palette, Arabic fonts and dashboard navigation.

**Verified:** 38 Qudrat tests, 218 assertions, including nine admin tests covering ordinary-admin management, guest/student denial, direct Livewire authorization on mount/update, required complete content and automatic publication, same-category passage links, shared previews, archive/restore, stale saves, escaping and import conflict preservation. PHP syntax and new-file formatting checks passed. The isolated Vite build passed with existing Browserslist/safelist warnings; no dependencies were upgraded. Browser checks covered real login/save, option search/empty results, keyboard radios, hints/feedback, and desktop (1440px), mobile (390px), and the normal panel width (597px). Independent UI review approved the reviewed scope after correcting mobile empty-row alignment and confirming bottom editor controls. All 1,200 supplied questions are published; an unchanged import still reports no duplicates or conflicts.

### Open the local admin review

Use the dedicated native instance from Phase 2. If it is already running on port 33317, do not run `start` again. From the repository root:

```powershell
./tools/qudrat-local.ps1 start              # only when the dedicated instance is stopped
./tools/qudrat-local.ps1 artisan migrate --path=database/migrations/2026_10_21_000002_publish_qudrat_content_by_default.php
# Use your existing local admin login. Do not run qudrat:review-setup against yarub.
./tools/qudrat-local.ps1 build
./tools/qudrat-local.ps1 serve
```

The review server uses PHP 8.3 at **http://127.0.0.1:8017**. Sign in at `/yarub-moshref-center`, then open `/admin-dashboard/qudrat`. Use an existing admin account from `yarub`; the earlier dedicated-database review credentials do not apply. The router uses its own non-live integration configuration, session files and caches while sharing your local `yarub` database. It does not load the project `.env`. Stop the review server with Ctrl+C in its terminal. `./tools/qudrat-local.ps1 stop` stops only the dedicated MySQL instance; its imported data persists.

Content workflow: create a complete question → save to publish immediately. Imports also publish new content automatically. Questions require a prompt, four choices, one correct answer, hint and explanation. Saved edits take effect immediately. Archive hides a question; restore republishes it after checking completeness. Attempted questions/options and historical snapshots are retained. Existing unarchived content is published by the additive migration `2026_10_21_000002_publish_qudrat_content_by_default.php`, verified on the isolated development database. Owner-requested automatic publication replaces the earlier draft/review gate. Phase 4 adds the student entry/player for every logged-in student; no purchase or subscription is required.

### Phase 4 — Student practice and progress (complete)

**Access decision (owner confirmed):** every logged-in student can use Qudrat. It is independent of existing course/lesson purchases and subscriptions.

**Build:**

- Add القدرات to student navigation and six category cards with descriptions, published counts, progress, first-attempt accuracy and start/resume actions.
- Add one-question-at-a-time practice with persisted selections, optional hints, server grading, feedback and explicit next action.
- Add stable session order, refresh/resume, passage grouping, results and mistake review using immutable content snapshots.
- Add ownership checks, publication checks, duplicate-submission protection and concurrent-request handling.
- Verify metrics, empty/exhausted banks, mobile/desktop layout, keyboard use and Arabic RTL.

**Done when:** all six types work; refresh retains state; reading groups stay intact; hints persist; retries do not inflate first-attempt accuracy; answers/explanations stay private before grading; concurrent submissions grade once; edits do not rewrite historical results; another student's sessions/results cannot be accessed.

**Start with:** `Read docs/QUDRAT.md and implement Phase 4 only. Update its status after verification.`

**Delivered on 2026-10-21:** `/student-dashboard/qudrat` is linked in student navigation. All six categories show descriptions, available-question counts, distinct-question progress, first-attempt accuracy, start/review actions and resume links. Ordinary authenticated students have access; guests and admins cannot practice as students. Existing payment, subscription and exam flows remain unchanged.

`Practice` owns transactionally locked starts, persisted choices/hints, snapshot grading, explicit next/completion and archive closure. `Progress` computes metrics only over the current available bank using each question's earliest answer. Student responses use an explicit allowlist: answer keys, hints, explanations and private snapshots stay off the page until the relevant hint/submission. Concurrent starts and grading serialize on the student row, and answers have a unique session-item constraint. Requests from stale tabs cannot advance a different question.

Sessions prefer up to 10 unseen questions, with stable source order and immutable snapshots. Reading keeps full passage groups in passage order, including partially seen groups and the last group in full; actual session length is shown. New practice resumes any active session in that category. Review is explicit: non-reading review prefers least-recently practised questions; reading review preserves source passage order. Unseen practice at exhaustion offers review rather than looping automatically. A session closes if any unsubmitted content becomes unavailable; submitted answers remain in its results and unanswered content stays private. Completed results retain their original content even after admin edits.

The shared Blade player uses native radios, keyboard focus, RTL text and preserved passage whitespace. Choices save automatically with a visible save/error status; a non-JavaScript save action is available. Hints persist without a scoring penalty. After **تحقق**, choices lock and feedback explains the correct answer; **التالي** advances, and the last question opens results. Results show correct/incorrect/answered counts, accuracy, hint usage and a **مراجعة الأخطاء** filter, including the original reading passage.

**Verified:** 51 Qudrat tests, 347 assertions on the separate disposable MySQL test database. Thirteen practice tests cover all six types, privacy, role/ownership checks, resume, hint/choice persistence, grading once, stale requests, reading groups, snapshots, archive closure, metrics, empty/exhausted banks and two parallel PHP processes starting/grading against MySQL. Browser verification used the existing demo student in local `yarub`: keyboard selection, autosave/refresh, hints, a completed 10-question practice, results/mistake review, updated progress and reading practice. Layout checks covered 1440px desktop, 390px mobile, 597px panel and 1270px user width. Independent UI review returned **ship**, with no material fixes; the existing broken avatar image is outside this change. The detector returned no findings. PHP formatting/syntax, diff checks and the isolated Vite build passed; existing Browserslist/safelist warnings remain.

No schema migration, reset, demo seeding or production operation was needed. Browser practice added history only for the existing local demo student; automated tests used `yarub_qudrat_test_local`. Review screenshots remain ignored local artifacts. Phase 5 verification and the proposed release/recovery plan are recorded below; the import command still rejects production.

To review: sign in as an existing local student and open `/student-dashboard/qudrat` using your normal native PHP/Vite workflow or the review server at `http://127.0.0.1:8017`. Run `./tools/qudrat-local.ps1 test` for isolated verification and `./tools/qudrat-local.ps1 build` for the verified asset build.


### Phase 5 — Verify and prepare release (complete locally)

**Complete:**

- Run focused tests against isolated data, including MySQL concurrency/constraints, permissions, answer leakage, import conflicts, snapshots and archiving during practice.
- Run appropriate PHP checks and the asset build in the isolated environment. Review actual content across all six categories on desktop/mobile and with keyboard navigation.
- Check existing student/admin journeys for regressions; distinguish unrelated stale Breeze failures from Qudrat failures.
- Record exact verified development/import commands and the automatic publication workflow in this document.
- Prepare proposed migration, content rollout and recovery steps consistent with `docs/DEPLOYMENT.md`. Preserve attempts/history. Production deployment and production seeding require a separate explicit task.

**Done when:** acceptance checks pass, the owner can review the feature locally, and release/recovery instructions are concrete. If required checks are blocked, document the blocker and leave the phase incomplete.

**Start with:** `Read docs/QUDRAT.md and complete Phase 5 only. Do not deploy or seed production.`

**Verified on 2026-10-22:** 54 Qudrat tests and 465 assertions passed on PHP 8.3.11 and isolated MySQL. The suite includes actual six-file content in every student player; answer/hint privacy; selection JSON allowlists; full reading groups; import reruns after practice without changing answers or snapshots; MySQL constraints and parallel starts/submissions; archive closure, ownership and first-attempt metrics. The new release tests exercise the existing student courses, lessons, tests, certificates and profile pages, plus admin dashboard, courses, lessons and Qudrat pages. Unsubscribed course/lesson access still redirects appropriately. These tests use their own fixtures with mail faked and stray HTTP requests blocked. Historical tables were migrated only on `yarub_qudrat_test_local`, with no broad demo seeding.

Content dry-run and the Vite build passed. The build still reports existing Browserslist/safelist warnings; dependencies were not changed. Browser verification covered real supplied content in all six categories at 1440px and 390px, native keyboard selection, autosave and refresh. Every page had four options and no horizontal overflow. Earlier Phase 4 checks cover hints, grading, next, completion/results, mistake review and the 597px/1270px layouts. The earlier independent UI review remains applicable because Phase 5 changes no application UI. Screenshots and the browser report are under ignored `storage/app/qudrat-local/review-phase5/`.

**Recovery rehearsal:** a new single-transaction SQL backup of local `127.0.0.1:3306/yarub` was restored into a newly created `yarub_qudrat_test_restore_*` database on the separate port 33317 instance. All **37 table checksums matched**, including Qudrat sessions, immutable snapshots and answers. No existing database was overwritten or dropped. The dump and `phase5-restore-report.json` remain under ignored `storage/app/qudrat-local/backups/`; the restore database is retained for inspection. This proves local backup/restore compatibility, not that a production backup location, account or restore procedure has been verified.

**Verification limits:** the default Breeze/legacy suite was not run against the normal connection; it still has stale route expectations and unsafe database defaults. The targeted regression tests above cover the actual dashboard routes instead. No live payment, mail, S3 upload/delete or production request was made. Educational correctness and source paragraph fidelity still require owner review. The only new Phase 5 code is focused verification tests; the importer still rejects production.

#### Proposed production release — separate authorization required

This is a reviewed sequence to finalize with the actual server facts in [DEPLOYMENT.md](DEPLOYMENT.md), not a script to execute blindly. Do not change `APP_ENV` to bypass the import guard, run demo seeders, reset tables, or roll back Qudrat migrations containing history.

1. **Resolve release prerequisites before pulling feature code:** confirm compatible production PHP/extensions and MySQL; inspect the deployed migration history and checkout; choose where matching Vite assets are built/published; confirm current route/view/config/OPcache handling and an approved way to suspend access while schema/assets/content are incomplete. Record a restorable pre-release database backup and the previous deploy commit/assets. Do not enable route/config caching merely for this feature: existing route-name duplication and direct `env()` integration reads need separate verification.
2. **Approve a controlled production import path as a separate code change.** `php artisan qudrat:import` currently refuses production, even with the local opt-ins. Proposed requirements: production-only opt-in, explicit expected database identity and reviewed source-bundle digest, interactive confirmation (or an explicitly authorized noninteractive flag), validation before writes, transactional stable upserts, default conflict skipping, unchanged archive/history protection, and safe repeat/recovery tests. Keep this guard change reviewed and tested before the deployment window. Its CLI flags do not exist yet; do not substitute a direct SQL fill or a temporary environment change. This is the outstanding implementation prerequisite for production content rollout.
3. **For an explicitly authorized release**, verify the exact approved commit and clean server checkout, then use the established command from `/var/www/yarub`: `sudo -u www-data git pull --ff-only origin main`. Stop on divergence. Install only locked dependencies if required and publish assets matching that commit using the confirmed process. `public/build` is ignored, so a pull alone does not supply the new Qudrat JS/CSS entries.
4. **Apply only missing Qudrat migrations after backup and runtime checks.** Candidate commands for the approved runtime/user are `php artisan migrate --path=database/migrations/2026_10_21_000001_create_qudrat_tables.php --force`, then `php artisan migrate --path=database/migrations/2026_10_21_000002_publish_qudrat_content_by_default.php --force`. Check migration history and actual tables first; stop if existing schema and history disagree. The first creates seven tables; the second changes publication defaults and publishes existing unarchived Qudrat content. MySQL DDL is not an all-or-nothing recovery mechanism. Do not edit applied migrations or run an unrelated migration batch as a shortcut.
5. **Validate and import the approved content through the future approved production path.** Validation-only command: `php artisan qudrat:import --dry-run` (no DB queries). Mutating import remains blocked until step 2 is implemented. Expect 6 categories, 1,200 source questions, 4,800 options and 40 passages on an empty Qudrat bank; new content is immediately published. Rerun should report unchanged records, with no duplicates. Preserve additional admin-created content and history. Conflicts require review of named identities; never blanket-overwrite them to make totals match.
6. **Refresh only confirmed caches/processes, then perform planned smoke checks** with approved admin/student accounts: admin list/edit/preview; student entry, start/resume, hint, answer/next/results; a reading group; mobile assets; guest/other-student denial; existing course/lesson/exam access. Grading smoke tests intentionally append practice history. Confirm logs, matching asset manifest, source inventory and import report. Do not make a live purchase or send mail to verify unrelated integrations.

#### Recovery decisions

- **Before successful import:** stop on validation/conflict/schema/runtime failure. Import validation runs before writes and unexpected importer exceptions roll back its data transaction; a migration may already have created tables or changed defaults. Keep those additive tables and investigate. Never use `migrate:fresh`, `refresh`, table drops or migration rollback to recover an import.
- **Application/asset failure:** suspend Qudrat access through the approved release mechanism, keep the database and practice history, and deploy the agreed known-good code plus matching assets using the established workflow. No forced Git reset or guessed service/cache sequence is prescribed. A normal forward Git revert can be prepared and reviewed separately.
- **Incorrect content:** use the admin editor or archive affected questions; review named importer conflicts individually. Existing snapshots/answers preserve what students saw. Do not delete attempts, change historical answer keys or republish archived content through a maintenance rerun.
- **Database damage:** stop further writes and preserve a fresh incident snapshot. Restore the pre-release backup into a **new recovery database first**, verify schema, counts and Qudrat snapshot/answer relationships, then reconcile any purchases, subscriptions, messages and practice records written since that backup before an explicitly approved cutover. Restoring an old whole-database backup over a live database would lose unrelated newer data and is not an automatic rollback step.

**Phase 5 result:** local acceptance and release/recovery preparation are complete. Production is intentionally not marked deployment-ready until step 1's server facts and step 2's guarded importer are resolved, reviewed and explicitly authorized. Neither deployment nor production content loading is part of this phase.


## Content: what is ready

All files below are inside `qudrat_content/`. No Word parsing or question generation is needed.

| File | Category | Questions | Passages |
| --- | --- | ---: | ---: |
| `01_verbal_analogies.json` | التناظر اللفظي | 200 | 0 |
| `02_sentence_completion.json` | إكمال الجمل | 200 | 0 |
| `03_contextual_errors.json` | الخطأ السياقي | 200 | 0 |
| `04_odd_word_out.json` | المفردة الشاذة | 200 | 0 |
| `05_reading_comprehension.json` | استيعاب المقروء | 200 | 40 |
| `06_association_and_difference.json` | الارتباط والاختلاف | 200 | 0 |

**Verified totals:** 1,200 questions, 4,800 options, 1,200 explanations, 1,200 hints and 40 passages. All files parse as UTF-8 JSON. IDs are unique, category order runs from 1–200, every question has four nonempty distinct choices and a valid answer reference, and every reading link resolves. No unused passages or duplicate question content was found when comparing category, passage, prompt and choices. Repeated generic prompts alone are not duplicates.

Reading groups range from 1–8 questions, not a fixed five. Distribution: 10 passages with 1 question, 2 with 2, 3 with 3, 1 each with 4/5/6, 14 with 7, and 8 with 8.

**Review note:** the current passage strings contain no paragraph breaks. The earlier extraction notes claimed paragraph preservation; compare the Word sources during owner review. Preserve supplied text until reviewed. Structural validation does not independently verify educational correctness or source fidelity; never silently rewrite wording or answer keys.

### JSON contract

- Each file: `category` (stable English slug), `title` (Arabic), `questions`; reading also has `passages`.
- Each question: `id`, `order`, `question`, four `options` with `id`/`text`, `correct_option_id`, `explanation`, `hint`; reading questions also have `passage_id`.
- Each passage: stable `id` and `text`. Its order follows the source array. Preserve paragraph breaks if supplied in future content.
- Question/passage IDs are external import identities. Option IDs `a,b,c,d` belong to one question; display them as أ، ب، ج، د.
- Preserve punctuation, diacritics, blanks, analogy-pair order and full instructions. Multiword completion choices stay one option.

## How this fits Yarub

Verified stack: Laravel 10.48.22, Livewire 3.5.2, PowerGrid 5.9.0, Vite 5.4.10, Tailwind 3.4.10 and Alpine 3.14.1. Keep existing dependencies and architecture; Yarub is separate from ARRIB.

- Reuse the session `web` guard and dashboard middleware: `auth`, `verified`, `student`/`admin`, `log.visit`. `User` does not implement `MustVerifyEmail`, so `verified` alone does not prove email verification is enforced.
- Add routes in `routes/student.php` and `routes/admin.php`. Add ownership policies for Qudrat; check them on every nested session action and admin Livewire action.
- Extend `resources/views/layouts/student.blade.php` and `admin.blade.php`; reuse navigation buttons, Arabic fonts, primary colors, form components, SweetAlert and installed PowerGrid patterns. Do not initialize another Alpine instance.
- Use explicit eager loading: `AppServiceProvider` blocks lazy loading outside production.
- Keep existing `Test`, `Question` and `TestAttempt` unchanged. They are tied to courses/lessons, whole-test submissions and one-attempt behavior; they lack Qudrat's passages, hints, resumable sessions and historical snapshots.
- MySQL is the configured default; existing dashboard SQL uses MySQL functions. Actual server/version/schema remain unverified.

### Implemented tables

Use new `App\Models\Qudrat*` models and additive migrations. Foreign keys, indexes and uniqueness must be checked against the actual isolated MySQL version.

| Table | Purpose and key constraints |
| --- | --- |
| `qudrat_categories` | Unique slug, Arabic title, sort order, publication state; fixed initial six |
| `qudrat_passages` | Unique external key, category, text, order, import baseline |
| `qudrat_questions` | Unique external key, category, optional passage, prompt/hint/explanation, order, correct option key, publication/archive state, import baseline |
| `qudrat_options` | Question, stable local key, text, order; unique question/key |
| `qudrat_practice_sessions` | User/category, practice or review, status, current position, start/completion/closure timestamps |
| `qudrat_session_questions` | Session/question, position, draft selection, hint state, private immutable content snapshot; unique session/question and session/position |
| `qudrat_session_answers` | One immutable submission per session item, user/question, chosen key, correctness, hint usage, submission time; index user/question for first-attempt queries |

Store one canonical correct key (`a`–`d`) per question and atomically validate exactly four keyed options. Same-category passages are required for reading and unavailable for other types. Every question save requires nonempty prompt, choices, hint and explanation. Category and question must both be published, and the question unarchived, for student availability. Restrict destructive deletion of referenced content/history; use archive. Keep prose in text columns and snapshots in private JSON.

### Code and routes

Keep responsibilities small: controllers under `app/Http/Controllers/{Admin,Student}/Qudrat/`; validation/import/practice/progress services under `app/Services/Qudrat/`; Qudrat policies registered in `AuthServiceProvider`; `app/Livewire/QudratQuestionsTable.php`; separate admin/student response allowlists; and `app/Console/Commands/ImportQudratContent.php`.

Views belong in `resources/views/{admin,student}/qudrat/`. Shared components belong in `resources/views/components/qudrat/`: category card, player, prompt, choices, passage, progress, hint and feedback. Use the same player markup for admin preview and student practice.

| Route group | Planned endpoints |
| --- | --- |
| `/admin-dashboard/qudrat`, names `admin.qudrat.*` | GET overview; PATCH category metadata; GET question list/create/edit/preview; POST question create; PUT question update; PATCH question archive/restore; GET passage list/edit; PUT passage update |
| `/student-dashboard/qudrat`, names `student.qudrat.*` | GET category cards; POST category session start; GET session/resume; PATCH item selection; POST item hint/answer; POST session next; GET results/review |

Use normal web CSRF/session protection, scoped bindings and ownership checks. Starting sessions and changing state use write methods; GET requests must not generate new practice orders.

## Rules each implementation phase must preserve

### Safe, repeatable imports

Validate every input file before writes. Use stable IDs for transactional upserts and report created, updated, unchanged, skipped and conflicting counts. Never delete missing-source records, replace option identities, reset progress or reactivate archived content on rerun. New imported content publishes automatically.

Track the last imported checksum/version for category metadata, passages and question-plus-options content. Compare current records with that baseline to detect admin edits, including when the incoming file is unchanged. Skip/report conflicts by default; allow only deliberate overwrite of named IDs. Report dependent passage conflicts, recheck under locks, and roll back unexpected failures. Publication/archive state and historical snapshots are outside source updates.

Implemented command syntax (run through the isolated helper above until a separate environment is verified):

```text
php artisan qudrat:import --path=qudrat_content --dry-run
php artisan qudrat:import --path=qudrat_content
php artisan qudrat:import --path=qudrat_content --overwrite-id=verbal_analogies_001
```

Keep validation-only mode free of writes and content mutations restricted to verified isolated development/testing environments. Do not connect to an unspecified database merely to validate JSON.

### Student behavior and history

1. Prefer unseen questions in sessions of up to 10. Resume unfinished practice; new/review practice is explicit. At exhaustion offer review instead of an endless retry loop.
2. For reading, select consecutive whole passage groups in source order until roughly 10 questions, including the last group in full. Show actual length. Partial-seen rule: include the entire published group and treat earlier attempted questions as repeats.
3. Save selections as drafts, not attempts. Provide **تلميح** only when requested and record use server-side. Enable **تحقق** after a choice; grade on the server, lock the response, then show correct/incorrect feedback, the correct choice and explanation. Advance only with **التالي**.
4. Persist order, draft choices, hints and submissions across refresh/resume. Pin private snapshots of prompts, options, correct keys, feedback and passages when creating the session. Admin edits must not rewrite current exercises or historical results.
5. Authorize ownership and current item/state on every request. Validate choice membership. Use consistent row locks and unique answer-per-item constraints so concurrent starts/submissions cannot duplicate sessions or grading. Serialize first submissions per user and identify the earliest submitted answer deterministically.
6. Recheck publication on every student read/action. Safely close affected active sessions on archive, notify the student, preserve submitted history and hide newly restricted unsubmitted content. Completed owned results may retain their historical snapshots.
7. Results show score, correct/incorrect counts, hint usage, **مراجعة الأخطاء**, **تدريب جديد** and return navigation. Wrong-answer review includes the original reading passage. Unanswered questions are not silently submitted or marked wrong.

Progress = distinct submitted questions in the current published bank / published questions. First-attempt accuracy = correct first submissions / questions with first submissions in that bank. Retries never rewrite first attempts. Session scores use only that session's submitted answers; hints are marked but carry no penalty. Handle zero counts and empty categories explicitly.

### Admin, presentation and security

- Support create/edit with automatic publication, preview and archive/restore, four choices and exactly one correct answer. Never delete attempted questions or passages with dependent questions. Ordinary admins manage content; do not add super-admin restrictions without a product reason.
- Use shared player controls across categories. Analogies retain pair direction; completions retain blanks; contextual errors use supplied word choices without mandatory inline word clicking; odd-word prompts remain compact; association/difference uses the standard layout.
- Reading passages appear beside the question on desktop and above on mobile, with preserved whitespace and continued access between linked questions. Avoid fixed heights that clip text.
- Use keyboard-accessible native radios, visible focus, readable Arabic type, RTL layout, responsive controls, and feedback text/icons alongside color.
- Keep source JSON, correct keys/flags, explanations and snapshots out of student HTML, hidden elements, Alpine/Livewire state and bundles before submission. Hints are returned only on request. Use separate admin/student allowlists and escaped text rendering.
- Do not alter payments, subscriptions, existing exams or other unrelated behavior. V1 excludes timed/mixed exams, leaderboards, AI generation, media, arbitrary categories/question formats and a general exam builder.

## Decisions and environment requirements

| Item | Resolve by |
| --- | --- |
| Who can use Qudrat: every logged-in student, independent of purchases/subscriptions. | Resolved by owner for Phase 4 |
| Local runtime: owner confirmed native PHP/Vite, no Docker. Dedicated native MySQL, isolated browser review, and minimum local login/dashboard fixtures are verified above. | Resolved for Phases 2–3 |
| PHP/MySQL compatibility: default PHP remains 8.4.5, which locked OpenSpout excludes. The helper uses verified PHP 8.3.11 with required extensions; Composer platform checks pass. Dedicated MySQL is 8.0.43. | Phase 2 resolved; keep using the compatible executable |
| Test isolation: default `phpunit.xml` still does not isolate the database. Use the dedicated Qudrat configuration/test base and helper; do not run the legacy feature suite against an unspecified connection. | Qudrat isolation implemented; default suite unchanged |
| Source review: educational correctness and passage paragraph structure need owner review. | Ongoing editorial review; publication is automatic |

Use `.env.example` as the starting point; do not reuse real credentials. Existing demo seeding is broad, and even page views may write visitor records. Establish minimum local fixtures deliberately. Add plain validator unit tests and focused import/admin/ownership/practice/grading/progress/history feature tests as each phase is built; test concurrency and constraints on isolated MySQL. Keep exact verified commands and phase results in this document as work progresses.
