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
}
