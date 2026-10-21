# Qudrat (القدرات)

Add verbal aptitude practice to the existing Yarub application. Students practice six question categories; admins review, edit and publish the content.

**Current status: Phase 1 is complete. No Qudrat feature code or database tables have been implemented. Phase 2 is next.**

This is the single Qudrat requirements and roadmap document. Content lives in `qudrat_content/` at the repository root. Follow `AGENTS.md` for project conventions and environment safety.

## Roadmap

Implement one phase at a time, only when requested. At the end of each phase, update its status here and report what changed, what was tested, any blockers and the next step. Do not mark a phase complete until its acceptance checks pass.

| Phase | Deliverable | Status |
| --- | --- | --- |
| 1. Inspect and plan | Inspect Yarub, validate JSON, choose integration approach | Complete — discovery only |
| 2. Database and import | Create Qudrat storage and safely import development drafts | Not started — next |
| 3. Admin management | Review, edit, preview and publish content locally | Not started |
| 4. Student practice | Practice sessions, hints, grading, resume and progress | Not started |
| 5. Verification and release preparation | Verify the complete feature and document rollout | Not started |

### Phase 1 — Inspect and plan (complete)

Completed on 2026-10-21 against checkout `799df14`:

- Inspected authentication, roles, routes, models, layouts, assets, database configuration and tests.
- Independently parsed and structurally validated all six JSON files; confirmed the inventory below.
- Chose separate Qudrat tables while reusing Yarub authentication, layouts and admin UI conventions.
- Identified the unresolved access rule and local environment requirements below.

**Result:** a verified content inventory and implementation plan. No feature was built, Laravel was not booted, and no database or production system was accessed.

### Phase 2 — Database and development import (next)

**Build:**

- Establish an isolated development/test environment with a disposable database and non-live integrations before booting Laravel.
- Add Qudrat migrations, models, relationships, validation and authorization foundations using the table plan below.
- Add an explicit `qudrat:import` command with validation-only mode and default input `qudrat_content/`.
- Import the supplied content into the development database as drafts. Do not add it to automatic boot/deployment or the broad demo seeder.
- Add focused validator/import tests, including reruns, admin-edit conflicts and rollback on failure.

**Done when:** the development database contains 6 categories, 1,200 questions, 4,800 options and 40 passages; a second import creates no duplicates; invalid input causes no partial writes; admin edits survive reruns; all content remains draft and existing records/progress are preserved.

**Start with:** `Read docs/QUDRAT.md and implement Phase 2 only. Update this document with results and verified local commands.`

### Phase 3 — Admin management and content review

**Build:**

- Add القدرات to the existing admin sidebar and show category total/published/draft counts.
- Add a paginated question table with prompt/option search and category/publication filters, sorted by category and source order.
- Add question create/edit, preview, publish/unpublish and archive actions. Keep the initial category set fixed.
- Add shared-passage editing with linked-question navigation and a clear warning that one edit affects multiple questions.
- Build shared question-rendering components now so admin preview and the later student player use the same markup.
- Present seeded drafts for owner review, including educational correctness and reading paragraph formatting.

**Done when:** ordinary admins can manage and preview content; students/guests cannot access admin pages or Livewire actions; invalid questions cannot publish; shared-passage previews update correctly; drafts remain inaccessible to students. Browser file upload is not required.

**Start with:** `Read docs/QUDRAT.md and implement Phase 3 only. Update its status after verification.`

### Phase 4 — Student practice and progress

**Before enabling access:** resolve who is entitled to Qudrat. Existing course/lesson billing rules do not establish a Qudrat access rule.

**Build:**

- Add القدرات to student navigation and six category cards with descriptions, published counts, progress, first-attempt accuracy and start/resume actions.
- Add one-question-at-a-time practice with persisted selections, optional hints, server grading, feedback and explicit next action.
- Add stable session order, refresh/resume, passage grouping, results and mistake review using immutable content snapshots.
- Add ownership checks, publication checks, duplicate-submission protection and concurrent-request handling.
- Verify metrics, empty/exhausted banks, mobile/desktop layout, keyboard use and Arabic RTL.

