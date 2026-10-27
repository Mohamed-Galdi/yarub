<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VideoLibraryAccess
{
    public function handle(Request $request, Closure $next, string $role = 'public')
    {
        // These are media/JSON endpoints; never redirect an unauthorized player to HTML.
        $request->headers->set('Accept', 'application/json');
        if ($role !== 'public' && ! $request->user()) {
            return response()->json(['message' => 'يلزم تسجيل الدخول.'], 401)->header('Cache-Control', 'private, no-store');
        }
        if ($role !== 'public' && ($request->user()->trashed() || $request->user()->role !== $role)) {
            return response()->json(['message' => 'غير مسموح.'], 403)->header('Cache-Control', 'private, no-store');
        }
        $response = $next($request);
        $response->headers->set('Cache-Control', 'private, no-store');
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        return $response;
    }
}
