# YARUB project overview

Repository inspection at `a0efd0b`, 2026-10-21. Facts below come from tracked source unless marked otherwise. No production, database, real `.env`, or external service was inspected. ARRIB is a separate client project.

## Stack

| Component | Manifest constraint | Locked version |
| --- | --- | --- |
| PHP | `^8.1` | Runtime is not locked; local CLI is 8.4.5 |
| Laravel | `^10.10` | 10.48.22 |
| Livewire / PowerGrid | `^3.5` / `^5.9` | 3.5.2 / 5.9.0 |
| WireUI / SweetAlert / Flatpickr | `^2.1.3` / `^7.2` / `^2.2` | 2.2.1 / 7.2.0 / 2.2 |
| Sanctum / Tinker | `^3.3` / `^2.8` | 3.3.3 / 2.9.0 |
| Guzzle / Flysystem S3 | `^7.2` / `3.0` | 7.9.2 / 3.0.0 |
| Laravel Excel / PhpSpreadsheet / OpenSpout | `^3.1` / `^1.29.1` / `^4.24` | 3.1.56 / 1.29.2 / 4.24.5 |
| Breeze / PHPUnit / Pint (development) | `^1.29` / `^10.1` / `^1.0` | 1.29.1 / 10.5.24 / 1.16.1 |
| Vite / Laravel Vite plugin | `^5.4.10` / `^1.0.0` | 5.4.10 / 1.0.5 |
| Tailwind / Alpine / Alpine focus | `^3.1.0` / `^3.4.2` / `^3.14.1` | 3.4.10 / 3.14.1 / 3.14.1 |
| Axios / Flowbite / Rollup / Video.js | `^1.3.1` / `^2.4.1` / `^4.24.4` / `^8.17.3` | 1.7.5 / 2.5.1 / 4.24.4 / 8.17.3 |

Versions are from `composer.lock` and `package-lock.json`, not a verification of production's installed packages. Composer's root PHP constraint is insufficient by itself: locked Symfony components require PHP >=8.2, while OpenSpout permits only PHP 8.1/8.2/8.3. Confirm a compatible PHP 8.2/8.3 runtime and required extensions against the entire lockfile. Debugbar, IDE Helper, Sail, and Ignition are development dependencies; no container deployment recipe was found.

## Users and request architecture

`RouteServiceProvider` loads `web.php` with sessions/CSRF and `api.php` under `/api`. `web.php` includes admin, student, and auth routes. One `users` table and web session guard serve both `student` and `admin` roles; custom middleware compares role strings. User ID 1 with role `admin` is the hard-coded super-admin and manages other admins. No policy mappings are configured.

Registration takes `first_name` + `last_name`, creates a student, emits `Registered`, logs in, and redirects to courses. `/login` handles students; `/yarub-moshref-center` handles admins. `LoginRequest` throttles by email/IP after five failures. Password reset/confirmation routes come from Breeze. Dashboards declare `auth`, `verified`, role, and `log.visit` middleware, but `User` does **not** implement `MustVerifyEmail`, so the presence of `verified` does not establish enforced verification.

`LogVisit` records IP/user agent at most once per IP in a rolling 24 hours on covered routes. `ViewServiceProvider` loads the first `ContactPage` for the guest layout; page controllers likewise assume CMS records exist. An empty database is not enough to render the site reliably. Application locale is Arabic, fallback English, timezone UTC (`config/app.php`).

## Main journeys and modules

