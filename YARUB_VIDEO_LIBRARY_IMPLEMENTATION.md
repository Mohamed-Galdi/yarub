# Yarub — Student Video Library, Public Showcase, and Admin Management

## Purpose

Add a video library inside the authenticated student dashboard, plus a separate public feature showcase with one example video and a login call to action, with an admin section for uploading and managing them. Videos complement the existing القدرات and مهارات اللغة العربية programs. They do not become lessons, training sessions, or student progress records.

This document is the implementation contract. Read it fully before starting. Implement only the phase explicitly requested by the user; complete its acceptance checks, report the result, and stop before the next phase. Do not implement the entire roadmap in one pass.

## Confirmed product decisions

| Area | Requirement |
| --- | --- |
| Audience | Full library: authenticated students. Public: one explicitly selected example only. No new subscription gate. |
| Navigation | Student dashboard الفيديوهات opens the library; public الفيديوهات opens the showcase |
| Content | Vertical videos, usually 1–3 minutes |
| Initial scale | Fewer than 50 videos in the first three months |
| Input | Ready-to-upload vertical MP4 files |
| Storage | Local server storage initially |
| Classification | Exactly one existing type per video; its program is derived from that type |
| Examples | نسق، برهان، مداد and the existing types of both programs |
| Metadata | Required title; optional description |
| Publication | Automatically visible after upload and processing succeed; admins may hide/show |
| Cover | Automatically generated frame, with optional custom image upload |
| Student library ordering | Newest first |
| Filter | One type dropdown, with options grouped by program |
| Search | Title and description |
| Watching | Overlay above the library, with a shareable URL |
| Continuing | Visitor chooses a related video; no automatic advance |
| Related videos | Same type first, then other types in the same program |
| Interactions | Playback and sharing only |
| Admin ordering | Newest first; no pinning or manual ordering |

## Scope boundaries

Do not add likes, comments, favorites, view counters, viewing history, progress tracking, subscriptions, video recommendations based on user behavior, infinite autoplay feeds, batch uploads, a video editor, or mandatory transcoding infrastructure. Do not modify the learning behavior or progress tracking of the existing programs.

The 1–3 minute duration is a typical length, not a confirmed hard duration limit. Portrait 9:16 is the presentation target; do not distort videos or silently crop meaningful content. Exact upload byte and dimension limits are implementation settings to resolve during discovery, not invented client requirements.

## Repository discovery and implementation rules

1. Read repository instructions and inspect the actual framework versions, frontend stack, admin/student/public layouts, routes, permissions, storage, queues, and testing conventions. Use the existing architecture; do not introduce another frontend framework.
2. Inspect the actual category/type models for القدرات and مهارات اللغة العربية. Do not assume model or table names, enumerate types from memory, or hardcode a duplicate taxonomy.
3. Inspect the successful Qudurat pages as the visual reference. Preserve Yarub's theme, Arabic typography, spacing conventions, and RTL behavior.
4. Use the project's **Impeccable** skill for admin, student, and public showcase UI work. Locate and read its instructions. If it is unavailable, report that explicitly and resolve access to it before claiming Impeccable was used. Do not silently substitute generic styling.
5. Keep changes focused. Preserve unrelated work, existing URLs, authentication, training data, and student behavior. Use additive migrations and development data first.
6. Do not change production configuration, run production migrations, or deploy as part of these implementation phases unless separately instructed. Prepare exact deployment steps and identify environment prerequisites.
7. Each phase must finish in a coherent state. Incomplete student/public features must not appear in live navigation. Do not expose an admin upload control until its supporting backend works.
8. Choose routine implementation details autonomously. Ask only about a genuine product ambiguity or unavailable dependency that blocks the requested phase.

## Data model and relationships

Create a dedicated video entity, following repository naming conventions. Suggested conceptual fields:

| Field | Purpose |
| --- | --- |
| Stable ID | Direct-link identity that survives title edits |
| Title | Required, validated text |
| Description | Optional plain text, safely rendered |
| Category relationship | Exactly one valid type from the existing supported programs |
| Storage disk and relative video path | Local storage now, portable storage abstraction later |
| Cover path and cover origin | Generated or custom cover |
| Duration, width, height, byte size, MIME/codec metadata | Server-inspected technical metadata |
| Processing state | Pending/processing, ready, or failed, using suitable project conventions |
| Visibility | Independent visible/hidden intent in the student library |
| Public example reference | One explicitly configured eligible library video; empty by default |
| Published timestamp | First successful automatic publication |
| Created/updated timestamps | Management and lifecycle |
| Uploading admin | If consistent with existing audit conventions |

