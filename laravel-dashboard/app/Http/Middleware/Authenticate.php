<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Authenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {

            // AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session expired. Please login again.',
                    'redirect' => route('cpo.login'),
                ], 401);
            }

            // NORMAL REDIRECT
            return redirect()->route('cpo.login');
        }

        // Role Check
        $user = Auth::user();
        if (!in_array($user->id_role ?? null, [1, 2])) {
            Auth::logout();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access.',
                    'redirect' => route('cpo.login'),
                ], 403);
            }

            return redirect()->route('cpo.login');
        }

        return $next($request);
    }

    protected function redirectTo($request): ?string
    {
        return route('cpo.login');
    }
}