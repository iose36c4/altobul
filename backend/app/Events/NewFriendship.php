<?php

namespace App\Events;

use App\Models\Friendship;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewFriendship implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Friendship $friendship,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("user.{$this->friendship->user_a_id}"),
            new PrivateChannel("user.{$this->friendship->user_b_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'friendship.created';
    }

    public function broadcastWith(): array
    {
        return [
            'friendship' => [
                'id' => $this->friendship->id,
                'user_a_id' => $this->friendship->user_a_id,
                'user_b_id' => $this->friendship->user_b_id,
                'status' => $this->friendship->status,
                'via_match' => $this->friendship->via_match,
                'created_at' => $this->friendship->created_at?->toISOString(),
            ],
        ];
    }
}
