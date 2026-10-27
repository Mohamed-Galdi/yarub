# Video library — Phase 3 admin management

Completed in isolated development on 2026-10-26. Scope: Phase 3 only of `YARUB_VIDEO_LIBRARY_IMPLEMENTATION.md`. The student catalog, student player, public showcase and their navigation remain unimplemented. Production has not been accessed or changed.

## Changes

- `Admin/VideoLibraryController` provides the admin directory, create and edit pages. Search covers title/description; a single grouped dropdown reads actual Qudrat categories/Arabic Skills banks; visibility filtering combines with search/type. Pagination is 12 rows, newest upload first with ID tie-breaker. Filter state survives edit/back links; a page emptied by deletion redirects to the last valid page. Relationships are eager loaded and eligibility is queried in one batch.
- `resources/views/admin/videos/` contains the directory, separate editing/upload page, file controls, progress feedback and native preview dialog. The table shows portrait cover, title, program/type, duration, student availability, independent processing/error state, first publication and actions. Mobile uses labeled summaries. Selected-example notices explicitly distinguish selection from current guest eligibility.
- `videos-admin.css` and `videos-admin.js` are dedicated Vite entries, loaded only on video admin pages. The shared admin layout adds **الفيديوهات** using its existing navigation component. New page routes use existing auth/verified/admin middleware and management policies; media/JSON routes retain Phase 2 authorization and JSON errors. Existing public/student navigation and authentication behavior are unchanged.
- Upload and replacement use real XHR body-upload progress, followed by an indeterminate inspection/poster phase. Forms disable duplicate submission, retain inputs/files after recoverable errors, map category validation to the grouped selector and keep request UUIDs across retries. Operation polling handles an already-running request. Metadata completion is independent of a concurrent media operation. Safe success messages survive reload; errors stay beside fields and in an accessible summary.
- Local selected files have object-URL previews, released on replacement/page exit. Covers can be replaced or reset to a generated frame; source replacement preserves custom covers. Protected preview uses native controls in a labeled dialog; Escape/close stops playback, releases src, restores focus and unlocks background scrolling. Delete confirmation names the video and sends the exact title required by the existing backend. Public-example selection warns that the item will be visible to guests; selection/removal, hide/show and deletion use existing CSRF-protected endpoints.
- One focused Phase 2 service correction: retrying a failed initial upload with the same request UUID now applies corrected title/description/category before processing, retaining the same record. A regression test verifies corrected metadata/program and no duplicates.

## Design review

Used the project's [Impeccable skill](../.agents/skills/impeccable/SKILL.md), its new-work, Operate and craft-floor guidance. This is a precisely specified extension of the existing Qudrat world, with structure and theme pinned by the user contract; no redesign or concept tournament was needed. Confirmed product facts are recorded in `PRODUCT.md`, and the surface direction in `.impeccable/surfaces/resources-views-admin-videos-index-blade-php.md`.

Preserved Hacen Arabic typography, Qudrat navy/slate/white surfaces, modest borders/radii, native controls and visible focus. No shipping raster design assets were added. Covers and test-pattern videos used in QA are synthetic application data.

The independent finish reviewer returned **disposition: ship**, with no material fixes. It accepted all eleven named viewport captures and the responsive adaptations. The independent documenter confirmed the extension matches incumbent tokens and left global design files unchanged. Pre-existing Hacen weight declarations and sidebar decoration/long-email behavior were not repaired or canonized as new design rules.

The detector ran once over the new views/CSS/JS. It flagged a conditional cover image as potentially lacking src. The markup was corrected to render an existing-cover image only when a source exists; the selected-cover preview creates an image only after receiving a real local object URL. No placeholder image URL was introduced.

## Verification

Real local browser testing used the complete backend with synthetic data and actual multipart requests/CSRF, not a static mock:

- Invalid initial upload displayed field feedback while retaining title, description, type and file selection; valid retry uploaded H.264 MP4 and a custom cover.
- Saved metadata, replaced the source, replaced the cover, regenerated a custom cover, and verified failed replacement kept the prior protected source and metadata.
- Hid/showed a video; selected/replaced/removed the single public example; confirmed both cancel and delete with the named item; verified successful deletion feedback and filtered-empty results.
- Combined type/visibility filters, searched titles, reset filters and navigated pagination. Rendering included long Arabic titles, failed items, hidden items, custom/generated covers and missing descriptions.
- Preview played the actual three-second fixture (`readyState=4`, duration=3, error=null). Closing paused playback, removed src and returned focus to the initiating preview button.
- Reviewed widths **320, 390, 768, 1280 and 1440 px** with no horizontal document overflow. Arabic fonts were awaited and sidebar transitions settled for evidence. Viewport overrides were reset afterward.

