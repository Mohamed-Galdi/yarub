# Phase 5 — Player, sharing and public showcase

Implemented on 2026-10-26 in isolated development only; interrupted final checks resumed on 2026-10-27. Phase 6 integrated QA/deployment handoff remains separate. No production access, migrations, deployment, push or dependency upgrades were performed.

## Result and files

The authenticated catalog cards now open an accessible native dialog over the library. `student/videos/_player.blade.php`, `videos-player.js` and the extended student video CSS implement playback, related selection, sharing, feedback and cleanup. `videos-student.js` coordinates catalog history without refetching/replacing the underlying grid for video-only Back/Forward. Catalog links retain their native open-in-new-tab behavior.

`GET /student-dashboard/videos/{video}` (`student.videos.show`) is a student-only JSON player/related endpoint. It reuses `VideoLibraryAccess:student`, `LibraryVideoPolicy::viewAny` and the shared eligibility scope. Guests receive 401, wrong roles 403, and hidden/deleted/unready/ineligible IDs 404 without video metadata. It returns an explicit allowlist: ID/title/type/duration/cover, plain-text description, protected media URL, clean share URL and a maximum of six related cards. No model serialization, filesystem paths, processing diagnostics or uploader details are exposed.

Related results exclude the current item, use the current video's program, rank the same type first and then other types of that program, and use first-publication/ID descending inside each rank. Eligibility is applied to every candidate. A bounded eager-loaded query avoids duplicates and N+1 loads. Related selection is independent of catalog search/type filters and never advances automatically. An empty related list removes its section.

`VideoShowcaseController` and `home/videos.blade.php`, with dedicated public CSS/JS entries, implement public `/videos` (`videos`). It uses the existing guest shell. The controller reads only `VideoLibrarySetting::eligiblePublicExample()` and passes that example's title, or null. Query parameters cannot select an ID, catalog or related results. The page contains one inline native player using the existing fixed selected-example media/cover routes, with `preload="none"`, no autoplay, a login/library CTA and an existing registration link. When selection is empty/ineligible, no player or fallback video appears. Students get a library CTA; administrators get management and the student-account explanation. The response is private/no-store.

Student desktop/mobile navigation now links to the library; public desktop/mobile navigation links to the showcase. A narrow student navigation fit adjustment removes fixed minimum link widths and prevents desktop label wrapping/clipping with the extra entry. Existing component colors, typography, hover behavior and account actions are retained. Dedicated public assets omit the guest shell's payment bundle on this informational route, following the existing learning-page pattern. Other routes retain their bundle selection.

## Playback and history contract

- Opening a card pushes `?video=<id>` over the current query/type/page. A marker identifies an entry created from the library. The background grid, filters and scroll remain intact.
- Related selection replaces the overlay entry, so Back closes in one step. Forward fetches current eligibility and reopens the selected video. Catalog history ignores video-only changes, while real catalog changes still load their matching results.
- Refresh and new-tab/direct URLs render the student library and reopen the requested video. Canonical page URLs retain a bounded numeric video ID or the `invalid` sentinel. Malformed IDs show a concise unavailable dialog without requesting arbitrary metadata.
- Closing a direct entry replaces its URL with the library URL instead of navigating to an unrelated previous page. Library-created entries close via Back. Visible Close, Escape and deliberate pointer-down/click outside the desktop dialog use the same cleanup. Player/control clicks do not close it.
- Native modal semantics trap focus and make the background inert. Background scrolling is locked while open. Closing restores the prior scroll and original card, or the search control after a direct entry/refresh. Mobile has a full-height overlay, a bounded portrait player, scrollable details and sticky Close with safe-area padding.
- Only the selected video receives a source. Source images/video use protected endpoints and full-image `object-fit: contain` playback. Native controls provide play/pause, seeking, mute/volume/fullscreen where supported. The preferred frame is portrait and bounded by viewport height.
- Explicit card/related activation attempts playback; rejection leaves a visible Play button. Direct entry and refresh wait for a user gesture. Loading/buffering, metadata/network failures, unavailable content and playback errors have feedback/retry paths. Retrying fetches eligibility again.
- Close/change/pagehide pause the old video, remove source/poster, call `load()` to release it, and abort in-flight metadata. Revision guards prevent stale requests from resurrecting playback. BFCache restoration reopens through current metadata; the public example reloads on BFCache return to recheck selection. No loop, automatic advance, background player collection or engagement tracking was added.

Sharing uses the native device share API when available. Cancellation is respected. A separate Copy button and share fallback use the clipboard; if unavailable/denied, a labeled readonly selected URL supports manual copying. Success/failure/cancellation feedback is announced. Shared links contain only the stable video ID, not personal filters, and grant no guest media access.

The GET catalog/search/pagination fallback remains usable without JavaScript. The overlay requires JavaScript, with explicit noscript guidance; no new frontend framework/player dependency was added.

## Authentication boundary

The showcase CTA targets the protected library route, using the existing authentication middleware to save the intended URL. Shared video links follow the same flow. `Services/VideoLibrary/LoginDestination` extends the student login destination allowlist only for the exact internal library route. It accepts bounded scalar `q`, `type`, `page`, `video` parameters, rejects external/alternate paths, fragments, control characters, array parameters and unknown redirect fields, and rebuilds the internal URL instead of redirecting to the input verbatim. Existing Qudrat/Arabic Skills destinations, default course destination and cart precedence are preserved.

The public example is still an explicit singleton, never the newest library item. Hiding, deactivating its type, deleting it or removing/changing selection blocks future guest example requests through the existing protected delivery checks. Public showcase queries never unlock the catalog, descriptions or related payloads. Previously downloaded bytes cannot be recalled; no DRM claim is made.

## Verification

