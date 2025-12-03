<?php

namespace App\Http\Middleware;

use App\Models\Event;
use Closure;
use Illuminate\Http\Request;
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
                // Exclude the entry routes (show, store name, and confirm identity)
                if ($request->routeIs('event.login') || 
                    $request->routeIs('participant.store') || 
                    $request->routeIs('participant.confirm')) {
                    return $next($request);
                }

                // Redirect to entry page if not authenticated
                return redirect()->route('event.login', $slug);
            }
        }

        // Store event in request for controllers to use
        $request->merge(['event' => $event]);

        return $next($request);
    }
}
