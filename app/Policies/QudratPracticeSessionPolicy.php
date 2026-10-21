<?php

namespace App\Policies;

use App\Models\QudratPracticeSession;
use App\Models\User;

class QudratPracticeSessionPolicy
{
    public function view(User $user, QudratPracticeSession $session): bool
    {
        return $user->role === 'student' && $user->id === $session->user_id;
    }

    public function update(User $user, QudratPracticeSession $session): bool
    {
        return $this->view($user, $session) && $session->status === 'active';
    }

    // Creation/access entitlement will be decided before Phase 4 exposes student routes.
    public function create(User $user): bool
    {
        return false;
    }

    public function delete(User $user, QudratPracticeSession $session): bool
    {
        return false;
    }
}