**Done when:** all six types work; refresh retains state; reading groups stay intact; hints persist; retries do not inflate first-attempt accuracy; answers/explanations stay private before grading; concurrent submissions grade once; edits do not rewrite historical results; another student's sessions/results cannot be accessed.

**Start with:** `Read docs/QUDRAT.md and implement Phase 4 only. Update its status after verification.`

### Phase 5 — Verify and prepare release

**Complete:**

- Run focused tests against isolated data, including MySQL concurrency/constraints, permissions, answer leakage, import conflicts, snapshots and publication changes during practice.
- Run appropriate PHP checks and the asset build in the isolated environment. Review actual content across all six categories on desktop/mobile and with keyboard navigation.
- Check existing student/admin journeys for regressions; distinguish unrelated stale Breeze failures from Qudrat failures.
- Record exact verified development/import commands and the publication workflow in this document.
- Prepare proposed migration, content rollout and recovery steps consistent with `docs/DEPLOYMENT.md`. Preserve attempts/history. Production deployment and production seeding require a separate explicit task.

**Done when:** acceptance checks pass, the owner can review the feature locally, and release/recovery instructions are concrete. If required checks are blocked, document the blocker and leave the phase incomplete.

**Start with:** `Read docs/QUDRAT.md and complete Phase 5 only. Do not deploy or seed production.`

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

### Proposed tables

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

Store one canonical correct key (`a`–`d`) per question and atomically validate exactly four keyed options. Same-category passages are required for reading and unavailable for other types. Publication requires nonempty prompt, choices, hint and explanation. Category and question must both be published, and the question unarchived, for student availability. Restrict destructive deletion of referenced content/history; use archive/unpublish. Keep prose in text columns and snapshots in private JSON.

### Proposed code and routes

Keep responsibilities small: controllers under `app/Http/Controllers/{Admin,Student}/Qudrat/`; validation/import/practice/progress services under `app/Services/Qudrat/`; Qudrat policies registered in `AuthServiceProvider`; `app/Livewire/QudratQuestionsTable.php`; separate admin/student response allowlists; and `app/Console/Commands/ImportQudratContent.php`.

Views belong in `resources/views/{admin,student}/qudrat/`. Shared components belong in `resources/views/components/qudrat/`: category card, player, prompt, choices, passage, progress, hint and feedback. Use the same player markup for admin preview and student practice.

| Route group | Planned endpoints |
| --- | --- |
| `/admin-dashboard/qudrat`, names `admin.qudrat.*` | GET overview; PATCH category metadata/publication; GET question list/create/edit/preview; POST question create; PUT question update; PATCH question publication/archive; GET passage list/edit; PUT passage update |
| `/student-dashboard/qudrat`, names `student.qudrat.*` | GET category cards; POST category session start; GET session/resume; PATCH item selection; POST item hint/answer; POST session next; GET results/review |

Use normal web CSRF/session protection, scoped bindings and ownership checks. Starting sessions and changing state use write methods; GET requests must not generate new practice orders.

## Rules each implementation phase must preserve

### Safe, repeatable imports

Validate every input file before writes. Use stable IDs for transactional upserts and report created, updated, unchanged, skipped and conflicting counts. Never delete missing-source records, replace option identities, reset progress or republish archived/draft content on rerun.

Track the last imported checksum/version for category metadata, passages and question-plus-options content. Compare current records with that baseline to detect admin edits, including when the incoming file is unchanged. Skip/report conflicts by default; allow only deliberate overwrite of named IDs. Report dependent passage conflicts, recheck under locks, and roll back unexpected failures. Publication/archive state and historical snapshots are outside source updates.

Proposed commands (not implemented yet):

```text
php artisan qudrat:import --path=qudrat_content --dry-run
php artisan qudrat:import --path=qudrat_content
php artisan qudrat:import --path=qudrat_content --overwrite-id=verbal_analogies_001
```

Keep validation-only mode free of writes and content mutations restricted to verified isolated development/testing environments. Do not connect to an unspecified database merely to validate JSON.

### Student behavior and history

