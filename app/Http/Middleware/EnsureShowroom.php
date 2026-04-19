<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureShowroom
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('api')->user();

        if (!$user || ($user->is_dealer != 1 && !$user->isMarketing())) {
            return response()->json([
                'message' => trans('translate.Only dealer/showroom can access this route')
            ], 403);
        }

        return $next($request);
    }
}




