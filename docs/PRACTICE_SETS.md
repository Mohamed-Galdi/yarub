# Selectable practice sets

Students select a type, then a practice set, in both القدرات and مهارات اللغة العربية. Catalog GET requests do not start sessions. Sets are independent: starting another set does not replace an unfinished one. Cards show the student's active session or latest completed result, with resume, retry and result links. Existing sessions without set metadata remain accessible.

Card surfaces identify state at a glance: completed sets use a green check and border on white, active sets an indigo status marker and position bar on white, unstarted sets a dashed neutral outline, unfinished attempts a quiet pause marker, and unavailable sets gray. Icons and labels accompany these colors. A completed set keeps its completion check during a retry. The coffee-filtration activity uses the same state styling as every other set, without a special featured background.

## Session presentation

Both practice players show answer feedback in a persistent toast centered in the viewport with a next-question action and an expandable answer/explanation. The toast does not time out. Keyboard focus moves to the result after checking an answer; the next action remains visible when long explanations scroll. Multiple-choice and true/false feedback omit the per-question point fraction. Arabic matching retains its partial score, and total/result scoring is unchanged.

Arabic Skills highlights the correct option and any incorrect selection after submission. Practice uses one compact toolbar, 20px reading/option text and 22–24px question text. Annotated reading activities omit the repeated bank/subject and generic reading instruction. Their passages sit alongside the answers on desktop and in collapsible, scrollable reading sections on smaller screens. The centered toast leaves the underlying page at its current scroll position; long questions remain scrollable. Matching avoids a duplicate option list and duplicate selected-answer text. Set cards omit the repeated sentence about choosing a set directly.

Presentation recognizes the annotated format conservatively and keeps ordinary prompts intact; no saved content or snapshots are rewritten. The redundant instruction about checking an answer to show the explanation is removed from both session screens. These presentation changes require an asset build and compiled-view refresh when released, with no additional migration.

## Membership and scoring

- Arabic Skills uses the immutable `context_group_number` for the original thirty groups of ten in each bank. Student type pages show the sets only; the full-model section and training-history list are removed for every type. Existing model/session records and admin review remain intact.
- Burhan's `coffee-filtration` set appears first and selects only `supp_burhan_coffee_filtration_01` through `_08`, in source order. With all eight published it contains eight questions and eighteen points. It does not add older questions to reach ten.
- Other Arabic Skills additions are grouped in creation order, ten per set, separately from the original groups and coffee activity.
- Qudrat groups in database creation/import order, ten at a time. Reading comprehension keeps each passage's questions together and closes a set once it reaches at least ten. A final set may be smaller.
- Both catalogs group all records before checking publication, so archiving a question does not move subsequent questions into another set. Cards show the remaining published count; a set with no published questions cannot start. Display-order edits do not affect Qudrat membership. A partially filled additions/final set can grow as more questions are added. Changing a Qudrat question's category or passage changes its grouping; this feature does not add an admin set editor.
- Existing session content snapshots, grading and answer privacy are preserved. Repeated starts resume only an active session of the selected set and student. New attempts calculate scores from the questions actually selected.

`practice_set_key` and `practice_set_title` are nullable session metadata. They distinguish selected sets from legacy dynamic practice without changing the existing mode values. History, results and admin student tracking identify selected sets. Requests without a set key remain supported for existing callers; student catalog controls lead to set selection.

## Release prerequisites

Follow [DEPLOYMENT.md](DEPLOYMENT.md). This change requires the two reviewed migrations below and a Vite asset build, as well as the existing route/view cache handling described in that runbook:

- `2026_10_29_000001_add_set_identity_to_qudrat_sessions.php`
- `2026_10_29_000002_add_set_identity_to_arabic_skill_sessions.php`

Apply these additive migrations before serving the updated session code. They add nullable columns and do not rewrite old attempts. No question reimport is required if the original banks and the Burhan supplement have already been imported. Databases missing the supplement must follow its separately documented guarded import process in [ARABIC_SKILLS.md](ARABIC_SKILLS.md); the catalog does not import content.

### Commands after pulling this release

Use the recorded server setup in the deployment runbook. Retain a restorable database backup and follow the established maintenance procedure while updating. Run each command separately and stop on failure. On the host:

```bash
cd /var/www/yarub

docker exec --user www-data --workdir /var/www/yarub yarub-php \
  php artisan migrate:status

docker exec --user www-data --workdir /var/www/yarub yarub-php \
  php artisan migrate --path=database/migrations/2026_10_29_000001_add_set_identity_to_qudrat_sessions.php --force

docker exec --user www-data --workdir /var/www/yarub yarub-php \
  php artisan migrate --path=database/migrations/2026_10_29_000002_add_set_identity_to_arabic_skill_sessions.php --force

sudo -u www-data npm --cache=/var/cache/yarub-npm run build

docker exec --user www-data --workdir /var/www/yarub yarub-php \
  php artisan route:clear

docker exec --user www-data --workdir /var/www/yarub yarub-php \
  php artisan view:clear

docker exec --user www-data --workdir /var/www/yarub yarub-php \
  php artisan migrate:status
```

The final status must show both new migrations as run. Check both student catalogs, starting/resuming a selected set, Burhan's first eight-question activity, and both admin card grids. This release changes no dependency lockfiles; install dependencies only if missing or another pulled change requires it, following the main runbook. Do not rerun seeders or content importers for this UI/session change.

**Local execution, 2026-10-29:** both migrations applied successfully to the explicitly authorized development database `127.0.0.1:3306/yarub`. Migration history and both nullable set identity columns were verified in each session table. No server commands were executed.

## Verification

Use the guarded disposable test connection in `QudratTestCase` / `ArabicSkillsTestCase`, not the root environment. Focused `PracticeSetsTest` coverage checks exact source membership, passage integrity, student isolation, independent resume/retry, Burhan's 18-point score, unavailable content and invalid set requests. Existing practice and admin page tests cover previous session behavior and form validation. Visual review uses test HTTP responses and built assets at 1440px, 768px and 390px, including expanded admin settings; review artifacts remain in ignored `storage/app/qudrat-local/practice-sets-review/`.
