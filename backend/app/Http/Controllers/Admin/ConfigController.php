<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateConfigRequest;
use App\Http\Resources\Admin\ConfigResource;
use App\Models\AppConfig;
use Illuminate\Http\JsonResponse;

class ConfigController extends Controller
{
    public function show(): JsonResponse
    {
        $configs = AppConfig::all()->keyBy('key')->map(fn($c) => $c->value);
        
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
        }
        
        // Clear cache
        \Illuminate\Support\Facades\Cache::flush();
        
        return response()->json([
            'message' => 'Configuration updated successfully',
        ]);
    }
}