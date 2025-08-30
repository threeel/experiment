<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountNotScheduledForDeletion
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && $user->delete_scheduled_at) {
            if (! $request->routeIs('account.deletion-status') && ! $request->routeIs('logout')) {
                return redirect()->route('account.deletion-status');
            }
        }

        return $next($request);
    }
}
