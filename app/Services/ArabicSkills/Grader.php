<?php

namespace App\Services\ArabicSkills;

use Illuminate\Validation\ValidationException;

class Grader
{
    public function grade(array $snapshot, array $response): array
    {
        $valid = false;
        $points = 0;
        if ($snapshot['type'] !== 'matching') {
            $valid = array_keys($response) === ['option_id'] && is_string($response['option_id'])
                && in_array($response['option_id'], array_column($snapshot['options'], 'id'), true);
            if ($valid) {
                $points = $response['option_id'] === $snapshot['answer']['option_id'] ? $snapshot['max_score'] : 0;
            }
        } elseif (array_keys($response) === ['matches'] && is_array($response['matches']) && array_is_list($response['matches']) && count($response['matches']) === count($snapshot['matching_items'])) {
            $matches = [];
            $valid = true;
            foreach ($response['matches'] as $match) {
                if (! is_array($match) || count($match) !== 2 || ! is_string($match['item_id'] ?? null) || ! is_string($match['option_id'] ?? null)
                    || ! in_array($match['item_id'], array_column($snapshot['matching_items'], 'id'), true)
                    || ! in_array($match['option_id'], array_column($snapshot['options'], 'id'), true) || isset($matches[$match['item_id']])) {
                    $valid = false;
                    break;
                }
                $matches[$match['item_id']] = $match['option_id'];
            }
            if ($valid) {
                $response = ['matches' => []];
                foreach ($snapshot['answer']['matches'] as $answer) {
                    $response['matches'][] = ['item_id' => $answer['item_id'], 'option_id' => $matches[$answer['item_id']]];
                    if ($matches[$answer['item_id']] === $answer['option_id']) {
                        $points += $snapshot['scoring']['points_per_correct_match'];
                    }
                }
            }
        }
        if (! $valid) {
            throw ValidationException::withMessages(['response' => 'أكمل الإجابة باستخدام الخيارات المعروضة لكل جزء من السؤال.']);
        }

        return ['response' => $response, 'earned_points' => $points,
            'result' => $points === $snapshot['max_score'] ? 'correct' : ($points > 0 ? 'partial' : 'incorrect')];
    }
}
