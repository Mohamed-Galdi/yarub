# Yarub Qudrat implementation guide

## Goal and project context

Add a new Arabic section named **القدرات** to the existing Yarub Laravel application. Students practice six categories of verbal aptitude questions. Administrators manage categories, questions, answers, explanations, hints, and shared reading passages.

Implement inside the existing project using its actual architecture, authentication, authorization, frontend components, and visual conventions. Yarub and ARRIB are separate applications. Do not introduce another application, replace the stack, or redesign unrelated pages. Verify versions and project conventions from the repository rather than assuming them from this document.

The supplied JSON files are the content input. They have already been extracted from six Word documents. Codex does not need to parse the Word files or generate questions. First seed the development database so the owner can review the result. Do not seed production, deploy, or change payment/subscription behavior as part of this implementation.

## Scope and content inventory

Place this guide at the repository root and the six JSON files under `qudrat_content/` at the root.

| File | Category | Questions | Shared passages |
| --- | --- | ---: | ---: |
| `01_verbal_analogies.json` | التناظر اللفظي | 200 | 0 |
| `02_sentence_completion.json` | إكمال الجمل | 200 | 0 |
| `03_contextual_errors.json` | الخطأ السياقي | 200 | 0 |
| `04_odd_word_out.json` | المفردة الشاذة | 200 | 0 |
| `05_reading_comprehension.json` | استيعاب المقروء | 200 | 40 |
| `06_association_and_difference.json` | الارتباط والاختلاف | 200 | 0 |

Total: 1,200 single-answer questions, 4,800 options, 1,200 explanations, 1,200 hints, and 40 passages. No image, audio, AI generation, or external service is needed for this feature.

The extraction removed document headers, teacher names, bibliography, source metadata, and editorial notes. It preserved question and option wording, explanations, hints, punctuation, and diacritics. Content is not independently educationally verified. Do not silently rewrite questions or change answer keys; surface suspected content issues for review.

## JSON contract

Each file contains `category` (stable English slug), `title` (Arabic display name), and `questions`. Reading comprehension also contains `passages`.

Each question contains:

| Field | Meaning |
| --- | --- |
| `id` | Stable external key, such as `verbal_analogies_001`; use for repeatable imports. |
| `order` | Original position within the category, from 1 to 200. |
| `question` | Question text, without the document's numbered heading. |
| `options` | Four objects with local IDs `a`, `b`, `c`, `d` and a `text` value. |
| `correct_option_id` | Local ID of the correct option. |
| `explanation` | Feedback shown after submission. |
| `hint` | Optional assistance shown before submission when requested. |
| `passage_id` | Reading questions only; references a passage in the same file. |

Each passage contains a stable `id` and `text`. Paragraph boundaries inside text use `\n\n`; preserve them when rendering. Passage IDs and question IDs are unique across the supplied content. Option IDs are unique within a question, not globally. Display option labels as أ، ب، ج، د while retaining stable IDs internally.

Do not split a two-word completion choice into separate answers. Preserve all original blanks and ellipses. Preserve the order within analogy pairs in RTL. The full prompt may contain its own instruction; do not try to split every prompt into structured semantic parts for V1.

## Data model and import behavior

Use normal application tables and relationships rather than loading the JSON files directly in the browser. Reuse suitable existing concepts only after checking their semantics; avoid forcing these questions into an unrelated lesson hierarchy.

Recommended entities, adapted to repository conventions:

- **Category:** stable slug, Arabic title, sort order, published state.
- **Passage:** stable external key, category, text, sort order.
- **Question:** stable external key, category, optional passage, prompt, hint, explanation, sort order, published state.
- **Option:** question, stable local key, text, sort order, correct flag; enforce exactly one correct option through application validation and an appropriate database design.
- **Practice session:** owner, category, status, ordered question list, current position, started/completed timestamps.
- **Session answer:** session, question, selected option, correctness, hint usage, submitted timestamp.

Store the chosen question order when a session starts so refreshes and resumptions are stable. Store immutable answer feedback snapshots or content versions so later admin edits cannot rewrite completed results. If using a join table for session questions, place order and per-session state there. Add foreign keys, indexes, uniqueness constraints, and ownership rules appropriate to the existing database.

### Imports

Provide an explicit development import command or seeder, following project conventions. Do not run it automatically on application boot or production deployment.