| Journey / routes | How it fits together |
| --- | --- |
| `/`, `/about`, `/contact`, `/courses`, `/lessons`, item detail pages | Root page controllers query CMS records and published catalog lists; search/type filters, pagination, reviews and suggested products feed `resources/views/home`. `POST /guest-messages` stores about/contact submissions. |
| `/cart` and `POST /cart/{add,update,remove,apply-coupon}` | `CartController` stores item details/prices, lesson plan, coupon and discount in the session. Coupons support percentage/fixed discounts and all/course/lesson/specific applicability. Cart changes reset discounts. |
| `GET /subscription`, `/thanks` | Cart Blade initializes Moyasar credit-card payment in SAR, multiplying displayed amount by 100. Callback goes to `SubController`, which checks login and callback fields, then transactionally writes Payment plus course/lesson subscriptions, increments coupon use and clears cart on `paid` + `APPROVED`. Other statuses show failure or return to cart. This is a browser return flow; no server verification/webhook implementation was found. |
| `/student-dashboard/{courses,lessons}` and detail/rating routes | Student controllers query subscriptions and show content/video with reviews. Lessons list uses active status and month/year expiry; detail controllers check membership only. Templates concatenate CloudFront domain with stored content URL and use Plyr playback. Courses and lessons are separate purchasable products; a lesson is not a child of a course. |
| `/student-dashboard/tests/{test}` with submit/result routes | Tests are attached to subscribed courses/lessons via `User::availableTests()`. Take page checks eligibility and prior attempt; submit scores answers as a percentage and stores a JSON answer map. Duplicate attempts are checked in controller code. Results compare saved answers with current questions. |
| `/student-dashboard/certificates`, public `/certificates/{view,download}/{id}` | Admin grants certificates to a user for a course or lesson. Public sharing is intentional in route comments. Download returns a Blade page using browser print/JavaScript PDF logic, not a server PDF generator. No automatic award on test pass was found. |
| `/student-dashboard/support`, `/admin-dashboard/support` | Conversations belong to students; messages carry a `student`/`admin` sender enum. Message creation hooks update last-message time and opposite-side unread counts; reading resets counts. Admin can initiate messages for selected students or all students. This is database-backed support, with no custom queued delivery found. |
| `/student-dashboard/profile`, `/admin-dashboard/account` | Student profile/avatar/password updates; separate admin account operations with super-admin checks. Users are soft-deleted/restored from admin student pages; admin deletion uses force-delete. |
| `/admin-dashboard` and courses/lessons/tests/coupons/pages/students/payments/data-export | Controllers manage catalog publication, content, tests/questions, discounts, CMS sections/FAQs/partners/testimonials, students, payments and analytics. Dashboard queries use MySQL `DATE_FORMAT`. PowerGrid tables provide search/pagination/export; date-filtered XLSX exports use `app/Exports` through Laravel Excel. |

The only declared API endpoint is `GET /api/user` with `auth:sanctum`. Sanctum installation does not imply a separate SPA/API architecture. Routes include some unused/stub controller methods; follow registered actions rather than method names alone.

## Data relationships

- `User` has payments, test attempts, certificates, reviews, progress and conversations; uses soft deletes. Payment and subscription user relations include trashed users for history.
- `student_course_sub` joins user/course/payment with `cost`, `is_active`, timestamps. `student_lesson_sub` additionally stores `sub_plan` (`monthly`/`annual`). These are explicit subscription models as well as Eloquent many-to-many pivots. There is no recurring billing job in the inspected code; expiry is calculated from subscription creation time.
- `Course` and `Lesson` each have content, tests, certificates and polymorphic reviews/progress. `contents`, `tests`, and `certificates` have nullable course/lesson foreign keys; the DB does not enforce exactly one parent.
- Tests have four-option questions with answer values `1`–`4`. Attempts join user/test and store answers as JSON plus integer score. No unique user/test constraint is declared.
- Coupons join selected courses/lessons through `coupon_course` and `coupon_lesson`. Payments store provider ID/status/message and amounts/discount metadata; provider ID is not declared unique, and amount columns are strings. Catalog prices are decimal columns but cast to integers; subscription costs are integers.
- Conversations have many messages, unread counters and status; guest messages are independent submissions. CMS uses `home_pages`, `home_page_reviews`, `about_pages`, `contact_pages`, `partners`, and `f_a_q_s`.
- Migrations are historical schema evidence, not proof of the current production schema. Several foreign keys cascade deletes. The content migration creates `contents` but its rollback drops `content`; do not assume rollback safety.

## Integrations, assets and configuration

