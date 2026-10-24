# Arabic Skills (مهارات اللغة العربية)

Build a new authenticated student section in the existing Yarub application named **مهارات اللغة العربية**, with an admin area for managing its content and reviewing student activity. Follow the current successful **القدرات / Qudurat** experience for navigation, practice sessions, feedback, results, and visual design. Use **Impeccable** throughout implementation. The existing Qudurat design is the visual reference and should remain stable.

This is the implementation specification and roadmap. **All five phases are complete locally (2026-10-23): storage/import, content loading into the existing development database, the session engine, the student interface, the admin interface, and final verification/polish. Production deployment/import remains a separate release action.** The JSON is the initial import content package. First inspect the actual repository and current Qudurat implementation; follow its Laravel conventions, frontend stack, authentication, roles, route organization, and component system. Do not introduce a new framework or assume a separate application is needed.

## 1 Content package and scope

This guide lives at `docs/ARABIC_SKILLS.md`. Keep the supplied JSON at `arabic_skills_content/`, the importer default. No copying is required. Paths below are relative to the repository root:

```text
docs/ARABIC_SKILLS.md
arabic_skills_content/
  01_nasaq.json
  02_rasm.json
  03_bayan.json
  04_midad.json
  05_burhan.json
  06_section_catalog.json
```

Only five source documents were supplied. There are **five question banks**, not six. The sixth JSON is a catalog that defines the section, lists its five banks, and records their counts and session defaults. It contains no additional questions. Do not render it as a sixth card, create a placeholder category, or seed its metadata as questions. A future sixth bank requires its own supplied content and a catalog entry.

| Bank ID | Display title | Subject | Questions | Original models |
| --- | --- | --- | --- | --- |
| `nasaq` | نَسَق | النحو وبناء الجملة | 300 | 10 × 30 |
| `rasm` | رَسْم | الإملاء والتحرير الكتابي | 300 | 10 × 30 |
| `bayan` | بَيان | البلاغة وتذوق النصوص | 300 | 10 × 30 |
| `midad` | مِداد | الكتابة الأدبية | 300 | 10 × 30 |
| `burhan` | بُرهان | الكتابة العلمية | 300 | 10 × 30 |

The initial package contains 1,500 main questions, 50 original models, and 3,000 possible points. These are source-package counts, not application limits. Admins may add unlimited valid questions to the five banks; do not cap banks at 300 or the section at 1,500. Each bank contains 120 multiple-choice questions, 90 true/false questions, and 90 matching groups. Each matching group is one main question with two independently scored links. Across all banks there are 450 matching groups containing 900 links; do not count those links as 900 main questions.

Keep the section separate from Qudurat in routes, content, sessions, results, and progress. Reuse appropriate presentation components and well-tested services where their behavior fits. Existing Qudurat records, its six categories, and its grading must not be changed to accommodate this content.

The source files also mention standalone HTML practice files. Those HTML files were not provided and are not dependencies. Implement the experience directly in Yarub and persist progress in Yarub's database. Do not reproduce paper-only name/date fields or the source's statement that results disappear on refresh.

## 2 Content contract

All JSON files use UTF-8 and `schema_version: "1.0.0"`. Read the real files before designing the importer. Preserve Arabic spelling, diacritics, punctuation, quotes, numerical values, line breaks, and deliberately incorrect answer choices. Especially in spelling and grammar questions, changing a letter or vowel can change the answer. Do not apply automatic Arabic normalization, spell correction, translation, or content rewriting.

Each bank file contains:

- `bank`: stable ID, title, subtitle, display order, expected counts, source filename and SHA-256, author, edition, and hint policy.
- `models`: ten source models with stable IDs, source order, exact question IDs, question counts, and maximum scores.
- `questions`: a flat list of 300 question records.

Each question contains `id`, source `number`, `model_number`, `context_group_number`, `skill`, `type`, `prompt`, `options`, `matching_items`, `answer`, `explanation`, `hint`, `max_score`, and `scoring`.

Question IDs such as `nasaq_001` are immutable import keys, not transient database IDs. `context_group_number` identifies the consecutive ten-question source context group within its bank, numbered 1–30; it is not a difficulty level. Context needed to answer a question is already included in its prompt. Preserve it in random practice so students do not depend on a previous question.

### Multiple choice

`type` is `multiple_choice`. There are three source options, each with a stable `id` (`a`, `b`, `c`), Arabic `label`, and exact `text`. `answer.option_id` identifies the correct option. `matching_items` is empty. `scoring.mode` is `all_or_nothing`.

Do not force a fourth option to match a Qudurat component. Do not derive the answer from its visual index or the Arabic label. Keep the source option order in V1.

### True or false

`type` is `true_false`. Options use stable string IDs `true` and `false`, with Arabic text **صح** and **خطأ**. `answer.option_id` is one of those strings; it is not a JSON boolean. `matching_items` is empty and grading is all or nothing.

### Matching

`type` is `matching`. `matching_items` contains two left-side items with IDs `item_1` and `item_2`; `options` contains two right-side choices with IDs `a` and `b`. `answer.matches` contains two objects with `item_id` and `option_id`. For example:

```json
{
  "matches": [
    { "item_id": "item_1", "option_id": "b" },
    { "item_id": "item_2", "option_id": "a" }
  ]
}
```

`scoring.mode` is `per_match` and `points_per_correct_match` is 1. The maximum for the whole question remains 2 points. Use stable IDs in both the student response and grading. A complete submission must contain exactly one valid mapping for each item. Each item is an independent selection: allow students to select the same choice for both items and grade each link separately. This preserves the source’s partial-credit rule: selecting one choice twice can earn 1/2. Do not force a bijection in the student interface or automatically fill the second answer. Reject duplicate item IDs, unknown IDs, or malformed payloads. Rejecting incomplete or malformed submissions should leave the question available to finish rather than recording a zero.

### Explanations and hints

Every question has an explanation extracted from the source answer key. The sources do not provide separate pre-answer hints, so every `hint` is `null`. Hide the hint action when no hint exists. Do not label an explanation as a hint or expose it before the answer is submitted. Admins may later add a genuine hint through the dedicated field.

### Content fidelity

The extraction checked all 1,500 question numbers, their answer keys, and matching mappings. Correct answer text was compared with the referenced option text. This validates the extraction against the supplied keys; it does not independently certify the educational correctness of the source material. Preserve the supplied content, allow admin review in development, and flag suspected source errors instead of silently changing them.

## 3 Training behavior and grading

Provide two modes within each bank:

| Mode | Selection | Main questions | Maximum points |
| --- | --- | --- | --- |
| تدريب سريع | Random active questions from the selected bank | 10 | 20 |
| نموذج تدريبي | The selected original model in source order | 30 | 60 |

