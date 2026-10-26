# YARUB — Admin Users Redesign and Student Progress Tracking

## Goal

Redesign the existing admin users list and student detail page into a modern, polished interface that fits YARUB's existing theme. Integrate student tracking for **القدرات** (Qudurat) and **مهارات اللغة العربية** (Arabic Skills) into these existing pages.

This is an **admin panel update only**. Do not redesign student pages, training sessions, public pages, or unrelated admin pages. Necessary backend tracking improvements are allowed, but must preserve current student-facing behavior.

This document describes desired behavior, not the repository's verified implementation. Inspect the actual application before choosing routes, models, fields, or components.

## Required design workflow

- Use the installed **Impeccable** skill and its applicable design, refinement, and review workflows. Read its instructions before designing or coding the interface. Merely mentioning Impeccable in the completion report is insufficient.
- If Impeccable is unavailable, report that clearly before starting visual implementation; do not claim to have used it.
- Inspect the current Qudurat interface as the preferred reference for visual quality and consistency, alongside the existing admin shell and shared design tokens.
- Preserve YARUB's identity: existing brand colors, Arabic typography, RTL behavior, navigation conventions, and appropriate shared components. Improve hierarchy, spacing, density, contrast, and interaction details.
- Aim for a restrained, professional interface. Avoid oversized buttons, giant headings, colorful dashboard tiles, decorative gradients, excessive shadows, redundant text, or analytics clutter.
- Keep changes to shared styles scoped so other pages do not unintentionally change.

## Current interface: issues to address

The supplied screenshots show:

- A users table with separate image, name, email, registration date, and actions columns.
- Repeated large “عرض الطالب” and red “حظر الطالب” buttons on every row.
- Broken profile images, visually noisy table styling, and a large page heading area.
- A student detail page with a large personal-information panel and four bright counters for courses, explanations, tests, and certificates.
- Existing detail tabs for **الدورات، الشروحات، الاختبارات، الشهادات**.
- A blocked-users list and an export/download control on the users page.

Preserve working account-management features, access to blocked users, existing detail sections, and export behavior if implemented. Verify what each feature does before refactoring it. Do not assume the screenshot proves backend behavior.

## 1. Users list

Rebuild the existing page rather than creating a second users directory for the new programs.

### Layout and controls

- Use a compact page header, a short clear title, and a well-spaced table container.
- Search by student name or email, with debouncing and a clear reset action.
- Use server-side pagination, search, filtering, and sorting. Preserve their state in query parameters and when returning from a student detail page.
- Default to registration date descending.
- Provide filters for account status and program participation: all students, Qudurat, Arabic Skills, or neither. Participation means at least one saved answer in that program.
- Make filters understandable and easy to clear. Do not add advanced filters without a clear need.
- Keep blocked-users access available in a compact, appropriate location.
- Retain existing export capability if functional; keep its permissions and make its exported scope clear. Do not build an unrelated reporting/export system.

### Table

Suggested desktop columns:

| Column | Required content |
| --- | --- |
| Student | Small reliable avatar or initials fallback, name, secondary email |
| Qudurat | Unique questions answered / available questions; subtle coverage indicator |
| Arabic Skills | Same presentation and definition as Qudurat |
| Last practice | Latest saved answer or completed session in either new program |
| Joined | Compact registration date |
| Status / actions | Compact status badge and an action menu |

Combine or adapt columns at smaller widths rather than making the page unusably wide. Use neutral body text and subtle separators, with clear hover and keyboard focus states.

- Make the row easy to open, while keeping the student's name a real keyboard-accessible link. Nested menu actions must not trigger row navigation. Email selection and normal browser link behavior must remain usable.
- Replace repeated large view/block buttons with a compact action menu.
- Preserve block/unblock behavior and permissions. Require confirmation for blocking, naming the affected student. Opening a row must never change account status.
- Handle missing and broken avatars gracefully without repeated failed-image icons.
- Show **لم يبدأ** for no recorded participation, rather than implying a failed score.
- Support meaningful sorting by joined date, last practice, and each program's coverage where the data supports it. Use a stable secondary sort and define placement of missing values.
- Treat last practice as learning activity in these programs, not as login activity or online presence.
- Include loading, empty, no-search-results, and error/retry states appropriate to the stack.

