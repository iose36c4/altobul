<?php

namespace App\Http\Middleware;

use App\Models\AppConfig;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InstallerGuardMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $config = AppConfig::getConfig();

        if ($config->get('installed', false)) {
            if ($request->expectsJson()) {
                abort(403, 'Installation is complete. Installer routes are disabled.');
            }

            abort(403, 'La instalación ya fue completada. El instalador está deshabilitado.');
        }

        return $next($request);
    }
}
