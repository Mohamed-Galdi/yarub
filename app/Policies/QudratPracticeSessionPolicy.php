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

    // Owner decision: Qudrat is available to every authenticated student.
    public function create(User $user): bool
    {
        return $user->role === 'student';
    }

    public function delete(User $user, QudratPracticeSession $session): bool
    {
        return false;
    }
}