The primary entry point is ten-question practice, matching Qudurat's short sessions. Access to original models is secondary and should not crowd the section landing page. Preserve source model membership even when individual questions are used in random practice.

For quick practice, select ten unique active questions and persist that exact selection and order when the session starts. Prefer unseen questions, then questions whose latest submitted result for this student was incorrect or partially correct, then other questions. Randomize within each tier and fill from the available bank without duplicates. Use the latest committed submission across both modes, ordered by submitted timestamp with a stable record-ID tie-breaker. Viewing a question or leaving it unsubmitted does not make it attempted. A later fully correct result supersedes an earlier wrong or partial result for selection priority. A new bank should not require arbitrary prerequisites or mastery gates. If fewer than ten active questions remain, show an explicit unavailable state for starting practice instead of silently changing the promised session length. Model mode requires all 30 model questions to be active; explain unavailability to admins and students if content is incomplete.

Use a single question at a time, with explicit submit and next actions. After a valid submission, show the answer and explanation, record the result once, and allow moving forward. For a matching question, submit and reveal both links together so one revealed link cannot guide the other before grading. Selecting options alone must not submit automatically. Refreshing or navigating away must resume the same session, preserving submitted answers and order.

Scoring follows the source exactly:

- Multiple choice and true/false: 2 points for a correct answer, 0 for an incorrect answer.
- Matching: 1 point per correct link, giving 0, 1, or 2 points.
- No negative marking.
- Percentage = total earned points ÷ total available points × 100. Round only for display.
- A question is fully correct only at 2/2. Track partial matching results separately from fully correct answers. Do not display 1/2 as a fully correct question.

Students submit complete responses to advance in V1. If the existing flow supports explicit skipping, a confirmed skip earns zero and must be clearly labeled; merely loading or leaving a question must not count as a wrong answer. Incomplete or abandoned sessions must not be presented as completed attempts. Do not permit resubmitting an answered question for extra points; retrying creates a new session.

A results page shows points and percentage, fully correct/partially correct/incorrect counts, and submitted answers with explanations. Provide clear actions to practice again, return to the bank, or return to the section. Show progress per bank, including unique attempted active questions out of the current active question count (initially 300) and completed sessions; repeat attempts do not increase unique coverage. Compute counts from active content where appropriate rather than hardcoding 300 into components.

## 4 Database and application architecture

Inspect the existing Qudurat schema and implementation first. Use dedicated tables for this section, with foreign keys to Yarub's existing users table. The following is the intended logical structure; adapt names and conventions to the repository while preserving these responsibilities.

| Table | Responsibility and important fields |
| --- | --- |
| `arabic_skill_banks` | Unique stable `slug`, title, subtitle, display order, active status, source metadata and import hash |
| `arabic_skill_models` | Bank ID, stable source ID, model number/title, source order; unique bank and model number |
| `arabic_skill_questions` | Unique `source_id`, bank ID, nullable model ID, nullable source number/context group for admin-added questions, skill, type, prompt, options JSON, matching items JSON, private answer JSON, explanation, nullable hint, scoring JSON, max score, active status, content revision |
| `arabic_skill_sessions` | User, bank, optional model, mode, status, started/completed timestamps, total available and earned points, selected question count |
| `arabic_skill_session_questions` | Session, question reference, fixed position, private immutable content/grading snapshot, submitted response JSON, earned points, result state, submitted timestamp; unique session/position and session/question |

Structured JSON columns fit the three compact question types; do not create unrelated polymorphic systems or separate tables for every option unless the existing architecture makes that demonstrably simpler. Keep bank, model, question, and session identities relational and indexed. Admin-added questions use stable generated IDs in a separate namespace such as `admin_<uuid>`, with no fabricated source number/context group. Admin-added questions stay outside the fixed original models (`model_id = null`) and join quick practice immediately. Keep the 30 source memberships of each original model fixed: adding a question to a bank must not append it to a source model. Moving/replacing original model members is outside V1; admins can edit their content or archive/restore them. Validate structured payloads by type in dedicated request/service code.

Keep answer keys separate from public DTOs/resources even if stored in JSON columns. Never return the question model or the entire private snapshot through an unrestricted serializer. The session snapshot must preserve exact prompt, options, mappings, explanation, hint, maximum score, and grading rules so later admin edits or imports cannot change an attempt already started. Return a deliberately constructed public projection for unanswered questions. Reveal grading details only for that question after its answer has been committed.

Use server-side session selection and grading. Enforce ownership on every session, answer, resume, and result route. Clients must not choose correct answers, scores, user IDs, or arbitrary questions for a session. Use transactions and row locks or equivalent concurrency controls so repeated requests, double clicks, concurrent tabs, and network retries cannot grade twice or inflate totals. The session total must agree with the sum of its question scores. Reject new answers after completion or abandonment. Keep one active session per user/bank/mode/model combination or provide an explicit resume/abandon flow before starting another.

Every valid new question is published and active immediately, whether imported or admin-created, matching Qudrat. There is no draft state, approval queue, save-as-draft, or separate publish action. Reject incomplete saves instead of persisting unpublished records. Existing questions support explicit archive/restore; banks support active/inactive controls. Editing or reimporting must not silently restore archived content. Restoration validates completeness. Use soft deactivation for banks/questions that have attempts. Existing snapshots and history must remain readable when content is edited or disabled. Deactivation prevents new selection, but students can resume and finish already-started sessions from their snapshots even if their bank or questions are subsequently disabled. Do not cascade-delete student history when reimporting. For this content size, derive progress from session records with appropriate indexes; add a cached progress table only if the repository already uses that pattern or query performance requires it.

## 5 Import and seed requirements

Implement a repeatable `arabic-skills:import` Artisan command following the existing `qudrat:import` workflow in `app/Console/Commands/ImportQudratContent.php` and `docs/QUDRAT.md`. It must read the catalog's explicit list of bank files; do not blindly import every JSON file as a bank.

The owner explicitly requests the **same existing local development database used by the normal app and Qudrat**, documented in `docs/QUDRAT.md` as loopback MySQL database `yarub`, so results are visible in the current development app. Do not substitute an isolated review database. Before implementation writes, verify the resolved local target, retain non-live integrations, take a recoverable backup using the existing local workflow, and apply only the new feature migrations/import. Preserve existing application/Qudrat data; never reset tables, run `migrate:fresh`, or invoke broad demo seeders. Do not read credential backups or print secrets.

Automated feature/concurrency tests must use a separate guarded disposable database, never the owner's review database. Adapt the existing local helper only as needed without changing Qudrat behavior.

Provide and locally test a guarded production importer matching Qudrat so the exact reviewed JSON can later be applied to production. Executing a production import or deploying still requires a separate release instruction.

The importer must:

1. Validate schema version, catalog/bank IDs, file references, counts, and required fields before changing data.
2. Validate unique source question IDs/numbers, ten models per bank, exactly 30 question references per model, correct bank/model membership, and context group boundaries. Each source-package question must belong to exactly one original model. These strict package checks do not cap database counts or require admin-added questions to belong to models.
3. Validate type-specific options, answer references, matching mappings, nonempty explanations, and maximum scores. Ensure each bank has 120/90/90 questions by type and 600 available points for this supplied package.
4. Read the JSON files, not the DOCX files. Keep the DOCX extraction process outside the application.
5. Upsert by stable bank/model/question keys in a transaction after validation. Use manifest hashes and content revisions to detect meaningful changes. An unchanged second import must create no duplicates or revision churn.
6. Preserve sessions, responses, question snapshots, and existing progress. Do not silently delete records absent from a later import. Report omissions and require an explicit separate deactivation operation if needed.
7. Treat JSON as the initial seed and later deliberate synchronization source. Do not run an unconditional import on every deploy or overwrite admin edits silently; report conflicts with admin-edited revisions and support an explicit reviewed update path.
8. Report created/updated/unchanged/conflicting records and final counts per bank, model, and type. Abort cleanly on validation errors without a partially imported package.

### Local and production command contract

The command is implemented. Standard Artisan examples below assume an explicitly configured, non-live local environment; use the verified local wrapper commands in the Phase 1 report on this workstation:

```powershell
# Validate the package and print its SHA-256 without a database connection.
php artisan arabic-skills:import --dry-run

# Use the explicitly opted-in existing local development database.
php artisan arabic-skills:import

# Later release only: exact target name and reviewed dry-run digest.
php artisan arabic-skills:import --production --database=<exact-database-name> --sha256=<reviewed-digest>
```

Support `--path` (default `arabic_skills_content`), `--dry-run`, development-only repeatable `--overwrite-id`, and Qudrat-equivalent `--production`, `--database`, `--sha256`, and `--force` options. Dry-run validates all files and prints a reproducible digest of the actual validated JSON content without opening a database connection. The embedded DOCX `source_sha256` is provenance, not the JSON package digest.

Use section-specific configuration equivalent to Qudrat: `ARABIC_SKILLS_ALLOW_DEVELOPMENT_IMPORT=true` and `ARABIC_SKILLS_EXISTING_LOCAL_DATABASE=yarub` opt in the existing loopback database only in local/development mode. Tests use a separately guarded disposable database. Production import requires console execution, production mode, `ARABIC_SKILLS_ALLOW_PRODUCTION_IMPORT=true`, the exact configured/connected database name, and a matching reviewed digest. Show the resolved target and require the same interactive confirmation as Qudrat unless the operator explicitly supplies `--force`; noninteractive execution without it fails. Reject incompatible flags and ambiguous connection overrides before mutation, following Qudrat.

Preserve admin edits, archive states, admin-added questions, attempts, and snapshots in both environments. Production must reject `--overwrite-id`; skip/report conflicts and dependent records without overwriting them. Match Qudrat exit codes: 0 success, 1 validation/environment/runtime failure, 2 non-conflicting records committed with conflicts skipped/reported. Validation failure writes nothing; runtime failure rolls back the import transaction. Unchanged reimports must not republish archived content. After implementation, record verified local commands and future production steps here, following `docs/DEPLOYMENT.md` and the Qudrat release workflow.

Do not expose server-side seed JSONs containing answer keys under a public assets directory or ship them to the browser. Keep `arabic_skills_content/` as private application input.

## 6 Student pages and visual design

Add the Arabic section label **مهارات اللغة العربية** to the authenticated student navigation alongside Qudurat. Suggested route family: `/arabic-skills`, `/arabic-skills/{bank}`, and `/arabic-skills/sessions/{session}`. Follow the real project's route naming and access conventions; public guests should use its existing login flow.

### Required design workflow

Use the project's installed **Impeccable** skills. Read the applicable local skill instructions before changing the interface. Inspect the current Qudurat landing, session, results, and admin screens and identify reusable components, tokens, layout patterns, spacing, typography, icons, button styles, borders, progress treatment, and responsive behavior. Apply Impeccable's relevant design, critique, and polish guidance. If the skill is unavailable, state that limitation and follow the existing Qudurat system without claiming Impeccable was used.

The desired result is a sibling section within Yarub. The current Qudurat style is already good: carry that style forward consistently. Extend its components where needed for true/false and matching while preserving Qudurat's appearance and behavior. Avoid introducing a new visual identity, decorative hero, oversized buttons, dense instructional headers, or generic dashboard clutter.

### Section landing page

Start with a compact title and only necessary supporting text, then immediately show five well-designed bank cards. Each card displays its name, subject, an appropriate icon from the existing icon set, total active question count including admin-added questions, and useful student progress. Make the relationship between the current active bank count and the ten-question practice length visually obvious using concise badges or the successful Qudurat treatment (initially **300 سؤال في البنك → 10 أسئلة في التدريب**). Render the bank count dynamically as admins add or archive questions. Count matching groups as questions, not individual links.

Use a primary **ابدأ التدريب** or **متابعة التدريب** action. Offer original models through a quiet secondary action or the bank page. Keep the card hierarchy and information density consistent with Qudurat. Do not add a sixth empty card.

### Bank page

Show the selected bank and subject, a prominent start/resume action for quick practice, a compact progress summary, and access to its ten original models. Model entries show model number, 30 questions, the latest completed result if available, and start/resume action. Keep model selection visually secondary to practice. Preserve navigation back to the section.

### Session page

Reuse the Qudurat session shell: clear progress, readable question content, purposeful spacing, and accessible answer controls. Multiple choice has three choices. True/false has two clear choices. Matching has two source items and two choices; use click/tap or native accessible selection controls that work without dragging. Show selected pairings clearly and let students change them before submission. Include keyboard and screen-reader support and do not rely on color alone.

Show feedback **beside or immediately below the active question/answer area**, with the next action nearby. The student must not repeatedly scroll to the bottom to discover whether an answer was correct. Use the available desktop space and an intentional mobile layout. A modest one-time scroll to bring new feedback into view may be needed on small screens; do not introduce repeated long page jumps or force the question and feedback into separate distant sections. Use an internal scroll region for unusually long explanations only if it remains accessible and does not hide essential controls.

For matching, show each submitted link and its correct mapping after submission. Explain partial credit clearly. For incorrect answers, preserve what the student selected and identify the correct answer. If a genuine admin-authored hint exists, present it as a separate pre-answer aid with a visibly different role from answer feedback. Explanations and correct mappings stay hidden until submission.

RTL must work across text, cards, selection controls, progress, focus order, and mobile layouts. Ensure Arabic diacritics are not clipped by tight line height. Preserve punctuation and numbers in Arabic prompts. Test actual long questions and choices from these files, not only short placeholders. Do not invent images, audio, writing tasks, timers, subscriptions, or additional learning content for V1.

