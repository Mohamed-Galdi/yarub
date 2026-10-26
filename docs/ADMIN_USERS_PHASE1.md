# Admin users and student tracking — Phase 1

Audit date: 2026-10-25. Inspected working tree at `3f6a7f5`. Scope: audit and implementation plan only, following `YARUB_ADMIN_USERS_ROADMAP.md` and `AGENTS.md`.

User clarification: **all student tracking belongs in the Users section**. The entry point is `admin.students`, followed by the existing student detail page with Qudrat and Arabic Skills tabs. Per-type metrics, session history and read-only session reviews stay within that student's Users context, preserving list/tab/page state on return. Administrators must not need to navigate to the Qudrat or Arabic Skills sections to track a student. Those sections supply existing code/data and visual references only for this work; existing Arabic review components may be reused internally, but their program-section navigation must not carry into the Users tracking flow. New review routes should be nested under the Users/student path and verify that the session belongs to the student in the URL.

## Findings and preserved behavior

| Area | Repository evidence | Preserve / planned change |
| --- | --- | --- |
| Users directory | `routes/admin.php:69–75`, `Admin/StudentController.php`, `Livewire/StudentsTable.php`, `views/admin/students/students.blade.php` | Keep `admin.students` and `admin.view_student`. Current table selects non-deleted students, searches name/email, supports sorting, page sizes, record counts and XLS export. Add explicit registration-descending default, URL state, program/status filters and aggregate columns. |
| Blocking | `User` uses `SoftDeletes`; controller `destroy()` / `restore()`; `DeletedStudentsTable`; `components/delete-confirmation.blade.php` | Blocking soft-deletes; restoration reverses it. Keep existing DELETE routes, CSRF and SweetAlert feedback. Replace generic irreversible-deletion wording with student-named block/unblock confirmation. Current deleted list/count are not role-scoped, while the main list is; constrain the student management surface consistently. |
| Student detail | `views/admin/students/view-student.blade.php`, `User::activeCourses()`, `activeLessons()`, `test_attempts()`, `certificates()` | Preserve active/published enrollment rules; course enrollment date/cost; lesson plan, start/end dates/cost; test title, course/lesson, score/date; certificate title/date/view link. Keep all four sections accessible. No new financial business rules. |
| Existing defects affecting detail | Controller loads certificates and test attempts, but the view also accesses unloaded enrollments and nested test/course/lesson relationships; non-production forbids lazy loading | Use explicit eager loading/counts for the selected section. Missing IDs currently use `find()` without a 404; enforce student-scoped `findOrFail()` and deliberately support blocked students for admin review. The lessons empty-state condition incorrectly checks active courses; fix this within the detail refactor. Avoid mutating pivot dates while formatting expiry. |
| Tabs/back navigation | `components/dynamic-tab-navigation.blade.php`, `components/btn/back.blade.php` | Existing Alpine tabs render all sections immediately and do not persist selection in the URL. Use scoped URL-aware navigation and deferred/paginated records, without changing unrelated consumers of shared tabs. |
| Exports | `StudentsTable`, `DeletedStudentsTable`, vendor `WithExport`; `Admin/DataExportController.php`, `Exports/UsersExport.php` | Preserve table XLS export and its nonvisual name/email/date fields. Vendor code prepares export data separately from pagination; verify filtered-all-rows behavior in isolated runtime. The separate date-range XLSX export includes blocked students and phone/status; retain that distinct scope. Its status mapping is reversed (`deleted_at == null` labels blocked): record and correct through a focused export regression check, without expanding reporting scope. No download was executed in this audit. |

Paths above abbreviate `app/Http/Controllers/`, `app/`, or `resources/` where unambiguous.

## Metric support

“Derivable” means supported by declared schema and current write paths, not verified against production records.

| Metric | القدرات | مهارات اللغة العربية |
| --- | --- | --- |
| Saved participation | Derivable: existence of `qudrat_session_answers` | Derivable: session item with non-null `submitted_at`; join session for owner |
| Unique current coverage | Derivable; existing student `Qudrat/Progress` already counts first answers against `QudratQuestion::published()` | Derivable; existing `ArabicSkills/Progress` counts distinct submitted questions via latest attempts, but admin eligibility must consistently use `ArabicSkillQuestion::active()` |
| First-attempt accuracy | Already calculated for students using `MIN(id)`; admin query should explicitly order earliest `submitted_at`, then `id` | Not displayed by existing progress service, but derivable from immutable submitted items ordered by `submitted_at`, then `id`; count only `result = correct` as correct, not partial points |
| Completed sessions | Derivable: `status = completed` | Derivable: `status = completed`; existing progress service already counts them |
| Last practice | Derivable: maximum answer `submitted_at` or completed session `completed_at` | Derivable: maximum submitted item timestamp or completed session timestamp |
| Session score | Existing results show correct / answered accuracy, alongside answered / total and hint count. For completed sessions all items are answered; closed-session accuracy must remain explicitly partial, with unanswered items separate | Stored `earned_points` / `available_points`; matching supports partial credit. Incomplete points must be labeled non-final |
| Read-only question review | Snapshots plus separate immutable answers exist; admin review route/policy still missing | Already implemented by admin `ArabicSkills/SessionController` and `views/admin/arabic-skills/session.blade.php` |
| Hints / assistance | `hint_requested_at` and answer `used_hint` exist; hints do not reduce correctness | Hint endpoint returns text without recording use; grading does not deduct hint points. Historical assistance is unknown |
| Pre-module / absent records | Historically unavailable; cannot infer missing attempts from legacy tests | Historically unavailable; cannot infer missing attempts from legacy tests |

