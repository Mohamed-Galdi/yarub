# Admin Users tracking — Phase 2

Implemented 2026-10-25. This phase adds the backend foundation for tracking inside **Users**. It does not redesign any page, change student practice behavior, or deploy anything.

## Foundation

- `app/Services/Admin/Tracking/ProgramMetrics.php`: SQL aggregates for distinct current coverage, chronological first-answer accuracy, saved participation, completed sessions and last practice. Type rows come from Qudrat categories / Arabic Skills banks, including unavailable sections with retained history. No fixed bank totals or per-student history hydration.
- `StudentTracking.php`: shared directory/detail calculations, admin-only student lookup including blocked accounts, server-side search/status/participation filters, bounded pagination and stable sorting. The directory takes two database queries (count and page), independent of page size. No caching/invalidation layer was introduced.
- `SessionRecords.php`: paginated session summaries and read-only historical review. It reads saved snapshots and submissions, never invokes student practice methods, and never changes session status or grading. The URL's student must own the session. Unanswered records, partial matching results, unavailable snapshots and non-final scores are explicit. Invalid/zero score denominators return no percentage.
- `StudentTrackingController.php` and `routes/admin.php`: JSON endpoints beneath Users, ready for the existing users pages to consume in Phases 3–4. Responses explicitly project user fields; password/token fields are not returned. Existing program-section screens and routes remain separate from this Users navigation flow.
- `AuthServiceProvider.php`: `view-student-tracking` Gate accepts existing admin role only. `QudratPracticeSessionPolicy.php` adds admin read-only `viewAny/review` without expanding student-owned `view/update`. Existing Arabic review authorization is retained. Both users PowerGrid components now reauthorize on each Livewire lifecycle and datasource access, including export requests.

## Users endpoints

All routes inherit `auth`, `verified`, `admin`, `log.visit`; service methods additionally authorize admin access. GET requests do not mutate learning records. Existing visit logging still applies to HTTP requests.

| Route name | GET path under `/admin-dashboard` | Data |
| --- | --- | --- |
| `admin.students.tracking.index` | `/students/tracking` | Paginated student summaries |
| `admin.students.tracking.show` | `/students/{student}/tracking` | One student and both program summaries |
| `admin.students.tracking.program` | `/students/{student}/tracking/{program}` | Type breakdown and paginated session history |
| `admin.students.tracking.review` | `/students/{student}/tracking/{program}/sessions/{session}` | Historical session summary and question review |

`program` is `qudrat` or `arabic_skills`. Student/session parameters are numeric and cross-student session access returns 404, including for admins. Non-student accounts are not tracking targets; blocked students remain reviewable by admins.

Directory query parameters: `search` (name/email), `status=active|blocked|all` (default active), `participation=all|qudrat|arabic_skills|neither`, `sort=joined|last_practice|qudrat_coverage|arabic_skills_coverage`, `direction=asc|desc`, `page`, and `per_page` (1–100; default 20). Default ordering is registration date descending then ID descending. Unavailable sorting values follow known values in both directions; ID breaks ties. Program history accepts `type_id` and `page`, uses 20 rows per page, and sorts start time then ID descending. Pagination retains query parameters. These endpoints support the future Users UI; no second users directory was added.

## Metric and history contract

- Participation requires a submitted answer; starting/viewing/selecting a draft/requesting a hint does not qualify. Saved submissions in unfinished, closed and abandoned sessions count. Repeated submissions to the same question do not inflate coverage.
- Coverage joins distinct submitted question IDs to current eligibility: published, unarchived Qudrat questions in published categories; active Arabic questions in active banks. Hidden content is removed from numerator and denominator, while participation, timestamps and history remain. A matching group counts as one question.
- First answers are ordered by `submitted_at`, then submission/item ID, independently in each program. Partial matching credit is not a correct first answer. Arabic points remain point-based session scores; Qudrat retains correct/answered accuracy and answered/total separately. Hints do not deduct correctness in existing grading; Arabic historical hint usage remains unknown.
- `coverage=null` means no eligible questions; `first_attempt_accuracy=null` means no known first answers or incomplete first-result evidence. The UI must render `غير متاح` for these values and `لم يبدأ` for no participation. `first_answer_results_known` describes retained eligible records only; `history_scope=recorded_submissions` does **not** certify that no earlier history is missing.
- Last practice is the latest submitted-answer or completed-session timestamp. Start, generic update, close and abandon times do not substitute for learning activity. Completion counts require the existing `completed` status.
- Reviews preserve snapshot prompt/options/correct answer/explanation and Qudrat passage context. Arabic prompts retain their original context and matching data. Current type/model titles are explicitly named `current_type_title` / `current_model_title`; they are not presented as historical snapshots. Invalid snapshots return `snapshot_available=false` and no substituted question. Draft selections are not exposed as submitted responses.
- Historical tracking start dates and production completeness were not inferred or backfilled. No production data was read. Pre-module attempts cannot be reconstructed, and Arabic hint-use history cannot be recovered. A later UI must keep these limits clear rather than label the metric all-time or unassisted.

## Schema and performance

No application migration, new tracking table/column or capture change was needed. Existing transactional, immutable submission flows already contain the required evidence. Existing content/owner/answer indexes are retained; no speculative indexes were added. Window functions follow the MySQL capability already used by Arabic Skills.

The focused tests verify a constant two-query directory at different page sizes, SQL pagination/filtering/sorting, and matching list/detail values. This establishes the absence of per-row query growth; it does not establish latency at production volume. Representative EXPLAIN/volume checks remain a Phase 5 gate. Reassess the Phase 1 candidate indexes against those results rather than adding all of them now.

## Verification

The new `tests/AdminTrackingTestCase.php` uses the guarded existing test environment, verifies loopback port **33317** and database **yarub_qudrat_test_local** before migrations, and migrates only users plus the two program schemas. Fixtures use transactions/rollback; existing databases are not reset. `phpunit.admin-tracking.xml` avoids the unsafe generic feature-suite configuration.

Run with the repository's PHP 8.3 runtime and generated isolated PHP configuration:

```powershell
& "$env:USERPROFILE/.config/herd/bin/php83/php.exe" -c storage/app/qudrat-local/php.ini vendor/phpunit/phpunit/phpunit -c phpunit.admin-tracking.xml
```

Focused coverage includes retries, repeated attempts, earliest timestamps and ties, unknown first results, active/closed/abandoned sessions, missing/archived content, stopped banks, zero denominators, partial matching scores, overlapping question IDs across programs, different owners, blocked students, snapshot edits/missing snapshots, read-only reviews, pagination and direct/component authorization.

Final verification on PHP 8.3.11 / the dedicated MySQL test database:

| Check | Result |
| --- | --- |
| New admin tracking suite | 14 tests, 165 assertions passed |
| Existing Qudrat suite | 63 tests, 529 assertions passed |
| Existing Arabic Skills suite | 46 tests, 13,150 assertions passed |
| PHP syntax checks | All 11 changed/new PHP files passed |
| Targeted Pint checks | All 8 new/already-formatted PHP files passed; unrelated formatting in the legacy users tables was preserved |
| `git diff --check` | Passed |

The focused suite also confirms admin initial table rendering and denies exports after the acting user changes to a student, exercising authorization on subsequent Livewire requests. Tests used the dedicated database; production history and production-volume query plans were not inspected. No frontend build/browser pass was performed for this backend-only phase; rendered Users UI work remains Phase 3 onward. No production credentials, real application environment files or backups were inspected, and no deployment/payment/mail operations occurred.

Next phase: **Phase 3 — redesign the existing Users list**, consuming this shared foundation. Do not begin automatically.
