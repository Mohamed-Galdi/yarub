<?php

namespace App\Policies;

use App\Models\LibraryVideo;
use App\Models\User;
use App\Models\VideoLibrarySetting;

class LibraryVideoPolicy
{
    public function viewAny(User $user): bool
    {
        return ! $user->trashed() && $user->role === 'student';
    }

    public function view(User $user, LibraryVideo $video): bool
    {
        return $this->viewAny($user) && $video->isEligible();
    }

    public function viewPublicExample(?User $user, LibraryVideo $video): bool
    {
        return $video->isEligible() && VideoLibrarySetting::whereKey(1)->where('public_example_video_id', $video->id)->exists();
    }

    public function manage(User $user): bool
    {
        return ! $user->trashed() && $user->role === 'admin';
    }

    public function preview(User $user, LibraryVideo $video): bool
    {
        return $this->manage($user);
    }

    public function create(User $user): bool
    {
        return $this->manage($user);
    }

    public function update(User $user, LibraryVideo $video): bool
    {
        return $this->manage($user);
    }

    public function delete(User $user, LibraryVideo $video): bool
    {
        return $this->manage($user);
    }
}
