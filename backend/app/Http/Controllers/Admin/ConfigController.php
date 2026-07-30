<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateConfigRequest;
use App\Models\AppConfig;
use App\Services\Admin\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class ConfigController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLog
    ) {}

    public function index(): JsonResponse
    {
        $configs = AppConfig::all()->keyBy('key')->map(fn ($c) => $c->value);

        return response()->json([
            'configs' => $configs,
        ]);
    }

    public function update(UpdateConfigRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $changes = [];

        foreach ($validated as $key => $value) {
            $oldConfig = AppConfig::where('key', $key)->first();
            $oldValue = $oldConfig?->value;

            AppConfig::updateOrInsert(
                ['key' => $key],
                [
                    'value' => $value,
                    'updated_by' => $request->user()->id,
                    'updated_at' => now(),
                ]
            );
            Cache::forget("app_config.{$key}");

            $changes[$key] = [
                'old_value' => $oldValue,
                'new_value' => $value,
            ];
        }

        foreach ($changes as $key => $change) {
            $this->auditLog->log('config.update', 'AppConfig', $key, $change, $request->user(), $request);
        }

        return response()->json([
            'message' => 'Configuration updated successfully',
        ]);
    }
}
