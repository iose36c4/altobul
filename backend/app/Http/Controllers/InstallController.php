<?php

namespace App\Http\Controllers;

use App\Models\AppConfig;
use App\Models\User;
use App\Services\ApiKeyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class InstallController extends Controller
{
    public function __construct(
        private ApiKeyService $apiKeyService,
    ) {}

    public function show(): JsonResponse
    {
        $config = AppConfig::getConfig();
        $installed = $config->get('installed', false);

        return response()->json([
            'installed' => $installed,
            'requires_installation' => ! $installed,
        ]);
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

    public function install(Request $request): JsonResponse
    {
        $config = AppConfig::getConfig();

        if ($config->get('installed', false)) {
            return response()->json([
                'error' => 'Already installed',
                'message' => 'Backend is already installed. Installation cannot be repeated.',
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'app_name' => ['nullable', 'string', 'max:100'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Create first admin user
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

            // Create default API keys
            $clientKey = $this->apiKeyService->createApiKey($admin, 'Client Production', 'CLIENT');
            $adminKey = $this->apiKeyService->createApiKey($admin, 'Admin Production', 'ADMIN');

            // Mark as installed
            $config->set('installed', true);
            $config->set('installed_at', now()->toISOString());
            $config->set('first_admin_id', $admin->id);
            $config->save();

            DB::commit();

            return response()->json([
                'message' => 'Backend installed successfully',
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
                        'warning' => 'Store this key securely. It will not be shown again.',
                    ],
                    'admin' => [
                        'name' => $adminKey['api_key']->name,
                        'type' => $adminKey['api_key']->type,
                        'raw_key' => $adminKey['raw_key'],
                        'warning' => 'Store this key securely. It will not be shown again.',
                    ],
                ],
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Installation failed',
                'message' => $e->getMessage(),
            ], 500);
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
