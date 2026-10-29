<?php

namespace App\Services\ArabicSkills;

use App\Models\ArabicSkillBank;
use App\Models\ArabicSkillSession;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class Progress
{
    public function __construct(private Attempts $attempts, private Practice $practice) {}

    public function banks(User $user): array
    {
        Gate::forUser($user)->authorize('create', ArabicSkillSession::class);

        return ArabicSkillBank::where(fn ($query) => $query->where('is_active', true)
            ->orWhereHas('sessions', fn ($sessions) => $sessions->where('user_id', $user->id)))
            ->orderBy('sort_order')->get()
            ->map(fn ($bank) => $this->bank($user, $bank))->all();
    }

    public function bank(User $user, ArabicSkillBank $bank): array
    {
        Gate::forUser($user)->authorize('create', ArabicSkillSession::class);
        $bank = ArabicSkillBank::findOrFail($bank->id);
        $activeIds = $bank->questions()->where('is_active', true)->pluck('id');
        $latest = $this->attempts->latest($user, $bank->id)->only($activeIds->all());
        $sessions = $user->arabicSkillSessions()->where('bank_id', $bank->id);
        $active = (clone $sessions)->where('status', 'active')->get();
        $ranked = (clone $sessions)->where('mode', 'model')->where('status', 'completed')->whereNotNull('model_id')
            ->select('id')->selectRaw('ROW_NUMBER() OVER (PARTITION BY model_id ORDER BY completed_at DESC, id DESC) AS completion_rank');
        $lastIds = DB::query()->fromSub($ranked, 'ranked')->where('completion_rank', 1)->select('id');
        $latestModels = ArabicSkillSession::whereIn('id', $lastIds)->get()->keyBy('model_id');
        $models = $bank->models()->withCount(['questions as active_question_count' => fn ($q) => $q->where('is_active', true)])->get();

        return ['bank' => $bank->only(['id', 'slug', 'title', 'subtitle', 'is_active']), 'total' => $activeIds->count(),
            'attempted' => $latest->count(), 'coverage' => $activeIds->isEmpty() ? 0 : $latest->count() / $activeIds->count() * 100,
            'completed_sessions' => (clone $sessions)->where('status', 'completed')->count(),
            'can_start_practice' => $bank->is_active && $activeIds->count() >= 10,
            'active_sessions' => $active->map(fn ($s) => $this->practice->summary($s))->all(),
            'models' => $models->map(function ($model) use ($bank, $latestModels, $active) {
                $last = $latestModels->get($model->id);
                $resume = $active->first(fn ($s) => $s->mode === 'model' && $s->model_id === $model->id);

                return ['id' => $model->id, 'title' => $model->title, 'number' => $model->number,
                    'active_question_count' => $model->active_question_count, 'can_start' => $bank->is_active && $model->active_question_count === 30,
                    'latest_completed' => $last ? $this->practice->summary($last) : null, 'resume' => $resume ? $this->practice->summary($resume) : null];
            })->all()];
    }

    public function history(User $user, ArabicSkillBank $bank, int $page = 1): array
    {
        Gate::forUser($user)->authorize('create', ArabicSkillSession::class);
        $query = $user->arabicSkillSessions()->where('bank_id', $bank->id);
        $page = max(1, $page);

        return ['page' => $page, 'per_page' => 20, 'total' => (clone $query)->count(),
            'sessions' => $query->orderByDesc('started_at')->orderByDesc('id')->forPage($page, 20)->get()
                ->map(fn ($s) => $this->practice->summary($s))->all()];
    }

    public function sets(User $user, ArabicSkillBank $bank): array
    {
        Gate::forUser($user)->authorize('create', ArabicSkillSession::class);
        $sessions = $user->arabicSkillSessions()->where('bank_id', $bank->id)->whereNotNull('practice_set_key')
            ->orderByDesc('completed_at')->orderByDesc('id')->get()
            ->map(fn ($s) => $this->practice->summary($s));

        return app(\App\Services\PracticeSetProgress::class)->cards(app(PracticeSets::class)->definitions($bank), $sessions, $bank->is_active);
    }
}
