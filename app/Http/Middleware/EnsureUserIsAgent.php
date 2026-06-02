<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserIsAgent
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user || ! method_exists($user, 'isAgent') || ! $user->isAgent()) {
            return response()->json([
                'message' => 'Forbidden. Agent access required.',
            ], 403);
        }

        return $next($request);
    }
}