### Results and history

Follow Qudurat's result layout and visual language. Show score and percentage, question outcomes including partial matching credit, and a readable review with explanations. Provide a restrained history list for the current bank with mode/model, completion date, score, and a review action. In-progress sessions have a clear resume state rather than appearing as completed results.

## 7 Admin experience

Add a dedicated **مهارات اللغة العربية** navigation group in the existing admin panel. Apply the same admin authorization policies as other managed educational content.

- Overview: concise counts for active banks/questions, completed sessions, and performance; keep detailed analytics out of V1.
- Banks: title, subject, order, active status, content totals, and edit action. Show the five seeded banks.
- Models: list the ten original models per bank and their question membership/counts.
- Questions: paginated, searchable list filtered by bank, model, type, skill, and active status. Show source ID/number, a short prompt preview, type, and edit/preview actions.
- Question editor: type-appropriate fields for prompt, options, correct answer or mapping, explanation, and optional hint. Creation publishes automatically; existing records have explicit archive/restore controls. Use normal form controls rather than requiring admins to edit raw JSON. Allow unlimited valid additions using stable generated IDs without disturbing source IDs. New questions publish immediately and join quick practice, without requiring original model membership. Admin additions stay outside the fixed source models. Use archive/restore for existing questions with no draft or manual publication controls. Keep source memberships explicit and validated; a model with fewer than its 30 active source questions is unavailable for new model sessions.
- Student activity: filter sessions by student, bank, model/mode, date, and status. Review the stored responses and scores using the attempt's snapshot, including partial matching points.
- Import feedback: make validation failures and conflicts understandable; reuse the existing import reporting pattern if present. A new upload/import dashboard is not required when an Artisan command and report suffice.

Changing a question type or answer must validate the entire payload and create a content revision. Do not mutate completed answers or in-progress session snapshots. Do not provide destructive bulk deletion of attempted content.

## 8 Implementation roadmap

### Phase 1 Repository review and data foundation

Inspect Qudurat's actual schema, routes, services, components, roles, and tests. Read the provided JSON package and relevant Impeccable instructions. Implement dedicated migrations, model relationships, validation, and idempotent importing. Import into the owner's existing local `yarub` database without resetting data and produce the per-bank count report. Implement and locally test the guarded production importer matching Qudrat. Confirm all 1,500 questions, all answer mappings, and all 50 model memberships are available for review. Do not start by redesigning Qudurat.

### Phase 1 delivery and verification — 2026-10-23

**Status: complete locally.** Production was not accessed. The implementation adds five dedicated tables, relational models and user linkage, admin-content/owned-session policy foundations, full-package and reusable per-question validation, revision tracking, private answer/snapshot serialization defaults, immutable attempt content/committed answers, and the explicit `arabic-skills:import` command. Creation activates valid questions immediately; admin additions receive generated IDs and nullable source/model fields, with no bank-size cap. Session selection, grading, public projections, and UI remain in their later phases.

**Repository findings:** Qudrat uses `routes/student.php` under `student-dashboard` with `auth/verified/student/log.visit` middleware and a dedicated admin route group. Its services, policies, `resources/views/student/qudrat/{index,practice,results}.blade.php`, admin content screens, and `resources/views/components/qudrat/` provide the implementation reference. The existing indigo `#292a65`, Arabic typography, compact buttons, cards, native radios, RTL spacing, and nearby feedback in `resources/css/qudrat.css` should carry into Phase 3. Its four-choice grading/player assumptions do not fit these three question types unchanged. Read the installed Impeccable instructions as required; no UI was changed or visually verified in this backend phase. Reload the relevant Impeccable workflow before Phase 3 interface work.

**Local import report:** the owner-approved `127.0.0.1:3306/yarub` database received only `2026_10_23_000001_create_arabic_skill_tables.php` and this module's content. All initial banks/questions are active.

| Bank | Models | Questions | Multiple choice | True/false | Matching groups | Points |
| --- | --- | --- | --- | --- | --- | --- |
| nasaq | 10 | 300 | 120 | 90 | 90 | 600 |
| rasm | 10 | 300 | 120 | 90 | 90 | 600 |
| bayan | 10 | 300 | 120 | 90 | 90 | 600 |
| midad | 10 | 300 | 120 | 90 | 90 | 600 |
| burhan | 10 | 300 | 120 | 90 | 90 | 600 |
| Total | 50 | 1,500 | 600 | 450 | 450 | 3,000 |

The second import reported **5 banks, 50 models, and 1,500 questions unchanged**, with zero created/updated/conflicting records and no revision churn. An independent database comparison verified all imported prompts, options, mappings, answers, explanations, hints, skills, scoring, and ordered model memberships against the JSON. Initial revisions remain 1. Educational correctness of source material is still an editorial review task; no wording/answer keys were corrected.

Before writes, the review target and non-live mail/filesystem settings were checked and a full single-transaction SQL backup was created at ignored `storage/app/qudrat-local/backups/arabic-skills-before-phase1-20261023-112749.sql` (SHA-256 `78310ac5349a1cf2cc929c361fee96b0f914508858baa6fdc6e0cf7b10ef6ebe`). All **36 pre-existing application tables, including Qudrat content and attempts, retained their pre-import checksums**. The migration ledger changed only to record the new migration; five module tables were added. Baseline checksums and the independent content verification report are retained in the same ignored backup directory. No reset/demo seeding ran.

Reviewed content digest: `bd92f3d973ac169cfb04d28a162cee238d7b9d7c5f89b89b7e017bd40b4b6b33`.

**Verified native local commands** (run from the repository root in PowerShell):

```powershell
$phpExe = "$env:USERPROFILE/.config/herd/bin/php83/php.exe"
$localIni = 'storage/app/qudrat-local/php.ini'

# Reuses Qudrat's ignored non-live review configuration; restricts the target to local yarub.
& $phpExe -c $localIni tools/arabic-skills-local.php arabic-skills:import --dry-run
& $phpExe -c $localIni tools/arabic-skills-local.php migrate --path=database/migrations/2026_10_23_000001_create_arabic_skill_tables.php
& $phpExe -c $localIni tools/arabic-skills-local.php arabic-skills:import
& $phpExe -c $localIni tools/arabic-skills-local.php arabic-skills:import

# Uses the existing dedicated disposable instance on port 33317, never review DB yarub.
# Start only if that dedicated instance is stopped:
# ./tools/qudrat-local.ps1 start
& $phpExe -c $localIni vendor/phpunit/phpunit/phpunit -c phpunit.arabic-skills.xml
./tools/qudrat-local.ps1 test
```

