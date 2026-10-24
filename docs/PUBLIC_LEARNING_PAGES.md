# Public learning feature pages

Implemented 2026-10-24. These Arabic, RTL pages introduce the existing student features and explain how to access them.

## Routes and navigation

| URL | Behavior |
| --- | --- |
| `/qudrat` | Public introduction to verbal Qudrat practice; named route `qudrat`. |
| `/arabic-skills` | Public introduction to the five Arabic Skills banks; named route `arabic-skills`. |
| `/lessons` | Permanent (301) redirect to `/qudrat`; retains the `lessons` route name for old links. |
| `/lessons/{lesson}` | Existing lesson detail route remains available. |

The public header, public and student footers, homepage discovery section, and cart navigation now point to the new pages. The former homepage lesson promotion is replaced by introductions to both features. Existing student lesson access and purchase flows remain in place.

## Content and access

The figures describe the original imported content, explicitly labeled as basic questions rather than a live count of administrator additions:

- Qudrat: 1,200 questions across six sections, 200 per section. Topics and totals follow the existing Qudrat content and `App\Services\Qudrat\ContentValidator`.
- Arabic Skills: 1,500 questions across five banks, 300 per bank and ten original models per bank. Banks are نَسَق، رَسْم، بَيان، مِداد، بُرهان, following `arabic_skills_content/` and the existing importer.

The pages explain question formats, feedback, progress review, and available training modes. Arabic Skills copy covers independent matching credit, quick practice, full models, resuming sessions, and practice priority based on the latest submitted answer. Course purchase or lesson subscription is not required to use these student features.

Primary buttons link to the appropriate protected student feature index. Guest requests therefore set the intended destination before opening login. Student login honors only these two feature destinations through an explicit allowlist; cart login retains priority and other logins retain the existing courses destination. Signed-in students receive direct feature links. Administrators see an explanation that practice requires a student account.

## Interactive previews and presentation

Each page includes an original illustrative sample: verbal analogy on Qudrat and grammatical matching on Arabic Skills. The samples provide an explanation, independent partial credit for matching, and a retry action. They are labeled as illustrations that are not saved to the learner's record.

`resources/js/learning-public.js` evaluates these samples entirely in the browser. Public rendering does not query question banks, expose stored answer snapshots, or create learner sessions. The two pages use a separate Vite JavaScript entry without the payment SDK or admin table scripts.

The design extends YARUB's existing navy palette, Hacen typography, and Arabic layout. Responsive layouts were checked at 390, 1024, and 1440 pixels, including mobile navigation, keyboard interaction, feedback focus, reset behavior, and partial matching answers. Header account actions are direct links, navigation includes current-page semantics, and the menu and cart have Arabic accessible names. Reduced-motion preferences are respected.

## Verification

Run database suites sequentially against the existing guarded disposable test database on port 33317, never the development or production database:

```powershell
./tools/qudrat-local.ps1 start
$testPhp = "$env:USERPROFILE/.config/herd/bin/php83/php.exe"
& $testPhp -c storage/app/qudrat-local/php.ini vendor/phpunit/phpunit/phpunit -c phpunit.learning-public.xml
& $testPhp -c storage/app/qudrat-local/php.ini vendor/phpunit/phpunit/phpunit -c phpunit.arabic-skills.xml
& $testPhp -c storage/app/qudrat-local/php.ini vendor/phpunit/phpunit/phpunit -c phpunit.qudrat.xml
node --test tests/Frontend/*.test.mjs
node tools/qudrat-build.mjs
```

Verified results:

- Public pages: 5 tests, 57 assertions, including navigation, bank-query isolation, and feature login redirects.
- Arabic Skills: 46 tests, 13,150 assertions.
- Qudrat: 63 tests, 529 assertions.
- Frontend: 5 tests, including three public preview tests.
- Production asset build, focused Pint checks, and `git diff --check` passed. Existing Browserslist and Tailwind safelist notices remain.
- Impeccable design review approved the pages; accessibility review confirmed the header corrections. The scoped detector completed with no findings.

Browser review used isolated temporary fixtures and a preview server; those fixtures were removed and the server stopped. Screenshots are local, ignored artifacts in `storage/app/qudrat-local/public-learning-shots/`, including `qudrat-desktop-final.png`, `arabic-desktop-final.png`, and their mobile counterparts.

This change requires no new database migration or content import and performs no production operation. Existing importers continue to manage the actual training content. Future changes to the original content totals should also update the public copy and its assertions.
