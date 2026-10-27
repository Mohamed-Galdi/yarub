<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\LibraryVideo::class => \App\Policies\LibraryVideoPolicy::class,
        \App\Models\ArabicSkillBank::class => \App\Policies\ArabicSkillContentPolicy::class,
        \App\Models\ArabicSkillModel::class => \App\Policies\ArabicSkillContentPolicy::class,
        \App\Models\ArabicSkillQuestion::class => \App\Policies\ArabicSkillContentPolicy::class,
        \App\Models\ArabicSkillSession::class => \App\Policies\ArabicSkillSessionPolicy::class,
        \App\Models\QudratCategory::class => \App\Policies\QudratContentPolicy::class,
        \App\Models\QudratPassage::class => \App\Policies\QudratContentPolicy::class,
        \App\Models\QudratQuestion::class => \App\Policies\QudratContentPolicy::class,
        \App\Models\QudratOption::class => \App\Policies\QudratContentPolicy::class,
        \App\Models\QudratPracticeSession::class => \App\Policies\QudratPracticeSessionPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Gate::define('view-student-tracking', fn (\App\Models\User $user) => $user->role === 'admin');
    }
}
