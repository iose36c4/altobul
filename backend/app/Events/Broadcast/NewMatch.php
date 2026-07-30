<?php

namespace App\Events\Broadcast;

use App\Models\User;
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
        public User $userA,
        public User $userB,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("user.{$this->userA->id}"),
            new PrivateChannel("user.{$this->userB->id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'match.new';
    }

    public function broadcastWith(): array
    {
        return [
            'match' => [
                'id' => $this->match->id,
                'user_a_id' => $this->match->user_a_id,
                'user_b_id' => $this->match->user_b_id,
                'expires_at' => $this->match->expires_at?->toISOString(),
                'created_at' => $this->match->created_at?->toISOString(),
            ],
            'other_user' => null, // Will be resolved per channel
        ];
    }

    public function broadcastWithUser(User $user): array
    {
        $other = $user->id === $this->userA->id ? $this->userB : $this->userA;

        return [
            'match' => [
                'id' => $this->match->id,
                'user_a_id' => $this->match->user_a_id,
                'user_b_id' => $this->match->user_b_id,
                'expires_at' => $this->match->expires_at?->toISOString(),
                'created_at' => $this->match->created_at?->toISOString(),
            ],
            'other_user' => [
                'id' => $other->id,
                'profile' => $other->profile ? [
                    'title' => $other->profile->title,
                    'description' => $other->profile->description,
                ] : null,
            ],
        ];
    }
}
