<?php

namespace App\Services\Admin\Tracking;

use App\Models\ArabicSkillQuestion;
use App\Models\QudratQuestion;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

class ProgramMetrics
{
    public function definition(string $program): array
    {
        Gate::authorize('view-student-tracking');

        return match ($program) {
            'qudrat' => ['sessions' => 'qudrat_practice_sessions', 'types' => 'qudrat_categories', 'type_key' => 'category_id', 'active' => 'is_published'],
            'arabic_skills' => ['sessions' => 'arabic_skill_sessions', 'types' => 'arabic_skill_banks', 'type_key' => 'bank_id', 'active' => 'is_active'],
            default => throw new InvalidArgumentException('Unknown tracking program.'),
        };
    }

    public function eligible(string $program): Builder
    {
        $this->definition($program);

        return $program === 'qudrat'
            ? QudratQuestion::published()->select('id', 'category_id as type_id')->toBase()
            : ArabicSkillQuestion::active()->select('id', 'bank_id as type_id')->toBase();
    }

    public function submissions(string $program, ?int $userId = null): Builder
    {
        $definition = $this->definition($program);
        $query = $program === 'qudrat'
            ? DB::table('qudrat_session_answers as answer')->join($definition['sessions'].' as session', 'session.id', '=', 'answer.session_id')
                ->select('answer.id', 'session.user_id', 'answer.question_id', 'answer.submitted_at', 'session.category_id as type_id', 'answer.is_correct')
                ->whereColumn('answer.user_id', 'session.user_id')
            : DB::table('arabic_skill_session_questions as answer')->join($definition['sessions'].' as session', 'session.id', '=', 'answer.session_id')
                ->select('answer.id', 'session.user_id', 'answer.question_id', 'answer.submitted_at', 'session.bank_id as type_id')
                ->selectRaw("CASE WHEN answer.response IS NULL THEN NULL WHEN answer.result = 'correct' THEN 1 WHEN answer.result IN ('partial', 'incorrect') THEN 0 ELSE NULL END AS is_correct");

        return $query->whereNotNull('answer.submitted_at')->when($userId !== null, fn ($q) => $q->where('session.user_id', $userId));
    }

    public function firstAnswers(string $program, ?int $userId = null): Builder
    {
        // Rank before discarding unavailable content or unknown results: a later answer
        // must never masquerade as the first answer to an edited/historical question.
        $ranked = DB::query()->fromSub($this->submissions($program, $userId), 'submitted')
            ->select('submitted.*')
            ->selectRaw('ROW_NUMBER() OVER (PARTITION BY user_id, question_id ORDER BY submitted_at, id) AS attempt_rank');

        return DB::query()->fromSub($ranked, 'ranked')->where('attempt_rank', 1);
    }

    public function answered(string $program, ?int $userId = null, bool $byType = false): Builder
    {
        $query = DB::query()->fromSub($this->firstAnswers($program, $userId), 'first_answer')
            ->joinSub($this->eligible($program), 'eligible', 'eligible.id', '=', 'first_answer.question_id')
            ->select('first_answer.user_id')
            ->selectRaw('COUNT(*) AS answered, COUNT(first_answer.is_correct) AS known_first_answers, COALESCE(SUM(first_answer.is_correct), 0) AS first_correct')
            ->groupBy('first_answer.user_id');

        if ($byType) {
            $query->addSelect('eligible.type_id')->groupBy('eligible.type_id');
        }

        return $query;
    }

    public function activity(string $program, ?int $userId = null, bool $byType = false): Builder
    {
        $query = DB::query()->fromSub($this->submissions($program, $userId), 'submitted')
            ->select('user_id')->selectRaw('COUNT(*) AS submissions, MAX(submitted_at) AS last_answer_at')->groupBy('user_id');
        if ($byType) {
            $query->addSelect('type_id')->groupBy('type_id');
        }

        return $query;
    }

    public function completions(string $program, ?int $userId = null, bool $byType = false): Builder
    {
        $definition = $this->definition($program);
        $query = DB::table($definition['sessions'])->select('user_id')
            ->selectRaw('COUNT(*) AS completed_sessions, MAX(completed_at) AS last_completion_at')
            ->where('status', 'completed')->when($userId !== null, fn ($q) => $q->where('user_id', $userId))->groupBy('user_id');
        if ($byType) {
            $query->addSelect($definition['type_key'].' as type_id')->groupBy($definition['type_key']);
        }

        return $query;
    }

    public function types(string $program, int $userId): array
    {
        $definition = $this->definition($program);
        $totals = DB::query()->fromSub($this->eligible($program), 'eligible')
            ->select('type_id')->selectRaw('COUNT(*) AS total')->groupBy('type_id');
        $rows = DB::table($definition['types'].' as type')
            ->leftJoinSub($totals, 'totals', 'totals.type_id', '=', 'type.id')
            ->leftJoinSub($this->answered($program, $userId, true), 'answers', 'answers.type_id', '=', 'type.id')
            ->leftJoinSub($this->activity($program, $userId, true), 'activity', 'activity.type_id', '=', 'type.id')
            ->leftJoinSub($this->completions($program, $userId, true), 'completions', 'completions.type_id', '=', 'type.id')
            ->select('type.id', 'type.title', 'type.slug', 'type.'.$definition['active'].' as available', 'totals.total', 'answers.answered', 'answers.known_first_answers', 'answers.first_correct', 'activity.submissions', 'activity.last_answer_at', 'completions.completed_sessions', 'completions.last_completion_at')
            ->orderBy('type.sort_order')->orderBy('type.id')->get();

        // Include hidden types for historical activity; eligible totals are zero for them.
        return $rows->map(fn ($row) => ['id' => $row->id, 'title' => $row->title, 'slug' => $row->slug,
            'available' => (bool) $row->available] + $this->format((array) $row))->all();
    }

    public function format(array $values): array
    {
        Gate::authorize('view-student-tracking');
        $total = (int) ($values['total'] ?? 0);
        $answered = (int) ($values['answered'] ?? 0);
        $known = (int) ($values['known_first_answers'] ?? 0);
        $correct = (int) ($values['first_correct'] ?? 0);
        $last = max($values['last_answer_at'] ?? '', $values['last_completion_at'] ?? '') ?: null;

        return ['available_questions' => $total, 'answered_questions' => $answered,
            'coverage' => $total ? round($answered / $total * 100, 2) : null,
            'known_first_answers' => $known, 'first_correct' => $correct,
            // Incomplete first-answer evidence must not look like complete accuracy.
            'first_attempt_accuracy' => $known && $known === $answered ? round($correct / $known * 100, 2) : null,
            'first_answer_results_known' => $known === $answered,
            'history_scope' => 'recorded_submissions',
            'participated' => (int) ($values['submissions'] ?? 0) > 0,
            'completed_sessions' => (int) ($values['completed_sessions'] ?? 0), 'last_practice_at' => $last];
    }
}
