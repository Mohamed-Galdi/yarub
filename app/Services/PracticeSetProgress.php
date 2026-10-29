<?php

namespace App\Services;

use Illuminate\Support\Collection;

class PracticeSetProgress
{
    /** Attach only the current student's session summaries; question content stays server-side. */
    public function cards(Collection $definitions, Collection $sessions, bool $enabled): array
    {
        $bySet = $sessions->groupBy('practice_set_key');

        return $definitions->map(function ($set) use ($bySet, $enabled) {
            $history = $bySet->get($set['key'], collect());
            $resume = $history->firstWhere('status', 'active');
            $latest = $history->firstWhere('status', 'completed');
            $incomplete = $history->first(fn ($session) => in_array($session['status'], ['abandoned', 'closed'], true));

            return array_diff_key($set, ['question_ids' => true]) + ['can_start' => $enabled && $set['available'] > 0,
                'resume' => $resume, 'latest_completed' => $latest, 'latest_incomplete' => $incomplete];
        })->all();
    }
}