The local wrapper supplies Arabic Skills opt-ins for this process only; it reads `storage/app/qudrat-local/.env` through the existing bootstrap, without changing the normal app's configuration. It accepts only this importer or the exact module migration path. The Arabic Skills suite reuses Qudrat's guarded `yarub_qudrat_test_local` connection on port 33317, migrates only the user prerequisite and Arabic Skills tables, and rolls back test data. Qudrat's importer/environment classes and normal helper behavior are unchanged. Do not run the two database suites simultaneously against the shared disposable test database.

**Verification:** Arabic Skills suite: 18 tests, 12,259 assertions passed on PHP 8.3.11 / native MySQL 8.0.43. Coverage includes all supplied question payloads, malformed packages, catalog path validation, source membership, repeat imports/revisions, admin conflicts, explicit overwrites, archives, additions, omissions, transaction rollback, same-bank constraints, private serialization, immutable snapshots/answers, authorization foundations, dry-run without queries, production opt-in/target/digest checks, interactive/noninteractive execution, and process-scoped approval. All 23 changed PHP files passed syntax checks, and all 21 new PHP files passed Pint. The existing Qudrat suite passed 63 tests and 529 assertions on its isolated connection. No asset build was needed because Phase 1 changes no frontend files or dependencies. The legacy feature suite was not run because its database isolation remains unresolved.

**Later production release:** follow `docs/DEPLOYMENT.md` and Qudrat's established guarded-import workflow. After an authorized release has verified the server runtime, exact target, backup, migration history, and release sequencing, apply only the missing Arabic Skills migration, validate the deployed private JSON with `php artisan arabic-skills:import --dry-run`, and compare its digest with the reviewed local digest above. Provision the process-only `ARABIC_SKILLS_ALLOW_PRODUCTION_IMPORT=true` opt-in and run the documented `--production --database=... --sha256=...` import; use `--force` only for explicitly approved noninteractive execution. Review the counts/conflicts, then rerun to verify unchanged records. Do not include imports in automatic deployment or touch existing Qudrat data. These production steps have been simulated on the disposable local database; no production release/import was performed.

### Phase 2 Session engine and progress

Implement both session modes, stable selection/snapshots, ownership checks, server grading for all three types, matching partial credit, resume behavior, completion, and progress/history queries. Verify concurrency and repeat submission handling before building on the API contract.

### Phase 2 delivery and verification — 2026-10-23

**Status: complete locally.** Added `App\Services\ArabicSkills\Practice`, `Grader`, `Attempts`, and `Progress`, plus the student-only session-creation policy. No migrations or content reimports were needed. The owner's review database and seeded content were not changed in this phase; all session fixtures and concurrency workers used the guarded disposable `yarub_qudrat_test_local` database on port 33317.

**Session contract for Phase 3:**

- `Practice::start($user, $bank, 'practice')` creates/resumes an exact ten-question session; `start($user, $bank, 'model', $model)` creates/resumes the selected thirty-question model. It returns an internal session model for routing, not a student response body. Use the explicit projections below for student data.
- Selection randomizes within unseen, latest incorrect/partial, and other tiers, without duplicates. Recency uses submitted timestamp then row ID across both modes, including submitted answers from abandoned attempts; loading an unanswered question is not an attempt. MySQL 8 window queries return one latest result per question. Model sessions preserve source number order. Insufficient active content produces Arabic validation messages before creating any session.
- Starts and mutations use transactions and a consistent owner-first row lock. Repeated starts resume one active session per user/bank/mode/model. Each selected question stores its complete private immutable snapshot, including all grading content and the bank/model labels used for that attempt. Existing sessions resume even after bank/question deactivation.
- `show($user, $session)` returns an explicit public projection of the current question only. `answer($user, $session, $itemId, $response)` validates and commits one complete response, returns feedback for that submitted question, and waits for `next($user, $session, $itemId)`. No selection-save or skip action is provided in V1; local option changes alone never submit. Refresh preserves order, committed responses, feedback, and the current position. Unsubmitted selections are not persisted by this engine.
- Response shape is `['option_id' => 'a']` (multiple choice), `['option_id' => 'true']` or `'false'` (string IDs), or `['matches' => [['item_id' => 'item_1', 'option_id' => 'a'], ['item_id' => 'item_2', 'option_id' => 'b']]]`. Both matching items are required; selecting the same option twice is valid. Unknown IDs, duplicate item IDs, booleans, incomplete mappings, extra payload fields, and client-supplied scores are rejected without storing a response or revealing feedback.
- The grader awards 0/2 for choice and true/false questions and 0/1/2 for matching. Session earned points are reconciled from stored item scores. Retries cannot overwrite committed answers; stale Next requests cannot advance another question. The final Next completes the session only after every selected item has a submitted response. Closed sessions reject answer submissions. A failure saving totals rolls back the response too.
- `hint($user, $session, $itemId)` returns only a genuine snapshot hint for the current unanswered question; unavailable hints produce a validation message. `has_hint` controls visibility. Answer keys, grading rules, full snapshots, future items, and unrevealed explanations are excluded from public projections. Hint requests do not submit answers or change points.
- `abandon($user, $session)` explicitly closes an incomplete session; starting again creates a separate attempt. `results($user, $session)` requires completion and returns points, the unrounded percentage, correct/partial/incorrect counts, and all submitted responses with their original explanations and correct mappings. Incomplete/abandoned attempts remain distinct history states and cannot be requested as completed results.
- `Progress::banks($user)`, `bank($user, $bank)`, and `history($user, $bank, $page)` provide active-content coverage, distinct attempted counts, completed-session counts, resumable sessions, original-model availability/latest completed results, and history paginated at twenty sessions. Repeated attempts do not inflate coverage. Archived questions leave the active coverage denominator/numerator, while history remains available. Latest model results are fetched together rather than one query per model.
- Every operation rereads and authorizes the stored session owner. Do not accept user IDs, selected question lists, answer keys, snapshots, or scores from student requests. Phase 3 controllers must use the authenticated user and these services behind the normal student middleware/CSRF flow. HTTP routes, frontend controls, and browser accessibility checks belong to Phase 3; they have not been added or claimed verified here.

**Verification:** the complete Arabic Skills suite passed **32 tests and 12,451 assertions**, including **14 Phase 2 tests / 192 assertions**. Tests cover both modes, fixed order/resume, all scoring outcomes, matching partial credit and malformed responses, private projections, hints, stale model/ownership checks, latest-result priority and timestamp/ID tie-breaking, point-based results, deactivation and edited answer keys, incomplete/abandoned sessions, history/coverage isolation, and atomic rollback. A separate-process MySQL test holds the owner lock until both workers are ready, then proves concurrent starts create one session, competing matching submissions commit once with consistent totals, and parallel Next requests advance one position only. Its committed fixtures are cleaned up by exact owner/bank identity. All eight Phase 2 PHP files passed syntax checks and Pint. The existing Qudrat regression suite also passed **63 tests and 529 assertions**. After the batched latest-model query change, all thirteen focused session/progress tests passed again (171 assertions). No frontend files or dependencies changed; no asset build or browser UI verification was needed for this phase.

