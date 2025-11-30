<?php

namespace App\Http\Middleware;

use App\Models\Event;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureGradingPasswordAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $slug = $request->route('slug');

        if (! $slug) {
            return $next($request);
        }

        $event = Event::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        // Check if grading password is authenticated
        $isAuthenticated = $request->session()->get("event_{$event->id}_grading_auth", false);

        if (! $isAuthenticated) {
            // Allow the password authentication routes
            if ($request->routeIs('grade.show') || $request->routeIs('grade.password')) {
                return $next($request);
            }

            return redirect()->route('grade.show', $slug);
        }

        // Store event in request for controllers to use
        $request->merge(['event' => $event]);

        return $next($request);
    }
}
