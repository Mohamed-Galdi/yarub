<?php

namespace App\Http\Controllers\Student\Qudrat;

use App\Http\Controllers\Controller;
use App\Models\QudratCategory;
use App\Models\QudratPracticeSession;
use App\Services\Qudrat\Practice;
use App\Services\Qudrat\Progress;
use Illuminate\Http\Request;

class PracticeController extends Controller
{
    public function index(Request $request, Progress $progress)
    {
        $this->authorize('create', QudratPracticeSession::class);

        return view('student.qudrat.index', ['categories' => $progress->categories($request->user())]);
    }

    public function start(Request $request, QudratCategory $category, Practice $practice)
    {
        $data = $request->validate(['mode' => ['required', 'in:practice,review'], 'set_key' => ['nullable', 'string', 'max:100']]);
        $session = $practice->start($request->user(), $category, $data['mode'], $data['set_key'] ?? null);

        return redirect()->route('student.qudrat.show', $session);
    }

    public function category(Request $request, QudratCategory $category, Progress $progress)
    {
        $this->authorize('create', QudratPracticeSession::class);
        abort_unless($category->is_published, 404);

        return view('student.qudrat.category', ['category' => $category, 'sets' => $progress->sets($request->user(), $category),
            'legacySessions' => $request->user()->qudratSessions()->where('category_id', $category->id)
                ->whereNull('practice_set_key')->where('status', 'active')->get()]);
    }

    public function show(Request $request, QudratPracticeSession $session, Practice $practice)
    {
        $data = $practice->visit($request->user(), $session);
        if ($data['session']->status !== 'active') {
            return redirect()->route('student.qudrat.results', $session);
        }

        return view('student.qudrat.practice', $data);
    }

    public function selection(Request $request, QudratPracticeSession $session, int $item, Practice $practice)
    {
        return $this->choice($request, $session, $item, $practice, 'selection');
    }

    public function answer(Request $request, QudratPracticeSession $session, int $item, Practice $practice)
    {
        return $this->choice($request, $session, $item, $practice, 'answer');
    }

    private function choice(Request $request, QudratPracticeSession $session, int $item, Practice $practice, string $action)
    {
        $data = $request->validate(['option' => ['required', 'string', 'in:a,b,c,d']]);
        $result = $practice->visit($request->user(), $session, $action, $item, $data['option']);
        if ($request->expectsJson()) {
            return response()->json(['saved' => isset($result['question']), 'selected' => $result['selected'] ?? null]);
        }

        return redirect()->route('student.qudrat.show', $session);
    }

    public function hint(Request $request, QudratPracticeSession $session, int $item, Practice $practice)
    {
        $data = $request->validate(['option' => ['nullable', 'string', 'in:a,b,c,d']]);
        $practice->visit($request->user(), $session, 'hint', $item, $data['option'] ?? null);

        return redirect()->route('student.qudrat.show', $session);
    }

    public function next(Request $request, QudratPracticeSession $session, int $item, Practice $practice)
    {
        $practice->visit($request->user(), $session, 'next', $item);

        return redirect()->route('student.qudrat.show', $session);
    }

    public function results(Request $request, QudratPracticeSession $session, Practice $practice)
    {
        $this->authorize('view', $session);
        if ($session->status === 'active') {
            return redirect()->route('student.qudrat.show', $session);
        }

        return view('student.qudrat.results', $practice->results($request->user(), $session) + ['mistakesOnly' => $request->boolean('mistakes')]);
    }
}
