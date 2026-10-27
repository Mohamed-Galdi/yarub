<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LibraryVideo;
use App\Models\VideoLibrarySetting;
use App\Services\VideoLibrary\PublicExample;
use App\Services\VideoLibrary\VideoManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VideoController extends Controller
{
    public function store(Request $request, VideoManager $manager)
    {
        $this->authorize('create', LibraryVideo::class);
        $data = $request->validate($this->fileRules(true));
        $video = $manager->upload($request->user(), $request->only(['title', 'description', 'qudrat_category_id', 'arabic_skill_bank_id', 'program']), $data['video'], $request->file('cover'), $data['request_key']);

        return $this->result($video);
    }

    public function show(LibraryVideo $video)
    {
        $this->authorize('preview', $video);

        return $this->result($video);
    }

    public function operation(Request $request, string $key)
    {
        $this->authorize('manage', LibraryVideo::class);
        $operation = DB::table('video_media_operations')->where('request_key', $key)->where('admin_id', $request->user()->id)->first();
        abort_unless($operation && $operation->video_id !== null, 404);

        return $this->result(LibraryVideo::findOrFail($operation->video_id));
    }

    public function update(Request $request, LibraryVideo $video, VideoManager $manager)
    {
        return $this->result($manager->updateMetadata($request->user(), $video, $request->only(['title', 'description', 'qudrat_category_id', 'arabic_skill_bank_id', 'program'])));
    }

    public function replace(Request $request, LibraryVideo $video, VideoManager $manager)
    {
        $this->authorize('update', $video);
        $data = $request->validate($this->fileRules(false) + ['reset_cover' => 'sometimes|boolean']);

        return $this->result($manager->replace($request->user(), $video, $request->file('video'), $request->file('cover'), $request->boolean('reset_cover'), $data['request_key']));
    }

    public function visibility(Request $request, LibraryVideo $video, VideoManager $manager)
    {
        $data = $request->validate(['is_visible' => 'required|boolean']);

        return $this->result($manager->visibility($request->user(), $video, (bool) $data['is_visible']));
    }

    public function destroy(Request $request, LibraryVideo $video, VideoManager $manager)
    {
        $data = $request->validate(['confirmation' => 'required|string']);
        $manager->delete($request->user(), $video, $data['confirmation']);

        return response()->noContent();
    }

    public function example(Request $request, PublicExample $example)
    {
        $this->authorize('manage', LibraryVideo::class);
        $data = $request->validate(['video_id' => 'present|nullable|integer|exists:library_videos,id']);
        $example->select($request->user(), $data['video_id'] === null ? null : LibraryVideo::findOrFail($data['video_id']));

        return response()->noContent();
    }

    private function fileRules(bool $required): array
    {
        return [
            'request_key' => 'required|uuid',
            'video' => [$required ? 'required' : 'nullable', 'file', 'max:'.config('video_library.max_video_kib')],
            'cover' => ['nullable', 'file', 'max:'.config('video_library.max_cover_kib')],
        ];
    }

    private function result(LibraryVideo $video)
    {
        $selected = VideoLibrarySetting::whereKey(1)->where('public_example_video_id', $video->id)->exists();

        return response()->json(['video' => $video->only([
            'id', 'title', 'description', 'qudrat_category_id', 'arabic_skill_bank_id', 'program',
            'duration_seconds', 'width', 'height', 'byte_size', 'cover_origin', 'is_visible', 'published_at', 'processing_state',
        ]), 'processing' => $video->processing_token !== null, 'processing_error' => $video->processing_error,
            'is_public_example' => $selected], $video->processing_token === null ? 200 : 202);
    }
}