Use the same isolated suite commands as Phase 1; for focused verification:

```powershell
& $phpExe -c $localIni vendor/phpunit/phpunit/phpunit -c phpunit.arabic-skills.xml --filter 'PracticeTest|ConcurrencyTest'
```

### Phase 3 Student interface

Use Impeccable and the current Qudurat components to build the five-bank landing, bank/model selection, question interactions, nearby feedback, results, and history. Check real Arabic content on desktop and mobile and polish the flow before considering it complete.

### Phase 3 delivery and verification — 2026-10-23

**Status: complete locally.** The authenticated student navigation now includes **مهارات اللغة العربية** alongside Qudrat. Open `/student-dashboard/arabic-skills` in the existing local application with an existing student account. This uses the same local `yarub` content imported in Phase 1; no new migration, import, user creation, or practice fixture was applied to the owner's review database during this phase.

**Delivered:** five-bank landing with dynamic active counts/coverage and ten-question start/resume; bank pages with thirty-question source models, availability, latest results and paginated history; all three question interactions; explicit answer/Next actions; nearby explanations/correct mappings and partial credit; completed results with original responses; genuine hint requests; explicit abandonment; validation, empty and retry states. Incomplete sessions link to resume, abandoned sessions stay labelled incomplete, and results use earned points. Score fractions use `bdi dir="ltr"` inside RTL text. All initially seeded hints remain unavailable and their buttons are hidden.

Student routes use `student.arabic-skills.*` under the existing `auth/verified/student/log.visit` middleware and CSRF flow:

| Route | Purpose |
| --- | --- |
| `GET /student-dashboard/arabic-skills` | Five-bank landing |
| `GET /student-dashboard/arabic-skills/banks/{bank}` | Bank, models and history |
| `POST /student-dashboard/arabic-skills/banks/{bank}/sessions` | Start/resume quick practice or a model |
| `GET /student-dashboard/arabic-skills/sessions/{session}` | Current owned session |
| `POST .../sessions/{session}/items/{item}/answer` | Complete response submission |
| `POST .../sessions/{session}/items/{item}/next` | Advance or complete |
| `POST .../sessions/{session}/items/{item}/hint` | Genuine hint only |
| `POST .../sessions/{session}/abandon` | Confirmed closure without completion |
| `GET .../sessions/{session}/results` | Completed results and review |

The controller always supplies the authenticated user to Phase 2 services and renders explicit public projections. It never serializes private question/session snapshots or future answers. Cross-owner access is rejected across every session endpoint. Native forms work without enhanced requests; the route-specific JS entry progressively replaces the current section after successful submissions, keeps selected matching text readable, disables duplicate actions while pending, focuses feedback/Next or the new prompt, and gives hints their own focus target. A twenty-second abort restores controls and initiating focus after a stalled request; retrying is safe because grading commits only once. Actual screen-reader speech and a live network stall were not simulated; accessible names/headings/focus were inspected through the browser AX/DOM, and timeout/hint recovery has focused Node behavior tests.

**Design system and Impeccable:** ran the installed context workflow and read the applicable extension, craft, critique and polish guidance. This is an ordinary extension of the existing Qudrat world: reused its navy `#292a65`, Hacen font inheritance, card/icon/button/progress styles and green/orange feedback. Added only scoped matching controls, model/history rows and a 52rem answer workspace. Independent design assessment approved the scoped pages after recapturing final evidence; a separate focused review confirmed the navigation clipping fix resolved. Independent technical assessment ran the detector once over the student view directory (exit 0, zero findings), then identified five manual accessibility/recovery issues. Its follow-up marked all five resolved after hint focus, review heading hierarchy, contextual action names, Arabic shell control names and bounded request recovery were implemented. The detector does not cover every reusable component or establish assistive-technology certification; live injection/overlays were unavailable because browser evaluation is read-only. The documenter confirmed existing tokens/components remain intact; no new visual-identity files were introduced.

The added navigation item exposed clipping in the old fixed header at 1024px. The student header now uses the existing collapsed menu below 1280px and its original expanded treatment above it. The already-touched shared nav component now marks only the active item with `aria-current`; the original sizing rule uses a dedicated attribute to preserve appearance. Support/home/logout and the toggle have Arabic accessible names. Qudrat practice components and grading remain unchanged. An empty avatar in the disposable screenshot fixture is inherited shell behavior, not newly authored learning content.

**Browser verification:** actual login and a full thirty-question model were exercised against disposable local test data. The result was **23/60 (38.3%), with 7 correct, 9 partial and 14 incorrect**, confirming point-based results; both independent matching selections could use the same option. Refresh/resume, keyboard selectors, model navigation/history, real long `burhan_192` content, and feedback placement were checked at 390px, 597px, 1024px, 1280px and 1440px. At 390px the matching feedback and Next action fit together after a single modest scroll, with no horizontal overflow. Font readiness was confirmed for the final capture. Screenshots remain under ignored `storage/app/qudrat-local/phase3-shots/` (including `landing-final.png`, `matching-mobile-viewport.png`, `long-question-mobile.png`, `hint-focus-final.png` and `results-headings-final.png`). The temporary loopback review server on port 8018 was stopped; its committed browser fixtures/content were removed by exact test-owner/bank scope from the disposable database before automated suites. No root environment credentials, production system or live integration was used.

**Automated checks:** the final Arabic Skills suite passed **38 tests / 12,982 assertions**, including **6 student HTTP tests / 531 assertions**; the Qudrat regression suite passed **63 tests / 529 assertions**. The frontend recovery suite passed **2 tests**. New controller/test PHP syntax, JS syntax, Pint, and diff whitespace checks passed. The added HTTP tests cover real five-bank/model pages, every question type, private answer/explanation withholding, guest/other-student denial, malformed and score-injection payloads, partial-credit results, incomplete/abandoned history, unavailable models and genuine hints. Two Node tests cover stalled-request recovery and focused hint reveal. The isolated Vite build passed with existing Browserslist/safelist warnings; no dependencies were changed. Built assets are ignored as before and must be rebuilt as part of a separately authorized release.

```powershell
# Reuse the compatible native PHP and isolated test connection from Phase 1.
& $phpExe -c $localIni vendor/phpunit/phpunit/phpunit -c phpunit.arabic-skills.xml
node --test tests/Frontend/arabic-skills-student.test.mjs
./tools/qudrat-local.ps1 test
node tools/qudrat-build.mjs
```

### Phase 4 Admin interface

