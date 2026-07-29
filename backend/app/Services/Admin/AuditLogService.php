<?php

namespace App\Services\Admin;

use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuditLogService
{
    public function log(
        string $action,
        string $targetType,
        string $targetId,
        array $metadata = [],
        ?User $admin = null,
        ?Request $request = null
    ): void {
        $adminId = $admin?->id;
        $ipAddress = $request?->ip();
        $userAgent = $request?->userAgent();

        AdminAuditLog::create([
            'id' => (string) Str::uuid(),
            'admin_id' => $adminId,
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'metadata' => $metadata,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'created_at' => now(),
        ]);
    }
}
