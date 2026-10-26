<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\Tracking\SessionRecords;
use App\Services\Admin\Tracking\StudentDetailState;
use App\Services\Admin\Tracking\StudentTracking;
use App\Services\Admin\Tracking\UsersListState;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentTrackingController extends Controller
{
    public function index(Request $request, StudentTracking $tracking)
    {
        $this->authorize('view-student-tracking');
        $filters = $request->validate([
            'search' => 'nullable|string|max:255', 'status' => ['nullable', Rule::in(['active', 'blocked', 'all'])],
            'participation' => ['nullable', Rule::in(['all', 'qudrat', 'arabic_skills', 'neither'])],
            'sort' => ['nullable', Rule::in(['joined', 'last_practice', 'qudrat_coverage', 'arabic_skills_coverage'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])], 'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        return response()->json($tracking->directory($filters));
    }

    public function show(int $student, StudentTracking $tracking)
    {
        return response()->json($tracking->summary($student));
    }

    public function program(Request $request, int $student, string $program, StudentTracking $tracking, SessionRecords $sessions)
    {
        $request->validate(['page' => 'nullable|integer|min:1', 'type_id' => 'nullable|integer|min:1']);

        return response()->json(['types' => $tracking->breakdown($student, $program),
            'history' => $sessions->history($student, $program, $request->filled('type_id') ? $request->integer('type_id') : null)]);
    }

    public function review(Request $request, int $student, string $program, int $session, SessionRecords $sessions, StudentTracking $tracking)
    {
        $review = $sessions->review($student, $program, $session);
        if ($request->expectsJson()) {
            return response()->json($review);
        }
        $request->validate(['list' => 'sometimes|array']);
        $list = UsersListState::normalize($request->input('list', []));
        $state = StudentDetailState::normalize(array_replace($request->query(), ['tab' => $program]));
        $summary = $tracking->summary($student);

        return view('admin.students.session-review', compact('review', 'program', 'student', 'summary', 'list', 'state'));
    }
}
