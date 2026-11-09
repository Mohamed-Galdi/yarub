# Admin learning dashboard

`GET /admin-dashboard?days=30` uses the existing admin middleware and the
`view-student-tracking` gate. Supported periods are 7, 30 (default), and 90 days.
`DashboardAnalytics` provides aggregates; Blade renders the chart and rankings
without adding a JavaScript chart library or network dependency.

## Metric definitions

All student metrics exclude admins and soft-deleted students. Consequently,
deleting or restoring a student can change historical reports. Periods use the
application timezone, from midnight on the first day through the snapshot time
on the last day. Today is partial. The previous comparison has the same elapsed
duration and final-day cutoff, shifted back by the selected number of days.

| Metric | Source and interpretation |
| --- | --- |
| Active learners | Distinct students submitting a Qudrat answer, Arabic Skills response, existing course/lesson test attempt, or successfully loading a library video player during the period. Uses the existing `ProgramMetrics::submissions()` definition, including recorded Arabic skips. Page visits, sign-ins, enrollment, and merely opening a session do not count. |
| Repeat learners | Active students with recorded activity on at least two distinct calendar dates within the period; this is repeat participation, not cohort retention. |
| Learning interactions | Number of those submissions/test attempts and video opens. Repeat practice counts again. This combines different action types; it is not time spent or mastery. |
| Daily trend | Distinct active learners per day, deduplicated across programs and tests, with zero-filled dates and an accessible detailed table. Daily counts must not be summed to obtain unique period learners. |
| Completed sessions | Qudrat/Arabic sessions with `status=completed` and `completed_at` inside the period, including sessions started earlier. |
| Program/category participation | Distinct students submitting answers during the period, then submission volume. Historical hidden categories are retained and labeled. Published/active categories with no submissions remain visible at zero. Bars are scaled within each program. |
| Session completion rate | Completed sessions among sessions **started in the selected period**, as of the snapshot. Denominator includes active and closed/abandoned sessions. Both numerator and denominator are shown; zero starts displays a dash. Includes practice/review/model modes. It is not a completion-time cohort adjusted for how recently sessions started, nor an accuracy measure. |
| Course learning | Distinct students and attempt counts from tests linked to the course, submitted during the period, regardless of enrollment date. |
| Course demand | Distinct student/course enrollments created in the period. Duplicate subscription records do not inflate learner counts. This is not watching, revenue, or a test conversion rate. |
| Course rankings | Top ten by test participants, then new enrollment. The secondary list shows up to five published courses without test submissions, ordered by lowest new enrollment. Courses without tests can appear here; no quality judgment is implied. |
| Video opens | Recorded player openings and distinct student viewers during the period, ranked by video and grouped by current category. Current eligible video count remains independent of reporting window. |
| Platform context | Student totals, new student counts, and published-course totals are omitted from the dashboard at the user’s request. |

No financial information or explanation ranking is displayed. Existing financial,
explanation, exports, and student-tracking pages remain available through their
existing navigation and routes.

## Query and cache behavior

Aggregations run in SQL. No answer/session history is loaded into PHP. The daily
series is bounded to 90 entries; the course lists are bounded to ten/five. Category
rankings load the category catalog with grouped results, with local scrolling for
long lists. Video titles are eager-loaded to respect lazy-loading prevention.
Query count is independent of reporting days and learner count.

The controller caches a snapshot for 120 seconds per reporting window and date,
using the configured cache store. The header shows its actual cutoff; two minutes
is a cache lifetime, not background polling. A normal page request refreshes an
expired snapshot. Authorization is checked even on cache hits. No personal student
details are included in the payload. Video-open tracking requires the new `video_views` migration and writes one row per distinct opening.

At significant production volumes, inspect MySQL EXPLAIN plans and real timings
before adding indexes or pre-aggregated daily tables. Existing source migrations
primarily index session ownership and question history, rather than global date
reporting. Potential date indexes are `submitted_at` on both answer tables,
`started_at`/`completed_at` on both session tables, and `created_at` on attempts and
subscriptions. These are proposals, not schema changes shipped in this task.

## Simple video-open tracking

Library views are successful player-metadata loads for authenticated students,
not proof of playback. Reopening counts again; pause, seek, play, and retries within
one opening do not. Public examples and admin previews do not supply the tracking
endpoint. Existing policies reject hidden/unavailable videos and non-students.

The player POSTs to `student.videos.views` with a UUID opening ID and CSRF token.
The server supplies the user and timestamp, validates the UUID, rate-limits to
60 requests/minute, re-checks video access, and deduplicates by user/opening ID.
GET metadata and media delivery remain read-only. Failed analytics requests do not
block playback; transient requests can retry with the same opening ID. Offline or
blocked requests may be missed; no durable offline queue is added.

`video_views` stores video, student, opening ID, and `viewed_at`, with date and
student/date indexes. It stores no IP address or user agent. Hard deletion of the
video/student cascades to views; soft-deleted students are excluded from reporting.
Categories reflect the video's current category, and replacing its file retains
its opens. There is no background job or aggregation service.

Dashboard totals, period-unique viewers, top ten videos (including available zero-view
videos when space permits), and category comparisons use recorded opens. Video opens
also count toward active students, repeat activity, daily trends, and interactions.
A student appearing in practice and video activity is counted once in active totals.
Category detail is collapsed to keep the dashboard concise. Available-video count is
current; activity figures follow the selected reporting period. No historical views
are fabricated. Course playback remains untracked; watch time and video completion
are intentionally out of scope.

Before enabling the updated application, apply
`2026_11_09_000001_create_video_views_table.php` to the intended database using the
project's verified deployment process and build frontend assets. The migration has
only been executed against fresh in-memory test databases here, never against an
existing local or production database. Deploy schema before code that queries it.

## Verification

Run the focused suite without Laravel boot or real environment loading:

```powershell
php vendor/phpunit/phpunit/phpunit --no-configuration --bootstrap vendor/autoload.php --do-not-cache-result tests/Unit/DashboardAnalyticsTest.php
```

The suite creates only a new SQLite `:memory:` database with minimal schema contracts.
It covers empty periods, deduplication, account exclusions, cross-day participation,
completion cohorts, distinct enrollments, period boundaries, authorization, video
eligibility/eager loading, and fixed query count across reporting windows.
PHP 8.3: six focused tests, 26 assertions pass; the full isolated Unit directory
also passes (28 tests, 145 assertions). PHP 8.4 emits existing dependency deprecations.

The Vite build passed using an empty dedicated `envDir` under
`storage/app/dashboard-review`; no real environment file was read. Existing
Browserslist/safelist warnings remain. Isolated Blade renders and browser checks
cover populated and empty states at 1440, 390, and 320 px; period links and the daily
data disclosure were exercised. Screenshots and temporary fixtures remain in that
ignored runtime directory. No production data or integration was used. SQLite
verification does not replace MySQL plan/load testing on production-scale data.


## Presentation refinement

The overview uses one divided metric surface with an indigo lead metric, separate
program comparison surfaces, and a compact activity chart. Repeated descriptions
and ranking explanations are consolidated in the closed `تعريف المؤشرات`
disclosure. Watching limitations remain visible beside course/library data.
This is a dashboard-local presentation change; shared design tokens and metric
calculations are unchanged.


Video-open verification: 32 isolated PHP unit tests (155 assertions), including
migration creation, duplicate openings, reopenings, video eligibility, account access,
and activity/ranking aggregation; 13 player tests, including retry IDs, stale/failed
metadata loads and non-blocking tracking failures. No live media or production
integration was called.