1. Prefer unseen questions in sessions of up to 10. Resume unfinished practice; new/review practice is explicit. At exhaustion offer review instead of an endless retry loop.
2. For reading, select consecutive whole passage groups in source order until roughly 10 questions, including the last group in full. Show actual length. Proposed partial-seen rule: include the entire published group and treat earlier attempted questions as repeats.
3. Save selections as drafts, not attempts. Provide **تلميح** only when requested and record use server-side. Enable **تحقق** after a choice; grade on the server, lock the response, then show correct/incorrect feedback, the correct choice and explanation. Advance only with **التالي**.
4. Persist order, draft choices, hints and submissions across refresh/resume. Pin private snapshots of prompts, options, correct keys, feedback and passages when creating the session. Admin edits must not rewrite current exercises or historical results.
5. Authorize ownership and current item/state on every request. Validate choice membership. Use consistent row locks and unique answer-per-item constraints so concurrent starts/submissions cannot duplicate sessions or grading. Serialize first submissions per user and identify the earliest submitted answer deterministically.
6. Recheck publication on every student read/action. Proposed default: safely close affected active sessions on unpublish/archive, notify the student, preserve submitted history and hide newly restricted unsubmitted content. Completed owned results may retain their historical snapshots.
7. Results show score, correct/incorrect counts, hint usage, **مراجعة الأخطاء**, **تدريب جديد** and return navigation. Wrong-answer review includes the original reading passage. Unanswered questions are not silently submitted or marked wrong.

Progress = distinct submitted questions in the current published bank / published questions. First-attempt accuracy = correct first submissions / questions with first submissions in that bank. Retries never rewrite first attempts. Session scores use only that session's submitted answers; hints are marked but carry no penalty. Handle zero counts and empty categories explicitly.

### Admin, presentation and security

- Support create/edit/preview/publish/unpublish/archive, four choices and exactly one correct answer. Never delete attempted questions or passages with dependent questions. Ordinary admins manage content; do not add super-admin restrictions without a product reason.
- Use shared player controls across categories. Analogies retain pair direction; completions retain blanks; contextual errors use supplied word choices without mandatory inline word clicking; odd-word prompts remain compact; association/difference uses the standard layout.
- Reading passages appear beside the question on desktop and above on mobile, with preserved whitespace and continued access between linked questions. Avoid fixed heights that clip text.
- Use keyboard-accessible native radios, visible focus, readable Arabic type, RTL layout, responsive controls, and feedback text/icons alongside color.
- Keep source JSON, correct keys/flags, explanations and snapshots out of student HTML, hidden elements, Alpine/Livewire state and bundles before submission. Hints are returned only on request. Use separate admin/student allowlists and escaped text rendering.
- Do not alter payments, subscriptions, existing exams or other unrelated behavior. V1 excludes timed/mixed exams, leaderboards, AI generation, media, arbitrary categories/question formats and a general exam builder.

## Decisions and environment requirements

| Item | Resolve by |
| --- | --- |
| Who can use Qudrat: every logged-in student or a specific existing purchase? No Qudrat entitlement exists; course/lesson rules cannot simply be copied. | Before Phase 4 access is enabled |
| Local runtime: no project Dockerfile/Compose/runbook was found. Sail being installed does not prove a configured container workflow. Confirm real services/commands or agree isolated native setup. | Before Phase 2 database work |
| PHP/MySQL compatibility: local PHP was observed as 8.4.5, but locked OpenSpout excludes 8.4. Verify the full lockfile on a compatible runtime; do not incidentally upgrade dependencies. | Before running Laravel/dependencies |
| Test isolation: current `phpunit.xml` does not isolate the database; feature tests use `RefreshDatabase`. Set explicit disposable DB/non-live services, avoid stale cached configuration, and verify the target before tests. | Before database tests |
| Source review: educational correctness and passage paragraph structure need owner review. | Before Phase 3 publication |

Use `.env.example` as the starting point; do not reuse real credentials. Existing demo seeding is broad, and even page views may write visitor records. Establish minimum local fixtures deliberately. Add plain validator unit tests and focused import/admin/ownership/practice/grading/progress/history feature tests as each phase is built; test concurrency and constraints on isolated MySQL. Keep exact verified commands and phase results in this document as work progresses.
