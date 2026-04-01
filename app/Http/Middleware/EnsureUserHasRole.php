<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->hasRole($roles)) {
            return $next($request);
        }

        $targetRoute = $user->isAdminOrManager() ? 'site.home' : 'site.client';

        return redirect()
            ->route($targetRoute)
            ->with('error', 'Vous n avez pas acces a cette page.');
    }
}
