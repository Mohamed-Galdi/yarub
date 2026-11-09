<?php

namespace App\Services\Admin;

use App\Models\LibraryVideo;
use App\Services\Admin\Tracking\ProgramMetrics;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class DashboardAnalytics
{
    public function __construct(private ProgramMetrics $metrics) {}

    private function students(Builder $query, string $column): Builder
    {
        return $query->whereIn($column, DB::table('users')->select('id')->where('role', 'student')->whereNull('deleted_at'));
    }

    private function submissions(string $program): Builder
    {
        return $this->students(DB::query()->fromSub($this->metrics->submissions($program), 'a'), 'a.user_id');
    }

    private function videoViews(): Builder
    {
        return $this->students(DB::table('video_views'), 'user_id');
    }

    private function events(CarbonImmutable $start, CarbonImmutable $end): Builder
    {
        // Bound each UNION branch before materialization, including on MySQL versions
        // that cannot push an outer date predicate into UNION-derived tables.
        $events = $this->submissions('qudrat')->whereBetween('submitted_at', [$start, $end])
            ->select('user_id', 'submitted_at as occurred_at');
        $events->unionAll($this->submissions('arabic_skills')->whereBetween('submitted_at', [$start, $end])
            ->select('user_id', 'submitted_at as occurred_at'));
        $events->unionAll($this->students(DB::table('test_attempts'), 'user_id')->whereBetween('created_at', [$start, $end])
            ->select('user_id', 'created_at as occurred_at'));

        $events->unionAll($this->videoViews()->whereBetween('viewed_at', [$start, $end])
            ->select('user_id', 'viewed_at as occurred_at'));

        return DB::query()->fromSub($events, 'activity');
    }

    public function overview(int $days, ?CarbonImmutable $now = null): array
    {
        Gate::authorize('view-student-tracking');
        if (! in_array($days, [7, 30, 90], true)) {
            throw new \InvalidArgumentException('Unsupported dashboard period.');
        }
        $end = $now ?? CarbonImmutable::now();
        $start = $end->startOfDay()->subDays($days - 1);
        // Compare equal elapsed durations, including the same portion of the final day.
        $previousStart = $start->subDays($days);
        $previousEnd = $end->subDays($days);
        $current = $this->events($start, $end);
        $active = (clone $current)->distinct()->count('user_id');
        $previousActive = $this->events($previousStart, $previousEnd)->distinct()->count('user_id');
        $daily = (clone $current)->selectRaw('DATE(occurred_at) as day, COUNT(DISTINCT user_id) as students, COUNT(*) as actions')
            ->groupByRaw('DATE(occurred_at)')->orderBy('day')->get()->keyBy('day');
        $trend = [];
        for ($date = $start; $date->lte($end); $date = $date->addDay()) {
            $row = $daily->get($date->toDateString());
            $trend[] = ['date' => $date->toDateString(), 'label' => $date->format('m/d'),
                'students' => (int) ($row->students ?? 0), 'actions' => (int) ($row->actions ?? 0)];
        }
        $programs = [];
        foreach (['qudrat' => 'القدرات', 'arabic_skills' => 'مهارات اللغة العربية'] as $key => $title) {
            $definition = $this->metrics->definition($key);
            $answers = $this->submissions($key)->whereBetween('submitted_at', [$start, $end]);
            $byType = (clone $answers)->select('type_id')->selectRaw('COUNT(*) as answers, COUNT(DISTINCT user_id) as students')->groupBy('type_id');
            $sessions = $this->students(DB::table($definition['sessions']), 'user_id');
            $cohort = (clone $sessions)->whereBetween('started_at', [$start, $end]);
            $cohortByType = (clone $cohort)->select($definition['type_key'].' as type_id')
                ->selectRaw("COUNT(*) as started, SUM(CASE WHEN status = 'completed' AND completed_at <= ? THEN 1 ELSE 0 END) as finished", [$end])
                ->groupBy($definition['type_key']);
            $rows = DB::table($definition['types'].' as t')
                ->leftJoinSub($byType, 'a', 'a.type_id', '=', 't.id')
                ->leftJoinSub($cohortByType, 's', 's.type_id', '=', 't.id')
                ->select('t.id', 't.title', 't.'.$definition['active'].' as available')
                ->selectRaw('COALESCE(a.answers, 0) as answers, COALESCE(a.students, 0) as students, COALESCE(s.started, 0) as started, COALESCE(s.finished, 0) as finished')
                ->orderByDesc('students')->orderByDesc('answers')->orderBy('t.id')->get();
            $programs[$key] = ['title' => $title, 'rows' => $rows,
                'students' => (clone $answers)->distinct()->count('user_id'), 'answers' => (clone $answers)->count(),
                'started' => (clone $cohort)->count(),
                'completed' => (clone $sessions)->where('status', 'completed')->whereBetween('completed_at', [$start, $end])->count(),
                'unused' => $rows->filter(fn ($row) => $row->available && (int) $row->students === 0)->count()];
        }
        $enrollments = $this->students(DB::table('student_course_sub'), 'user_id')->whereBetween('created_at', [$start, $end])
            ->select('course_id')->selectRaw('COUNT(DISTINCT user_id) as students')->groupBy('course_id');
        $tests = $this->students(DB::table('test_attempts as a')->join('tests as t', 't.id', '=', 'a.test_id'), 'a.user_id')
            ->whereBetween('a.created_at', [$start, $end])->whereNotNull('t.course_id')->select('t.course_id')
            ->selectRaw('COUNT(*) as attempts, COUNT(DISTINCT a.user_id) as learners')->groupBy('t.course_id');
        $courseQuery = DB::table('courses as c')->leftJoinSub($enrollments, 'e', 'e.course_id', '=', 'c.id')
            ->leftJoinSub($tests, 't', 't.course_id', '=', 'c.id')->select('c.id', 'c.title', 'c.is_published')
            ->selectRaw('COALESCE(e.students, 0) as students, COALESCE(t.attempts, 0) as attempts, COALESCE(t.learners, 0) as learners');
        $courses = (clone $courseQuery)->orderByDesc('learners')->orderByDesc('students')->orderBy('c.id')->limit(10)->get();
        $quietCourses = (clone $courseQuery)->where('c.is_published', true)->whereNull('t.course_id')
            ->orderBy('students')->orderBy('c.id')->limit(5)->get();

        $views = $this->videoViews()->whereBetween('viewed_at', [$start, $end]);
        $videoActivity = ['opens' => (clone $views)->count(), 'viewers' => (clone $views)->distinct()->count('user_id')];
        $byVideo = (clone $views)->select('video_id')->selectRaw('COUNT(*) as opens, COUNT(DISTINCT user_id) as viewers')->groupBy('video_id');
        $videoRankings = DB::table('library_videos as v')->leftJoinSub($byVideo, 'a', 'a.video_id', '=', 'v.id')
            ->where(fn ($q) => $q->whereNotNull('a.video_id')->orWhereIn('v.id', LibraryVideo::eligible()->select('id')))
            ->select('v.id', 'v.title')->selectRaw('COALESCE(a.opens, 0) as opens, COALESCE(a.viewers, 0) as viewers')
            ->orderByDesc('opens')->orderByDesc('viewers')->orderBy('v.id')->limit(10)->get();
        $videoCategoryActivity = DB::query()->fromSub((clone $views), 'a')
            ->join('library_videos as v', 'v.id', '=', 'a.video_id')
            ->leftJoin('qudrat_categories as q', 'q.id', '=', 'v.qudrat_category_id')
            ->leftJoin('arabic_skill_banks as b', 'b.id', '=', 'v.arabic_skill_bank_id')
            ->select('v.qudrat_category_id', 'v.arabic_skill_bank_id', 'q.title as qudrat_title', 'b.title as arabic_title')
            ->selectRaw('COUNT(*) as opens, COUNT(DISTINCT a.user_id) as viewers')
            ->groupBy('v.qudrat_category_id', 'v.arabic_skill_bank_id', 'q.title', 'b.title')
            ->orderByDesc('opens')->orderBy('v.qudrat_category_id')->orderBy('v.arabic_skill_bank_id')->get();

        return compact('days', 'start', 'end', 'active', 'previousActive', 'trend', 'programs', 'courses', 'quietCourses', 'videoActivity', 'videoRankings', 'videoCategoryActivity') + [
            'actions' => (clone $current)->count(),
            'repeatStudents' => DB::query()->fromSub((clone $current)->select('user_id')->groupBy('user_id')
                ->havingRaw('COUNT(DISTINCT DATE(occurred_at)) >= 2'), 'returning')->count(),
            'students' => DB::table('users')->where('role', 'student')->whereNull('deleted_at')->count(),
            'newStudents' => DB::table('users')->where('role', 'student')->whereNull('deleted_at')->whereBetween('created_at', [$start, $end])->count(),
            'publishedCourses' => DB::table('courses')->where('is_published', true)->count(),
            'videos' => LibraryVideo::eligible()->count(),
            'videoCategories' => LibraryVideo::eligible()->select('qudrat_category_id', 'arabic_skill_bank_id')
                ->selectRaw('COUNT(*) as total')->groupBy('qudrat_category_id', 'arabic_skill_bank_id')
                ->orderByDesc('total')->orderBy('qudrat_category_id')->orderBy('arabic_skill_bank_id')
                ->with(['qudratCategory:id,title', 'arabicSkillBank:id,title'])->get(),
        ];
    }
}
