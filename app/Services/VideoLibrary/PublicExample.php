<?php

namespace App\Services\VideoLibrary;

use App\Models\LibraryVideo;
use App\Models\User;
use App\Models\VideoLibrarySetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class PublicExample
{
    public function select(User $admin, ?LibraryVideo $video): void
    {
        Gate::forUser($admin)->authorize('manage', LibraryVideo::class);
        DB::transaction(function () use ($video) {
            $setting = VideoLibrarySetting::whereKey(1)->lockForUpdate()->firstOrFail();
            if ($video !== null && ! $video->isEligible()) {
                throw ValidationException::withMessages(['public_example_video_id' => 'اختر فيديو متاحًا للطلاب.']);
            }
            $setting->public_example_video_id = $video?->id;
            $setting->save();
        });
    }
}
