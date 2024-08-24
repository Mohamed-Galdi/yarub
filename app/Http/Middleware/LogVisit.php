<?php

namespace App\Http\Middleware;

use App\Models\Visitor;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogVisit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $ip = $request->ip();
        $userAgent = $request->userAgent();

        // Check if this IP has visited in the last 24 hours
        $existingVisit = Visitor::where('ip_address', $ip)
            ->where('visited_at', '>=', now()->subDay())
            ->first();

        if (!$existingVisit) {
            Visitor::create([
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'visited_at' => now(),
            ]);
        }

        return $next($request);
    }
}