Build bank/model management, searchable question management with all three editors, question previews, activation controls, and student session review. Confirm content revisions and admin edits do not change past or active attempts.

### Phase 4 delivery and verification — 2026-10-23

**Status: complete locally.** The admin sidebar now links to **مهارات اللغة العربية** at `/admin-dashboard/arabic-skills`. It uses the existing authenticated, verified admin middleware, role policies and CSRF protection. This phase requires no migration or content reimport. The normal local `yarub` database retains its Phase 1 content and existing application data; no browser fixture, question edit or training attempt was written there during Phase 4. Production was not accessed.

**Delivered:** bank title/subject/order/availability settings with published/archive counts; original model titles, fixed membership and availability counts; question search by prompt/source ID, bank, model, type, skill and publication state; twenty-row pagination retaining filters; native editors and previews for multiple choice, true/false and matching; archive/restore; filtered student activity and read-only attempt review.

All complete new questions publish immediately with generated `admin_` IDs and no source model/number/context membership. There is no draft state, addition cap or delete endpoint. Existing questions retain their bank/source identity and original model membership. Existing archived questions remain archived when edited. Matching editors retain two independent answer mappings, including the same option for both. Optional empty hints keep the student hint control hidden. Models remain unavailable when any of their thirty source questions is archived or their bank is stopped; additions never replace model members.

Bank/model/question updates and archive/restore use transactions, row locks and optimistic revision tokens. Listing aggregate counts are excluded from tokens so settings forms and locked records agree. Stale edits fail with an Arabic recovery message; failed bank/model forms reopen, retain submitted values and the original token, and require reload/reconciliation before a stale edit can be retried. Import baselines remain unchanged so subsequent imports can report admin edits. The editor never modifies session snapshots, stored responses or scores.

Admin activity filters include student name/email, bank, model, practice/model mode, state and start-date range. Review shows submitted responses, original correct answers/explanations, submission times and awarded points from each item's snapshot. Unsubmitted items are labelled unanswered; incomplete totals are explicitly non-final. Historical bank/model labels are read from the snapshot in review. Each reviewed question has its own radio group; submission counts use Arabic “من” and score fractions use `bdi dir="ltr"` to preserve RTL meaning.

Routes use `admin.arabic-skills.*`:

| Route | Purpose |
| --- | --- |
| `GET /admin-dashboard/arabic-skills` | Banks and original models |
| `PATCH .../banks/{bank}` | Bank settings and availability |
| `PATCH .../models/{model}` | Original model title |
| `GET .../questions` | Search and filters |
| `GET .../questions/create`, `POST .../questions` | Complete new question and immediate publication |
| `GET .../questions/{question}/edit`, `PUT .../questions/{question}` | Edit existing content |
| `GET .../questions/{question}/preview` | Admin-only question, answer/explanation and hint preview |
| `PATCH .../questions/{question}/archive` | Explicit archive or restore |
| `GET .../sessions`, `GET .../sessions/{session}` | Activity and immutable response review |

**Design and browser evidence:** ordinary extension of the Qudrat admin world, preserving its navy, Hacen typography, form/tabs/buttons/notices and native controls. The Arabic Skills admin routes use the existing single Livewire/Alpine admin entry to avoid duplicate initialization. Reusable student question/feedback components also render admin previews and historical responses. Scoped admin CSS supplies responsive filter grids, management rows and matching editor spacing. No new visual-identity files or dependency changes were needed.

Authenticated browser checks used disposable data only on `127.0.0.1:33317/yarub_qudrat_test_local`: five real banks, matching editor save, new true/false immediate publication, archive/restore, bank save and failed-form input recovery, and a three-response in-progress model review. Verified 390px and 1440px layouts, font readiness, independent selected radio groups, and no horizontal overflow. Screenshots are ignored under `storage/app/qudrat-local/phase4-shots/`; use the four overlapping `matching-mobile-{top,options,mappings,save}.png` captures rather than the unreliable full-page capture, and `review-{desktop,mobile}-final.png` for corrected count direction. The temporary review server was stopped and its exact disposable fixtures were removed before automated tests.

The independent technical reviewer ran the detector once on the new admin view directory (exit 0, zero findings), then found shared review radio names and lost/collapsed bank/model error inputs. Both findings were fixed and scored resolved. The finish reviewer requested valid overlapping mobile evidence, then identified RTL submission-count reversal; its final verdict was **ship** for that correction against the preceding scoped admin review. The documenter confirmed the ordinary extension preserves the incumbent identity. Actual screen-reader speech was not tested.

**Automated checks:** the final Arabic Skills suite passes **45 tests / 13,128 assertions**, including seven admin HTTP tests with **146 assertions**. These cover all three editors/previews, immediate publication and identity injection, invalid saves without drafts, stale update/archive/bank/model conflicts, search filters and escaping, archive-preserving edits, immutable grading after content edits/deactivation, snapshot response review, activity/date filters, validation recovery, guest/student denial and absence of a delete endpoint. The Qudrat regression suite passes **63 tests / 529 assertions**. PHP syntax, Pint and diff whitespace checks pass. The isolated Vite build passes with the existing Browserslist/safelist warnings; the frontend recovery tests pass (2 tests).

```powershell
& $phpExe -c $localIni vendor/phpunit/phpunit/phpunit -c phpunit.arabic-skills.xml --filter AdminPagesTest
& $phpExe -c $localIni vendor/phpunit/phpunit/phpunit -c phpunit.arabic-skills.xml
./tools/qudrat-local.ps1 test
node --test tests/Frontend/arabic-skills-student.test.mjs
node tools/qudrat-build.mjs
```

### Phase 5 Verification and final polish

Run the repository's appropriate automated checks and focused feature tests. Verify source counts, reimport behavior, grading, authorization, resume, feedback placement, responsive RTL, keyboard access, empty/error states, and Qudurat regression checks. Apply Impeccable critique/polish to the new pages. Provide a concise implementation summary, development seed report, test results, and any unresolved content issues.

### Phase 5 delivery and verification — 2026-10-23

**Status: complete locally.** All feature acceptance checks below are covered by the dedicated automated suites, source/database comparison and scoped browser evidence, subject to the explicit verification limits below. No production access, deployment or import was performed.

**Final recovery correction:** stopping a bank previously removed its landing card even for students with existing attempts or results. The landing now includes stopped banks only when the authenticated student owns sessions in them. Existing attempts remain resumable and completed history remains discoverable; students without those sessions do not see the stopped bank. New quick/model attempts remain blocked. Availability text now distinguishes a stopped bank from a bank/model with insufficient active questions. The added HTTP scenario checks both students, resume, full completion after deactivation, retained results/history, and rejected new starts.

