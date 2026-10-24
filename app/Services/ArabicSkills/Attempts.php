<?php

namespace App\Services\ArabicSkills;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Attempts
{
    public function latest(User $user, int $bankId): Collection
    {
        // Timestamp determines recency; ID breaks ties, including submissions in the same second.
        $ranked = DB::table('arabic_skill_session_questions as item')
            ->join('arabic_skill_sessions as session', 'session.id', '=', 'item.session_id')
            ->where('session.user_id', $user->id)->where('item.bank_id', $bankId)->whereNotNull('item.submitted_at')
            ->select(['item.question_id', 'item.result', 'item.earned_points'])
            ->selectRaw('ROW_NUMBER() OVER (PARTITION BY item.question_id ORDER BY item.submitted_at DESC, item.id DESC) AS attempt_rank');

        return DB::query()->fromSub($ranked, 'ranked')->where('attempt_rank', 1)->get()->keyBy('question_id');
    }
}
