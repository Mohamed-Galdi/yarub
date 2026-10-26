<?php

namespace App\Services\Admin\Tracking;

class StudentDetail
{
    public function __construct(private StudentTracking $tracking, private SessionRecords $sessions) {}

    public function page(int $studentId, array $state, array $list): array
    {
        $student = $this->tracking->student($studentId);
        $student->loadCount(['activeCourses', 'activeLessons', 'test_attempts', 'certificates']);
        $summary = $this->tracking->summary($studentId);
        $tab = $state['tab'];
        $types = [];
        $history = null;
        $records = null;
        if (in_array($tab, StudentTracking::PROGRAMS, true)) {
            $types = $this->tracking->breakdown($studentId, $tab);
            $typeId = $state[$tab.'_type'] ?? null;
            abort_if($typeId !== null && ! in_array($typeId, array_column($types, 'id'), true), 404);
            $history = $this->sessions->history($studentId, $tab, $typeId, $tab.'_page', $state[$tab.'_page']);
        } elseif ($tab === 'courses') {
            $records = $student->activeCourses()->orderByDesc('student_course_sub.created_at')->orderByDesc('student_course_sub.id')
                ->paginate(20, ['courses.*'], 'courses_page', $state['courses_page']);
        } elseif ($tab === 'lessons') {
            $records = $student->activeLessons()->orderByDesc('student_lesson_sub.created_at')->orderByDesc('student_lesson_sub.id')
                ->paginate(20, ['lessons.*'], 'lessons_page', $state['lessons_page']);
        } elseif ($tab === 'tests') {
            $records = $student->test_attempts()->with(['test.course', 'test.lesson'])->orderByDesc('created_at')->orderByDesc('id')
                ->paginate(20, ['*'], 'tests_page', $state['tests_page']);
        } elseif ($tab === 'certificates') {
            $records = $student->certificates()->with(['course', 'lesson'])->orderByDesc('created_at')->orderByDesc('id')
                ->paginate(20, ['*'], 'certificates_page', $state['certificates_page']);
        }
        $navigation = $state + ['list' => $list];
        $history?->appends($navigation);
        $records?->appends($navigation);

        return compact('student', 'summary', 'state', 'list', 'navigation', 'types', 'history', 'records');
    }
}
