<?php

namespace App\Http\Controllers\Admin\ArabicSkills;

use App\Http\Controllers\Controller;
use App\Models\ArabicSkillBank;
use App\Models\ArabicSkillModel;
use App\Models\ArabicSkillSession;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SessionController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', ArabicSkillSession::class);
        $data = $request->validate(['student' => 'nullable|string|max:255', 'bank_id' => 'nullable|integer|exists:arabic_skill_banks,id', 'model_id' => 'nullable|integer|exists:arabic_skill_models,id', 'mode' => ['nullable', Rule::in(['practice', 'model'])], 'status' => ['nullable', Rule::in(['active', 'completed', 'abandoned'])], 'from' => 'nullable|date_format:Y-m-d', 'to' => 'nullable|date_format:Y-m-d|after_or_equal:from']);
        $query = ArabicSkillSession::with(['user', 'bank', 'sourceModel'])->withCount(['items as submitted_count' => fn ($q) => $q->whereNotNull('submitted_at')]);
        foreach (['bank_id', 'model_id', 'mode', 'status'] as $field) {
            if (isset($data[$field])) {
                $query->where($field, $data[$field]);
            }
        }
        if (! empty($data['student'])) {
            $term = '%'.$data['student'].'%';
            $query->whereHas('user', fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', $term)->orWhere('email', 'like', $term)));
        }
        if (isset($data['from'])) {
            $query->whereDate('started_at', '>=', $data['from']);
        }
        if (isset($data['to'])) {
            $query->whereDate('started_at', '<=', $data['to']);
        }

        return view('admin.arabic-skills.sessions', ['sessions' => $query->orderByDesc('id')->paginate(20)->withQueryString(), 'banks' => ArabicSkillBank::orderBy('sort_order')->get(), 'models' => ArabicSkillModel::with('bank')->orderBy('bank_id')->orderBy('number')->get()]);
    }

    public function show(ArabicSkillSession $session)
    {
        $this->authorize('review', $session);
        $session->load(['user', 'bank', 'sourceModel', 'items']);

        return view('admin.arabic-skills.session', compact('session'));
    }
}
