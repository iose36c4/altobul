<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class InstallController
{
    public function show(): View|RedirectResponse
    {
        if (env('ADMIN_API_KEY')) {
            return redirect()->route('admin.login');
        }

        return view('install.index', [
            'defaultUrl' => env('ADMIN_API_BASE_URL', 'http://localhost:8000'),
        ]);
    }

    public function testConnection(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'backend_url' => ['required', 'string', 'url', 'max:255'],
            'api_key' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Parámetros inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        $baseUrl = rtrim($request->input('backend_url'), '/');
        $apiKey = $request->input('api_key');

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'X-API-Key' => $apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($baseUrl.'/api/admin/verify');

            if ($response->ok()) {
                return response()->json(['success' => true, 'message' => 'Conexión exitosa']);
            }

            return response()->json([
                'success' => false,
                'message' => 'Clave API inválida (HTTP '.$response->status().')',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de conexión: '.$e->getMessage(),
            ]);
        }
    }

    public function save(Request $request): JsonResponse|RedirectResponse|View
    {
        $validator = Validator::make($request->all(), [
            'backend_url' => ['required', 'string', 'url', 'max:255'],
            'api_key' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            return back()->withErrors($validator)->withInput();
        }

        $baseUrl = rtrim($request->input('backend_url'), '/');
        $apiKey = $request->input('api_key');

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'X-API-Key' => $apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($baseUrl.'/api/admin/verify');

            if (! $response->ok()) {
                $message = 'No se pudo conectar. Verificá la URL y la clave API.';

                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 422);
                }

                return back()->withErrors(['connection' => $message])->withInput();
            }
        } catch (\Throwable $e) {
            $message = 'Error de conexión: '.$e->getMessage();

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return back()->withErrors(['connection' => $message])->withInput();
        }

        $this->writeEnv('ADMIN_API_BASE_URL', $baseUrl);
        $this->writeEnv('ADMIN_API_KEY', $apiKey);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Configuración guardada']);
        }

        return view('install.success');
    }

    private function writeEnv(string $key, string $value): void
    {
        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            return;
        }

        $envContent = file_get_contents($envPath);

        $escapedKey = preg_quote($key, '/');
        if (preg_match("/^{$escapedKey}=.*/m", $envContent)) {
            $envContent = preg_replace("/^{$escapedKey}=.*/m", "{$key}={$value}", $envContent);
        } else {
            $envContent .= "\n{$key}={$value}";
        }

        file_put_contents($envPath, $envContent);
    }
}
