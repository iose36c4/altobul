<?php

namespace App\Events\Broadcast;

use App\Models\Photo;
use App\Models\PhotoAccess;
use App\Models\Post;
use App\Models\PostAccess;
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
        public ProfileFieldValueAccess|PhotoAccess|PostAccess $grant,
        public ProfileFieldValue|Photo|Post|null $resource,
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
        $resourceType = match (true) {
            $this->resource instanceof Photo => 'photo',
            $this->resource instanceof Post => 'post',
            default => 'profile_field',
        };

        $resourceId = match (true) {
            $this->resource instanceof Photo => $this->resource->id,
            $this->resource instanceof Post => $this->resource->id,
            default => $this->resource?->id,
        };

        return [
            'grant' => [
                'id' => $this->grant->id,
                'resource_type' => $resourceType,
                'resource_id' => $resourceId,
                'granted_by' => [
                    'id' => $this->grantedBy->id,
                ],
                'granted_at' => $this->grant->granted_at?->toISOString(),
                'expires_at' => $this->grant->expires_at?->toISOString(),
            ],
            'resource' => [
                'type' => $resourceType,
                'id' => $resourceId,
            ],
        ];
    }
}
