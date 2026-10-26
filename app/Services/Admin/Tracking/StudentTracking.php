<?php

namespace App\Services\Admin\Tracking;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class StudentTracking
{
    public const PROGRAMS = ['qudrat', 'arabic_skills'];

    public function __construct(private ProgramMetrics $metrics) {}

    public function student(int $id): User
    {
        Gate::authorize('view-student-tracking');

        return User::withTrashed()->where('role', 'student')->findOrFail($id);
    }

    /** A SQL builder shared by the paginated directory and the individual summary. */
    public function usersQuery(?int $userId = null): Builder
    {
        Gate::authorize('view-student-tracking');
        $query = User::withTrashed()->where('users.role', 'student')
            ->select(['users.id', 'users.name', 'users.email', 'users.avatar', 'users.deleted_at', 'users.created_at'])
            ->when($userId !== null, fn ($q) => $q->where('users.id', $userId));
        foreach (self::PROGRAMS as $program) {
            $totals = DB::query()->fromSub($this->metrics->eligible($program), 'eligible')->selectRaw('COUNT(*) AS total');
            $query->crossJoinSub($totals, $program.'_total')
                ->leftJoinSub($this->metrics->answered($program, $userId), $program.'_answers', $program.'_answers.user_id', '=', 'users.id')
                ->leftJoinSub($this->metrics->activity($program, $userId), $program.'_activity', $program.'_activity.user_id', '=', 'users.id')
                ->leftJoinSub($this->metrics->completions($program, $userId), $program.'_completions', $program.'_completions.user_id', '=', 'users.id')
                ->selectRaw($program.'_total.total AS '.$program.'_total');
            foreach (['answered', 'known_first_answers', 'first_correct'] as $field) {
                $query->selectRaw('COALESCE('.$program.'_answers.'.$field.', 0) AS '.$program.'_'.$field);
            }
            $query->selectRaw('COALESCE('.$program.'_activity.submissions, 0) AS '.$program.'_submissions')
                ->addSelect($program.'_activity.last_answer_at as '.$program.'_last_answer_at')
                ->selectRaw('COALESCE('.$program.'_completions.completed_sessions, 0) AS '.$program.'_completed_sessions')
                ->addSelect($program.'_completions.last_completion_at as '.$program.'_last_completion_at');
            $query->selectRaw('COALESCE('.$program.'_answers.answered, 0) * 100.0 / NULLIF('.$program.'_total.total, 0) AS '.$program.'_coverage');
        }

        return $query->selectRaw("NULLIF(GREATEST(COALESCE(qudrat_activity.last_answer_at, ''), COALESCE(qudrat_completions.last_completion_at, ''), COALESCE(arabic_skills_activity.last_answer_at, ''), COALESCE(arabic_skills_completions.last_completion_at, '')), '') AS last_practice_at");
    }

    public function directory(array $filters): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $page = $this->directoryQuery($filters)
            ->paginate(min(100, max(1, (int) ($filters['per_page'] ?? 20))))->withQueryString();
        $page->through(fn ($student) => $this->present($student));

        return $page;
    }

    public function directoryQuery(array $filters): Builder
    {
        $query = $this->usersQuery();
        $status = $filters['status'] ?? 'active';
        if ($status === 'active') {
            $query->whereNull('users.deleted_at');
        } elseif ($status === 'blocked') {
            $query->whereNotNull('users.deleted_at');
        }
        if (($search = $filters['search'] ?? '') !== '') {
            $query->where(fn ($q) => $q->where('users.name', 'like', '%'.$search.'%')->orWhere('users.email', 'like', '%'.$search.'%'));
        }
        $program = $filters['participation'] ?? 'all';
        if (in_array($program, self::PROGRAMS, true)) {
            $query->where($program.'_activity.submissions', '>', 0);
        } elseif ($program === 'neither') {
            foreach (self::PROGRAMS as $name) {
                $query->whereNull($name.'_activity.user_id');
            }
        }
        $sort = match ($filters['sort'] ?? 'joined') {
            'last_practice' => 'last_practice_at',
            'qudrat_coverage' => 'qudrat_coverage',
            'arabic_skills_coverage' => 'arabic_skills_coverage',
            default => 'users.created_at',
        };
        $direction = ($filters['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        if ($sort !== 'users.created_at') {
            // Alias in ORDER BY; unavailable metrics always follow known values.
            $query->orderByRaw('ISNULL('.$sort.') ASC');
        }

        return $query->orderBy($sort, $direction)->orderBy('users.id', $direction);
    }

    public function summary(int $studentId): array
    {
        $this->student($studentId);

        return $this->present($this->usersQuery($studentId)->firstOrFail());
    }

    public function breakdown(int $studentId, string $program): array
    {
        $this->student($studentId);

        return $this->metrics->types($program, $studentId);
    }

    public function present(User $student): array
    {
        Gate::authorize('view-student-tracking');
        $programs = [];
        foreach (self::PROGRAMS as $program) {
            $values = [];
            foreach (['total', 'answered', 'known_first_answers', 'first_correct', 'submissions', 'completed_sessions', 'last_answer_at', 'last_completion_at'] as $field) {
                $values[$field] = $student->getAttribute($program.'_'.$field);
            }
            $programs[$program] = $this->metrics->format($values);
        }

        return ['id' => $student->id, 'name' => $student->name, 'email' => $student->email,
            'avatar' => $student->avatar, 'blocked' => $student->trashed(), 'joined_at' => $student->created_at?->toIso8601String(),
            'last_practice_at' => max($programs['qudrat']['last_practice_at'] ?? '', $programs['arabic_skills']['last_practice_at'] ?? '') ?: null,
            'programs' => $programs];
    }
}
