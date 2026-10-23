# Arabic Skills (مهارات اللغة العربية)

Build a new authenticated student section in the existing Yarub application named **مهارات اللغة العربية**, with an admin area for managing its content and reviewing student activity. Follow the current successful **القدرات / Qudurat** experience for navigation, practice sessions, feedback, results, and visual design. Use **Impeccable** throughout implementation. The existing Qudurat design is the visual reference and should remain stable.

This is the implementation specification and roadmap; the feature is not yet implemented. The JSON is a ready-to-import content package. First inspect the actual repository and current Qudurat implementation; follow its Laravel conventions, frontend stack, authentication, roles, route organization, and component system. Do not introduce a new framework or assume a separate application is needed.

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

The following is the required interface to implement; these Arabic Skills commands are not yet implemented:

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

### Phase 2 Session engine and progress

Implement both session modes, stable selection/snapshots, ownership checks, server grading for all three types, matching partial credit, resume behavior, completion, and progress/history queries. Verify concurrency and repeat submission handling before building on the API contract.

### Phase 3 Student interface

Use Impeccable and the current Qudurat components to build the five-bank landing, bank/model selection, question interactions, nearby feedback, results, and history. Check real Arabic content on desktop and mobile and polish the flow before considering it complete.

### Phase 4 Admin interface

Build bank/model management, searchable question management with all three editors, question previews, activation controls, and student session review. Confirm content revisions and admin edits do not change past or active attempts.

### Phase 5 Verification and final polish

Run the repository's appropriate automated checks and focused feature tests. Verify source counts, reimport behavior, grading, authorization, resume, feedback placement, responsive RTL, keyboard access, empty/error states, and Qudurat regression checks. Apply Impeccable critique/polish to the new pages. Provide a concise implementation summary, development seed report, test results, and any unresolved content issues.

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
