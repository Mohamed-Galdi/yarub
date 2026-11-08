---
name: YARUB panels
description: Shared foundations for the Arabic admin and student panels.
colors:
  brand: "#292a65"
  brand-soft: "#eeedf7"
  brand-hover: "#3e3f74"
  canvas: "#f6f7fa"
  surface: "#ffffff"
  ink: "#20223a"
  muted: "#565d71"
  divider: "#dfe2ec"
  focus: "#545584"
  success: "#216346"
  success-soft: "#e9f5ee"
  danger: "#92263a"
  danger-soft: "#fff0f2"
  danger-divider: "#d5a6b0"
typography:
  headline:
    fontFamily: "judur, hacen, sans-serif"
    fontSize: "clamp(1.8rem, 3vw, 2.4rem)"
    fontWeight: 700
    lineHeight: 1.35
  section-title:
    fontFamily: "judur, hacen, sans-serif"
    fontSize: "1.25rem"
    fontWeight: 700
    lineHeight: 1.35
  card-title:
    fontFamily: "judur, hacen, sans-serif"
    fontSize: "1.125rem"
    fontWeight: 700
    lineHeight: 1.35
  control:
    fontFamily: "hacen, sans-serif"
    fontSize: ".9rem"
    lineHeight: 1.55
  label:
    fontFamily: "hacen, sans-serif"
    fontSize: ".875rem"
    lineHeight: 1.55
  status:
    fontFamily: "hacen, sans-serif"
    fontSize: ".8rem"
    lineHeight: 1.55
  account-detail:
    fontFamily: "hacen, sans-serif"
    fontSize: ".7rem"
    lineHeight: 1.55
  metric:
    fontFamily: "hacen, sans-serif"
    fontSize: "clamp(1.375rem, 2.6vw, 2rem)"
    fontWeight: 700
    lineHeight: 1.55
  wordmark:
    fontFamily: "judur, hacen, sans-serif"
    fontSize: "1.6rem"
    fontWeight: 700
    lineHeight: 1.55
  body:
    fontFamily: "hacen, sans-serif"
    lineHeight: 1.55
rounded:
  control: "8px"
  card: "14px"
  status: "20px"
spacing:
  control-gap: "8px"
  grid-gap: "16px"
  surface-inset: "clamp(16px, 2vw, 24px)"
  section-gap: "28px"
components:
  button-primary:
    backgroundColor: "{colors.brand}"
    textColor: "{colors.surface}"
    rounded: "{rounded.control}"
    padding: "6px 14px"
  button-secondary:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.brand}"
    rounded: "{rounded.control}"
    padding: "6px 14px"
  button-quiet:
    textColor: "{colors.brand}"
    rounded: "{rounded.control}"
    padding: "6px 14px"
  course-card:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.ink}"
    rounded: "{rounded.card}"
    padding: "{spacing.surface-inset}"
  status-published:
    backgroundColor: "{colors.success-soft}"
    textColor: "{colors.success}"
    rounded: "{rounded.status}"
    padding: "3px 10px"
  nav-current:
    backgroundColor: "{colors.brand-soft}"
    textColor: "{colors.brand}"
    rounded: "{rounded.control}"
    padding: "7px 10px"
---

# YARUB panel design system

The admin and student panels use the same neutral surfaces, Arabic typography, spacing, and indigo action hierarchy. The admin panel keeps its sidebar, and the student panel keeps its collapsible top bar. Metric, course, lesson, test, and coupon cards share this system. Training exercises, tracking screens, and media players retain the controls required by their tasks. The stylesheet is loaded by the two panel layouts.

## Foundations

| Role | Rule |
| --- | --- |
| Typography | Hacen for body, controls, and numbers. Judur for page, section, and card headings. Page headings are 29–38 px, section headings 20 px, and card headings 18 px. Use 14–16 px for supporting text. |
| Surfaces | Canvas `#f6f7fa`, white cards, and a 1 px `#dfe2ec` divider. Cards use 14 px corners. |
| Color | Use existing YARUB indigo `#292a65` for the main action and focused accents. Active navigation uses a light indigo tint. Published status uses green with a text label. Unpublished and metadata use neutral tones. |
| Spacing | Use 8 px for closely related controls, 16 px between cards, 16–24 px inside surfaces and at page edges, and 28 px between sections. |
| Layout | Begin with one page heading and its primary action, followed by labeled sections. Let controls wrap, use logical properties, and isolate mixed-direction data with `bdi` or `dir="auto"`. Preserve visible focus and 44 px action targets. |
| Navigation | Keep the admin sidebar and student top bar. Use one clearly marked current destination with `aria-current="page"`. At narrow widths preserve navigation access through a labeled toggle. |

