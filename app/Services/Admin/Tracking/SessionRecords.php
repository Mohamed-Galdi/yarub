<?php

namespace App\Services\Admin\Tracking;

use App\Models\ArabicSkillSession;
use App\Models\QudratPracticeSession;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

class SessionRecords
{
    public function __construct(private StudentTracking $tracking, private ProgramMetrics $metrics) {}

    public function history(int $studentId, string $program, ?int $typeId = null, string $pageName = 'page', ?int $page = null): LengthAwarePaginator
    {
        $this->tracking->student($studentId);
        $definition = $this->metrics->definition($program);
        $query = $program === 'qudrat'
            ? QudratPracticeSession::with('category')->withCount(['items', 'answers', 'answers as correct_count' => fn ($q) => $q->where('is_correct', true), 'answers as hints_count' => fn ($q) => $q->where('used_hint', true)])
            : ArabicSkillSession::with(['bank', 'sourceModel'])->withCount(['items as submitted_count' => fn ($q) => $q->whereNotNull('submitted_at')]);

        return $query->where('user_id', $studentId)
            ->when($typeId !== null, fn ($q) => $q->where($definition['type_key'], $typeId))
            ->orderByDesc('started_at')->orderByDesc('id')->paginate(20, ['*'], $pageName, $page)->withQueryString()
            ->through(fn ($session) => $this->summary($program, $session));
    }

    public function review(int $studentId, string $program, int $sessionId): array
    {
        $this->tracking->student($studentId);
        $this->metrics->definition($program);
        $session = $program === 'qudrat'
            ? QudratPracticeSession::with(['category', 'items.answer'])->where('user_id', $studentId)->findOrFail($sessionId)
            : ArabicSkillSession::with(['bank', 'sourceModel', 'items'])->where('user_id', $studentId)->findOrFail($sessionId);
        Gate::authorize('review', $session);

        if ($program === 'qudrat') {
            $session->setAttribute('items_count', $session->items->count());
            $answers = $session->items->pluck('answer')->filter();
            $session->setAttribute('answers_count', $answers->count());
            $session->setAttribute('correct_count', $answers->where('is_correct', true)->count());
            $session->setAttribute('hints_count', $answers->where('used_hint', true)->count());
        } else {
            $session->setAttribute('submitted_count', $session->items->whereNotNull('submitted_at')->count());
        }

        return ['session' => $this->summary($program, $session),
            'items' => $session->items->map(function ($item) use ($program) {
                $snapshot = $item->content_snapshot;
                $valid = $this->validSnapshot($program, $snapshot);
                $submitted = $program === 'qudrat' ? $item->answer?->submitted_at : $item->submitted_at;
                $response = $program === 'qudrat' ? $item->answer?->selected_option_key : $item->response;
                $result = $program === 'qudrat'
                    ? ($item->answer ? ($item->answer->is_correct ? 'correct' : 'incorrect') : 'unanswered')
                    : ($submitted ? ($item->result ?? 'unavailable') : 'unanswered');
                $keys = $program === 'qudrat'
                    ? ['prompt', 'options', 'category', 'title', 'passage', 'correct_key', 'hint', 'explanation']
                    : ['prompt', 'options', 'matching_items', 'skill', 'type', 'answer', 'hint', 'explanation', 'scoring', 'max_score', 'bank', 'model_title', 'content_revision'];

                return ['id' => $item->id, 'question_id' => $item->question_id, 'position' => $item->position,
                    'snapshot_available' => $valid, 'question' => $valid ? array_intersect_key($snapshot, array_flip($keys)) : null,
                    'response' => $submitted ? $response : null, 'result' => $result,
                    'submitted_at' => $submitted?->toIso8601String(),
                    'used_hint' => $program === 'qudrat' ? ($item->answer?->used_hint ?? ($item->hint_requested_at !== null)) : null,
                    'earned_points' => $program === 'arabic_skills' && $submitted ? $item->earned_points : null];
            })->all()];
    }

    private function summary(string $program, QudratPracticeSession|ArabicSkillSession $session): array
    {
        $qudrat = $program === 'qudrat';
        $numerator = (int) ($qudrat ? $session->correct_count : $session->earned_points);
        $denominator = (int) ($qudrat ? $session->answers_count : $session->available_points);
        $validScore = $denominator > 0 && $numerator >= 0 && $numerator <= $denominator;

        return ['id' => $session->id, 'type_id' => $qudrat ? $session->category_id : $session->bank_id,
            'current_type_title' => $qudrat ? $session->category->title : $session->bank->title,
            'mode' => $session->mode, 'model_id' => $qudrat ? null : $session->model_id,
            'current_model_title' => $qudrat ? null : $session->sourceModel?->title,
            'status' => $session->status, 'started_at' => $session->started_at?->toIso8601String(),
            'completed_at' => $session->completed_at?->toIso8601String(),
            'closed_at' => $qudrat ? $session->closed_at?->toIso8601String() : null,
            'closure_reason' => $qudrat ? $session->closure_reason : null,
            'abandoned_at' => $qudrat ? null : $session->abandoned_at?->toIso8601String(),
            'question_count' => (int) ($qudrat ? $session->items_count : $session->selected_question_count),
            'submitted_count' => (int) ($qudrat ? $session->answers_count : $session->submitted_count),
            'score' => ['kind' => $qudrat ? 'correct_answers' : 'points', 'earned' => $numerator,
                'out_of' => $denominator, 'valid' => $validScore,
                'percentage' => $validScore ? round($numerator / $denominator * 100, 2) : null,
                'final' => $session->status === 'completed'],
            'hint_count' => $qudrat ? (int) $session->hints_count : null];
    }

    private function validSnapshot(string $program, mixed $snapshot): bool
    {
        if (! is_array($snapshot) || ! is_string($snapshot['prompt'] ?? null) || ! is_array($snapshot['options'] ?? null)) {
            return false;
        }
        if ($program === 'qudrat') {
            if (count($snapshot['options']) !== 4 || ! array_is_list($snapshot['options'])) {
                return false;
            }
            foreach ($snapshot['options'] as $option) {
                if (! is_array($option) || ! is_string($option['key'] ?? null) || ! is_string($option['text'] ?? null)) {
                    return false;
                }
            }
            $keys = array_column($snapshot['options'], 'key');
            sort($keys);

            return $keys === ['a', 'b', 'c', 'd'] && is_string($snapshot['correct_key'] ?? null)
                && in_array($snapshot['correct_key'], $keys, true)
                && (($snapshot['category'] ?? null) !== 'reading_comprehension' || is_string($snapshot['passage'] ?? null));
        }

        try {
            app(\App\Services\ArabicSkills\ContentValidator::class)->validateQuestion($snapshot);
        } catch (\InvalidArgumentException) {
            return false;
        }

        return true;
    }
}