Do not persist a separately editable program field that can disagree with the selected type. Derive the program from the relationship.

Choose a relationship after inspecting the repository: an existing shared taxonomy is preferable when present; otherwise use a constrained adapter/relationship across the two existing category systems. Enforce supported types and referential integrity as far as the chosen schema allows. Do not restructure both programs solely for this feature. Explain the chosen approach in the phase report.

Determine how category deletion/deactivation works. Prefer blocking deletion of a referenced category with a useful admin message, unless the project has a safer established policy. Never silently leave orphaned classifications. Ensure inactive or unavailable classifications cannot expose unintended public content.

Content eligibility is a single reusable rule: processing is ready, visibility is visible, and the category is eligible. Apply it together with authenticated student authorization to listings, search, related results, direct links, covers, and media delivery. Admin preview is a separately authorized exception. Anonymous access is limited to the explicitly selected eligible public example and its cover; public requests must never choose arbitrary library IDs.

Use first publication time descending with a stable ID tie-breaker for student library ordering. Hiding/showing or metadata edits must not bump the publication date. Admin ordering uses creation time descending with a stable tie-breaker.

## Upload and file lifecycle

- Restrict all management operations to existing administrators through server-side authorization. Protect mutations using the project's request/CSRF conventions.
- Upload one video at a time. Required: title, type, video. Optional: description and custom cover.
- Validate actual file content as well as extension, byte size, readable duration, orientation, and playback compatibility. MP4 is a container; do not assume every MP4 upload will play in target browsers.
- Resolve and document the supported codecs/profile and configurable byte limits during discovery. Give actionable validation errors for unsupported files. Do not silently introduce conversion or accept broken playback.
- Validate custom covers as supported raster images; reject executable/unsafe formats and excessive dimensions or size. Render descriptions as plain text.
- Use generated storage names and relative paths, safe media inspection commands, bounded processing time, and restricted processing inputs. Never interpolate upload names into shell commands.
- Inspect media and generate an appropriately sized portrait poster, preferably from a nonblank frame. Preserve source aspect ratio; allow a custom cover to replace a poor generated result.
- Show actual upload progress, then a distinct processing state. The video is not available to students until required validation, stored assets, and metadata are complete.
- If an asynchronous job is needed, use the existing queue setup. Jobs must be retry-safe and must not publish a video that an admin has hidden or deleted while processing. If processing can safely remain synchronous at this scale, document the bounded approach and timeout requirements.
- No draft publishing workflow is required. Technical pending/failed states are not editorial drafts.
- Failure leaves a clear admin error and no partial student-facing item. Preserve form text where practical. Provide retry or replacement without creating duplicate library records.
- Editing metadata must not require re-uploading. Existing covers can be replaced, and a custom cover can be removed in favor of a regenerated frame.
- On video replacement, validate and prepare the new file before switching the record. Keep the previous playable version intact if replacement fails. Preserve hidden status, identity, and publication time. Regenerate generated covers; preserve custom covers unless explicitly replaced/reset.
- Hide/show is reversible. Delete requires confirmation naming the video and removes its managed video, cover, and temporary files. Prevent queued jobs from recreating deleted assets. If filesystem cleanup fails after a DB mutation, log it and arrange safe retry rather than leaving a broken library record.

## Storage, delivery, and deployment requirements

Use Laravel's existing filesystem abstraction and a dedicated logical storage area. Do not store absolute host paths or hardcoded media URLs in the database.

The local storage location must persist across deployments and container replacement. Inspect Docker volumes/mounts and existing shared storage arrangements. Never assume the container writable layer is persistent.

Library files must not be anonymously reachable through static public-storage URLs. Keep assets outside directly exposed public paths and enforce student authentication plus content eligibility on every request. The only guest exception is the selected eligible public example and its cover. Admin preview uses separate authorization. Prefer an internal web-server file handoff after an application authorization and eligibility check when supported; otherwise use a verified response implementation that supports byte-range requests without reading entire files into application memory. Do not implement naive whole-file PHP streaming.