Evidence is stored locally under `.impeccable/review/video-admin/` (excluded from Git): directory desktop/mobile/tablet/default viewport, create/edit, validation, empty results, playback and delete confirmation. Browser full-page stitching produced invalid RTL/positioning artifacts; those were replaced with settled viewport screenshots. These are viewport evidence, not claimed full-page captures or real-device coverage. Browser tooling briefly captured before fonts/transitions settled; those shots were also replaced before the independent review.

Automated checks:

| Check | Result |
| --- | --- |
| Video library PHPUnit suite (`phpunit.video-library.xml`) | 26 tests / 420 assertions passed, including four new admin page tests and media retry correction. |
| Qudrat (`phpunit.qudrat.xml`, AdminContentTest/PracticeTest) | 22 tests / 234 assertions passed. |
| Arabic Skills (`phpunit.arabic-skills.xml`, FoundationTest/AdminPagesTest/StudentPagesTest) | 17 tests / 721 assertions passed. |
| Public pages/login (`phpunit.learning-public.xml`) | 5 tests / 57 assertions passed. |
| Existing admin Users list (`phpunit.admin-tracking.xml`, UsersListTest) | 7 tests / 75 assertions passed, including expected simulated query-failure recovery. |
| Frontend Node suite (`node --test` over `tests/Frontend/*.test.mjs`) | 9 tests passed, including four new progress/retry/validation/concurrent-metadata cases. |
| Isolated Vite build (`node tools/qudrat-build.mjs`) | Passed; both new entries in manifest. Existing Browserslist age and Tailwind datatable safelist notices remain non-blocking. |
| PHP syntax and `node --check resources/js/videos-admin.js` | Passed. Blade rendering is covered by page tests and the browser workflow. |
| `git diff --check` and focused status/diff review | Passed; unrelated pre-existing roadmap deletion and untracked implementation contract preserved. |

Total: **77 PHP tests / 1,507 assertions**, plus **9 frontend tests** passed. The isolated MySQL instance started for verification was stopped after the final checks.

## Reproduce the browser review safely

`tools/video-library-review.php` and `tools/video-library-router.php` are development-only helpers outside public/. They reuse the guarded test bootstrap, require the exact disposable `yarub_qudrat_test_local` schema on loopback, and never load the owner's real root environment. The review router accepts loopback requests under PHP's cli-server only and uses a synthetic fixture administrator for this UI review; it is not a production authentication entry point. Feature tests separately verify guest/student denial.

Do not run the browser fixture and assertion-counting database suites simultaneously: browser fixtures are committed so HTTP requests can see them. Clean them up first. The setup refuses to overwrite an existing fixture manifest or mix with existing video test data, and cleanup removes only the fixture-owned videos/accounts/types and their managed files.

```powershell
./tools/qudrat-local.ps1 start
& "$env:USERPROFILE/.config/herd/bin/php83/php.exe" -c storage/app/qudrat-local/php.ini tools/video-library-review.php prepare
node tools/qudrat-build.mjs
& "$env:USERPROFILE/.config/herd/bin/php83/php.exe" -c storage/app/qudrat-local/php.ini -d upload_max_filesize=250M -d post_max_size=260M -d max_execution_time=120 -S 127.0.0.1:8026 -t public tools/video-library-router.php
# Open http://127.0.0.1:8026/admin-dashboard/videos
# Stop the review server before final cleanup/testing.
& "$env:USERPROFILE/.config/herd/bin/php83/php.exe" -c storage/app/qudrat-local/php.ini tools/video-library-review.php cleanup
```

The private disk/reserve relaxation is confined to the disposable review directory. Actual HTTP upload/post limits were raised only with process-local `-d` flags on this loopback server. No project/production environment values were edited. The fixture rows and managed media were removed after browser checks; the review server was stopped.

## Remaining prerequisites and handoff

No new migration or dependency is required by Phase 3. A later authorized release needs the new Vite entries built, the two previously reviewed migrations present, and the Phase 2 private persistent storage, FFmpeg/GD, upload/time/memory limits, backups and cleanup scheduling prerequisites. Follow `DEPLOYMENT.md`; production body-size limits, container persistence and real Safari/Android behavior remain unverified. No deployment was run.

Next phase: **Phase 4 — Student dashboard library and search/filter experience**. Stop here until requested.

The subsequently requested Phase 4 implementation is recorded in [VIDEO_LIBRARY_STUDENT.md](VIDEO_LIBRARY_STUDENT.md).
