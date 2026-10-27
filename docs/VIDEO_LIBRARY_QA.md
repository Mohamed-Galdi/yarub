# Phase 6 video library QA

Development-only final acceptance, 2026-10-27. This completes the roadmap's integrated QA and handoff scope; production release requires the explicit operator checks in [VIDEO_LIBRARY_RELEASE.md](VIDEO_LIBRARY_RELEASE.md). No production access, deployment, push, payment or mail occurred.

## Changes from Phase 5

- `tests/Feature/VideoLibrary/JourneyTest.php` adds a complete HTTP lifecycle and paired synthetic database-row/private-file restore drill using real FFmpeg media and exact byte-range responses.
- `resources/js/videos-player.js` snapshots the current record and revision for asynchronous share/clipboard actions. Closing or switching videos while the device responds can no longer throw on released metadata, reopen stale fallback UI or copy a different video's URL. Two frontend tests reproduce these failures and verify recovery.
- `config/video_library.php`, the admin form and `resources/js/videos-admin.js` coordinate a configurable 450-second media request timeout with transfer plus synchronous processing, and explain HTTP 413 server-size rejection while preserving input. The metadata timeout stays 150 seconds. A focused frontend test covers default/configured/invalid budgets and rejection recovery; the rendered form contract is asserted in AdminPagesTest.
- Guarded review helpers add a clearly labeled synthetic Arabic teaching clip with readable text, a cover derived from actual video, missing descriptions and synthetic role/session switching. Production authentication and media storage are unaffected.
- The release handoff documents known infrastructure facts, exact future migration commands, limits, private persistence, cleanup, consistent backups and rollback safeguards, with unresolved production prerequisites identified explicitly.

No schema change, package upgrade, progress tracking, subscription change or unrelated program refactor was introduced in this phase. Existing work and the unrelated roadmap deletion remain preserved.

## Acceptance evidence

| Requirement | Local evidence | Remaining release check |
| --- | --- | --- |
| Complete lifecycle | New integration test: upload → ready/available → actual login/intended link → combined search/type → metadata/range/cover → fresh authentication roundtrip → hide/deny → show → explicitly select example → guest showcase/CTA → remove/revoke media and cover → delete/cleanup. | Repeat with planned accounts and representative client media behind the real front server. |
| Authorization | Existing feature tests plus integrated journey distinguish admin, student and guest; public example access never grants catalog/related/student assets. Current eligibility applies to files and covers. | Prove no static aliases, public links or CDN rules bypass protected delivery. |
| Range/cleanup/recovery | Exact 206 range bytes, suffixes, HEAD/416 and invalid-range behavior; real tool validation, retries, idempotency, concurrent submissions, replacement safety and interrupted cleanup covered by the feature suite. | Actual server/proxy seeking, aborted transfer behavior and cleanup scheduler monitoring. |
| Persistence | Synthetic snapshot preserves row ID, relative paths, media/cover SHA256, operation records and selected-example identity through deletion/restoration. Cleanup after advancing two hours protects restored live assets. | Full consistent database/media backup restore and actual container recreation with the intended persistent volume. |
| Limits | Real small HTTP upload and processing passed; application size/space/format validation and frontend 413 recovery tested. PHP review server explicitly configured for 250M/260M. | Actual maximum-size HTTP upload and proxy/FPM rejection; sufficient temp/private capacity and effective timeout/memory limits. Small-file success does not prove maximum-size handling. |
| Existing platform | Targeted Qudrat, Arabic Skills, discovery/login/cart and admin-user checks run in the isolated database. | Normal release smoke checks; no live transaction was made for QA. |

The database-row/file restore drill is intentionally narrower than an actual full production restore. It never rewinds existing learning or payment data. Default disk reserves remain unchanged; review/tests explicitly relax them only on disposable private disks.

## Visual and interaction review

The project's Impeccable skill guided an ordinary final refinement of the existing Arabic/RTL design. Its session context, polish reference and craft floor were applied; no redesign, global DESIGN.md replacement or new production raster assets were needed. The prior critique lookup returned no usable snapshot, so this pass was independent. One detector scan of the three changed UI files reported no regex findings; it did not run parsed-page contrast/size rules and is not presented as comprehensive accessibility evidence.

