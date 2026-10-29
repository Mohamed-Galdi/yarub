<?php

namespace App\Services\ArabicSkills;

use App\Models\ArabicSkillBank;
use App\Models\ArabicSkillModel;
use App\Models\ArabicSkillQuestion;
use App\Models\ArabicSkillSession;
use App\Models\ArabicSkillSessionQuestion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class Practice
{
    public function __construct(private Grader $grader, private Attempts $attempts) {}

    public function start(User $user, ArabicSkillBank $bank, string $mode = 'practice', ?ArabicSkillModel $model = null, ?string $setKey = null): ArabicSkillSession
    {
        Gate::forUser($user)->authorize('create', ArabicSkillSession::class);
        if (! in_array($mode, ['practice', 'model'], true) || ($mode === 'model') !== ($model !== null) || ($setKey !== null && $mode !== 'practice')) {
            throw ValidationException::withMessages(['mode' => 'اختر تدريبًا سريعًا أو نموذجًا محددًا.']);
        }

        return DB::transaction(function () use ($user, $bank, $mode, $model, $setKey) {
            // All mutations lock the owner first, serializing starts/answers across browser tabs.
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $bank = ArabicSkillBank::whereKey($bank->id)->lockForUpdate()->firstOrFail();
            if ($model) {
                $model = ArabicSkillModel::whereKey($model->id)->where('bank_id', $bank->id)->lockForUpdate()->firstOrFail();
            }
            $active = $user->arabicSkillSessions()->where('bank_id', $bank->id)->where('mode', $mode)
                ->where('model_id', $model?->id)->where('practice_set_key', $setKey)->where('status', 'active')->lockForUpdate()->first();
            if ($active) {
                return $active;
            }
            if (! $bank->is_active) {
                throw ValidationException::withMessages(['practice' => 'هذا البنك غير متاح لبدء تدريب جديد حاليًا.']);
            }
            $query = ArabicSkillQuestion::where('bank_id', $bank->id)->where('is_active', true);
            $set = null;
            if ($setKey !== null) {
                $set = app(PracticeSets::class)->definitions($bank)->firstWhere('key', $setKey);
                abort_unless($set, 404);
                $records = $query->whereIn('id', $set['question_ids'])->lockForUpdate()->get()->keyBy('id');
                $questions = collect($set['question_ids'])->map(fn ($id) => $records->get($id))->filter();
                if ($questions->isEmpty()) {
                    throw ValidationException::withMessages(['practice' => 'لا توجد أسئلة منشورة في هذه المجموعة حاليًا.']);
                }
            } elseif ($model) {
                $questions = $query->where('model_id', $model->id)->orderBy('number')->orderBy('id')->lockForUpdate()->get();
                if ($questions->count() !== 30) {
                    throw ValidationException::withMessages(['practice' => 'النموذج غير متاح: يجب توفر أسئلته الثلاثين كاملة.']);
                }
            } else {
                $pool = $query->orderBy('id')->lockForUpdate()->get();
                if ($pool->count() < 10) {
                    throw ValidationException::withMessages(['practice' => 'التدريب غير متاح: يجب توفر عشرة أسئلة نشطة على الأقل.']);
                }
                $latest = $this->attempts->latest($user, $bank->id);
                $groups = $pool->groupBy(fn ($q) => ! $latest->has($q->id) ? 0 : ($latest[$q->id]->result === 'correct' ? 2 : 1));
                $questions = collect();
                foreach ([0, 1, 2] as $priority) {
                    $questions = $questions->concat(($groups[$priority] ?? collect())->shuffle());
                }
                $questions = $questions->take(10);
            }
            $session = ArabicSkillSession::create(['user_id' => $user->id, 'bank_id' => $bank->id, 'model_id' => $model?->id,
                'mode' => $mode, 'practice_set_key' => $setKey, 'practice_set_title' => $set['title'] ?? null,
                'status' => 'active', 'current_position' => 1, 'started_at' => now(),
                'selected_question_count' => $questions->count(), 'available_points' => $questions->sum('max_score'), 'earned_points' => 0]);
            foreach ($questions->values() as $index => $question) {
                $snapshot = $question->only(['source_id', 'content_revision', 'skill', 'type', 'prompt', 'options', 'matching_items', 'answer', 'explanation', 'hint', 'scoring', 'max_score']);
                $snapshot['bank'] = ['slug' => $bank->slug, 'title' => $bank->title, 'subtitle' => $bank->subtitle];
                $snapshot['model_title'] = $model?->title;
                $snapshot['practice_set_title'] = $set['title'] ?? null;
                $session->items()->create(['bank_id' => $bank->id, 'question_id' => $question->id, 'position' => $index + 1, 'content_snapshot' => $snapshot]);
            }

            return $session;
        }, 3);
    }

    public function show(User $user, ArabicSkillSession $session): array
    {
        return $this->locked($user, $session, fn ($current) => $this->projection($current));
    }

    public function answer(User $user, ArabicSkillSession $session, int $itemId, array $response): array
    {
        return $this->locked($user, $session, function ($current) use ($itemId, $response) {
            $this->requireActive($current);
            $item = $current->items()->whereKey($itemId)->lockForUpdate()->firstOrFail();
            // A network retry returns committed state; it cannot replace or grade an answer twice.
            if ($item->submitted_at === null) {
                abort_unless($item->position === $current->current_position, 409, 'أعد تحميل السؤال الحالي.');
                $graded = $this->grader->grade($item->content_snapshot, $response);
                $item->update($graded + ['submitted_at' => now()]);
                $current->update(['earned_points' => (int) $current->items()->sum('earned_points')]);
            }

            return $this->projection($current);
        });
    }

    public function next(User $user, ArabicSkillSession $session, int $itemId): array
    {
        return $this->locked($user, $session, function ($current) use ($itemId) {
            $item = $current->items()->whereKey($itemId)->lockForUpdate()->firstOrFail();
            // Replaying Next must not advance the next, unanswered question.
            if ($current->status === 'completed' || $item->position < $current->current_position) {
                return $this->projection($current);
            }
            $this->requireActive($current);
            abort_unless($item->position === $current->current_position, 409, 'أعد تحميل السؤال الحالي.');
            if ($item->submitted_at === null) {
                throw ValidationException::withMessages(['response' => 'أكمل الإجابة وتحقق منها قبل الانتقال.']);
            }
            if ($current->current_position === $current->selected_question_count) {
                abort_unless($current->items()->whereNull('submitted_at')->doesntExist(), 409);
                $current->update(['status' => 'completed', 'completed_at' => now(), 'earned_points' => (int) $current->items()->sum('earned_points')]);
            } else {
                $current->update(['current_position' => $current->current_position + 1]);
            }

            return $this->projection($current);
        });
    }

    public function hint(User $user, ArabicSkillSession $session, int $itemId): array
    {
        return $this->locked($user, $session, function ($current) use ($itemId) {
            $this->requireActive($current);
            $item = $current->items()->whereKey($itemId)->where('position', $current->current_position)->firstOrFail();
            abort_unless($item->submitted_at === null, 409);
            $hint = $item->content_snapshot['hint'];
            if ($hint === null) {
                throw ValidationException::withMessages(['hint' => 'لا يوجد تلميح لهذا السؤال.']);
            }

            return ['item_id' => $item->id, 'hint' => $hint];
        });
    }

    public function abandon(User $user, ArabicSkillSession $session): array
    {
        return $this->locked($user, $session, function ($current) {
            if ($current->status === 'abandoned') {
                return $this->summary($current);
            }
            $this->requireActive($current);
            $current->update(['status' => 'abandoned', 'abandoned_at' => now()]);

            return $this->summary($current);
        });
    }

    public function results(User $user, ArabicSkillSession $session): array
    {
        return $this->locked($user, $session, function ($current) {
            abort_unless($current->status === 'completed', 409, 'هذا التدريب غير مكتمل.');
            $items = $current->items()->get();

            return ['session' => $this->summary($current), 'correct' => $items->where('result', 'correct')->count(),
                'partial' => $items->where('result', 'partial')->count(), 'incorrect' => $items->where('result', 'incorrect')->count(),
                'reviews' => $items->map(fn ($item) => ['item_id' => $item->id, 'position' => $item->position,
                    'question' => $this->publicQuestion($item), 'response' => $item->response, 'feedback' => $this->feedback($item)])->all()];
        });
    }

    private function locked(User $user, ArabicSkillSession $session, callable $action): array
    {
        return DB::transaction(function () use ($user, $session, $action) {
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $current = ArabicSkillSession::whereKey($session->id)->lockForUpdate()->firstOrFail();
            // Authorize the freshly read owner/status, never the caller's potentially stale model.
            Gate::forUser($user)->authorize('view', $current);

            return $action($current);
        }, 3);
    }

    private function requireActive(ArabicSkillSession $session): void
    {
        abort_unless($session->status === 'active', 409, 'هذا التدريب مغلق. ابدأ تدريبًا جديدًا.');
    }

    public function summary(ArabicSkillSession $session): array
    {
        return ['id' => $session->id, 'bank_id' => $session->bank_id, 'model_id' => $session->model_id, 'mode' => $session->mode,
            'practice_set_key' => $session->practice_set_key, 'practice_set_title' => $session->practice_set_title,
            'status' => $session->status, 'position' => $session->current_position, 'question_count' => $session->selected_question_count,
            'available_points' => $session->available_points, 'earned_points' => $session->earned_points,
            'percentage' => $session->available_points ? $session->earned_points / $session->available_points * 100 : 0,
            'started_at' => $session->started_at?->toIso8601String(), 'completed_at' => $session->completed_at?->toIso8601String(),
            'abandoned_at' => $session->abandoned_at?->toIso8601String()];
    }

    private function projection(ArabicSkillSession $session): array
    {
        $data = ['session' => $this->summary($session)];
        if ($session->status !== 'active') {
            return $data;
        }
        $item = $session->items()->where('position', $session->current_position)->firstOrFail();

        return $data + ['item_id' => $item->id, 'question' => $this->publicQuestion($item),
            'response' => $item->submitted_at ? $item->response : null, 'feedback' => $this->feedback($item)];
    }

    private function publicQuestion(ArabicSkillSessionQuestion $item): array
    {
        $snapshot = $item->content_snapshot;

        return array_intersect_key($snapshot, array_flip(['skill', 'type', 'prompt', 'options', 'matching_items', 'max_score', 'bank', 'model_title', 'practice_set_title']))
            + ['has_hint' => $snapshot['hint'] !== null];
    }

    private function feedback(ArabicSkillSessionQuestion $item): ?array
    {
        if ($item->submitted_at === null) {
            return null;
        }
        $snapshot = $item->content_snapshot;

        return ['answer' => $snapshot['answer'], 'explanation' => $snapshot['explanation'],
            'earned_points' => $item->earned_points, 'max_score' => $snapshot['max_score'], 'result' => $item->result];
    }
}
