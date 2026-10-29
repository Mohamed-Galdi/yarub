<?php

namespace App\Services\Qudrat;

use App\Models\QudratCategory;
use App\Models\QudratPracticeSession;
use App\Models\QudratQuestion;
use App\Models\QudratSessionAnswer;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class Progress
{
    public function categories(User $user): array
    {
        $bank = QudratQuestion::published()->get(['id', 'category_id']);
        $first = QudratSessionAnswer::whereIn('id', DB::table('qudrat_session_answers')
            ->selectRaw('MIN(id)')->where('user_id', $user->id)->groupBy('question_id'))
            ->whereIn('question_id', $bank->pluck('id'))->get(['question_id', 'is_correct']);
        $active = QudratPracticeSession::where('user_id', $user->id)->where('status', 'active')->get()->keyBy('category_id');

        return QudratCategory::where('is_published', true)->orderBy('sort_order')->get()->map(function ($category) use ($bank, $first, $active) {
            $ids = $bank->where('category_id', $category->id)->pluck('id');
            $attempts = $first->whereIn('question_id', $ids);

            return ['category' => $category, 'total' => $ids->count(), 'attempted' => $attempts->count(),
                'progress' => $ids->isEmpty() ? 0 : (int) round($attempts->count() / $ids->count() * 100),
                'accuracy' => $attempts->isEmpty() ? null : (int) round($attempts->where('is_correct', true)->count() / $attempts->count() * 100),
                'active' => $active->get($category->id)];
        })->all();
    }

    public function sets(User $user, QudratCategory $category): array
    {
        $sessions = $user->qudratSessions()->where('category_id', $category->id)->whereNotNull('practice_set_key')
            ->withCount(['items', 'answers', 'answers as correct_count' => fn ($q) => $q->where('is_correct', true)])
            ->orderByDesc('completed_at')->orderByDesc('id')->get()->map(fn ($s) => [
                'id' => $s->id, 'practice_set_key' => $s->practice_set_key, 'status' => $s->status,
                'position' => $s->current_position, 'question_count' => $s->items_count,
                'percentage' => $s->answers_count ? $s->correct_count / $s->answers_count * 100 : 0,
            ]);

        return app(\App\Services\PracticeSetProgress::class)->cards(app(PracticeSets::class)->definitions($category), $sessions, $category->is_published);
    }
}