Support normal playback, seeking, correct media headers, and appropriate byte-range behavior. Anonymous requests for every non-example video and cover must be rejected, even if its library visibility is visible. Hidden/deleted/unready assets are unavailable to students and showcase visitors alike. A browser may retain bytes already downloaded; hiding is not DRM. Configure caching so it does not bypass the current authorization and eligibility checks.

Document inspection results and setup steps for:

- Available disk capacity, expected file sizes, and space needed for temporary/replacement files.
- Reverse proxy/web-server body size and timeout limits, PHP upload/post limits, and application validation limits as one aligned configuration.
- Media inspection/poster tools, such as FFmpeg/ffprobe, available inside the actual application/worker environment.
- Queue worker requirements if processing is queued; writable directories and ownership.
- Persistent mounts and backups of both database records and media assets.
- A reasonable operational disk-space check and cleanup approach; no new monitoring dashboard is needed.

Do not install packages on production or change its limits without instruction. Missing production access is a documented deployment prerequisite, not a reason to invent server facts.

## Admin experience

Add an admin navigation item named **الفيديوهات** using the existing authorization/navigation patterns.

### List

- Compact, polished paginated table; newest uploads first.
- Search title and description; grouped type filter and visibility filter; reset filters.
- Show cover/title, program/type, duration, visibility, publication date, and concise actions.
- Display processing/failed states distinctly from hidden/visible status. Never label an unready upload as available to students.
- Actions: edit, hide/show, preview, delete, and select/remove the single public example. No manual ordering or pinning.
- Useful empty, filtered-empty, loading, and error states. Responsive presentation without unreadable horizontal overflow.

### Upload/edit

- Clear hierarchy with minimal explanatory text; clear required fields.
- Group type choices under their actual program. Show the optional description as secondary.
- Preview selected video and cover; show format/size requirements before upload.
- Distinguish upload progress from server processing. Prevent accidental duplicate submission.
- Show generated cover after processing, allow custom replacement, and show server-side field errors beside the relevant controls.
- Keep destructive actions separate and confirm deletion. Hiding should be quick and clearly reversible.

## Student dashboard library experience

- Protected route suggested: `/student/videos`; adapt to the actual dashboard route/localization conventions and use its student layout.
- Require student authentication/authorization for pages, list/search APIs, related results, metadata, posters, and video bytes. Admins use management/preview authorization.
- Guests following a library page link must log in and then return to the intended internal library/video URL. Validate return URLs against open redirects. Unauthorized media/API requests return appropriate errors, not login HTML as video bytes.
- Add **الفيديوهات** to desktop/mobile student dashboard navigation once the complete player experience is ready. Public navigation links to the separate showcase.
- Short heading, search, and one grouped type dropdown. Use **الكل** for no type restriction. No separate program filter.
- Search title and description on the server using safe queries. Trim input, debounce typing where appropriate, reset pagination on search/filter changes, and avoid stale responses overwriting newer results.
- Preserve query, type, and page in URL state. Refreshing or sharing a filtered URL restores it. Invalid filter values have a predictable validated fallback.
- Use normal pagination consistent with the platform; no infinite feed for V1.
- Responsive grid of 9:16 portrait covers. Each card shows title, duration, and a subtle type label. No card descriptions, view counters, autoplaying previews, or oversized decorative controls.
- Avoid stretched/cropped video content. Covers may crop for grid consistency, but the actual player must show the full video.
- Lazy-load offscreen covers, reserve dimensions to avoid layout shifts, and avoid preloading every video file.
- Provide polished empty-library, no-results, loading, unavailable-video, and retry/error states.

## Public showcase and login conversion

The public `/videos` page introduces the video feature. It must not display the student catalog, search, filter, related-video results, or protected metadata. Public **الفيديوهات** navigation points here.

