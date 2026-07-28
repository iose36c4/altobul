<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminWebGuardMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('admin.login');
        }

        if (! Auth::user()->isAdmin()) {
            Auth::logout();

            return redirect()->route('admin.login')->withErrors(['email' => 'No tenés permisos de administrador']);
        }

        if (Auth::user()->status !== 'active') {
            Auth::logout();

            return redirect()->route('admin.login')->withErrors(['email' => 'Tu cuenta está deshabilitada']);
        }

        return $next($request);
    }
}
