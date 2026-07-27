<?php

namespace App\Http\Controllers;

use App\Models\AppConfig;
use Illuminate\Http\JsonResponse;

class SystemController extends Controller
{
    public function health(): JsonResponse
    {
        $config = AppConfig::getConfig();

        return response()->json([
            'status' => 'ok',
            'installed' => $config->get('installed', false),
            'version' => config('app.version', '1.0.0'),
            'api_version' => 'v1',
            'compatible_clients' => ['1.0.0'],
            'database' => 'connected',
            'timestamp' => now()->toISOString(),
        ]);
    }

    public function compatibility(): JsonResponse
    {
        $config = AppConfig::getConfig();

        return response()->json([
            'installed' => $config->get('installed', false),
            'api_version' => 'v1',
            'minimum_client_version' => '1.0.0',
            'compatible_applications' => [
                'client' => ['1.0.0'],
                'admin' => ['1.0.0'],
                'mobile' => ['1.0.0'],
            ],
            'requirements' => [
                'php' => '>=8.2',
                'database' => 'PostgreSQL 15+ with PostGIS',
            ],
        ]);
    }
}
