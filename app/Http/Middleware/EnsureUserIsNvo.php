<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsNvo
{
    /**
     * Allow only authenticated NVO (or admin) accounts through.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || (! $user->isNvo() && ! $user->isAdmin())) {
            return response()->json([
                'message' => 'This action requires an NVO account.',
            ], 403);
        }

        return $next($request);
    }
}
