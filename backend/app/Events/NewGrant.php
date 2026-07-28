<?php

namespace App\Events;

use App\Models\ProfileFieldValueAccess;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewGrant implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ProfileFieldValueAccess $grant,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("user.{$this->grant->grantee_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'grant.created';
    }

    public function broadcastWith(): array
    {
        return [
            'grant' => [
                'id' => $this->grant->id,
                'field_value_id' => $this->grant->field_value_id,
                'grantee_id' => $this->grant->grantee_id,
                'granted_by' => $this->grant->granted_by,
                'expires_at' => $this->grant->expires_at?->toISOString(),
                'created_at' => $this->grant->created_at?->toISOString(),
            ],
        ];
    }
}
