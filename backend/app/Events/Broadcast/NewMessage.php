<?php

namespace App\Events\Broadcast;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Message $message,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("conversation.{$this->message->conversation_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.new';
    }

    public function broadcastWith(): array
    {
        $this->message->loadMissing('sender.profile');

        return [
            'conversation_id' => $this->message->conversation_id,
            'message' => [
                'id' => $this->message->id,
                'content' => $this->message->content,
                'sender' => $this->message->sender ? [
                    'id' => $this->message->sender->id,
                    'email' => $this->message->sender->email,
                    'profile' => $this->message->sender->profile ? [
                        'title' => $this->message->sender->profile->title,
                    ] : null,
                ] : null,
                'created_at' => $this->message->created_at?->toISOString(),
            ],
        ];
    }
}
