<?php

namespace App\Services\Qudrat;

use App\Models\QudratCategory;
use App\Models\QudratPassage;
use App\Models\QudratQuestion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class QuestionEditor
{
    public function revision(QudratQuestion $question): string
    {
        $question->loadMissing('options');

        return hash('sha256', serialize([$question->getAttributes(), $question->options->map->getAttributes()->all()]));
    }

    public function save(array $input, ?QudratQuestion $existing = null): QudratQuestion
    {
        $data = Validator::make($input, [
            'category_id' => ['required', 'integer', Rule::exists('qudrat_categories', 'id')->whereIn('slug', array_column(ContentValidator::FILES, 0))],
            'prompt' => ['required', 'string', 'max:10000'],
            'hint' => ['required', 'string', 'max:10000'],
            'explanation' => ['required', 'string', 'max:10000'],
            'sort_order' => ['required', 'integer', 'min:1', 'max:65535'],
            'passage_id' => ['nullable', 'integer'],
            'correct_option_key' => ['required', Rule::in(['a', 'b', 'c', 'd'])],
            'options' => ['required', 'array:a,b,c,d', 'size:4'],
            'options.a' => ['required', 'string', 'max:10000'],
            'options.b' => ['required', 'string', 'max:10000'],
            'options.c' => ['required', 'string', 'max:10000'],
            'options.d' => ['required', 'string', 'max:10000'],
            'revision' => [$existing ? 'required' : 'nullable', 'string'],
        ], [], ['prompt' => 'السؤال', 'hint' => 'التلميح', 'explanation' => 'التفسير', 'category_id' => 'القسم', 'passage_id' => 'النص القرائي'])->validate();

        return DB::transaction(function () use ($data, $existing) {
            $categoryIds = array_filter([$data['category_id'], $existing?->category_id]);
            $categories = QudratCategory::whereIn('id', $categoryIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $category = $categories[$data['category_id']];
            $passageId = $data['passage_id'] ?? null;
            if ($category->slug === 'reading_comprehension') {
                $passage = QudratPassage::whereKey($passageId)->where('category_id', $category->id)->lockForUpdate()->first();
                if (! $passage) {
                    throw ValidationException::withMessages(['passage_id' => 'اختر نصًا قرائيًا من القسم نفسه.']);
                }
            } elseif ($passageId !== null) {
                throw ValidationException::withMessages(['passage_id' => 'النص القرائي متاح لقسم استيعاب المقروء فقط.']);
            }

            $question = $existing ? QudratQuestion::whereKey($existing->id)->lockForUpdate()->firstOrFail() : new QudratQuestion;
            $question->setRelation('options', $question->exists ? $question->options()->lockForUpdate()->get() : collect());
            if ($existing) {
                $this->checkRevision($question, $data['revision']);
                if ($question->category_id !== $category->id && $question->sessionQuestions()->exists()) {
                    throw ValidationException::withMessages(['category_id' => 'لا يمكن نقل سؤال مرتبط بتدريب سابق إلى قسم آخر.']);
                }
            } else {
                $question->external_key = 'admin_'.str_replace('-', '', (string) Str::uuid());
            }
            $question->fill([
                'category_id' => $category->id, 'passage_id' => $passageId,
                'prompt' => $data['prompt'] ?? '', 'hint' => $data['hint'] ?? null,
                'explanation' => $data['explanation'] ?? null, 'sort_order' => $data['sort_order'],
                'correct_option_key' => $data['correct_option_key'], 'is_published' => $question->archived_at === null,
            ])->save();
            foreach (['a', 'b', 'c', 'd'] as $position => $key) {
                $option = $question->options->firstWhere('local_key', $key) ?? $question->options()->make(['local_key' => $key]);
                $option->fill(['text' => $data['options'][$key], 'sort_order' => $position + 1])->save();
            }

            // Keep the import baseline unchanged so the next maintenance import reports admin edits.
            return $question->fresh(['category', 'passage', 'options']);
        });
    }

    public function archive(QudratQuestion $question, bool $archived, string $revision): void
    {
        DB::transaction(function () use ($question, $archived, $revision) {
            QudratCategory::whereKey($question->category_id)->lockForUpdate()->firstOrFail();
            $locked = QudratQuestion::whereKey($question->id)->lockForUpdate()->firstOrFail();
            $locked->setRelation('options', $locked->options()->lockForUpdate()->get());
            $this->checkRevision($locked, $revision);
            if (! $archived) {
                // Restoring legacy content must pass the same complete-content checks as creation.
                $locked = $this->save([
                    'category_id' => $locked->category_id, 'passage_id' => $locked->passage_id,
                    'prompt' => $locked->prompt, 'hint' => $locked->hint, 'explanation' => $locked->explanation,
                    'sort_order' => $locked->sort_order, 'correct_option_key' => $locked->correct_option_key,
                    'options' => $locked->options->pluck('text', 'local_key')->all(), 'revision' => $revision,
                ], $locked);
            }
            $locked->update(['is_published' => ! $archived, 'archived_at' => $archived ? now() : null]);
        });
    }

    private function checkRevision(QudratQuestion $question, string $revision): void
    {
        if (! hash_equals($this->revision($question), $revision)) {
            throw ValidationException::withMessages(['revision' => 'عُدّل السؤال بعد فتح الصفحة. أعد تحميله وراجع أحدث نسخة قبل الحفظ.']);
        }
    }
}
