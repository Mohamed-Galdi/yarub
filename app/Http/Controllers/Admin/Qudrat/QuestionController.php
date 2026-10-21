<?php

namespace App\Http\Controllers\Admin\Qudrat;

use App\Http\Controllers\Controller;
use App\Models\QudratCategory;
use App\Models\QudratPassage;
use App\Models\QudratQuestion;
use App\Services\Qudrat\QuestionEditor;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', QudratQuestion::class);
        $filters = $request->validate(['category_id' => 'nullable|integer|exists:qudrat_categories,id', 'passage_id' => 'nullable|integer|exists:qudrat_passages,id']);

        return view('admin.qudrat.questions.index', compact('filters'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', QudratQuestion::class);
        $question = new QudratQuestion(['category_id' => $request->integer('category_id') ?: null, 'sort_order' => 201]);
        $question->setRelation('options', collect());

        return $this->form($question);
    }

    public function store(Request $request, QuestionEditor $editor)
    {
        $this->authorize('create', QudratQuestion::class);
        $question = $editor->save($request->all());

        return redirect()->route('admin.qudrat.questions.edit', $question)->with('qudrat_status', 'تم إنشاء السؤال.');
    }

    public function edit(QudratQuestion $question)
    {
        $this->authorize('update', $question);
        $question->load('options');

        return $this->form($question);
    }

    public function update(Request $request, QudratQuestion $question, QuestionEditor $editor)
    {
        $this->authorize('update', $question);
        $editor->save($request->all(), $question);

        return redirect()->route('admin.qudrat.questions.edit', $question)->with('qudrat_status', 'تم حفظ السؤال.');
    }

    public function preview(QudratQuestion $question)
    {
        $this->authorize('view', $question);
        $question->load(['category', 'passage', 'options']);

        return view('admin.qudrat.questions.preview', compact('question'));
    }

    public function archive(Request $request, QudratQuestion $question, QuestionEditor $editor)
    {
        $this->authorize('update', $question);
        $data = $request->validate(['archived' => 'required|boolean', 'revision' => 'required|string']);
        $editor->archive($question, (bool) $data['archived'], $data['revision']);

        return back()->with('qudrat_status', $data['archived'] ? 'تمت أرشفة السؤال مع حفظ سجل التدريبات.' : 'أُلغيت الأرشفة وأُعيد نشر السؤال.');
    }

    private function form(QudratQuestion $question)
    {
        $categories = QudratCategory::orderBy('sort_order')->get();
        $passages = QudratPassage::orderBy('sort_order')->get();
        $revision = $question->exists ? app(QuestionEditor::class)->revision($question) : '';

        return view('admin.qudrat.questions.form', compact('question', 'categories', 'passages', 'revision'));
    }
}
