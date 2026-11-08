# Authentication

## Refinement — 2026-11-08

The user requested an always-visible Google button with a clear multicolor logo on student login and registration, and border-only input focus project-wide. The shared input stylesheet removes outer rings, outlines and focus shadows while preserving an active border and authentication error color. Authentication and application bundles both import it. Buttons and links retain their existing focus treatment. Browser checks cover login/registration at 1440, 390 and 320 px plus 24 control focus checks using the public, admin and student stylesheet combinations. Evidence: `.impeccable/review/auth-followup`. Authentication suite: 43 tests, 249 assertions passed; existing vendor deprecations remain.

Mode: Operate. Targets: resources/views/auth, resources/views/layouts/auth.blade.php, resources/css/auth.css, resources/js/auth.js.

## Direction contract

THESIS: A clear Arabic account entry point with a persistent YARUB brand panel and a focused white form. Replace the old translucent forms and decorative illustrations.

OWN-WORLD: Existing indigo #292a65, white, ink #20223a, muted #565d71, dividers #dfe2ec. Judur headings and Hacen controls. Eight-pixel control corners, visible labels, inline errors and explicit feedback.

STORY: Students choose Google or credentials, recover access when needed, and return to their learning destination. Administrators see their separate credentials-only form.

FIRST VIEWPORT: Right-hand indigo panel occupies 40% of desktop; logo at top, Arabic learning message at center, home link at foot. White left column centers a 440px form. At 760px the panel becomes a compact header; forms retain full labels and 50px controls. Password reveal and busy submission are the signature interactions.

FORM: Form with brand panel, explicitly selected by the user. Code-led. seed:22ad530cc02e0b6f. The user's selection governs composition.

FINISH: unreviewed and undocumented is unfinished; this build ends with the finish review, the verdict, DESIGN.md, and every shipping raster carrying its provenance

## Finish record — 2026-11-06

- Documentation complete: appended authentication usage and coverage to `DESIGN.md`, preserving its existing content and token frontmatter. This is an extension of YARUB's established visual world.
- Source evidence: authentication stylesheet/script, shared layout, field/Google/submit components, and all authentication views. The shared palette, Hacen/Judur fonts, and 8 px control radius match the incumbent system. Local form headings use weight 400, line-height 1.5, `clamp(30px, 3vw, 38px)`, and 30 px on mobile; the panel heading ramp remains unchanged.
- Composition evidence: `.impeccable/review/auth` contains ten page/state samples at 1440, 390, and 320 px plus `checks.json`. Its checks record no page-level overflow, prevention of duplicate submission, and history restoration. The contact sheet shows the selected white-form/indigo-panel composition; revised individual verification and Google-account captures show the corrected supporting-action hierarchy.
- Review disposition: initial **fix**; verification logout and Google optional-password actions were changed to secondary styling, with corrected spacing and spinner treatment. Follow-up reviewer verdict: **ship at fix-list scope**, checking all six revised captures and component source for those corrections. This verdict covers visual hierarchy, not backend or live OAuth approval.
- Asset provenance: reused existing `public/assets/logos/logo_white.png`; no newly generated raster assets. Google mark remains inline SVG in the existing auth component source.
- Token/sidecar comparison: shared primitives match. Authentication-specific layout, controls, and heading roles are local extensions documented in `DESIGN.md`; `.impeccable/design.json` is unchanged and continues to cover panels. Its older pilot narrative predates the current panel rollout text, a pre-existing documentation drift outside this extension.
- Limits: no claim of live Google OAuth, live mail delivery, or production verification. Review captures are development evidence.
