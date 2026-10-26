# Users list — Phase 3

Completed 2026-10-25. Student tracking remains inside **Users**. No program-management navigation is required to inspect list coverage. No production access, deployment, application-schema migration or student-facing redesign occurred.

## What changed

- Reworked the existing `admin.students` page and `StudentsTable` Livewire component into a compact RTL directory. The component uses focused Blade rendering within the existing Livewire stack, replacing its PowerGrid-specific presentation. It consumes the shared Phase 2 aggregate queries for both coverage columns and last practice.
- Added debounced name/email search, status and saved-participation filters, reset, server pagination/page sizes, registration/last-practice/coverage sorting, and query-string state. Default registration ordering, stable ID tie-breaking and unavailable-value ordering come from the shared query service.
- Combined identity/avatar/email into one cell; added initial fallbacks including cached-image-error handling, restrained coverage bars, status badges and native popover actions. Narrow layouts use two-column student summaries. Keyboard-accessible name links retain normal browser behavior; email selection and nested actions do not navigate rows.
- Kept the blocked-users route as a redirect into the same directory's blocked filter. Blocking/restoring retains the existing DELETE routes, is admin-only and student-scoped, handles retries, and names the student in reversible-action confirmations. Current list state survives actions. Legacy blocked-table queries now also exclude admins.
- Preserved name/email/registration XLSX export across all matching pages, using the shared filtered SQL query and existing Excel library. Text cells remain literal, including formula-like names. The separate existing date-range export remains in place; its reversed active/blocked label was corrected.
- Student detail retains its existing presentation and legacy sections. Its back link now restores validated list state; explicit eager loading and student-scoped, blocked-aware lookup allow this journey without lazy-loading failures. Phase 4's detail redesign and tracking tabs have not begun.

Main files: `app/Livewire/StudentsTable.php`, `app/Services/Admin/Tracking/{StudentTracking,UsersListState}.php`, `app/Exports/StudentsListExport.php`, `app/Http/Controllers/Admin/StudentController.php`, `resources/views/livewire/students-table.blade.php`, `resources/views/admin/students/{students,pagination,view-student}.blade.php`, `resources/css/admin-users.css`, `resources/js/users-admin.js`, and the Users-specific branches in `layouts/admin.blade.php` / `vite.config.js`.

## Design and interaction verification

Applied the installed Impeccable established-world, Operate, craft-floor and polish guidance. The accepted roadmap/Phase 1 plan pins the design; the surface contract is `.impeccable/surfaces/resources-views-admin-students-students-blade-php.md`. No replacement visual world or image comp was introduced.

Inherited Qudrat navy `#292a65`, Hacen, white/slate surfaces, subtle borders, native controls and focus treatment. Local additions are limited to directory density, coverage bars, avatars, status and popover styling. A compact shell and corrected 768px sidebar breakpoint apply only to Users routes; unrelated pages retain their shell. The documenter confirmed inherited tokens and local extensions. Existing sidebar indigo/glow and font-weight declaration drift remain outside this phase; no global DESIGN.md was invented.

Browser verification used synthetic fixtures only on guarded `127.0.0.1:33317/yarub_qudrat_test_local`, served by a private loopback review server. Checked 1440px desktop, 700px intermediate and 390px narrow layouts; no horizontal document overflow at measured desktop/narrow widths. Verified search/filter changes, pagination, page 2 → student detail → back, account cancel/block/restore, download, missing avatars, and no-results recovery. Sorting behavior is covered by automated component/query tests. Native popover Enter/Tab reaches its link; Escape closes it and restores trigger focus. Browser console checks reported no errors/warnings during the exercised Users flows.

The independent finish review initially requested visible export scope on mobile. That was fixed; the documentation pass also exposed a cached-avatar error race, which was fixed with initialization/error handling and recaptured. Reviewer follow-up disposition: **ship for the two scored fixes**, with no visible regressions attributable to them. This is not a claim of a full accessibility certification. The independent documenter rechecked both final captures and confirmed clean avatar fallbacks. A single detector pass over view/CSS/JS found no regex findings; parsed-page contrast/tiny-text rules did not run.

Final screenshots are ignored local review artifacts under `storage/app/qudrat-local/users-phase3-shots/`, notably `desktop-final.jpg` and `mobile-final.jpg`. Browser fixtures were removed by exact owner/content identity checks; the private review server was stopped. Existing test data was not reset.

## Checks and remaining scope

- Combined tracking/Users tests: **21 tests, 240 assertions passed**, including filtered all-pages export and literal spreadsheet text, account scope/retries, state restoration, preserved detail access and a simulated query-failure/retry state.
- Isolated Vite production build passed; existing Browserslist and unmatched safelist warnings remain. No dependencies were upgraded.
- Neighboring Qudrat admin content regression: **9 tests, 105 assertions passed**.
- Neighboring Arabic Skills admin pages regression: **7 tests, 146 assertions passed**.
- Targeted Pint checks and syntax checks for all 9 Phase 3 PHP files passed; `git diff --check` passed (Git also reported a line-ending normalization notice for the pre-existing student-detail template).

Historical metric limits remain those in Phase 2: only recorded submissions are measurable, no tracking start date was invented, and Arabic hint usage is unknown. No new tracking capture or database index was needed. Production-scale query-plan checks and broader refinement/regression remain Phase 5 work.

Next requested phase would be **Phase 4 — student detail redesign and tracking**. This phase stops before that work.