Data sources: `Services/{Qudrat,ArabicSkills}/{Practice,Progress}.php`, `ArabicSkills/{Attempts,Grader}.php`, corresponding session/question/answer models, and migrations `2026_10_21_000001_create_qudrat_tables.php` / `2026_10_23_000001_create_arabic_skill_tables.php`.

### Calculation contract

- Coverage uses distinct **current eligible** question IDs. Qudrat requires published category/question and no archive date; Arabic Skills requires active bank/question. Minimum session lengths (10 practice / 30 model) govern starting sessions, not the question denominator. Count a matching question once. Resolve types dynamically: Qudrat categories; Arabic Skills banks as primary content sections, with actual `skill` / response `type` breakdowns inside them. Session history remains bank/model/mode-based because a session can mix response types.
- Participation uses any saved submission, including historical answers to subsequently hidden content. Thus a participating student can have zero current coverage. “Neither” means no saved submission in either program; merely starting a session does not qualify.
- Repeated answers affect history, not coverage. Submitted items in active, closed or abandoned sessions still count. Draft selections, hints, question views, starts and abandonment timestamps do not count as practice. Use answer/completion timestamps across all retained history for last practice, independently of current content visibility.
- First accuracy selects the earliest eligible submitted record per program/question using timestamp then ID; restrict to current eligible questions for current summaries. Arabic matching partial results remain partial in review and unsuccessful for this binary accuracy metric. Never substitute point percentage or latest-result accuracy. Do not call either accuracy metric unassisted.
- Qudrat answers reject normal model updates/deletes; Arabic items reject changes to committed response/result/time and snapshots. Both submission flows use owner/session locks and retry guards; no new answer-capture pipeline is needed. Model protections do not prove that old records were never altered externally.
- Zero eligible questions yields unavailable coverage; zero known first answers yields `غير متاح` accuracy. No recorded participation displays `لم يبدأ`. A short coverage tooltip explains changes when content availability changes.

### Historical limits

Snapshots preserve Qudrat prompt/options/correct answer/explanation/passage and Arabic Skills prompt/options/matching items/answer/scoring/revision. Reviews must use those snapshots and stored grading, never current edited answer keys. Qudrat items without answers and Arabic items without `submitted_at` are unanswered, not incorrect. Arabic context is included in prompt text according to `docs/ARABIC_SKILLS.md`; no separate shared-passage table is required.

The migration filenames identify introduction dates, not verified production tracking start dates. There is no demonstrated historical gap in the current capture code, but completeness, deployment date, malformed legacy records and live counts remain unverified. Phase 2 should validate stored-record integrity in an appropriate environment before asserting all-time completeness. If history is incomplete, show supported session metrics and `غير متاح` or a verified tracking-start label; do not backfill invented answers. Missing/malformed snapshots must be labeled unavailable.

## Visual direction and page structure

Read installed Impeccable `SKILL.md`, ran its context command once, and read `reference/shape.md` and `reference/operate.md`. Apply its restrained Operate principles within the supplied detailed brief. The loader found no PRODUCT/DESIGN/surface brief; no design-context files were invented or generated in this audit.

Inspected current Qudrat Blade/CSS and local screenshots `storage/app/qudrat-local/review/{overview-desktop,questions-mobile}.png` and `review-phase5/phase5-overview.png`. These ignored historical captures are visual references, not current rendered verification: overview copy/publication controls and student grid styling differ from current source. Current templates and tokens take precedence.

- Preserve navy `#292a65`, `pr` palette (`tailwind.config.js`), Hacen body typography, existing Judur shell typography, RTL navigation and icon vocabulary. Qudrat supplies white surfaces, slate text, subtle borders, modest radii, compact actions, underlined active navigation, visible focus and restrained progress tracks (`resources/css/qudrat.css`). Use scoped admin-users styles rather than changing Qudrat classes globally.
- Users: compact title/toolbar; debounced name/email search with reset; status/program filters; blocked shortcut and clearly scoped export. Table cells combine avatar/initials, real name link and isolated email, two coverage summaries, last practice, joined and compact action menu. Desktop retains meaningful columns; narrow layouts stack secondary content while preserving every action. Support keyboard, text selection and modifier-click; menus must not trigger row navigation or clip inside table overflow.
- Detail: compact profile/status/date header, state-preserving back link and account menu. Overview has two restrained program summaries. Dedicated Qudrat/Arabic tabs contain dynamic breakdowns and server-paginated history. Keep legacy sections in a reachable secondary group with quiet count badges. URL stores active section and independent pagination. Reviews show historical context, submitted response/correct answer/result/explanation, with distinct unanswered/partial/closed states.
- Keep loading, empty, no-results, unavailable-data and retry states. Use Arabic application locale and configured UTC timezone (`config/app.php`), with exact accessible timestamps and isolated numeric/email fragments.
- The oversized page title/padding originates in `layouts/admin.blade.php`; later phases need optional per-page compact shell slots/variants for users routes only. Their existing script path also differs from Qudrat: adopt the already working single Livewire/Alpine entry pattern only on these routes, checking PowerGrid/export/SweetAlert behavior. Do not globally rewrite the shell or script stack.