## Reuse on a page

Opt in through the page's layout by loading `resources/css/panels.css` via Vite and applying `yarub-panel` to the containing shell. Both panel layouts load these foundations. The content components work independently of the navigation shell. Use the `panel-heading` Blade section in the admin layout when a view supplies its own page heading and uses shared components throughout. Use `panel-own-title` for older views that supply a heading but still use standard legacy form controls. Use `panel-student-content` for student pages with the standard content width.

- `x-panel.heading` takes `title`, optional `description`, and an optional `actions` slot. Use `panel-section` and `panel-section-heading` for the sections that follow.
- `x-panel.card` provides a neutral surface and an optional level-three heading. Use it directly for content or charts. Use `x-panel.metric` inside a definition list with `label`, `value`, and optional `unit`. Group a course title, state, metadata, and actions in one card. Let long Arabic or mixed-direction titles wrap.
- `x-panel.button` takes `href` for navigation or `type` for a form button. Use `primary` for the main action, `secondary` for a supporting action, and `quiet` for a lower-emphasis action.
- `x-panel.empty` takes `title`, `message`, and an optional action slot. Keep the explanation close to the missing content and point to an available next step.
- Use `panel-grid` with a layout modifier for metrics, charts, or courses. Course cards show publication state above the title, counts and price below a divider, and separate links for viewing and editing.

## Tables, forms, and filters

Use `panel-table-region` on the named, focusable scroll wrapper for a standard table. Standard form controls inherit the panel treatment through `panel-legacy`. Program editors and video filters keep their existing markup and interactions.

- **Tables** use one surface with a clear header row, quiet row dividers, and explicit row actions. Preserve existing sort and pagination behavior. Use tabular numerals for numeric columns and a labeled, horizontally scrollable table region where the columns need more room.
- **Forms** use persistent labels above controls, 44 px minimum control height, and inline hints and errors associated with their fields. Group related fields under a heading and keep the primary submit action with its cancel or back action.
- **Filters** sit immediately above the results. Wrap controls at narrow widths, keep applied values visible, and provide a clear reset action when filters are active. Distinguish a filtered result with zero matches from an empty collection.


## Editor groups and dialogs

Use `panel-editor-group` for a lesson or question group within an editor, and `panel-editor-group-heading` for its heading row. Apply these classes to the initial markup and to markup inserted by JavaScript. Keep IDs, field names, required attributes, upload bindings, and removal handlers with the existing form.

Use `panel-dialog` on dialog content while preserving the existing outer dialog, focus handling, visibility controls, and backdrop. Use `panel-button--danger` for destructive actions. Password changes use the regular action hierarchy. `panel-busy-overlay` is the responsive blocking state used by course and lesson saves.

Conversation bubbles use `panel-message--own` and `panel-message--other`. A brand tint identifies the current sender, while message text stays dark. The default panel avatar lives at `public/assets/images/panel-avatar.svg` and appears when an account has no avatar. Profile image preview keeps its existing image element and upload behavior.

## Rollout coverage and verification

The rollout covers every currently routed admin and student HTML page, including aliases. The route inventory contains 76 page URLs. All were rendered with isolated sample data at 1440 px and 390 px, with no page-level horizontal overflow. Data tables retain their local horizontal scrolling where required.

| Page family | Included surfaces |
| --- | --- |
| Admin overview and content | Dashboard, course and lesson catalogs, creation, editing, and subscriber detail views |
| Admin assessments | Test catalog, creation, editing, detail, and attempt review |
| Admin accounts and operations | Admin accounts and dialogs, coupons, certificates, payments, exports, support lists and replies, and visitor messages |
| Admin site-content editing | Home, about, and contact editors, partner and FAQ forms and dialogs |
| Admin programs and tracking | Qudrat categories, question editors and previews, passages, Arabic banks and models, question editors and previews, training records, student list, profile tabs, and session review |
| Admin video library | Catalog, filters, upload form, and metadata/media editing |
| Student learning | Courses, lessons, content players, tests, results, and certificates |
| Student account and support | Profile, password dialog, conversations, creation, and replies |
| Student programs and media | Qudrat and Arabic catalogs, groups, active exercises, results, and video catalog |

Client interaction checks cover adding and removing course editor rows, adding test questions, opening and closing password dialogs, copying coupon codes, starting a test and selecting an answer, selecting an Arabic exercise answer, mobile admin navigation with Escape, and student training navigation. Existing route identifiers and request field names were compared against the original templates.

