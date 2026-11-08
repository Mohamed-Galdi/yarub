<?php

namespace App\Services\Auth;

use Illuminate\Http\Request;

class StudentDestination
{
    public static function pull(Request $request): string
    {
        $intended = $request->session()->pull('url.intended');
        $base = url('/student-dashboard');
        if ($intended === route('google.account')) {
            return $intended;
        }

        // Only internal student pages can be restored after authentication.
        if (is_string($intended) && strlen($intended) <= 2048
            && ! preg_match('/[\x00-\x20\x7f\\\\]/', $intended)
            && ($intended === $base || str_starts_with($intended, $base.'/') || str_starts_with($intended, $base.'?'))) {
            if (str_starts_with($intended, route('student.videos.index'))) {
                return \App\Services\VideoLibrary\LoginDestination::from($intended) ?? route('student.courses.index');
            }

            return $intended;
        }

        return route('student.courses.index');
    }
}
