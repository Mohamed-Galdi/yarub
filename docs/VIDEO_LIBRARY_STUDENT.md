# Phase 4 — Student dashboard video catalog

Implemented on 2026-10-26 in isolated development only. This report covers Phase 4 of `YARUB_VIDEO_LIBRARY_IMPLEMENTATION.md`; the player, sharing, login return-to-video, public showcase and navigation entries remain Phase 5 work.

## Changes and architecture

- Added `Student/VideoLibraryController`, the student Blade catalog/results partial, dedicated `videos-student.css` and a small `videos-student.js` module. The existing student layout selects these Vite entries only on video routes. Other learning pages keep their existing bundles and behavior.
- `GET /student-dashboard/videos` (`student.videos.index`) uses session authentication, the existing student middleware and the `LibraryVideoPolicy::viewAny` gate. Guests go to login; administrators use the separate management area. The page is private/no-store. It does not create visitor or learning-progress records.
- `GET /student-dashboard/videos/catalog` (`student.videos.catalog`) uses the existing `VideoLibraryAccess:student` middleware and the same policy. Guests receive JSON 401; wrong-role/deleted accounts receive 403. Its private/no-store response contains only escaped card/pagination HTML, normalized URL/state and count. No generic model serialization, description, storage paths, uploader details, processing diagnostics or video source URLs are returned.
- Both requests use `LibraryVideo::eligible()` and `newestPublished()`: ready, visible and currently published Qudrat category or active Arabic Skills bank; first publication descending, ID descending. No subscription condition was added. Both classification relationships are eager-loaded with only their card fields.
- The single dropdown reads the actual active classifications, grouped under القدرات and مهارات اللغة العربية. Titles and descriptions are searched together with bound Eloquent queries, combined with the selected type. There is no duplicate taxonomy or separate program selector.
- Twelve items per page. Count before selecting rows lets stale/out-of-range pages clamp to the last page without a large offset query. Card queries select only ID, title, type IDs and duration. This scale uses ordinary SQL search; no search service or dependency was introduced.

## URL and interaction contract

`q`, `type` (`q:<category-id>` or `a:<bank-id>`) and `page` restore the same catalog on refresh. Search is trimmed and limited to 200 characters. Non-string/invalid/deleted/inactive types fall back to all; malformed search falls back to empty, malformed pages to 1, excessive pages clamp safely. Canonical URLs omit empty filters/page 1 and discard unrelated parameters.

The native GET form, reset link and Laravel pagination work without JavaScript. Enhancement debounces search by 350 ms, handles IME composition, resets pagination on filter changes and requests the authenticated results endpoint. AbortController plus an immediately incremented revision prevents stale responses from committing, including during debounce. Requests time out after 15 seconds. Only successful responses replace results and commit normalized history; Back/Forward restore controls and fetch their matching results. BFCache restoration refreshes authorization/results.

Loading uses an announced status and `aria-busy` while retaining the current layout. Failure retains inputs and prior results, announces the problem and offers retry. Pagination and successful retries focus the result summary. Separate empty-library/no-results copy explains recovery. Missing covers retain their frame with a text fallback. Keyboard focus, modified links/new-tab behavior and reduced motion are preserved.

Cards are semantic **static articles** with `data-library-video-id` as the Phase 5 integration boundary. There are no fake play buttons, dead links, video elements, source URLs, autoplay previews or player preloads. Covers use existing protected student asset routes, explicit dimensions, 9:16 reserved frames, lazy loading and async decoding. Grid cover cropping is deliberate; no playback crop is introduced. Duration handles hours as well as short clips. Titles wrap fully; descriptions and engagement counters are absent.

No student/public navigation item, related endpoint, selected-video overlay or public catalog was added. Phase 5 must extend this boundary with real accessible activation, selected-video state/history and safe login return handling before adding navigation. An unavailable direct-video state belongs to that player work; Phase 4 does not pretend to implement it.

## UI verification and Impeccable

Used the repository skill at `.agents/skills/impeccable/SKILL.md`, the existing session context, new-work/Operate guidance and craft floor. The precise roadmap permits shaping this local Qudrat extension directly; no new brand, concept tournament or global design-system change was made. Direction is recorded in `.impeccable/surfaces/resources-views-student-videos-index-blade-php.md`.

Actual local browser checks used synthetic student/admin accounts and committed disposable fixtures on `yarub_qudrat_test_local`, PHP 8.3 and loopback MySQL. Media were explicit three-second FFmpeg test patterns and a solid custom cover, not production teaching assets. Checked combined type/search, reset, pagination focus, refresh restoration, Back/Forward, long Arabic titles, zero results, immediate hide-all/true empty state, and real connection failure/retry by stopping/restarting only the review server. No video elements/source URLs are present in the loaded catalog. The browser's scoped evaluator does not expose Resource Timing; this is a DOM/source verification, not a claimed browser network trace.

