<?php

namespace App\Policies;

use App\Models\ArabicSkillSession;
use App\Models\User;

class ArabicSkillSessionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function review(User $user, ArabicSkillSession $session): bool
    {
        return $user->role === 'admin';
    }

    public function create(User $user): bool
    {
        return $user->role === 'student';
    }

    public function view(User $user, ArabicSkillSession $session): bool
    {
        return $user->role === 'student' && $user->id === $session->user_id;
    }

    public function update(User $user, ArabicSkillSession $session): bool
    {
        return $this->view($user, $session) && $session->status === 'active';
    }

    public function delete(User $user, ArabicSkillSession $session): bool
    {
        return false;
    }
}
