<?php

namespace App\Http\Controllers;

use App\Models\AppConfig;
use App\Models\User;
use App\Services\ApiKeyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InstallController extends Controller
{
    public function __construct(
        private ApiKeyService $apiKeyService,
    ) {}

    public function show(Request $request): View|JsonResponse
    {
        $config = AppConfig::getConfig();
        $installed = $config->get('installed', false);

        if ($request->expectsJson()) {
            return response()->json([
                'installed' => $installed,
                'requires_installation' => ! $installed,
            ]);
        }

        $installedAt = $config->get('installed_at');
        $adminEmail = null;
        if ($installed && $adminId = $config->get('first_admin_id')) {
            $admin = User::find($adminId);
            $adminEmail = $admin?->email;
        }

        return view('installer.index', compact('installed', 'installedAt', 'adminEmail'));
    }

    public function status(): JsonResponse
    {
        $config = AppConfig::getConfig();
        $installed = $config->get('installed', false);

        $checks = [
            'database' => $this->checkDatabase(),
            'migrations' => $this->checkMigrations(),
            'app_config' => $installed,
            'first_admin' => $installed && User::where('role', 'admin')->exists(),
        ];

        $allPassed = collect($checks)->every(fn ($v) => $v === true);

        return response()->json([
            'installed' => $installed,
            'checks' => $checks,
            'ready' => $allPassed,
        ]);
    }

    public function install(Request $request): View|JsonResponse|RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Advisory lock ensures only one installation can proceed at a time
            DB::select('SELECT pg_advisory_xact_lock(1)');

            $config = AppConfig::getConfig();
            if (($config->get('installed') ?? false)) {
                DB::rollBack();

                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'Already installed',
                        'message' => 'Backend is already installed. Installation cannot be repeated.',
                    ], 400);
                }

                return back()->withErrors(['general' => 'El backend ya está instalado.']);
            }

            $validator = Validator::make($request->all(), [
                'email' => ['required', 'email', 'max:255'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'app_name' => ['nullable', 'string', 'max:100'],
            ]);

            if ($validator->fails()) {
                DB::rollBack();

                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'Validation failed',
                        'errors' => $validator->errors(),
                    ], 422);
                }

                return back()->withErrors($validator)->withInput();
            }

            $userId = (string) Str::uuid();

            DB::table('users')->insert([
                'id' => $userId,
                'email' => $request->input('email'),
                'password_hash' => Hash::make($request->input('password')),
                'email_verified_at' => now(),
                'verification_status' => 'not_verified',
                'status' => 'active',
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('profiles')->insert([
                'user_id' => $userId,
                'location' => DB::raw('ST_SetSRID(ST_MakePoint(0, 0), 4326)'),
                'location_precision_meters' => config('app.location_default_precision_meters', 1000),
                'discoverable' => true,
                'profile_visibility' => 'PUBLIC',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $admin = User::find($userId);

            $clientKey = $this->apiKeyService->createApiKey($admin, 'Client Production', 'CLIENT');
            $adminKey = $this->apiKeyService->createApiKey($admin, 'Admin Production', 'ADMIN');

            $config->set('installed', true);
            $config->set('installed_at', now()->toISOString());
            $config->set('first_admin_id', $admin->id);
            $config->save();

            DB::commit();

            $data = [
                'admin' => [
                    'id' => $admin->id,
                    'email' => $admin->email,
                    'role' => $admin->role,
                ],
                'api_keys' => [
                    'client' => [
                        'name' => $clientKey['api_key']->name,
                        'type' => $clientKey['api_key']->type,
                        'raw_key' => $clientKey['raw_key'],
                    ],
                    'admin' => [
                        'name' => $adminKey['api_key']->name,
                        'type' => $adminKey['api_key']->type,
                        'raw_key' => $adminKey['raw_key'],
                    ],
                ],
            ];

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Backend installed successfully',
                    ...$data,
                ], 201);
            }

            return view('installer.success', $data);

        } catch (\Throwable) {
            DB::rollBack();

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Installation failed',
                    'message' => 'An unexpected error occurred during installation. Please check server logs.',
                ], 500);
            }

            return back()->withErrors(['general' => 'Error inesperado durante la instalación. Revisa los logs del servidor.'])->withInput();
        }
    }

    private function checkDatabase(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function checkMigrations(): bool
    {
        try {
            $migrated = DB::table('migrations')->count();

            return $migrated > 0;
        } catch (\Throwable) {
            return false;
        }
    }
}