## 2. Student detail page

Redesign the existing detail page and retain all existing working information and actions.

### Header and navigation

- Use a compact profile header with avatar fallback, name, email, account status, joined date, and last practice.
- Include a clear back link that preserves users-list state, plus a compact account action menu.
- Remove the oversized personal-information card and the four large colored counters.
- Retain useful legacy counts through restrained badges or tab labels instead of bright tiles.
- Preserve existing courses, explanations, tests, and certificates, including existing financial/enrollment data and detail links. Do not change their business rules.
- Add dedicated tabs for **القدرات** and **مهارات اللغة العربية**, plus a concise overview if useful.
- Group legacy sections if needed to avoid an overcrowded tab bar, while keeping each reachable. Support smaller widths without clipped labels.
- Keep active tab and pagination state in the URL. Load detailed records only when needed.

### Overview

Keep this compact: two program summaries showing coverage, first-attempt accuracy when available, completed sessions, and last practice. Avoid charts unless they answer a concrete question better than a table or concise metric.

### Program tabs

Use the same admin tracking structure for both programs, populated from actual content types in the database. Do not hardcode question totals or assume that both programs contain the same number of types.

For each content type, show:

- Type name.
- Unique questions answered / currently available questions, plus coverage percentage.
- First-attempt accuracy, if supported by stored answer history.
- Completed sessions.
- Last practice.

Follow this with paginated session history: start date/time, type, completed/in-progress status using actual application statuses, score when valid, and a detail action.

If session answer records exist, let admins open a read-only session review with the question, selected answer, correct answer, answer result, explanation when available, and required shared passage/context. Distinguish unanswered questions from incorrect answers. Retain context for historical attempts where possible; never fabricate a historical answer from currently edited content.

## 3. Tracking definitions and data integrity

Use explicit, consistent definitions across the list and detail pages.

| Metric | Definition |
| --- | --- |
| Coverage | Distinct currently available question IDs with a saved submitted answer / currently available questions in that program or type |
| First-attempt accuracy | Correct answers on each question's earliest eligible submitted answer / number of questions with a known first submitted answer |
| Completed sessions | Sessions marked completed by the existing application, not sessions merely opened |
| Last practice | Latest reliable saved-answer timestamp or completed-session timestamp in the relevant program scope |
| Session score | Score according to existing grading rules, clearly distinguished from coverage and first-attempt accuracy |

Rules:

- Repeated answers to one question increase practice history, not unique-question coverage.
- Saved answers in unfinished sessions count toward coverage; unfinished sessions do not count as completed sessions.
- Opening a session or viewing a question does not count as answering it.
- Use the earliest submitted answer with a stable tie-breaker when calculating first-attempt accuracy. Establish whether existing answer records are immutable or overwritten.
- If hints or answer reveals are tracked, inspect their effect on scoring and document it. Do not label accuracy as “unassisted” unless the data proves it.
- A zero denominator produces an unavailable metric, not a divide-by-zero error or misleading 0% accuracy.
- Use **غير متاح** where historical data is insufficient. Never substitute overall accuracy for first-attempt accuracy under the same label.
- Scope question identity by program/type as required; IDs from separate tables must not collide.
- Use current active/published/eligible question rules consistently for coverage. Exclude removed/unavailable questions from both numerator and denominator, while preserving historical session records. Explain in a short tooltip that coverage can change when content changes.
- If only session totals exist historically, show supported session metrics. Do not invent per-question history or silently present partial history as complete.
- Where reliable historical first answers cannot be reconstructed, omit that accuracy metric or label it clearly as measured since a known tracking start date.
- Do not call coverage “mastery.” No mastery model, leaderboards, rankings, or student comparison engine is requested.

## 4. Technical scope

