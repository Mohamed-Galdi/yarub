<?php

namespace App\Http\Controllers\Admin\Qudrat;

use App\Http\Controllers\Controller;
use App\Models\QudratCategory;
use App\Models\QudratPassage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PassageController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', QudratPassage::class);
        $passages = QudratPassage::withCount('questions')->orderBy('sort_order')->paginate(20);

        return view('admin.qudrat.passages.index', compact('passages'));
    }

    public function edit(QudratPassage $passage)
    {
        $this->authorize('update', $passage);
        $passage->load('category');
        $questions = $passage->questions()->orderBy('sort_order')->get();

        return view('admin.qudrat.passages.edit', compact('passage', 'questions'));
    }

    public function update(Request $request, QudratPassage $passage)
    {
        $this->authorize('update', $passage);
        $data = $request->validate(['text' => 'required|string|max:20000', 'revision' => 'required|string']);
        DB::transaction(function () use ($passage, $data) {
            QudratCategory::whereKey($passage->category_id)->lockForUpdate()->firstOrFail();
            $locked = QudratPassage::whereKey($passage->id)->lockForUpdate()->firstOrFail();
            if (! hash_equals(hash('sha256', $locked->text), $data['revision'])) {
                throw ValidationException::withMessages(['revision' => 'عُدّل النص بعد فتح الصفحة. أعد تحميل الصفحة قبل الحفظ.']);
            }
            $locked->update(['text' => $data['text']]);
        });

        return back()->with('qudrat_status', 'تم حفظ النص المشترك. ستستخدمه المعاينات والتدريبات الجديدة؛ السجلات السابقة محفوظة.');
    }
}
