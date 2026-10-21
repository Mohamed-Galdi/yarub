<?php

namespace App\Http\Controllers\Admin\Qudrat;

use App\Http\Controllers\Controller;
use App\Models\QudratCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', QudratCategory::class);
        $categories = QudratCategory::withCount([
            'questions as total_count',
            'questions as published_count' => fn ($query) => $query->where('is_published', true)->whereNull('archived_at'),
            'questions as archived_count' => fn ($query) => $query->whereNotNull('archived_at'),
        ])->orderBy('sort_order')->get();

        return view('admin.qudrat.index', compact('categories'));
    }

    public function update(Request $request, QudratCategory $category)
    {
        $this->authorize('update', $category);
        $data = $request->validate(['title' => 'required|string|max:255', 'sort_order' => 'required|integer|min:1|max:65535']);
        $data['is_published'] = true;
        DB::transaction(fn () => QudratCategory::whereKey($category->id)->lockForUpdate()->firstOrFail()->update($data));

        return back()->with('qudrat_status', 'تم حفظ إعدادات القسم.');
    }
}