The four legacy view files `admin/Xdashboard`, `admin/courses/VimeoCreate`, `admin/courses/VimeoEdit`, and `admin/students/deleted` have no direct rendering route. The blocked-student route redirects to the shared student list. Public pages, shareable certificate documents, JSON endpoints, and binary exports sit outside the panel visual inventory.

Verification used a disposable SQLite database and sample media metadata. It checks rendering and client interactions. Live video playback, uploads, mail, payments, and production deployment require their configured environments.

Legacy course and lesson players retain the class names used by their selection scripts. Panel-scoped CSS supplies their neutral surfaces and dark text, including the active selection. Their inherited utility color names do not describe the rendered panel appearance.

## Authentication usage and coverage

All project-owned inputs, textareas, and selects use `resources/css/input-focus.css`, shared by the application and authentication bundles. Focus changes the existing border to YARUB indigo without outlines, shadows, or Tailwind rings. Dark inputs use a light indigo border, authentication errors retain their red border, and forced-color mode uses the system highlight border. Links and buttons retain their keyboard focus treatment. Student login and registration always show the bordered Google action with its multicolor logo; provider configuration is checked when starting the flow.

Authentication extends the established YARUB palette, Hacen/Judur pairing, control corners, and feedback colors through `layouts.auth`, `resources/css/auth.css`, and `resources/js/auth.js`. Its user-selected composition places a focused white form beside a right-hand indigo brand panel. The form width is capped at 440 px; the brand column is 40%, rising to 42% from 1440 px. At 760 px and below, the brand panel becomes a compact logo/home header and its promotional copy is hidden. This composition belongs to authentication, not the panel navigation system.

Use `x-auth.field` for persistent labels, associated hints/errors, mixed-direction email/password input, and password reveal; use `x-auth.submit` for submission feedback and its `secondary` option for supporting actions. Google entry appears only when configured. Keep verification resend and Google linking as primary actions; logout and the optional password email action use the outlined secondary treatment. Text fields and full-width actions have a 50 px minimum height; password reveal has a 44 px target. Preserve visible keyboard focus and reduced-motion support. The script prevents repeat submissions while busy, restores controls on history return, and focuses an error summary.

Authentication headings are a local rule: Judur at weight 400 and line-height 1.5, with the form heading using `clamp(30px, 3vw, 38px)` and an explicit 30 px mobile size. Brand copy has its own larger heading. These surface roles do not replace the panel heading ramp in the frontmatter. The existing white YARUB logo at `public/assets/logos/logo_white.png` is reused; no new generated raster ships with this surface.

Coverage includes student login/registration, administrator login, forgotten/reset passwords, email verification and invalid verification links, password confirmation, Google account linking, and validation feedback. Captures and `checks.json` under `.impeccable/review/auth` cover ten page/state samples at 1440, 390, and 320 px; the recorded checks report no page overflow, repeat-submission prevention, and history restoration. The finish reviewer returned **ship at fix-list scope** after checking the six revised verification/Google-account captures and component source for the two hierarchy corrections. This is a visual review, not certification of live OAuth, mail delivery, or production operation.

The existing frontmatter and `.impeccable/design.json` remain unchanged. Authentication matches their shared colors, font families, and control radius; its layout, control sizing, and heading roles are documented locally above. The sidecar still describes panel components and panel breakpoints, and its older pilot narrative predates the rollout coverage recorded here; it is not an authentication inventory.

## Course and explanation cards

The four course and explanation catalogs use `x-panel.learning-card` inside `panel-learning-grid`. The shared composition has an inline content-type indicator, an optional publication state, a linked 22 px Judur title, a two-line excerpt from the existing description, grouped metadata, and an action footer. Titles wrap in full. Descriptions are escaped plain-text summaries and are omitted when empty.

Pass `title`, `href`, `type`, and `description`, with `kind="lesson"` for explanations. The required `details` slot contains definition-list groups. Admin cards also use the `status` and `pricing` slots. Put the existing View/Edit or Continue links in the default slot. Use `:heading-level="3"` when a section already provides a level-two heading.

The admin course card shows its single course price. Explanation cards retain separate monthly and annual prices. Student cards show enrollment date or remaining subscription days, including the existing expiring-soon state. This component and its styles are scoped to the four learning catalogs.

Card hover borders reuse the existing `pr-50` brand shade `#9495b2`. The expiring-subscription text reuses the panel warning shade `#754c08`. Inline title links use a 3 px corner only around their keyboard-focus outline.