| Area | Settings / behavior |
| --- | --- |
| Core and database | `APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_DEBUG`, `APP_URL`, `ASSET_URL`; `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`. MySQL is the example/default and required by existing dashboard SQL. |
| Session/cache/logging | `SESSION_DRIVER`, `SESSION_LIFETIME`, `CACHE_DRIVER`, `LOG_CHANNEL`, `LOG_LEVEL`; Redis/Memcached options exist in config. Example defaults are file sessions/cache, not verified production values. |
| Moyasar | `MOYASAR_TEST_KEY`, `MOYASAR_KEY` in `config/services.php`; current cart explicitly reads `services.moyasar.test_key`. Actual key mode is unknown; names do not prove test/live status. Neither is in `.env.example`. Only the intended publishable key belongs in the browser. |
| Media storage | `FILESYSTEM_DISK`, `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `AWS_BUCKET`, `AWS_URL`, `AWS_ENDPOINT`, `AWS_USE_PATH_STYLE_ENDPOINT`; direct controller `env('AWS_CLOUDFRONT_DOMAIN')` reads (missing from example). Public avatars/testimonial images use `storage/app/public` and `/storage`. |
| Mail | `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`; optional `MAILGUN_*`, `POSTMARK_TOKEN`, SES config exists. Actual production transport/delivery is unverified. |
| Queues/broadcasting | `QUEUE_CONNECTION` defaults/example to `sync`; database/Redis/SQS configuration exists, but no custom jobs/`ShouldQueue` usage was found. `failed_jobs` migration exists; no jobs table migration found. `BROADCAST_DRIVER`, `PUSHER_*`, `VITE_PUSHER_*` are scaffold configuration; browser Echo/Pusher code is commented out. |

Admin uploads first write to public `temp_videos`, then stream to S3 `course_videos`/`lesson_videos` and store object paths in `Content.url`. Both controllers clean the shared temp folder. Replacement/deletion code inconsistently selects public versus S3 disks; test storage operations carefully with isolated buckets. No live storage request was made.

Vite inputs are `resources/css/app.css` and `resources/js/app.js`. JavaScript imports Axios, Flowbite, Alpine/focus and Composer-vendored PowerGrid assets; Tailwind presets also reference WireUI/PowerGrid under `vendor`. Layouts separately load CDN scripts (Moyasar 1.14.0, Plyr 3.7.8, jQuery, SweetAlert, Alpine, Preline and charts), sometimes with floating versions or duplicate bundled libraries. CDN dependencies are not fully represented by npm locks. Vimeo-named legacy views exist but are not evidence of an active Vimeo API integration.

`app/Console/Kernel.php` has no active scheduled tasks. `routes/console.php` supplies the scaffold `inspire`; `message:send-demo` actually saves a message despite its description claiming otherwise. Server cron/process-manager state remains unknown.

## Verification gaps and follow-up work

- **Payment integrity:** no provider fetch/signature verification or replay guard found in `SubController`; cart prices originate in request data, then session. Review these separately before payment changes. Coupon usage limit is stored but not checked by `applyCoupon`; specific-item applicability also needs cases for overlapping course/lesson IDs.
- **Authorization consistency:** lesson list expiry/active filtering differs from detail membership checks, and test availability uses unfiltered subscriptions. Test submission lacks the eligibility check used by the take page; support reply POST lacks the ownership check present on its GET. Ratings and some content operations also need focused authorization review. These are static findings, not exploited or runtime-tested.
- **Authentication/test drift:** `MustVerifyEmail` is absent; `RouteServiceProvider::HOME` remains `/dashboard` without a matching route. Breeze tests expect that redirect, `name` registration input and `/profile` endpoints, whereas the application uses different inputs/routes. Duplicate `student.conversations.create` route names and `/admin-dashboard/certificates/pdf` pointing at a missing `pdf` method need verification before route caching.
- **Fresh local setup:** CMS singleton data and default images are assumed. Seeders populate many demo records; do not run them on an existing DB. Confirm the minimal fixture set and actual production schema independently. No progress update route/controller was found despite the Progress model/table.
- **Dependencies:** this is an old dependency snapshot with Debugbar/other development tooling and external CDN scripts. No current advisory audit or support-lifecycle assessment was performed. Treat security remediation as a separate scoped task; do not infer that the lockfiles or a unit pass establish security or PHP 8.4 compatibility.
- **Operations:** confirm production PHP/extensions, web server/document root, asset build location, caches, storage link/permissions, mail/payment mode, backup/restore process, worker/cron state and restart requirements. See [deployment](DEPLOYMENT.md).

## Local LSP artifact

At inspection, `../lsp-48ac232383192778.php.local-backup` was absent; `storage/framework/lsp-48ac232383192778.php` was still present and untracked (3,107 bytes). It was read as text only. It defines reflection/path helpers, conditionally loads Pest test configuration, inspects test traits/custom expectations and outputs JSON: likely a PHP language-server/editor metadata probe, not application functionality. Exact generating extension is unverified. No reference was found in app/bootstrap/routes/config/composer.json/tests.

It can likely be deleted when the editor/language-server task using it has finished; the editor may recreate it. Keep it out of Git and deployment. Neither deletion nor moving was performed, preserving the pre-existing local state. Contents and local literal values are intentionally not reproduced.

Inspection checks: manifests/locks, route/middleware/controller/model/migration sources, main views/assets/providers, integrations, exports/tables, scheduling/queue config, seed entry point, and existing tests; Git baseline/status and environment-file ignore/tracking checks. The isolated PHPUnit Unit suite passed (one placeholder assertion). Laravel feature tests, build, migrations, seeds, dependency installation/audits, deployment and live integrations were not run.
