<?php

namespace App\Http\Controllers;

use App\Models\AppConfig;
use App\Models\User;
use App\Services\ApiKeyService;
use Illuminate\Database\Connection;
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
        $config = $this->getConfig();
        $installed = $config->get('installed', false);

        if ($request->expectsJson()) {
            return response()->json([
                'installed' => $installed,
                'requires_installation' => ! $installed,
            ]);
        }

        if ($installed) {
            $installedAt = $config->get('installed_at');
            $adminEmail = null;
            if ($adminId = $config->get('first_admin_id')) {
                $admin = User::find($adminId);
                $adminEmail = $admin?->email;
            }

            return view('installer.index', compact('installed', 'installedAt', 'adminEmail'));
        }

        $step = $request->input('step', 1);

        return view('installer.wizard', [
            'step' => (int) $step,
            'dbConfig' => $this->getDatabaseConfigFromEnv(),
        ]);
    }

    public function testDatabase(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'db_host' => ['required', 'string', 'max:255'],
            'db_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'db_database' => ['required', 'string', 'max:255'],
            'db_username' => ['required', 'string', 'max:255'],
            'db_password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Parámetros inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $connection = $this->createTemporaryConnection($request->all());
            $connection->getPdo();
            $connection->disconnect();

            return response()->json([
                'success' => true,
                'message' => 'Conexión exitosa',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo conectar: '.$e->getMessage(),
            ]);
        }
    }

    public function saveDatabase(Request $request): JsonResponse|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'db_host' => ['required', 'string', 'max:255'],
            'db_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'db_database' => ['required', 'string', 'max:255'],
            'db_username' => ['required', 'string', 'max:255'],
            'db_password' => ['required', 'string'],
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

        try {
            $connection = $this->createTemporaryConnection($request->all());
            $connection->getPdo();
            $connection->disconnect();
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo conectar: '.$e->getMessage(),
                ]);
            }

            return back()->withErrors(['db_connection' => 'No se pudo conectar: '.$e->getMessage()])->withInput();
        }

        $this->writeDatabaseToEnv($request->only([
            'db_host', 'db_port', 'db_database', 'db_username', 'db_password',
        ]));

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Configuración de BD guardada']);
        }

        return redirect()->route('install.show', ['step' => 2]);
    }

    public function saveAdmin(Request $request): JsonResponse|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'admin_email' => ['required', 'email', 'max:255'],
            'admin_password' => ['required', 'string', 'min:8', 'confirmed'],
            'admin_name' => ['nullable', 'string', 'max:255'],
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

        session(['install_admin' => [
            'email' => $request->input('admin_email'),
            'password' => $request->input('admin_password'),
            'name' => $request->input('admin_name'),
        ]]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('install.show', ['step' => 3]);
    }

    public function saveApiKeys(Request $request): JsonResponse|RedirectResponse
    {
        $keys = $request->input('api_keys', []);

        if (empty($keys)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debes crear al menos una clave API',
                ], 422);
            }

            return back()->withErrors(['api_keys' => 'Debes crear al menos una clave API'])->withInput();
        }

        foreach ($keys as $index => $keyData) {
            $validator = Validator::make($keyData, [
                'name' => ['required', 'string', 'max:255'],
                'type' => ['required', 'string', 'in:CLIENT,ADMIN'],
                'expires_days' => ['nullable', 'integer', 'min:1'],
            ]);

            if ($validator->fails()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Clave API #'.($index + 1).': '.$validator->errors()->first(),
                        'errors' => $validator->errors(),
                    ], 422);
                }

                return back()->withErrors($validator)->withInput();
            }
        }

        session(['install_api_keys' => $keys]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('install.show', ['step' => 4]);
    }

    public function execute(Request $request): JsonResponse|RedirectResponse
    {
        $adminData = session('install_admin');
        $apiKeysData = session('install_api_keys');

        if (! $adminData || ! $apiKeysData) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesión de instalación incompleta. Empezá de nuevo.',
                ], 400);
            }

            return redirect()->route('install.show', ['step' => 1])
                ->withErrors(['general' => 'Sesión de instalación incompleta. Empezá de nuevo.']);
        }

        try {
            DB::beginTransaction();

            DB::select('SELECT pg_advisory_xact_lock(1)');

            $config = AppConfig::getConfig();
            if (($config->get('installed') ?? false)) {
                DB::rollBack();

                return $this->installerAlreadyDone($request);
            }

            $userId = (string) Str::uuid();

            DB::table('users')->insert([
                'id' => $userId,
                'email' => $adminData['email'],
                'password_hash' => Hash::make($adminData['password']),
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

            $createdKeys = [];
            foreach ($apiKeysData as $keyData) {
                $result = $this->apiKeyService->createApiKey(
                    $admin,
                    $keyData['name'],
                    $keyData['type'],
                    $keyData['expires_days'] ?? null,
                );

                $createdKeys[] = [
                    'name' => $result['api_key']->name,
                    'type' => $result['api_key']->type,
                    'raw_key' => $result['raw_key'],
                ];
            }

            $config->set('installed', true);
            $config->set('installed_at', now()->toISOString());
            $config->set('first_admin_id', $admin->id);
            $config->save();

            DB::commit();

            session()->forget(['install_admin', 'install_api_keys']);

            $data = [
                'admin' => [
                    'email' => $admin->email,
                    'role' => $admin->role,
                ],
                'api_keys' => $createdKeys,
            ];

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Backend instalado correctamente',
                    ...$data,
                ], 201);
            }

            return view('installer.success', $data);

        } catch (\Throwable) {
            DB::rollBack();

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Error durante la instalación',
                    'message' => 'Revisá los logs del servidor.',
                ], 500);
            }

            return back()->withErrors([
                'general' => 'Error inesperado durante la instalación. Revisá los logs del servidor.',
            ])->withInput();
        }
    }

    public function install(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            DB::select('SELECT pg_advisory_xact_lock(1)');

            $config = AppConfig::getConfig();
            if (($config->get('installed') ?? false)) {
                DB::rollBack();

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
                DB::rollBack();

                return response()->json([
                    'error' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
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
                    ],
                    'admin' => [
                        'name' => $adminKey['api_key']->name,
                        'type' => $adminKey['api_key']->type,
                        'raw_key' => $adminKey['raw_key'],
                    ],
                ],
            ], 201);

        } catch (\Throwable) {
            DB::rollBack();

            return response()->json([
                'error' => 'Installation failed',
                'message' => 'An unexpected error occurred during installation. Please check server logs.',
            ], 500);
        }
    }

    public function status(): JsonResponse
    {
        $config = $this->getConfig();
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

    private function getConfig(): AppConfig
    {
        return AppConfig::getConfig();
    }

    private function getDatabaseConfigFromEnv(): array
    {
        return [
            'db_host' => env('DB_HOST', '127.0.0.1'),
            'db_port' => env('DB_PORT', '5432'),
            'db_database' => env('DB_DATABASE', 'altobul'),
            'db_username' => env('DB_USERNAME', ''),
            'db_password' => env('DB_PASSWORD', ''),
        ];
    }

    private function createTemporaryConnection(array $data): Connection
    {
        config()->set('database.connections.installer', [
            'driver' => 'pgsql',
            'host' => $data['db_host'],
            'port' => $data['db_port'],
            'database' => $data['db_database'],
            'username' => $data['db_username'],
            'password' => $data['db_password'],
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ]);

        return DB::connection('installer');
    }

    private function writeDatabaseToEnv(array $data): void
    {
        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            return;
        }

        $envContent = file_get_contents($envPath);

        $replacements = [
            'DB_CONNECTION' => 'pgsql',
            'DB_HOST' => $data['db_host'],
            'DB_PORT' => (string) $data['db_port'],
            'DB_DATABASE' => $data['db_database'],
            'DB_USERNAME' => $data['db_username'],
            'DB_PASSWORD' => $data['db_password'],
        ];

        foreach ($replacements as $key => $value) {
            if (preg_match("/^{$key}=.*/m", $envContent)) {
                $envContent = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $envContent);
            } else {
                $envContent .= "\n{$key}={$value}";
            }
        }

        file_put_contents($envPath, $envContent);

        $this->resetDatabaseConfig($data);
    }

    private function resetDatabaseConfig(array $data): void
    {
        $dbConfig = [
            'driver' => 'pgsql',
            'host' => $data['db_host'],
            'port' => (int) $data['db_port'],
            'database' => $data['db_database'],
            'username' => $data['db_username'],
            'password' => $data['db_password'],
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ];

        config()->set('database.connections.pgsql', $dbConfig);
        DB::purge('pgsql');
    }

    private function installerAlreadyDone(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Ya instalado',
                'message' => 'El backend ya está instalado.',
            ], 400);
        }

        return redirect()->route('install.show', ['step' => 1])
            ->withErrors(['general' => 'El backend ya está instalado.']);
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