Actual Chromium/IAB review covered verified CSS widths 320, 390, 768, 1280 and 1440, with no horizontal catalog/dialog/showcase overflow. Reviewed states include real generated/custom covers, short/long Arabic titles, missing descriptions, no results, hidden direct-link unavailable state, ready/failed admin rows and mobile upload/edit forms. A real local XHR uploaded the teaching clip without an optional cover/description, generated its poster and made it ready; admin selection, hide, guest showcase removal, actual shared-link login, unavailable response, and restored visibility were exercised. Native playback reached readyState 4 and duration 4 without a media error. Copy-link success, native controls and the separate guest login CTA were exercised; prior Phase 5 history/focus/source-release checks remain recorded in its report.

Fifteen unedited native viewport captures are retained locally in `.impeccable/review/video-final/`: catalog default/desktop/mobile/tablet, player desktop/mobile/scrolled details, small no-results, public desktop/mobile/empty, admin desktop/upload/edit and unavailable mobile. The backend's raster sizes are slightly smaller than the verified CSS viewport sizes; captures were not resized or stitched. The teaching clip is labeled local test content. No physical device, Safari engine or Android native-share coverage is claimed.

A fresh independent finish reviewer inspected all 15 captures and sampled the Phase 6 implementation, returning **disposition: ship**, no material fixes, and all five contract sections. It accepted inherited navigation styling and the unrelated synthetic-email overflow in admin chrome. The independent documenter returned **No changes**: existing product/surface contracts cover this refinement. No global design file or sidecar was created. Stale historical phase labels and the unset Impeccable buildPath were reported without unsolicited repair.

## Automated results

All checks below passed after the final production code changes. Database suites used the explicit PHP 8.3 executable, isolated ini and named guarded configurations, sequentially. Commands and isolation prerequisites are in the [release handoff](VIDEO_LIBRARY_RELEASE.md#development-reproduction).

| Check | Actual result |
| --- | --- |
| `phpunit.video-library.xml` | 40 tests / 677 assertions, including complete lifecycle and paired restore. |
| `phpunit.qudrat.xml`, AdminContentTest and PracticeTest | 22 tests / 234 assertions. |
| `phpunit.arabic-skills.xml`, FoundationTest, AdminPagesTest and StudentPagesTest | 17 tests / 721 assertions. |
| `phpunit.learning-public.xml` | 5 tests / 57 assertions, including existing login/cart destinations. |
| `phpunit.admin-tracking.xml`, UsersListTest | 7 tests / 75 assertions; its intentional query-failure scenario emitted the expected warning. |
| `node --test tests/Frontend/*.test.mjs` | 26 tests passed, including the new upload-limit/timeout and two asynchronous sharing race cases. |
| `node tools/qudrat-build.mjs` | Passed; all video entries and matching assets present in the manifest. Existing Browserslist/datatable notices remain non-blocking. |
| PHP lint / `node --check` | Passed for Phase 6 PHP helpers/config/journey and both changed JavaScript modules. Blade form rendered in tests and the browser. |
| Detector / whitespace / diff review | One detector pass, no regex findings; parsed-page rules not run. `git diff --check` and Phase 6 new-file trailing-whitespace checks passed. Existing line-ending notices remain non-blocking. |

Total: **91 distinct PHP tests / 1,764 assertions**, plus **26 frontend tests**. Focused/repeated runs are not counted twice. The isolated build and frontend suite ran after the final UI code changes; later documentation/helper-fixture changes did not change production assets.

The browser fixture accounts, records and managed media were removed before the final database suites. Screenshots remain local evidence. The browser tab, viewport override and loopback review server were closed. The dedicated MySQL instance was stopped after verification, and its loopback listener was confirmed absent.

## Handoff

Production persistent-volume identity, real HTTP/FPM/proxy settings, media-tool/ownership verification, scheduler installation, consistent backup/restore and established maintenance/OPcache procedures remain required operator prerequisites. Real Safari/iOS/Android playback and native sharing also remain unverified. The release guide provides concrete checks and preserves uploaded media and existing data during recovery.

There is no further implementation phase in this roadmap. Stop after Phase 6; deployment requires separate authorization.
