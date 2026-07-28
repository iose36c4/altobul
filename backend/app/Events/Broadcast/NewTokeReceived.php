<?php

namespace App\Events\Broadcast;

use App\Models\Toke;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewTokeReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Toke $toke,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("user.{$this->toke->receiver_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'toke.received';
    }

    public function broadcastWith(): array
    {
        $this->toke->loadMissing(['sender.profile', 'receiver.profile']);

        return [
            'toke' => [
                'id' => $this->toke->id,
                'sender_id' => $this->toke->sender_id,
                'receiver_id' => $this->toke->receiver_id,
                'status' => $this->toke->status,
                'expires_at' => $this->toke->expires_at?->toISOString(),
                'created_at' => $this->toke->created_at?->toISOString(),
                'sender' => $this->toke->sender ? [
                    'id' => $this->toke->sender->id,
                    'profile' => $this->toke->sender->profile ? [
                        'title' => $this->toke->sender->profile->title,
                        'description' => $this->toke->sender->profile->description,
                    ] : null,
                ] : null,
            ],
        ];
    }
}
