<?php

namespace App\Events\Broadcast;

use App\Models\ProfileFieldValue;
use App\Models\ProfileFieldValueAccess;
use App\Models\User;
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
        public ProfileFieldValue $fieldValue,
        public User $grantee,
        public User $grantedBy,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("user.{$this->grantee->id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'grant.new';
    }

    public function broadcastWith(): array
    {
        return [
            'grant' => [
                'id' => $this->grant->id,
                'field_value_id' => $this->grant->field_value_id,
                'granted_by' => [
                    'id' => $this->grantedBy->id,
                ],
                'granted_at' => $this->grant->granted_at?->toISOString(),
                'expires_at' => $this->grant->expires_at?->toISOString(),
            ],
            'field_value' => [
                'id' => $this->fieldValue->id,
                'field' => $this->fieldValue->field ? [
                    'id' => $this->fieldValue->field->id,
                    'slug' => $this->fieldValue->field->slug,
                    'name' => $this->fieldValue->field->name,
                ] : null,
            ],
        ];
    }
}
