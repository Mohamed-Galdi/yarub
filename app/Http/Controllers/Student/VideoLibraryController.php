<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ArabicSkillBank;
use App\Models\LibraryVideo;
use App\Models\QudratCategory;
use Illuminate\Http\Request;

class VideoLibraryController extends Controller
{
    public function show(string $video)
    {
        $this->authorize('viewAny', LibraryVideo::class);
        $current = LibraryVideo::eligible()->with(['qudratCategory:id,title', 'arabicSkillBank:id,title'])->findOrFail($video);
        $field = $current->qudrat_category_id !== null ? 'qudrat_category_id' : 'arabic_skill_bank_id';
        $related = LibraryVideo::eligible()->with(['qudratCategory:id,title', 'arabicSkillBank:id,title'])
            ->whereKeyNot($current->id)->whereNotNull($field)
            ->orderByRaw('CASE WHEN '.$field.' = ? THEN 0 ELSE 1 END', [$current->$field])->newestPublished()->limit(6)
            ->get(['id', 'title', 'duration_seconds', 'qudrat_category_id', 'arabic_skill_bank_id']);

        return response()->json($this->card($current) + [
            'description' => $current->description,
            'media' => route('student.videos.asset', [$current, 'media']),
            'share_url' => route('student.videos.index', ['video' => $current->id]),
            'related' => $related->map(fn ($item) => $this->card($item))->all(),
        ]);
    }

    private function card(LibraryVideo $video): array
    {
        return ['id' => $video->id, 'title' => $video->title, 'type' => $video->qudratCategory?->title ?? $video->arabicSkillBank?->title,
            'duration' => $video->duration_seconds, 'cover' => route('student.videos.asset', [$video, 'cover'])];
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', LibraryVideo::class);
        $data = $this->catalog($request);

        return response()->view('student.videos.index', $data)->header('Cache-Control', 'private, no-store');
    }

    public function results(Request $request)
    {
        $this->authorize('viewAny', LibraryVideo::class);
        $data = $this->catalog($request);

        // An explicit presentation payload: no model serialization, source URLs or diagnostics.
        return response()->json([
            'html' => view('student.videos._results', $data)->render(),
            'url' => $data['canonicalUrl'], 'state' => $data['state'],
            'total' => $data['videos']->total(),
        ]);
    }

    private function catalog(Request $request): array
    {
        $typeGroups = [
            ['label' => 'القدرات', 'prefix' => 'q', 'items' => QudratCategory::where('is_published', true)->orderBy('sort_order')->orderBy('id')->get(['id', 'title'])],
            ['label' => 'مهارات اللغة العربية', 'prefix' => 'a', 'items' => ArabicSkillBank::where('is_active', true)->orderBy('sort_order')->orderBy('id')->get(['id', 'title'])],
        ];
        // Malformed, removed and inactive types fall back to all. Never feed raw query arrays to SQL.
        $allowed = collect($typeGroups)->flatMap(fn ($group) => $group['items']->map(fn ($item) => $group['prefix'].':'.$item->id));
        $search = $request->query('q');
        $search = is_string($search) ? mb_substr(trim($search), 0, 200) : '';
        $type = $request->query('type');
        $type = is_string($type) && $allowed->containsStrict($type) ? $type : '';
        $page = filter_var($request->query('page', 1), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 1000000]]) ?: 1;
        $query = LibraryVideo::eligible()->with(['qudratCategory:id,title', 'arabicSkillBank:id,title'])->newestPublished();
        if ($search !== '') {
            $query->where(fn ($q) => $q->where('title', 'like', '%'.$search.'%')->orWhere('description', 'like', '%'.$search.'%'));
        }
        if ($type !== '') {
            [$prefix, $id] = explode(':', $type);
            $query->where($prefix === 'q' ? 'qudrat_category_id' : 'arabic_skill_bank_id', $id);
        }
        // Count once, clamp stale pagination after hiding/deletion, and use only the card fields.
        $total = (clone $query)->toBase()->getCountForPagination();
        $page = min($page, max(1, (int) ceil($total / 12)));
        $rows = $query->forPage($page, 12)->get(['id', 'title', 'qudrat_category_id', 'arabic_skill_bank_id', 'duration_seconds']);
        $state = ['q' => $search, 'type' => $type, 'page' => $page];
        $parameters = array_filter($state, fn ($value, $key) => $key === 'page' ? $value > 1 : $value !== '', ARRAY_FILTER_USE_BOTH);
        $canonicalUrl = route('student.videos.index', $parameters);
        if ($request->has('video')) {
            $id = $request->query('video');
            $parameters['video'] = is_string($id) && preg_match('/^[1-9][0-9]{0,17}$/D', $id) ? $id : 'invalid';
            $canonicalUrl = route('student.videos.index', $parameters);
        }
        $videos = new \Illuminate\Pagination\LengthAwarePaginator($rows, $total, 12, $page, ['path' => route('student.videos.index'), 'query' => array_diff_key($parameters, ['page' => true, 'video' => true])]);

        return compact('videos', 'state', 'typeGroups', 'canonicalUrl');
    }
}
