<?php

namespace App\Services\ArabicSkills;

use App\Models\ArabicSkillBank;
use Illuminate\Support\Collection;

class PracticeSets
{
    public function definitions(ArabicSkillBank $bank): Collection
    {
        // Include archived records when grouping: archiving never moves later questions into another set.
        $questions = $bank->questions()->orderBy('number')->orderBy('id')
            ->get(['id', 'source_id', 'context_group_number', 'number', 'is_active']);
        $coffee = $questions->filter(fn ($q) => $bank->slug === 'burhan'
            && preg_match('/^supp_burhan_coffee_filtration_0[1-8]$/', $q->source_id))->sortBy('source_id');
        $sets = collect();
        if ($coffee->isNotEmpty()) {
            $sets->push($this->definition('coffee-filtration', 'وصف تجربة ترشيح القهوة', $coffee));
        }
        foreach ($questions->whereNotNull('context_group_number')->groupBy('context_group_number')->sortKeys() as $number => $group) {
            $sets->push($this->definition('context-'.$number, 'المجموعة '.$number, $group));
        }
        $extra = $questions->whereNull('context_group_number')->whereNotIn('id', $coffee->pluck('id')->all())->sortBy('id');
        foreach ($extra->chunk(10) as $index => $group) {
            $sets->push($this->definition('extra-'.$group->first()->id, 'أسئلة إضافية — المجموعة '.($index + 1), $group));
        }

        return $sets;
    }

    private function definition(string $key, string $title, Collection $questions): array
    {
        return ['key' => $key, 'title' => $title, 'question_ids' => $questions->pluck('id')->values()->all(),
            'total' => $questions->count(), 'available' => $questions->where('is_active', true)->count()];
    }
}
