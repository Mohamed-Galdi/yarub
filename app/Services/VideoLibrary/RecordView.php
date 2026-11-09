<?php

namespace App\Services\VideoLibrary;

use App\Models\LibraryVideo;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class RecordView
{
    public function record(User $student, LibraryVideo $video, string $openingId): void
    {
        Gate::forUser($student)->authorize('view', $video);

        // Repeated requests for the same opening must not add more views.
        DB::table('video_views')->insertOrIgnore([
            'video_id' => $video->id, 'user_id' => $student->id,
            'opening_id' => $openingId, 'viewed_at' => now(),
        ]);
    }
}
