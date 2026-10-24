<?php

namespace App\Http\Controllers\Admin\ArabicSkills;

use App\Http\Controllers\Controller;
use App\Models\ArabicSkillBank;
use App\Models\ArabicSkillModel;
use App\Services\ArabicSkills\QuestionEditor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContentController extends Controller
{
    public function index(QuestionEditor $editor)
    {
        $this->authorize('viewAny', ArabicSkillBank::class);
        $banks = ArabicSkillBank::withCount(['questions', 'questions as active_count' => fn ($q) => $q->where('is_active', true)])
            ->with(['models' => fn ($q) => $q->withCount(['questions', 'questions as active_count' => fn ($q) => $q->where('is_active', true)])])->orderBy('sort_order')->get();

        return view('admin.arabic-skills.banks', compact('banks', 'editor'));
    }

    public function updateBank(Request $request, ArabicSkillBank $bank, QuestionEditor $editor)
    {
        $this->authorize('update', $bank);
        $data = $request->validate(['title' => 'required|string|max:255', 'subtitle' => 'required|string|max:255', 'sort_order' => 'required|integer|min:1|max:65535', 'is_active' => 'required|boolean', 'revision' => 'required|string']);
        DB::transaction(function () use ($bank, $data, $editor) {
            $locked = ArabicSkillBank::whereKey($bank->id)->lockForUpdate()->firstOrFail();
            $editor->checkRevision($locked, $data['revision']);
            unset($data['revision']);
            $locked->update($data);
        });

        return back()->with('arabic_status', 'حُفظت إعدادات البنك.');
    }

    public function updateModel(Request $request, ArabicSkillModel $model, QuestionEditor $editor)
    {
        $this->authorize('update', $model);
        $data = $request->validate(['title' => 'required|string|max:255', 'revision' => 'required|string']);
        DB::transaction(function () use ($model, $data, $editor) {
            ArabicSkillBank::whereKey($model->bank_id)->lockForUpdate()->firstOrFail();
            $locked = ArabicSkillModel::whereKey($model->id)->lockForUpdate()->firstOrFail();
            $editor->checkRevision($locked, $data['revision']);
            $locked->update(['title' => $data['title']]);
        });

        return back()->with('arabic_status', 'حُفظ عنوان النموذج.');
    }
}
