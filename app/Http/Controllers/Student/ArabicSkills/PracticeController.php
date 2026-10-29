<?php

namespace App\Http\Controllers\Student\ArabicSkills;

use App\Http\Controllers\Controller;
use App\Models\ArabicSkillBank;
use App\Models\ArabicSkillModel;
use App\Models\ArabicSkillSession;
use App\Services\ArabicSkills\Practice;
use App\Services\ArabicSkills\Progress;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PracticeController extends Controller
{
    public function index(Request $request, Progress $progress)
    {
        return view('student.arabic-skills.index', ['banks' => $progress->banks($request->user())]);
    }

    public function bank(Request $request, ArabicSkillBank $bank, Progress $progress)
    {
        return view('student.arabic-skills.bank', ['entry' => $progress->bank($request->user(), $bank),
            'sets' => $progress->sets($request->user(), $bank)]);
    }

    public function start(Request $request, ArabicSkillBank $bank, Practice $practice)
    {
        $data = $request->validate(['mode' => ['required', 'in:practice,model'], 'model_id' => ['nullable', 'integer', 'min:1'], 'set_key' => ['nullable', 'string', 'max:100']]);
        $model = isset($data['model_id']) ? ArabicSkillModel::findOrFail($data['model_id']) : null;
        $session = $practice->start($request->user(), $bank, $data['mode'], $model, $data['set_key'] ?? null);

        return redirect()->route('student.arabic-skills.show', $session);
    }

    public function show(Request $request, ArabicSkillSession $session, Practice $practice)
    {
        $data = $practice->show($request->user(), $session);
        if ($data['session']['status'] === 'completed') {
            return redirect()->route('student.arabic-skills.results', $session);
        }
        if ($data['session']['status'] === 'abandoned') {
            return redirect()->route('student.arabic-skills.bank', $session->bank_id)->with('notice', 'أُغلق التدريب وحُفظت إجاباتك السابقة.');
        }

        return view('student.arabic-skills.practice', $data);
    }

    public function answer(Request $request, ArabicSkillSession $session, int $item, Practice $practice)
    {
        $this->authorize('view', $session);
        $data = $request->validate(['response' => ['required', 'array']]);
        if ($request->except(['_token', 'response']) !== []) {
            throw ValidationException::withMessages(['response' => 'أرسل الإجابة فقط باستخدام الخيارات المعروضة.']);
        }
        $practice->answer($request->user(), $session, $item, $data['response']);

        return redirect()->route('student.arabic-skills.show', $session);
    }

    public function next(Request $request, ArabicSkillSession $session, int $item, Practice $practice)
    {
        $practice->next($request->user(), $session, $item);

        return redirect()->route('student.arabic-skills.show', $session);
    }

    public function hint(Request $request, ArabicSkillSession $session, int $item, Practice $practice)
    {
        $hint = $practice->hint($request->user(), $session, $item);
        if ($request->expectsJson()) {
            return response()->json($hint);
        }

        return redirect()->route('student.arabic-skills.show', $session)->with('arabic_hint', $hint);
    }

    public function abandon(Request $request, ArabicSkillSession $session, Practice $practice)
    {
        $practice->abandon($request->user(), $session);

        return redirect()->route('student.arabic-skills.bank', $session->bank_id)->with('notice', 'أُغلق التدريب وحُفظت إجاباتك السابقة. يمكنك بدء تدريب جديد.');
    }

    public function results(Request $request, ArabicSkillSession $session, Practice $practice)
    {
        $this->authorize('view', $session);
        if ($session->status !== 'completed') {
            return redirect()->route('student.arabic-skills.show', $session);
        }

        return view('student.arabic-skills.results', $practice->results($request->user(), $session));
    }
}
