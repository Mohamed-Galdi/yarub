# Qudrat content extraction validation

The six supplied Word documents were extracted into six UTF-8 JSON files. Question text, choices, answer keys, explanations, hints, and shared reading passages were preserved. Document framing and source/editorial metadata were excluded from the application data.

| Category | Questions | Options | Explanations | Hints | Passages |
| --- | ---: | ---: | ---: | ---: | ---: |
| التناظر اللفظي | 200 | 800 | 200 | 200 | 0 |
| إكمال الجمل | 200 | 800 | 200 | 200 | 0 |
| الخطأ السياقي | 200 | 800 | 200 | 200 | 0 |
| المفردة الشاذة | 200 | 800 | 200 | 200 | 0 |
| استيعاب المقروء | 200 | 800 | 200 | 200 | 40 |
| الارتباط والاختلاف | 200 | 800 | 200 | 200 | 0 |
| Total | 1,200 | 4,800 | 1,200 | 1,200 | 40 |

## Checks completed

- Question numbers are consecutive from 1 through 200 in every source.
- Every question has four nonempty options in the original order.
- Every answer key references an existing option, and the answer text in the Word document matches that option (ignoring terminal punctuation).
- Every question has a nonempty explanation and hint.
- Every reading question references an extracted nonempty passage.
- Passage paragraphs are retained with blank-line separators.
- Generated question IDs and passage IDs are unique; option IDs are local to their question.
- All files parse successfully as JSON.

These are structural and source-fidelity checks. They are not an independent educational review of the questions, factual claims, explanations, or answer correctness. Source content has not been silently corrected. Review content in the development admin interface before publishing.

## Handoff

Extract `QUDRAT_READY_FOR_CODEX.zip` into the Yarub repository root. This places `QUDRAT_IMPLEMENTATION.md`, this validation report, and `qudrat_content/` together. Give Codex the initial prompt at the end of the implementation guide. The Word documents may be retained as source references but are not required for implementation.
