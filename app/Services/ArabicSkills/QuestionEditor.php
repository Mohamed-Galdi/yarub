<?php

namespace App\Services\ArabicSkills;

use App\Models\ArabicSkillBank;
use App\Models\ArabicSkillQuestion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class QuestionEditor
{
    public function revision(Model $record): string
    {
        // Aggregate counts from management listings are not stored content.
        return hash('sha256', serialize(array_intersect_key($record->getAttributes(), array_flip([
            ...$record->getFillable(), 'id', 'content_revision', 'created_at', 'updated_at',
        ]))));
    }

    public function checkRevision(Model $record, string $revision): void
    {
        if (! hash_equals($this->revision($record), $revision)) {
            throw ValidationException::withMessages(['revision' => 'عُدّل المحتوى بعد فتح الصفحة. أعد تحميل الصفحة وراجع أحدث نسخة قبل الحفظ.']);
        }
    }

    public function save(array $input, ?ArabicSkillQuestion $existing = null): ArabicSkillQuestion
    {
        $rules = [
            'bank_id' => ['required', 'integer', 'exists:arabic_skill_banks,id'],
            'type' => ['required', Rule::in(['multiple_choice', 'true_false', 'matching'])],
            'skill' => ['required', 'string', 'max:255'],
            'prompt' => ['required', 'string', 'max:20000'],
            'explanation' => ['required', 'string', 'max:20000'],
            'hint' => ['nullable', 'string', 'max:20000'],
            'revision' => [$existing ? 'required' : 'nullable', 'string'],
        ];
        $type = $input['type'] ?? '';
        $ids = $type === 'multiple_choice' ? ['a', 'b', 'c'] : ($type === 'matching' ? ['a', 'b'] : ['true', 'false']);
        if ($existing && $existing->type === $type) {
            $ids = array_column($existing->options, 'id');
        }
        $itemIds = $existing && $existing->type === 'matching' ? array_column($existing->matching_items, 'id') : ['item_1', 'item_2'];
        if ($type !== 'true_false') {
            foreach ($ids as $id) {
                $rules['options.'.$id] = ['required', 'string', 'max:20000'];
            }
        }
        if ($type === 'matching') {
            foreach ($itemIds as $id) {
                $rules['items.'.$id] = ['required', 'string', 'max:20000'];
                $rules['matches.'.$id] = ['required', 'string', Rule::in($ids)];
            }
        } else {
            $rules['correct'] = ['required', 'string', Rule::in($ids)];
        }
        $data = Validator::make($input, $rules, [], [
            'bank_id' => 'البنك', 'skill' => 'المهارة', 'prompt' => 'السؤال',
            'explanation' => 'تفسير الإجابة', 'correct' => 'الإجابة الصحيحة',
            'options.*' => 'نص الخيار', 'items.*' => 'عبارة المطابقة', 'matches.*' => 'المطابقة الصحيحة',
        ])->validate();

        return DB::transaction(function () use ($data, $existing, $ids, $type, $itemIds) {
            ArabicSkillBank::whereKey($data['bank_id'])->lockForUpdate()->firstOrFail();
            $question = $existing ? ArabicSkillQuestion::whereKey($existing->id)->lockForUpdate()->firstOrFail() : new ArabicSkillQuestion;
            if ($existing) {
                $this->checkRevision($question, $data['revision']);
                if ((int) $data['bank_id'] !== $question->bank_id) {
                    throw ValidationException::withMessages(['bank_id' => 'لا يمكن تغيير بنك السؤال الأصلي.']);
                }
            }
            $labels = ['a' => 'أ', 'b' => 'ب', 'c' => 'ج', 'd' => 'د', 'true' => 'صح', 'false' => 'خطأ'];
            $options = [];
            foreach ($ids as $id) {
                $old = collect($question->options ?? [])->firstWhere('id', $id);
                $options[] = ['id' => $id, 'label' => $old['label'] ?? $labels[$id], 'text' => $type === 'true_false' ? $labels[$id] : $data['options'][$id]];
            }
            $items = $matches = [];
            if ($type === 'matching') {
                foreach ($itemIds as $id) {
                    $items[] = ['id' => $id, 'text' => $data['items'][$id]];
                    $matches[] = ['item_id' => $id, 'option_id' => $data['matches'][$id]];
                }
            }
            $question->fill([
                'bank_id' => (int) $data['bank_id'], 'type' => $type, 'skill' => $data['skill'],
                'prompt' => $data['prompt'], 'explanation' => $data['explanation'], 'hint' => $data['hint'] ?? null,
                'options' => $options, 'matching_items' => $items,
                'answer' => $type === 'matching' ? ['matches' => $matches] : ['option_id' => $data['correct']],
                'scoring' => $type === 'matching' ? ['mode' => 'per_match', 'points_per_correct_match' => 1] : ['mode' => 'all_or_nothing'], 'max_score' => $type === 'matching' ? count($itemIds) : 2,
            ])->save();

            // Keep the import baseline and existing activation state; snapshots are never edited.
            return $question->fresh();
        });
    }

    public function archive(ArabicSkillQuestion $question, bool $archived, string $revision): void
    {
        DB::transaction(function () use ($question, $archived, $revision) {
            ArabicSkillBank::whereKey($question->bank_id)->lockForUpdate()->firstOrFail();
            $locked = ArabicSkillQuestion::whereKey($question->id)->lockForUpdate()->firstOrFail();
            $this->checkRevision($locked, $revision);
            $locked->update(['is_active' => ! $archived]);
        });
    }
}
