<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

class SessionIdleTimeout
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $timeout = env('VITE_SESSION_IDLE_TIMEOUT', 900); // 15 min
        $lastActivity = session('lastActivityTime');
        $now = Carbon::now()->timestamp;

        if ($lastActivity && ($now - $lastActivity) > $timeout) {
            auth()->user()?->currentAccessToken()?->delete();
            session()->forget('lastActivityTime');

            return response()->json([
                'message' => 'Sesión expirada por inactividad',
            ], 401);
        }

        // actualizar actividad
        session(['lastActivityTime' => $now]);

        return $next($request);
    }
}
