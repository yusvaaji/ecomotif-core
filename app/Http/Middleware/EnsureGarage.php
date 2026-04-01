<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureGarage
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('api')->user();

        if (!$user || $user->is_garage != 1) {
            return response()->json([
                'message' => trans('translate.Only garage owners can access this route')
            ], 403);
        }

        return $next($request);
    }
}