1. Validate the complete input before mutating content. Support a validation-only or dry-run command.
2. Validate IDs, category names, question order, four nonempty options, valid answer references, required hint/explanation values, and reading passage links.
3. Use stable external keys for upserts. Re-running the same import must not duplicate records or reset student progress.
4. Use transactions and return created, updated, unchanged, skipped, and conflicting counts.
5. Do not delete records merely because they disappear from a later input file.
6. Protect admin-edited records. Track the last imported content checksum/version and compare current content against that baseline. If an admin changed a record, skip it with a conflict report by default. Allow explicit, targeted overwrite only through a deliberate command option.
7. Seed categories/questions as drafts for review. Publish them through the admin workflow after review. Only published content is accessible to students.
8. Preserve option identity when editing/importing. Completed results retain their original question/option/feedback snapshots even if current content changes.

## Student experience

### Entry page

Add **القدرات** to the appropriate student navigation. Show six category cards with title, a short category-specific description, answered-question progress, first-attempt accuracy, and **بدء التدريب** or **متابعة التدريب**. Use published question counts. Keep the page focused; avoid an oversized introductory area or dense analytics.

### Practice sessions

V1 has practice mode only. For standalone categories, default to short sessions of up to 10 questions. Prefer unseen questions; when exhausted, offer explicit review practice. Resume an unfinished session instead of generating a new order on refresh. Start another session through an explicit action.

For reading comprehension, use whole passage groups. Select consecutive passage groups in original order until reaching roughly 10 questions; include the final group in full rather than splitting it to hit an exact count. Show the actual session length. Keep questions in their source order within each passage. Group sizes vary, so do not assume five questions per passage.

Use one question at a time across all six categories:

1. Show compact session progress, category, question, and four single-select options.
2. Let the student request **تلميح** before submission; show only the hint and record its use server-side.
3. Enable **تحقق** after selecting an option.
4. On submission, grade on the server and lock that response for the session.
5. Show correct/incorrect feedback, the correct option, and the explanation.
6. Advance with **التالي**. Do not automatically advance before feedback can be read.
7. Save after each meaningful action and allow refresh/resume without losing selection, hint state, or submitted answers.

Unsubmitted selections should persist as draft session state and must not count as attempted answers. A retry/review creates a new answer attempt, without rewriting the original submitted response. Do not trap students in an endless loop until every answer is correct.

### Shared player and category adaptations

Use shared progress, options, hint, feedback, grading, and navigation components. Category differences belong in the content layout:

| Category | Adaptation |
| --- | --- |
| التناظر اللفظي | Prominent prompt/pair; preserve pair direction and Arabic punctuation. |
| إكمال الجمل | Readable sentence with visible blanks; multiword options remain a single choice. |
| الخطأ السياقي | Prominent sentence, followed by the supplied word choices. No mandatory inline word-click interaction in V1. |
| المفردة الشاذة | Compact prompt and spacious word options. |
| استيعاب المقروء | Passage beside the active question on desktop; passage above on mobile. Preserve paragraph breaks and keep access to the passage while moving through its questions. |
| الارتباط والاختلاف | Standard prompt-and-options layout. |

Use a keyboard-accessible single-select pattern, visible focus, readable Arabic typography, responsive sizing, and feedback text/icons in addition to color. Avoid fixed-height containers that clip long text. Reuse the project's established styling and design tooling if available.

### Results and metrics

Show session score, correct/incorrect counts, hint usage, and **مراجعة الأخطاء**. Review should retain the associated passage for every reading question. Offer **تدريب جديد** and return to the category page.

- Attempted progress = distinct questions with at least one submitted answer / published questions in the category.
- First-attempt accuracy = correct first submissions / questions with a first submission. Retries do not overwrite this metric.
- Hint-assisted answers remain valid but are visibly marked; no special score penalty in V1.
- A session score reflects that session's submitted answers. Unanswered questions are not silently submitted or marked wrong.

Exclude drafts from student counts. Handle empty categories and exhausted question banks gracefully. If questions are unpublished during an active session, do not expose newly restricted content; notify the student and safely adjust or close the affected session. Preserve completed historical results.

## Admin experience

Add a **القدرات** area to the existing admin navigation and protect all endpoints using the project's actual admin authorization.

- **Category overview:** six categories, total/published/draft counts, publication controls. Fixed initial category set; arbitrary new category creation is outside V1.
- **Question list:** paginated table; search prompt and option text; filter by category and draft/published state. Default order follows the source order within category.
- **Question editor:** category, prompt, four editable options, correct option, explanation, hint, optional linked passage, order, and publication state. Support create, edit, preview, publish/unpublish, and safe archive behavior.
- **Passage management:** edit shared passage text once; list linked questions and allow navigation to them. Passage edits affect all linked questions, which must be clear in the editor.
- **Preview:** use the actual student rendering, including reading layout and feedback.
- **Validation:** exactly four nonempty options, exactly one correct option, required prompt/explanation/hint for published records, and a valid same-category passage for reading questions. Passage field is not available for other categories.

