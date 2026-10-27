<?php

namespace App\Services\VideoLibrary;

class LoginDestination
{
    public static function from(mixed $intended): ?string
    {
        $base = route('student.videos.index');
        if (! is_string($intended) || strlen($intended) > 2048 || preg_match('/[\x00-\x1F\x7F]/', $intended)) return null;
        if ($intended === $base) return $base;
        if (! str_starts_with($intended, $base.'?') || parse_url($intended, PHP_URL_FRAGMENT) !== null) return null;
        parse_str(parse_url($intended, PHP_URL_QUERY) ?? '', $query);
        $safe = [];
        foreach ($query as $key => $value) {
            if (! in_array($key, ['q', 'type', 'page', 'video'], true) || ! is_string($value) || preg_match('/[\x00-\x1F\x7F]/', $value)) return null;
            if ($key === 'q') $safe[$key] = mb_substr(trim($value), 0, 200);
            elseif ($key === 'type' && preg_match('/^[qa]:[1-9][0-9]{0,17}$/D', $value)) $safe[$key] = $value;
            elseif ($key === 'video') $safe[$key] = preg_match('/^[1-9][0-9]{0,17}$/D', $value) ? $value : 'invalid';
            elseif ($key === 'page' && filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 1000000]])) $safe[$key] = $value;
        }

        // Rebuild only this internal route, never redirect to a supplied URL verbatim.
        return route('student.videos.index', $safe);
    }
}