**Development seed report:** a read-only transaction against the approved `127.0.0.1:3306/yarub` database compared the imported questions and bank/model labels with the current validated JSON. It found **zero question-field or bank/model differences**, no wrong source-model memberships, no admin additions, and no nonempty hints. All 1,500 questions remain published. No content, user, session or application data was changed in that database during Phase 5.

| Bank | Models | Questions | Multiple choice | True/false | Matching | Points |
| --- | ---: | ---: | ---: | ---: | ---: | ---: |
| `nasaq` | 10 | 300 | 120 | 90 | 90 | 600 |
| `rasm` | 10 | 300 | 120 | 90 | 90 | 600 |
| `bayan` | 10 | 300 | 120 | 90 | 90 | 600 |
| `midad` | 10 | 300 | 120 | 90 | 90 | 600 |
| `burhan` | 10 | 300 | 120 | 90 | 90 | 600 |
| **Total** | **50** | **1,500** | **600** | **450** | **450** | **3,000** |

The local importer dry-run again reports the reviewed JSON digest **`bd92f3d973ac169cfb04d28a162cee238d7b9d7c5f89b89b7e017bd40b4b6b33`**. After the browser attempt below, an unchanged reimport on the disposable database reported **5 banks, 50 models and 1,500 questions unchanged**, with zero created, updated, skipped or conflicting records. Fingerprints of all five feature tables were identical before/after, including the stopped bank state, content revisions, completed session, snapshots, responses, timestamps and scores. The production target/digest/confirmation/conflict paths remain covered by isolated tests; production was not accessed or imported.

**Final walkthrough and polish:** used a separate temporary server on port 8019 and the guarded disposable database on port 33317. The user's existing browser tab was left in place. Authenticated student navigation started a ten-question practice; its bank was then stopped in the fixture database. The landing still offered resume. Keyboard submission, refresh recovery, explicit Next, repeated matching choices, nearby explanations, completion and history were exercised through the actual UI. All three question types appeared. The result was **10/20 (50%), with 3 correct, 4 partial and 3 incorrect answers**; selecting the same matching option twice gave **1/2**. A database check confirmed ten distinct selected/submitted items and item points summing to the session total. Authenticated admin search/review showed the same completed result and independent selected radio groups.

Checked 390px, 768px, 1024px and 1440px layouts without horizontal overflow; final captures explicitly awaited Hacen readiness. Prior Phase 3 real long-content/model checks and Phase 4 editor/error recovery evidence remain applicable. New evidence is ignored under `storage/app/qudrat-local/phase5-shots/`, particularly `landing-mobile-final.png`, `landing-tablet-final.png`, `landing-1024-refreshed.png`, `landing-desktop-final.png`, `paused-bank-mobile.png`, `partial-mobile.png`, `results-mobile.png` and `admin-review-desktop.png`. The independent finish reviewer returned **ship** for the changed recovery state after refreshed font-consistent evidence; the documenter confirmed the ordinary extension preserves the existing Qudrat identity. No additional detector pass or new design-system files were needed. The temporary server was stopped, its tab closed, viewport override reset and exact browser/concurrency fixtures removed from the disposable database.

**Verification scope and remaining limits:** source structure, answer mappings, memberships, point calculations, importer guards and data fidelity are verified; educational correctness of the supplied questions remains an editorial task, and no source wording/answer key was changed. Actual screen-reader speech, other browser engines and production runtime/deployment have not been verified. The shared shell emitted PowerGrid/Livewire errors on login/course/dashboard paths, a browser-Puppeteer error on the ordinary dashboard, and intermittent CDN Preline errors during student viewport changes. Fresh Arabic Skills admin navigations produced no new console errors. This feature pass did not change those shared third-party libraries or claim an application-wide clean console. Existing build warnings remain limited to the stale Browserslist data and unmatched Tailwind safelist pattern.

**Final automated results:** the clean isolated Arabic Skills suite passes **46 tests / 13,150 assertions**, including the new stopped-bank recovery scenario. Qudrat regression passes **63 tests / 529 assertions**. Frontend recovery passes **2 tests**. The compatible PHP 8.3 runtime passes syntax/Pint checks for the final changed PHP files; diff whitespace checks and the isolated Vite build pass. The stopped disposable database was restarted after the session pause; interrupted browser/concurrency fixtures were removed by exact identity before the clean successful runs. The general legacy `php artisan test` suite remains outside this verification because its database isolation is unresolved; use the dedicated suites above.

## 9 Acceptance criteria and focused tests

- The section initially displays five banks with the exact titles and subjects above, backed by 1,500 supplied main questions and 50 source models. Admins can add unlimited valid questions; counts and coverage denominators grow accordingly while original source model memberships stay fixed.
- Every bank imports 300 questions with the 120/90/90 type distribution and ten models of 30 questions each. The catalog is never treated as a bank.
- Importing unchanged files twice preserves counts, IDs, revisions, sessions, and scores. A malformed package fails without a partial write. Admin-edited content conflicts are reported.
- Practice selects exactly ten distinct active questions from the chosen bank. Model mode uses exactly its 30 questions in source order. Session selection and snapshots survive refresh.
- Multiple choice and true/false grade to 0 or 2. Matching grades each link independently to allow 0/1/2. Test both correct links, both incorrect links, and selecting the same option for both items with exactly one correct link. Repeated option choices are valid student responses; duplicate item IDs, incomplete mappings, and unknown IDs are invalid. Do not force a one-to-one response or auto-complete the remaining link, which would remove the source’s meaningful partial credit.
- Results and percentages use points, not only the number of fully correct questions. Repeated/concurrent answer submissions do not change the committed answer or add points twice.
- Guests and other students cannot access another user's session/results. Student payloads never contain future answers, private grading snapshots, or unrevealed explanations. Non-admins cannot change content.
- Valid imported/admin-created questions publish immediately. Incomplete saves fail without draft records; archive/restore is explicit and preserved through edits/reimports.
- Practice priority uses the latest committed result across both modes, including wrong-then-correct cases; unsubmitted questions remain unseen.
- Existing local `yarub` data survives the feature migrations/import. Automated tests use a separate disposable database. Production target/digest guards, conflict reporting, reruns, and incompatible flags are tested locally without accessing production.
- Hints are hidden for all initially seeded questions. Submitted questions reveal the correct answer and the full source explanation in a convenient visible location.
- Admin edits and reimports do not change snapshots or historical results. Deactivation blocks new selection without breaking history or preventing existing sessions from finishing from their snapshots.
- Unique attempted coverage does not increase when a student repeats the same question. Bank counts and scores stay isolated from Qudurat.
- The new pages visibly belong to the current Qudurat design system, with minimal introductory text, clear cards/icons, accessible matching, unclipped Arabic diacritics, and usable desktop/mobile RTL layouts.
- Run the existing relevant Qudurat regression tests after shared-component changes. No production import, deployment, or infrastructure changes are part of this document's content-preparation work.