- Use Impeccable and the existing public layout: concise Arabic heading, short feature explanation, one portrait example player, and a prominent login CTA. No invented counts, promises, or paid-access claims.
- Suggested heading: **تعلّم بطريقة أوضح مع فيديوهات يعرب**.
- Suggested copy: **شروحات قصيرة في القدرات ومهارات اللغة العربية. شاهد مثالًا، ثم سجّل الدخول لاستكشاف بقية الفيديوهات.**
- Guest CTA: **سجّل الدخول لمشاهدة جميع الفيديوهات**. Use existing login with the student library as a validated intended destination. If registration already exists, an optional secondary **إنشاء حساب** link may use it; do not build new authentication flows.
- For authenticated students, use **انتقل إلى مكتبة الفيديوهات**, linking directly to their dashboard library.
- The example uses an inline portrait player with poster, standard controls, accessible errors, and user-initiated playback. No autoplay with sound. No related videos on this public player.
- Admins explicitly choose or remove a single eligible library video as the public example through a dedicated control. Store one reference in a suitable existing settings mechanism or a small dedicated configuration entity. Default: none. Selecting a new example atomically replaces the old selection. Never automatically pick the latest video.
- Mark the selected example in admin list/edit views. This is a public preview setting, not library pinning or manual ordering.
- Hiding, deleting, or making the example ineligible immediately blocks subsequent guest metadata, cover, and byte requests. Clear the reference on deletion. When no eligible example is available, show polished feature copy and CTA without a broken player or an automatic fallback.
- If a hidden selected example becomes eligible again while still selected, it may reappear. Removing/changing the selection revokes new guest access to the previous example. Caches must respect this rule; previously downloaded bytes cannot be recalled.
- Replacing the selected video's source also updates the example after successful processing. Warn the admin that this item is the public example. A failed replacement preserves the prior working file.
- Publishing a library video means availability to students, not anonymous access. The public example is the only explicit exception. No subscription gate or progress tracking is added.
- Student share URLs always follow the protected library/login flow, even when that video is also the example. The public showcase has its own public URL.

## Player overlay and URL behavior

Use an accessible dialog above the library, retaining the visual context beneath it. Do not navigate to a standalone player layout.

Suggested canonical URL: `/student/videos?video=<stable-id>`, retaining optional library query/type/page parameters. Use another route shape only if it fits existing routing better and preserves all behaviors below.

### Layout and playback

- Desktop: prominent portrait player alongside title, type, optional description, sharing, and related cards.
- Mobile: screen-filling overlay with a bounded portrait player and scrollable details/related cards beneath. Respect device safe areas and keep close controls accessible.
- Provide play/pause, seek, volume/mute, fullscreen, and inline playback where supported. Prefer accessible native controls or an existing maintained project player over a new custom-control framework.
- After an explicit card click, attempt playback; handle browser rejection with a visible play control. Direct shared links should show the poster/play button without relying on autoplay.
- No automatic advance, loop, endless swipe feed, or background audio after closing.
- Pause and release the old player when changing videos. Only one video may play at a time.
- Show loading/buffering and useful playback errors. Provide a recovery path without forcing a full library reset.

### Navigation contract

1. Opening from the library adds a video URL entry to browser history and preserves the underlying search, filter, page, and scroll position.
2. Browser Back closes that overlay and restores the library; Forward reopens it correctly.
3. Choosing related videos updates the URL and content. Prefer replacing the current overlay entry so Back still closes the overlay in one step.
4. Refreshing a video URL and opening it in a new tab both render the student library with that video open after authentication.
5. Closing an overlay entered through a direct link removes the video parameter while staying on the library; it must not send the visitor to an unrelated previous site.
6. Close by visible button, Escape, and deliberate backdrop activation where appropriate. Interacting with player controls must not accidentally close it.
7. Lock background scrolling, trap focus inside, label the dialog, and restore focus to the originating card or a suitable library control on close.
8. Reject malformed, hidden, deleted, failed, and unavailable video IDs without leaking metadata. Keep the library usable and give a concise unavailable state.

Sharing uses the device share interface when available and a copy-link fallback with success/failure feedback. Share a clean direct video URL; personal viewing state is not required in the shared link. Shared library URLs require login and preserve the requested video through authentication. Sharing never grants anonymous asset access.

### Related selection