Reviewed CSS viewport widths 320, 390, 768, 1280 and 1440 with no horizontal document overflow. At 390, cover frames measured 168 × 299 CSS pixels. Native settled viewport captures are retained locally under `.impeccable/review/video-student/` (Git-excluded): `desktop.jpg`, `user-1280.jpg`, `mobile.jpg`, `tablet.jpg`, `filtered-desktop.jpg`, `mobile-page-two.jpg`, `small-mobile-empty.jpg`, `error-mobile.jpg`, `empty-library-mobile.jpg`. The page-two capture intentionally shows the scrolled long titles and pagination. The native screenshot backend returns slightly smaller rasters than the verified CSS viewports; files were saved unchanged, without image resizing or editing. These are viewport captures, not full-page or real-device evidence.

The detector ran once over the new views/CSS/JS: **no findings**. A fresh independent Impeccable finish reviewer inspected all nine captures and the implementation and returned **disposition: ship**, no material fixes. The independent documenter returned **No changes**, confirming inherited Hacen, indigo/slate/white, RTL, control vocabulary and responsive grid. Legacy shell shadows/hover scaling and Qudrat overrides remain outside scope. No `DESIGN.md` or design sidecar was created.

## Acceptance checks performed

All PHP checks used the explicit PHP 8.3 executable and isolated `storage/app/qudrat-local/php.ini` plus the named test configuration, never ordinary root `php artisan test`.

| Check | Actual result |
| --- | --- |
| `vendor/phpunit/phpunit/phpunit -c phpunit.video-library.xml` | 32 tests / 501 assertions passed: foundation, media lifecycle/ranges/authorization, admin pages and six new catalog feature tests. |
| Same configuration, `--filter 'AdminPagesTest\|StudentCatalogTest'` | 10 tests / 140 assertions passed again after tightening withheld-navigation assertions. |
| `-c phpunit.qudrat.xml --filter 'AdminContentTest\|PracticeTest'` | 22 tests / 234 assertions passed. |
| `-c phpunit.arabic-skills.xml --filter 'FoundationTest\|AdminPagesTest\|StudentPagesTest'` | 17 tests / 721 assertions passed. |
| `-c phpunit.learning-public.xml` | 5 tests / 57 assertions passed. |
| `-c phpunit.admin-tracking.xml --filter UsersListTest` | 7 tests / 75 assertions passed, including its intentional simulated query-failure warning. |
| `node --test` over all `tests/Frontend/*.test.mjs` | 15 passed, including six new student state/race/recovery/cover cases. |
| `node tools/qudrat-build.mjs` | Passed; dedicated student CSS/JS in the manifest. Existing outdated Browserslist/datatable safelist notices are unchanged. |
| PHP lint, JS syntax/import checks, Blade rendering | Passed for the changed controller, routes, review helpers and catalog tests; page/fragment rendering exercised in PHP and browser checks. |
| `git diff --check`, new-file whitespace and status review | Passed. Existing line-ending notices are non-blocking; unrelated work preserved. |

Total: **83 distinct PHP tests / 1,588 assertions**, plus **15 frontend tests**. Repeated targeted tests are not counted twice. Feature coverage includes guest/wrong-role/deleted-user denial, both inactive taxonomies, every processing state, hide-after-list, publication tie-breaks, combined description search/type filtering, URL restoration, clamped/malformed/obsolete filters, escaped titles, private field exclusion, lazy protected covers, long durations and absent student navigation/player markup. Frontend coverage includes stale responses during debounce, filter page reset, failed-request preservation/retry, Back, pagination focus, modified clicks, IME, timeouts/session expiry and missing-cover fallback. Browser fixture records/managed media were cleaned up, the review server and dedicated MySQL instance stopped, and the temporary browser tab/viewport override removed.

## Safe reproduction

Use the same guarded development harness documented in `VIDEO_LIBRARY_ADMIN.md`; never boot against the owner's real root environment. Prepare with `tools/video-library-review.php prepare-student` rather than `prepare`, then start its loopback router and open `/student-dashboard/videos`. Only the synthetic student is authenticated by that development router. Production authentication is unchanged and separately tested.

Optional `hide-student` / `show-student` helper commands change only ready videos owned by the recorded synthetic administrator, require a student fixture manifest, and allow the empty-state check. `cleanup` removes the recorded student as well as the fixture administrator/videos/types and managed assets. Do not run database suites while committed browser fixtures exist. Stop the server and run cleanup first. Browser viewport overrides and tabs are restored/closed after review.

## Prerequisites and phase boundary

No new migration, dependency, queue worker or infrastructure is required for Phase 4. A future authorized deployment still needs both previous migrations, private persistent media storage, media tooling, aligned limits, backups and cleanup scheduling from `VIDEO_LIBRARY_MEDIA.md`; production mount/limit facts and real Safari/Android behavior remain unverified. Build the two additional Vite entries with the authorized deployment workflow. Root environment files, production, payments, mail and existing learning records were not used or changed. Unrelated roadmap deletion and prior implementation work are preserved.

Next: **Phase 5 — Student player, sharing, and public showcase**. Stop after Phase 4 until requested.

The subsequently requested Phase 5 implementation is recorded in [VIDEO_LIBRARY_PLAYER.md](VIDEO_LIBRARY_PLAYER.md); its player/navigation behavior supersedes the staging boundaries in this historical Phase 4 report.
