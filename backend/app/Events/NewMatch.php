<?php

namespace App\Events;

use App\Models\UserMatch;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMatch implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public UserMatch $match,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("user.{$this->match->user_a_id}"),
            new PrivateChannel("user.{$this->match->user_b_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'match.created';
    }

    public function broadcastWith(): array
    {
        return [
            'match' => [
                'id' => $this->match->id,
                'user_a_id' => $this->match->user_a_id,
                'user_b_id' => $this->match->user_b_id,
                'status' => $this->match->status,
                'expires_at' => $this->match->expires_at?->toISOString(),
                'created_at' => $this->match->created_at?->toISOString(),
            ],
        ];
    }
}