A repeated generic prompt such as «اختر المفردة الشاذة» is not a duplicate by itself. Duplicate detection must consider category, passage, prompt, and choices. Do not auto-delete questions on that basis.

Use archive/unpublish instead of destructive deletion for attempted questions. Never remove a passage while active questions depend on it. Keep editor controls compact, Arabic RTL consistent, and tables usable without excessive bold text or oversized buttons.

## Security and integration

- Authenticate student routes and enforce ownership on sessions, answers, results, and review endpoints.
- Apply existing entitlement rules only where their relevance is verified. Do not invent a new subscription requirement, grant paid access automatically, or change billing logic. Report any unresolved product access decision.
- Do not expose `correct_option_id`, correct flags, or explanations in pre-submission student payloads, page props, hidden HTML, or downloadable client bundles. Keep JSON inputs server-side. Use separate admin and student response serializers.
- Deliver hints only when requested and record use on the server. Return the correct option and explanation after an authorized submission.
- Validate submitted option membership. Prevent duplicate grading from double clicks and concurrent requests using transactions and uniqueness constraints.
- Render text safely, using the project's output escaping. Do not treat source text as executable HTML.
- Preserve the existing student and admin functionality outside this section.

## Roadmap with acceptance gates

Implement phases in order. Complete only the phase explicitly requested in the working prompt. At the end of each phase, report files changed, behavior delivered, verification performed, outstanding decisions, and the next phase. Do not mark a phase complete while its acceptance checks fail.

### Phase 1 Repository discovery and technical plan

Read repository instructions and inspect authentication, role checks, UI stack, route conventions, content models, database engine, local Docker workflow, and tests. Inspect the six supplied JSON files and validate their counts and relationships. Identify relevant existing access rules without changing them.

Deliver a repository-specific plan listing proposed routes, tables, relationships, components, commands, and checks. Explain what can be reused. No feature code, schema mutation, dependency upgrade, production access, or deployment in this phase.

Acceptance: the plan reflects verified repository facts; content inventory is confirmed; assumptions and any unresolved access decision are explicit; no unrelated changes.

### Phase 2 Database and development import

Implement migrations, models, relationships, validation, authorization foundations, and the development-only import/validation command. Add conflict-safe repeat import behavior and draft publication state. Seed the local/development database for review using the project's actual container commands. Do not guess container names or execute against a production connection.

Acceptance: 6 categories, 1,200 questions, 4,800 options, and 40 passages; valid passage links; second import creates no duplicates; admin-change conflicts are preserved and reported; malformed inputs do not partially import; existing records/progress remain intact.

### Phase 3 Admin management and content review

Implement category overview, question list/editor, passage management, preview, and publication controls. Show seeded drafts to the owner for review. Add essential validation and permission checks. Keep imports accessible as maintenance commands; a browser file uploader is not required for V1.

Acceptance: admins can review, edit, preview, publish and unpublish content; non-admins cannot use admin actions; editing one shared passage updates the appropriate previews; draft content remains inaccessible to students.

### Phase 4 Student practice and progress

Implement navigation, category cards, shared player, reading layout, session selection/resume, hints, server grading, feedback, results, review, and first-attempt metrics. Preserve session order and completed-answer history when content changes.

Acceptance: all six categories work on desktop/mobile; reading groups remain intact; students can resume; retries do not inflate first-attempt accuracy; correct answers/explanations are absent before submission; concurrent submissions do not double-count.

### Phase 5 Verification and release preparation

Run appropriate focused tests and the repository's required checks. Verify authorization, answer leakage, import reruns/conflicts, reading linkage, session persistence, historical results, RTL, keyboard use, and long content. Use the real supplied content for manual review across all six types.

Document exact development setup, import/validation commands, publication workflow, and proposed deployment/rollback steps. Prepare a migration/content rollout plan and note how existing attempts are preserved. Do not execute production seeding or deployment under this guide.

Acceptance: checks pass or concrete blockers are documented; the owner can review the feature locally; commands are verified against the repository; deployment remains a separate explicit action.

## Outside the first version

Timed exams, cross-category mixed tests, leaderboards, AI hints/explanations, audio/images, arbitrary question formats, new billing rules, and a general-purpose exam builder are outside scope. Do not add speculative abstractions or dependencies for them.

## Initial prompt for Codex

Read `QUDRAT_IMPLEMENTATION.md` and inspect the six JSON files in `qudrat_content/`. This is the existing Yarub project, separate from ARRIB. Follow repository instructions and implement Phase 1 only: inspect the current architecture, validate the supplied content, and provide a concrete repository-specific implementation plan. Do not modify application code, dependencies, or database schema, seed any database, or deploy anything yet. Report verified facts, proposed files/routes/tables/components, relevant access rules, open decisions, and the checks needed for later phases.