All database work used the guarded PHP 8.3/loopback MySQL development harness and exact disposable `yarub_qudrat_test_local` schema. Root environment files/credentials were not read. Browser fixtures are synthetic three-second FFmpeg test patterns/custom blue cover and local accounts/contact fields, not actual instructional content. The review helper now supplies the guest shell's contact prerequisite and can switch to normal session authentication with `review-session` for a real local login test. Production authentication is unchanged. Never run assertion-counting database suites while committed browser fixtures exist; clean them first.

Actual browser checks covered card playback (`readyState=4`, duration 3, no decode error), related replacement then one-step Back, Forward, refresh without autoplay, new-tab/direct close, filtered-library restoration, pause/source release/focus/scroll restoration, Escape, deliberate backdrop click, native focus containment, copy success, long Arabic titles, sticky mobile Close, unavailable hidden links and server-stop/request-failure/retry. A guest followed a clean shared link, saw login, authenticated with the synthetic account through the actual form/session flow, and returned to that exact video. The anonymous selected public example played using native controls; hiding it removed the player without a fallback. Student/public mobile navigation entries were exercised.

CSS widths 320, 390, 768, 1280 and 1440 were reviewed with no horizontal catalog/dialog/showcase overflow. Student navigation labels measured 64px tall at 1280 after the fit correction; the fixture account fit its 48px-high area. The native capture backend returns slightly smaller rasters than verified CSS viewport sizes. Captures are unchanged native viewport files, not full-page or real-device evidence.

Screenshots are retained locally (Git-excluded) in `.impeccable/review/video-player/`: desktop/default/tablet/mobile/small player, mobile related/long-title/error/unavailable, desktop navigation, and public desktop/default/tablet/mobile/played-controls/empty selection. The scrolled captures intentionally show the relevant details or controls. No generated production raster assets were introduced.

Impeccable followed the existing session context, prescribed local-extension contracts, new-work/Operate guidance and craft floor. One detector pass found only the inherited `text-slate-100` on `bg-indigo-600` navigation treatment; it is existing near-white brand styling, retained for the independent review. No new visual world or global design-system replacement was requested.

A fresh independent finish reviewer inspected all 16 required captures and sampled the source, returning **disposition: ship** with all five contract sections and no material fixes. It accepted the inherited navigation color treatment and the disclosed native-capture scaling. The independent documenter returned **No changes** after checking the final student/public views, assets, layouts, navigation, Tailwind and surface contracts. No global `DESIGN.md` or design sidecar was created.

### Automated acceptance checks

Final PHP regression checks completed on 2026-10-27 after the interrupted run. Commands used the explicit PHP 8.3 executable with `-c storage/app/qudrat-local/php.ini`, `vendor/phpunit/phpunit/phpunit`, and the named isolated test configuration; ordinary root `php artisan test` was not used.

| Check | Actual result |
| --- | --- |
| `-c phpunit.video-library.xml` | 38 tests / 595 assertions passed, including six new player/showcase/login feature tests and the existing foundation/media/range/admin/catalog checks. |
| `-c phpunit.qudrat.xml --filter 'AdminContentTest\|PracticeTest'` | 22 tests / 234 assertions passed. |
| `-c phpunit.arabic-skills.xml --filter 'FoundationTest\|AdminPagesTest\|StudentPagesTest'` | 17 tests / 721 assertions passed. |
| `-c phpunit.learning-public.xml` | 5 tests / 57 assertions passed; existing feature/default/cart login behavior preserved. |
| `-c phpunit.admin-tracking.xml --filter UsersListTest` | 7 tests / 75 assertions passed; its intentional simulated query failure emitted the expected warning. |
| `node --test` over `tests/Frontend/*.test.mjs` | 23 passed, including seven player tests and the video-only catalog history case. |
| `node tools/qudrat-build.mjs` | Passed after the final UI changes; player/public entries verified present in the manifest on 2026-10-27. Existing Browserslist/datatable safelist notices remain non-blocking. |
| PHP lint / `node --check` | Passed for changed controllers, login destination, routes, development helpers, player tests and video scripts. Blade pages/fragments also rendered through tests and actual browser flows. |
| `git diff --check`, new-file whitespace and status review | Passed; existing line-ending notices are non-blocking. Prior implementation work and unrelated roadmap deletion are preserved. |

Total: **89 distinct PHP tests / 1,682 assertions**, plus **23 frontend tests**. Repeated and interrupted runs are not counted twice. Coverage includes student-only metadata/related access, current eligibility and inactive classifications, bounded same-type/same-program ranking, no public catalog or automatic example, direct URLs, safe intended login destinations/open-redirect rejection, native history semantics, stale-request cancellation, source cleanup, blocked autoplay, manual copy fallback and native-share cancellation.

The recorded browser fixtures/accounts/contact data and managed media were cleaned up before these database suites. The review server was stopped, and its browser tab and viewport override were removed. The dedicated MySQL instance was stopped after the final checks.

## Remaining prerequisites

No new migration or package is needed for Phase 5. A future authorized release needs the previous database/private-media prerequisites and the new Vite entries built. Actual production persistence, reverse-proxy/PHP limits, media tooling/ownership, backups, cleanup scheduling and real Safari/Android/native share coverage remain deployment/Phase 6 checks. The catalog/related/player/public pages introduce no subscriptions, learning records or progress behavior.

Next: **Phase 6 — Integrated QA, visual polish, and deployment handoff**. Stop after Phase 5 until requested.

Phase 6 was subsequently authorized; its current results and deployment prerequisites are recorded in [final QA](VIDEO_LIBRARY_QA.md) and the [release/recovery handoff](VIDEO_LIBRARY_RELEASE.md). The evidence above remains the historical Phase 5 record.