Select a small bounded list (default six) of eligible student-library videos. Rank other videos of the same type first, then other types within the same program; order each group newest first. Exclude the current video, duplicates, hidden/unready items, and videos from other programs. Do not require related items to match the library's current search/filter. If no related videos exist, omit the section without empty decorative space.

## Visual quality requirements

Use **Impeccable** throughout UI implementation and polish. A functional upload form and a generic grid are not sufficient completion criteria.

- Preserve Yarub's identity and established Qudurat visual quality.
- Arabic RTL layout, correct mixed-direction text handling, readable titles, restrained color, and consistent spacing.
- Professional, compact admin UI; student video cards should be attractive without becoming a social-media clone.
- Minimal copy, with a concise feature introduction and CTA on the public showcase. Do not add long promotional paragraphs, unnecessary statistics, or implementation explanations to user-facing pages.
- Clear hover/focus/selected/disabled states, good contrast, usable touch targets, keyboard access, and reduced-motion support.
- Verify small mobile, tablet, and desktop widths with real portrait covers and Arabic titles of different lengths.

## Phased roadmap

### Phase 1 — Discovery, architecture, and database foundation

**Goal:** Resolve the real repository dependencies and establish the data model without exposing incomplete UI.

Tasks:

1. Complete repository/runtime/category/Impeccable discovery.
2. Record the relationship design, proposed routes, media storage/delivery approach, processing strategy, proposed configurable limits, and deployment prerequisites in a concise repository document.
3. Implement the additive migration, model/relationships, validation concepts, content-eligibility query rule, student authorization, and single public-example configuration, plus management authorization foundation.
4. Add focused tests for category validity, program derivation, eligibility, and permissions where appropriate.

Acceptance:

- Exactly one valid type per video; no contradictory program assignment.
- Student access and the narrow public-example exception are represented in the authorization design.
- No duplicated category list, destructive migration, existing-program regression, or premature navigation link.
- Migration and targeted tests pass in development. Unknown production prerequisites are explicit.

**Stop and report before Phase 2.**

### Phase 2 — Media upload, processing, and protected delivery

**Goal:** Make the media lifecycle reliable before building its polished interface.

Tasks:

1. Implement validated upload, media inspection, generated/custom covers, metadata extraction, and processing states.
2. Implement automatic publication after readiness, hide/show, metadata update, safe file replacement, deletion, and cleanup.
3. Implement protected student media/cover delivery, authorized admin preview, and the selected eligible public-example exception, all with seeking support.
4. Handle duplicate submission, processing errors, retries, and hide/delete during in-flight work.
5. Test success and failure paths with small representative fixtures; document local tool/worker setup.

Acceptance:

- A compatible portrait MP4 becomes available to authenticated students only after required assets exist. Upload success does not designate it as a public example.
- Invalid/corrupt/incompatible uploads fail clearly; failed replacement preserves the old working video.
- Guests cannot fetch non-example metadata, covers, or bytes through guessed/direct URLs. Hidden/deleted/unready content is denied to students and showcase guests.
- Seeking works; range delivery is verified; no full-file memory buffering.
- Deletion and replacement clean up assets safely; retry cannot resurrect deleted content.

**Stop and report before Phase 3.**

### Phase 3 — Admin video management UI

**Goal:** Let administrators manage the complete lifecycle through a polished interface.

Tasks:

1. Use Impeccable and existing admin components to implement list, search, type/visibility filters, pagination, and actions.
2. Implement upload/edit forms, previews, progress, processing/error states, generated/custom cover controls, single public-example selection/removal, and deletion confirmation.
3. Add the admin navigation entry once the workflow works end to end.
4. Verify authorization and responsive/RTL behavior.

Acceptance:

- Admin can upload, preview, edit, replace, hide/show, delete, and select/remove the public example without developer intervention.
- Non-admins cannot call management endpoints.
- Processing and publication states are clear; forms preserve useful data on recoverable errors.
- Actual UI has been reviewed at desktop and mobile sizes using Impeccable guidance.

**Stop and report before Phase 4.**

### Phase 4 — Student dashboard library and search/filter experience

**Goal:** Build the authenticated student dashboard library foundation.

Tasks:

1. Implement authenticated student list/search/filter/pagination endpoints using student authorization and the shared content-eligibility rule.
2. Build the responsive portrait grid with Impeccable, grouped dropdown, URL state, and loading/empty/error states.
3. Optimize covers and avoid eager video downloads.
4. Prepare the card-to-overlay integration boundary; keep this incomplete student feature out of production navigation until Phase 5 is ready. Do not ship dead player controls as a completed experience.

Acceptance:

- Search and type filtering work together; pagination and URL restoration are correct.
- Only eligible videos appear to authorized students, newest first with stable ordering; guest page/API requests cannot retrieve the catalog.
- Arabic content and cover proportions look good at all target widths.
- Page loading does not download the entire video collection.

**Stop and report before Phase 5.**

### Phase 5 — Student player, sharing, and public showcase

**Goal:** Complete student viewing and the separate guest showcase, then expose their respective navigation links.

Tasks:

1. Implement the responsive, accessible player overlay and complete navigation/history contract.
2. Add playback states/controls, cleanup on close/change, related selection, and sharing fallback.
3. Handle protected direct links, login return-to-video, refresh, unavailable videos, and browser playback restrictions.
4. Integrate cards and add الفيديوهات to desktop/mobile student dashboard navigation. Build the separate public showcase, example player, and login CTA; link public navigation to this showcase.
5. Add focused interaction/browser tests for history, focus, playback cleanup, login redirects, student access, and public-example boundaries.

Acceptance:

- Open, close, Back, Forward, refresh, direct-link entry, and related selection behave as specified.
- Library state/scroll survives closing; only one player is active; closing stops audio.
- Related ranking is correct; empty related sets and hidden direct links are handled cleanly.
- Shared library links require login and reopen the intended video afterward. Guests can watch only the selected eligible example, and cannot retrieve library/search/related payloads. Mobile layout and keyboard use are verified.

**Stop and report before Phase 6.**

### Phase 6 — Integrated QA, visual polish, and deployment handoff

**Goal:** Deliver a verified feature with concrete operational instructions.

Tasks:

1. Run targeted integration checks and the repository's relevant existing checks. Verify no regression in existing programs or permissions.
2. Perform Impeccable-guided polish with actual covers, long/short Arabic text, missing descriptions, and empty/error states.
3. Verify representative desktop and mobile browsers where available; report unavailable real-device coverage honestly.
4. Document exact development/production prerequisites, migration commands, persistent storage/mounts, upload limits, media tools, worker setup if needed, and backup/restore considerations.
5. Prepare a deployment checklist and rollback strategy that preserves uploaded media and existing data. Do not run production changes without instruction.

Acceptance:

- The full workflow works: upload → automatic student availability → log in → discover → watch → share → recipient login → intended video → hide → deny access → show → select public example → guest preview and CTA → remove example → deny guest asset access → delete.
- Range seeking, server limits, persistence, error recovery, and cleanup are verified locally or clearly identified as deployment checks.
- Relevant tests/build checks pass; remaining limitations are specific and actionable.
- Deployment instructions are based on observed repository/runtime facts, with unknowns explicitly labeled.

## Required report after every phase

Provide a concise report containing:

1. What changed and the files/components involved.
2. Acceptance criteria verified and commands/checks run, with actual results.
3. UI screenshots/previews when the phase changes UI and the environment supports them.
4. Any deviations, environment prerequisites, or unresolved blockers.
5. The next phase name, then stop. Never claim verification that was not performed.

## Initial prompt to Codex

```text
Read YARUB_VIDEO_LIBRARY_IMPLEMENTATION.md completely and follow the repository instructions.
Implement Phase 1 only: discovery, architecture, and database foundation.
Inspect the existing Qudurat and Arabic Skills category models before choosing relationships.
The full library belongs in the student dashboard; the public page shows only one explicitly
selected eligible example and a login CTA. Design authorization for this distinction.
Locate the project's Impeccable skill and record its availability for upcoming UI phases.
Preserve the existing programs and unrelated changes. Use the development environment only.
Complete Phase 1 acceptance checks, report actual results and deployment prerequisites,
and stop before Phase 2. Do not build the full feature yet.
```

For later phases, request: `Read YARUB_VIDEO_LIBRARY_IMPLEMENTATION.md and the completed implementation. Implement Phase N only, verify its acceptance checks, report results, and stop before the next phase.`
