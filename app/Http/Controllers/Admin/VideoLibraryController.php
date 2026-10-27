<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArabicSkillBank;
use App\Models\LibraryVideo;
use App\Models\QudratCategory;
use App\Models\VideoLibrarySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VideoLibraryController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('manage', LibraryVideo::class);
        $state = $request->validate([
            'q' => 'nullable|string|max:200', 'type' => ['nullable', 'string', 'regex:/^[qa]:[1-9][0-9]*$/D'],
            'visibility' => 'nullable|in:visible,hidden', 'page' => 'nullable|integer|min:1',
        ]);
        $query = LibraryVideo::with(['qudratCategory', 'arabicSkillBank'])->newestUploaded();
        if ($search = trim($state['q'] ?? '')) {
            $query->where(fn ($q) => $q->where('title', 'like', '%'.$search.'%')->orWhere('description', 'like', '%'.$search.'%'));
        }
        if (! empty($state['type'])) {
            [$program, $id] = explode(':', $state['type']);
            $query->where($program === 'q' ? 'qudrat_category_id' : 'arabic_skill_bank_id', $id);
        }
        if (! empty($state['visibility'])) {
            $query->where('is_visible', $state['visibility'] === 'visible');
        }
        $videos = $query->paginate(12)->withQueryString();
        if ($videos->currentPage() > $videos->lastPage()) {
            return redirect()->route('admin.videos.index', array_merge($state, ['page' => $videos->lastPage()]));
        }
        $eligibleIds = LibraryVideo::eligible()->whereKey($videos->pluck('id'))->pluck('id')->all();
        $selected = VideoLibrarySetting::with('publicExample')->findOrFail(1)->publicExample;

        return view('admin.videos.index', $this->types() + compact('videos', 'eligibleIds', 'selected', 'state'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', LibraryVideo::class);

        return $this->form($request, new LibraryVideo);
    }

    public function edit(Request $request, LibraryVideo $video)
    {
        $this->authorize('update', $video);

        return $this->form($request, $video);
    }

    private function form(Request $request, LibraryVideo $video)
    {
        $returnUrl = route('admin.videos.index', $request->only(['q', 'type', 'visibility', 'page']));
        $requestKey = (string) Str::uuid();
        $selected = $video->exists && VideoLibrarySetting::whereKey(1)->where('public_example_video_id', $video->id)->exists();

        return view('admin.videos.form', $this->types() + compact('video', 'returnUrl', 'requestKey', 'selected'));
    }

    private function types(): array
    {
        return ['typeGroups' => [
            ['label' => 'القدرات', 'prefix' => 'q', 'items' => QudratCategory::orderBy('sort_order')->orderBy('id')->get(), 'active' => 'is_published'],
            ['label' => 'مهارات اللغة العربية', 'prefix' => 'a', 'items' => ArabicSkillBank::orderBy('sort_order')->orderBy('id')->get(), 'active' => 'is_active'],
        ]];
    }
}