## Database, authorization and query plan

No new tracking tables, columns, answer backfill or schema rewrite is required for the requested metrics. Existing snapshots and submissions are sufficient at the code/schema level. Historical Arabic hint usage is unrecoverable and is not required for these metrics; do not add hint tracking merely to label accuracy unassisted.

Create a shared admin metric query service with program-specific SQL aggregates used by list and detail. Aggregate submissions/first attempts/completions before joining users; never call student progress services once per row or hydrate all answers. Existing student services have owner/student authorization assumptions and per-bank queries unsuitable for an admin directory. Compute eligible denominators once per scope, use separate program identities, and retain all-time session history independently of content filters. Avoid caching initially.

Server-sort by joined, last practice and each coverage ratio with stable user-ID tie-breaking; null activity/unavailable coverage sort last in both directions. Default `created_at DESC, id DESC`. Preserve validated query state through detail/back/block/restore, and reset pagination on filter changes. Restrict any return target to the users route and allowlisted query keys.

Existing indexes cover Qudrat `(user_id, question_id, id)`, session owner/type/status, content availability, and Arabic `(question_id, submitted_at, id)` plus session owner/bank/status/mode/model. Candidate additive indexes, subject to EXPLAIN on representative isolated data:

- Qudrat answers `(user_id, question_id, submitted_at, id)` for earliest ordering and `(user_id, submitted_at)` for activity if necessary.
- Arabic session items `(session_id, submitted_at, question_id, id)` for owner-session aggregation; session owner/time indexes if history sorting needs them.
- Users `(role, deleted_at, created_at, id)` for the directory default.

Do not add every candidate blindly. Name/email substring search will not become efficient simply by adding a conventional B-tree. Check aggregates used for sorting across all matching users, not just page hydration. Avoid duplicate indexes and preserve constraints.

Admin routes currently sit behind `auth`, `verified`, `admin`, `log.visit`; `AdminMiddleware` accepts role `admin`. Existing users tables lack the explicit per-Livewire-request Gate checks found in `QudratQuestionsTable::boot()/datasource()`. Add those checks for reads, exports and actions. Scope target accounts to students and handle blocked targets deliberately. Arabic already has admin `viewAny/review` session abilities; add equivalent read-only abilities/routes for Qudrat without widening student-owned `view/update` permissions. Reviews must not invoke student `Practice::visit/show` methods: those can lock or alter session availability. Test direct URLs and component requests.

## Later phases and verification gates

1. **Phase 2:** implement shared metrics and admin review authorization; add only justified indexes. Test repeat submissions, timestamp ties, active/closed/abandoned sessions, availability changes, partial matching, null denominators, mixed program IDs, missing snapshots and denied direct/component access. Keep existing submission/grading/hint behavior intact.
2. **Phase 3:** update existing PowerGrid list and blocked access; preserve XLS export, validate filtered scope and query-string return behavior. Add focused block/unblock/role-scope/export checks and student-named confirmations.
3. **Phase 4:** replace oversized detail presentation within Users, retain enrollment/financial/test/certificate semantics, reuse Arabic review rendering internally and add Qudrat snapshot review under student-scoped Users routes. Both reviews return to the student's relevant tracking tab, not a program management section. Fix affected legacy loading/empty-state issues. Load only selected detailed records.
4. **Phase 5:** rendered Impeccable audit/refinement at desktop/narrow widths, keyboard/menu/error checks, representative query plans and regression/build checks in isolated configuration.

Tests inspected include `tests/Feature/{Qudrat,ArabicSkills}/PracticeTest.php`, Arabic `AdminPagesTest`/`ConcurrencyTest`, Qudrat release tests, and module base cases. These cover substantial session/snapshot/retry behavior beyond old Breeze scaffolding. `QudratTestCase` guards `yarub_qudrat_test_local`; `ArabicSkillsTestCase` reuses it, but each migrates only its own module plus users. Combined admin/legacy tests require a guarded combined schema/fixtures. Global `phpunit.xml` still does not isolate its database: do not run the generic feature suite as-is.

Validation performed here: static route/controller/model/service/schema/test/vendor review, installed skill context, screenshot inspection and source comparison. No Laravel boot, database connection, tests, asset build, downloads, account actions or production access. No live counts, export runtime success, query plans or current rendered pages are claimed verified. No real environment files or credential backups were read.

No unresolved product decision blocks the proposed metric foundation. Runtime validation requires confirming an isolated environment first; repository docs describe a review server that can share an existing local database, so it must not be treated as disposable. Stop here: Phase 2 is a separate requested phase.
