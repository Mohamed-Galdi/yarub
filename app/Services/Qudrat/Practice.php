<?php

namespace App\Services\Qudrat;

use App\Models\QudratCategory;
use App\Models\QudratPracticeSession;
use App\Models\QudratQuestion;
use App\Models\QudratSessionAnswer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class Practice
{
    public function start(User $user, QudratCategory $category, string $mode): QudratPracticeSession
    {
        Gate::forUser($user)->authorize('create', QudratPracticeSession::class);

        return DB::transaction(function () use ($user, $category, $mode) {
            // All student operations lock the owner first: starts and first answers serialize per student.
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $category = QudratCategory::whereKey($category->id)->lockForUpdate()->firstOrFail();
            abort_unless($category->is_published, 404);
            $active = QudratPracticeSession::where('user_id', $user->id)->where('category_id', $category->id)
                ->where('status', 'active')->lockForUpdate()->first();
            if ($active && $this->available($active)) {
                return $active;
            }
            $seen = QudratSessionAnswer::where('user_id', $user->id)->pluck('question_id')->unique();
            $bank = QudratQuestion::published()->where('category_id', $category->id)
                ->with(['options', 'passage'])->orderBy('sort_order')->orderBy('id')->lockForUpdate()->get();
            if ($category->slug === 'reading_comprehension') {
                $groups = $bank->groupBy('passage_id')->sortBy(fn ($group) => [$group->first()->passage?->sort_order, $group->first()->passage_id])->filter(fn ($group) => $mode === 'review'
                    ? $group->contains(fn ($question) => $seen->contains($question->id))
                    : $group->contains(fn ($question) => ! $seen->contains($question->id)));
                $questions = collect();
                foreach ($groups as $group) {
                    $questions = $questions->concat($group);
                    if ($questions->count() >= 10) {
                        break;
                    }
                }
            } else {
                $questions = $bank->filter(fn ($question) => $mode === 'review'
                    ? $seen->contains($question->id) : ! $seen->contains($question->id));
                if ($mode === 'review') {
                    $last = QudratSessionAnswer::where('user_id', $user->id)->selectRaw('question_id, MAX(id) AS last_id')->groupBy('question_id')->pluck('last_id', 'question_id');
                    $questions = $questions->sortBy(fn ($question) => $last[$question->id] ?? 0);
                }
                $questions = $questions->take(10);
            }
            if ($questions->isEmpty()) {
                throw ValidationException::withMessages(['practice' => $mode === 'review'
                    ? 'لا توجد أسئلة سابقة للمراجعة. ابدأ تدريبًا جديدًا أولًا.'
                    : 'لا توجد أسئلة جديدة متاحة. يمكنك مراجعة الأسئلة السابقة.']);
            }
            $session = QudratPracticeSession::create(['user_id' => $user->id, 'category_id' => $category->id,
                'mode' => $mode, 'started_at' => now()]);
            foreach ($questions->values() as $position => $question) {
                $session->items()->create(['question_id' => $question->id, 'position' => $position + 1,
                    'content_snapshot' => ['prompt' => $question->prompt, 'hint' => $question->hint,
                        'explanation' => $question->explanation, 'correct_key' => $question->correct_option_key,
                        'category' => $category->slug, 'title' => $category->title, 'passage' => $question->passage?->text,
                        'options' => $question->options->map(fn ($option) => ['key' => $option->local_key, 'text' => $option->text])->all()]]);
            }

            return $session;
        }, 3);
    }

    public function visit(User $user, QudratPracticeSession $session, string $action = 'show', ?int $itemId = null, ?string $key = null): array
    {
        Gate::forUser($user)->authorize('view', $session);

        return DB::transaction(function () use ($user, $session, $action, $itemId, $key) {
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            QudratCategory::whereKey($session->category_id)->lockForUpdate()->firstOrFail();
            $session = QudratPracticeSession::whereKey($session->id)->lockForUpdate()->firstOrFail();
            if ($session->status !== 'active' || ! $this->available($session)) {
                return ['session' => $session];
            }
            $item = $session->items()->where('position', $session->current_position)->lockForUpdate()->firstOrFail();
            if ($itemId !== null) {
                // Old-page replays never apply an action to a different question.
                abort_unless($item->id === $itemId, 409, 'انتقلت إلى سؤال آخر. أعد تحميل التدريب.');
            }
            $answer = $item->answer()->first();
            if (in_array($action, ['selection', 'answer'], true)) {
                if (! in_array($key, array_column($item->content_snapshot['options'], 'key'), true)) {
                    throw ValidationException::withMessages(['option' => 'اختر إجابة من الخيارات المعروضة.']);
                }
                if (! $answer) {
                    $item->update(['draft_option_key' => $key]);
                    if ($action === 'answer') {
                        $answer = QudratSessionAnswer::create(['session_question_id' => $item->id,
                            'session_id' => $session->id, 'user_id' => $user->id, 'question_id' => $item->question_id,
                            'selected_option_key' => $key, 'is_correct' => $key === $item->content_snapshot['correct_key'],
                            'used_hint' => $item->hint_requested_at !== null, 'submitted_at' => now()]);
                    }
                }
            } elseif ($action === 'hint' && ! $answer && ! $item->hint_requested_at) {
                $item->update(['hint_requested_at' => now()]);
            } elseif ($action === 'next') {
                if (! $answer) {
                    throw ValidationException::withMessages(['practice' => 'تحقق من الإجابة قبل الانتقال للسؤال التالي.']);
                }
                if ($session->items()->where('position', '>', $item->position)->exists()) {
                    $session->update(['current_position' => $item->position + 1]);
                } else {
                    $session->update(['status' => 'completed', 'completed_at' => now()]);
                }

                return ['session' => $session];
            }
            if ($action === 'hint' && ! $answer && $key !== null
                && in_array($key, array_column($item->content_snapshot['options'], 'key'), true)) {
                $item->update(['draft_option_key' => $key]);
            }
            $snapshot = $item->content_snapshot;

            // Explicit response allowlist: never serialize the model or the private snapshot.
            return ['session' => $session, 'itemId' => $item->id, 'total' => $session->items()->count(),
                'question' => array_intersect_key($snapshot, array_flip(['prompt', 'options', 'category', 'title', 'passage'])),
                'selected' => $answer?->selected_option_key ?? $item->draft_option_key,
                'hint' => $item->hint_requested_at ? $snapshot['hint'] : null,
                'feedback' => $answer ? ['correct' => $answer->is_correct, 'correct_key' => $snapshot['correct_key'],
                    'explanation' => $snapshot['explanation'], 'used_hint' => $answer->used_hint] : null];
        }, 3);
    }

    private function available(QudratPracticeSession $session): bool
    {
        $ids = $session->items()->whereDoesntHave('answer')->pluck('question_id');
        $allowed = QudratQuestion::published()->whereIn('id', $ids)->pluck('id');
        $restricted = $ids->diff($allowed);
        // Also close a session if the category was hidden after its last answer.
        $categoryPublished = QudratCategory::whereKey($session->category_id)->where('is_published', true)->exists();
        if ($restricted->isNotEmpty() || ! $categoryPublished) {
            $session->items()->whereIn('question_id', $restricted)->update(['availability' => 'restricted']);
            $session->update(['status' => 'closed', 'closed_at' => now(), 'closure_reason' => 'content_unavailable']);

            return false;
        }

        return true;
    }

    public function results(User $user, QudratPracticeSession $session): array
    {
        Gate::forUser($user)->authorize('view', $session);
        abort_if($session->status === 'active', 409);
        $items = $session->items()->with('answer')->get();
        $answers = $items->filter(fn ($item) => $item->answer !== null);

        return ['session' => $session, 'total' => $items->count(), 'answered' => $answers->count(),
            'correct' => $answers->filter(fn ($item) => $item->answer->is_correct)->count(),
            'hints' => $answers->filter(fn ($item) => $item->answer->used_hint)->count(),
            'reviews' => $answers->map(fn ($item) => ['question' => array_intersect_key($item->content_snapshot,
                array_flip(['prompt', 'options', 'category', 'title', 'passage'])),
                'selected' => $item->answer->selected_option_key, 'correct' => $item->answer->is_correct,
                'correct_key' => $item->content_snapshot['correct_key'], 'explanation' => $item->content_snapshot['explanation'],
                'used_hint' => $item->answer->used_hint])->values()->all()];
    }
}