- Inspect repository instructions, existing authorization, routes, controllers, models, UI framework, and tests before changing code. Reuse the project's actual stack and conventions; do not migrate frameworks or add a dashboard framework.
- Audit both programs' session and answer storage. Prefer existing records and minimal additive changes to duplicate tracking tables.
- Any required new tracking capture must work server-side with the existing answer submission flow, be idempotent against retries, and preserve grading, hint/reveal behavior, and student UI.
- Protect all admin pages, metric endpoints, and session reviews with existing admin authorization. Verify direct URL access, not just hidden navigation.
- Avoid destructive migrations or rewrites of existing historical records. Do not modify production data or deploy as part of this task.
- Use efficient aggregates, pagination, eager loading, and targeted indexes where justified. Avoid querying every student's full answer history for each table row.
- Share metric calculations between list and detail pages so values do not drift. If caching is introduced, define invalidation after saved answers, session completion, and content availability changes.
- Keep email and numeric fragments readable in RTL layouts. Use the application's timezone and date conventions; keep exact timestamps accessible where helpful.
- Use real development data for review when available. Any necessary fixtures belong only in an isolated development/test context and must not overwrite existing users or attempts.

## 5. Implementation phases

Implement only the requested phase. Each phase must leave the application usable. Do not automatically continue into the next phase.

### Phase 1 — Audit and implementation plan

Read this document, repository instructions, and Impeccable. Inspect current users pages, both program implementations, schemas, authorization, and relevant tests.

Deliver a concise repository-grounded plan containing:

1. Existing routes/files/components to update and current features to preserve.
2. A metric-support matrix for each program: available now, derivable, missing, or historically incomplete.
3. Proposed page structure, using current Qudurat styling and YARUB design tokens as references.
4. Minimal schema/index changes if required, with historical limitations and performance considerations.
5. Adjustments to later phases based on findings, including risks or genuine blockers.

Do not implement UI, migrations, or tracking changes in this phase. Do not ask the user to explain information discoverable in the repository. Make routine implementation choices independently; surface only material unresolved product decisions.

### Phase 2 — Data and metric foundation

Implement reusable, authorized metric queries and necessary minimal additive tracking changes. Keep existing student behavior unchanged. Document historical limitations.

Validate distinct coverage, repeated attempts, first-answer ordering, incomplete sessions, unavailable content, empty denominators, program isolation, admin access, and any retry-sensitive new tracking. Add focused tests for these behaviors using the repository's testing conventions.

Acceptance: reliable metrics are available without N+1 queries or fabricated history, and existing session/answer flows still work.

### Phase 3 — Users list redesign

Use Impeccable to implement the new list: compact profile cells, tracking summaries, search, filters, sorting, pagination, status, avatar fallback, compact actions, and blocked-user access. Preserve existing export functionality where present.

Acceptance: the page is polished in RTL on desktop and narrow screens; existing management features work; opening a student and returning preserves list state; tracking uses Phase 2 calculations.

### Phase 4 — Student detail redesign and tracking

Use Impeccable to implement the compact header, navigation, overview, two program tabs, per-type breakdowns, session history, and read-only session review where data supports it. Preserve all existing legacy detail sections and their functions.

Acceptance: admins can inspect an individual student's activity in both programs without losing access to existing records; list/detail numbers agree; unavailable historical metrics are represented honestly.

### Phase 5 — Refinement and regression verification

Run the applicable Impeccable audit/refinement workflow on the actual rendered pages. Fix findings rather than merely listing them. Check responsive layout, Arabic text wrapping, keyboard navigation, contrast, menus, pagination, missing avatars, empty states, and errors.

Run relevant automated checks and focused regression tests for authorization, blocking/unblocking, preserved legacy sections, and metric correctness. Inspect query behavior with representative data. Visually verify both pages and both program tabs using the project's available preview/browser workflow.

Acceptance: no known regressions in preserved features; production build/checks pass where available; no student-facing redesign or unrelated styling changes.

## Completion report for each phase

State what changed or was learned, relevant files, validation performed, data limitations, and the next phase. Clearly distinguish verified results from checks that could not be run. Keep the report concise. Do not deploy.
