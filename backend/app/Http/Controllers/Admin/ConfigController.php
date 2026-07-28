<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateConfigRequest;
use App\Models\AppConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class ConfigController extends Controller
{
    public function show(): JsonResponse
    {
        $configs = AppConfig::all()->keyBy('key')->map(fn ($c) => $c->value);

        return response()->json([
            'configs' => $configs,
        ]);
    }

    public function update(UpdateConfigRequest $request): JsonResponse
    {
        foreach ($request->validated() as $key => $value) {
            AppConfig::updateOrInsert(
                ['key' => $key],
                [
                    'value' => $value,
                    'updated_by' => $request->user()->id,
                    'updated_at' => now(),
                ]
            );
            Cache::forget("app_config.{$key}");
        }

        return response()->json([
            'message' => 'Configuration updated successfully',
        ]);
    }
}
