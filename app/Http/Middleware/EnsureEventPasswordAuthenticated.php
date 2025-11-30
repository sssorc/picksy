<?php

namespace App\Http\Middleware;

use App\Models\Event;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class EnsureEventPasswordAuthenticated
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

        // If event has a password requirement
        if ($event->password) {
            $isAuthenticated = $request->session()->get("event_{$event->id}_password_auth", false);

            if (! $isAuthenticated) {
                // Exclude the password authentication route itself
                if ($request->routeIs('event.password')) {
                    return $next($request);
                }

                return Inertia::render('Public/EventPassword', [
                    'event' => [
                        'title' => $event->title,
                        'slug' => $event->slug,
                    ],
                ])->toResponse($request);
            }
        }

        // Store event in request for controllers to use
        $request->merge(['event' => $event]);

        return $next($request);
    }
}
