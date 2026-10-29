<?php

namespace App\Services\Qudrat;

use App\Models\QudratCategory;
use Illuminate\Support\Collection;

class PracticeSets
{
    public function definitions(QudratCategory $category): Collection
    {
        // IDs preserve import/addition order even when an administrator changes display order.
        // Group before checking publication so archiving cannot shift set boundaries.
        $questions = $category->questions()->orderBy('id')
            ->get(['id', 'passage_id', 'is_published', 'archived_at']);
        $groups = $category->slug === 'reading_comprehension'
            ? $questions->groupBy(fn ($q) => $q->passage_id === null ? 'question-'.$q->id : 'passage-'.$q->passage_id)->values()
            : $questions->map(fn ($q) => collect([$q]));
        $sets = collect();
        $batch = collect();
        foreach ($groups as $group) {
            $batch = $batch->concat($group);
            if ($batch->count() >= 10) {
                $sets->push($this->definition($batch, $sets->count() + 1));
                $batch = collect();
            }
        }
        if ($batch->isNotEmpty()) {
            $sets->push($this->definition($batch, $sets->count() + 1));
        }

        return $sets;
    }

    private function definition(Collection $questions, int $number): array
    {
        return ['key' => 'questions-'.$questions->first()->id, 'title' => 'المجموعة '.$number,
            'question_ids' => $questions->pluck('id')->values()->all(), 'total' => $questions->count(),
            'available' => $questions->filter(fn ($q) => $q->is_published && $q->archived_at === null)->count()];
    }
}
