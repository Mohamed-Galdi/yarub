<?php

namespace App\Http\Controllers\Admin\ArabicSkills;

use App\Http\Controllers\Controller;
use App\Models\ArabicSkillBank;
use App\Models\ArabicSkillModel;
use App\Models\ArabicSkillQuestion;
use App\Services\ArabicSkills\QuestionEditor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class QuestionController extends Controller
{
    public const TYPES = ['multiple_choice' => 'اختيار من متعدد', 'true_false' => 'صح أو خطأ', 'matching' => 'مطابقة'];

    public function index(Request $request)
    {
        $this->authorize('viewAny', ArabicSkillQuestion::class);
        $filters = $request->validate(['q' => 'nullable|string|max:255', 'bank_id' => 'nullable|integer|exists:arabic_skill_banks,id', 'model_id' => 'nullable|integer|exists:arabic_skill_models,id', 'type' => ['nullable', Rule::in(array_keys(self::TYPES))], 'skill' => 'nullable|string|max:255', 'active' => 'nullable|boolean']);
        $query = ArabicSkillQuestion::with(['bank', 'sourceModel']);
        foreach (['bank_id', 'model_id', 'type', 'skill'] as $field) {
            if (isset($filters[$field])) {
                $query->where($field, $filters[$field]);
            }
        }
        if (isset($filters['active'])) {
            $query->where('is_active', $filters['active']);
        }
        if (! empty($filters['q'])) {
            $term = '%'.$filters['q'].'%';
            $query->where(fn ($q) => $q->where('prompt', 'like', $term)->orWhere('source_id', 'like', $term));
        }

        return view('admin.arabic-skills.questions', [
            'questions' => $query->orderBy('bank_id')->orderBy('id')->paginate(20)->withQueryString(),
            'banks' => ArabicSkillBank::orderBy('sort_order')->get(),
            'models' => ArabicSkillModel::with('bank')->when($filters['bank_id'] ?? null, fn ($q, $id) => $q->where('bank_id', $id))->orderBy('bank_id')->orderBy('number')->get(),
            'skills' => ArabicSkillQuestion::distinct()->orderBy('skill')->pluck('skill'), 'types' => self::TYPES,
        ]);
    }

    public function create(Request $request, QuestionEditor $editor)
    {
        $this->authorize('create', ArabicSkillQuestion::class);
        $data = $request->validate(['type' => ['nullable', Rule::in(array_keys(self::TYPES))], 'bank_id' => 'nullable|integer|exists:arabic_skill_banks,id']);

        return $this->form(new ArabicSkillQuestion(['type' => $data['type'] ?? 'multiple_choice', 'bank_id' => $data['bank_id'] ?? null]), $editor);
    }

    public function edit(ArabicSkillQuestion $question, QuestionEditor $editor)
    {
        $this->authorize('update', $question);

        return $this->form($question, $editor);
    }

    private function form(ArabicSkillQuestion $question, QuestionEditor $editor)
    {
        return view('admin.arabic-skills.form', ['question' => $question, 'banks' => ArabicSkillBank::orderBy('sort_order')->get(), 'types' => self::TYPES, 'revision' => $question->exists ? $editor->revision($question) : '']);
    }

    public function store(Request $request, QuestionEditor $editor)
    {
        $this->authorize('create', ArabicSkillQuestion::class);
        $question = $editor->save($request->all());

        return redirect()->route('admin.arabic-skills.questions.edit', $question)->with('arabic_status', 'أُضيف السؤال ونُشر مباشرةً.');
    }

    public function update(Request $request, ArabicSkillQuestion $question, QuestionEditor $editor)
    {
        $this->authorize('update', $question);
        $editor->save($request->all(), $question);

        return back()->with('arabic_status', 'حُفظ السؤال. التدريبات السابقة والجارية تحتفظ بنسختها الأصلية.');
    }

    public function archive(Request $request, ArabicSkillQuestion $question, QuestionEditor $editor)
    {
        $this->authorize('update', $question);
        $data = $request->validate(['archived' => 'required|boolean', 'revision' => 'required|string']);
        $editor->archive($question, (bool) $data['archived'], $data['revision']);

        return back()->with('arabic_status', $data['archived'] ? 'أُرشف السؤال مع الاحتفاظ بسجل التدريبات.' : 'أُعيد نشر السؤال.');
    }

    public function preview(ArabicSkillQuestion $question)
    {
        $this->authorize('view', $question);
        $question->load(['bank', 'sourceModel']);

        return view('admin.arabic-skills.preview', compact('question'));
    }
}
