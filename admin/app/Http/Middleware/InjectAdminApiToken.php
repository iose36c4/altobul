<?php

namespace App\Http\Middleware;

use App\Services\BackendApiService;
use Closure;
use Illuminate\Http\Request;

class InjectAdminApiToken
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->api_token) {
            app(BackendApiService::class)->withUserToken(auth()->user()->api_token);
        }

        return $next($request);
    }
}
