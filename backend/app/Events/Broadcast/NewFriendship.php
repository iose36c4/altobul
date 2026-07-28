<?php

namespace App\Events\Broadcast;

use App\Models\Friendship;
use App\Models\User;
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
        return 'friendship.new';
    }

    public function broadcastWith(): array
    {
        return [
            'friendship' => [
                'id' => $this->friendship->id,
                'user_a_id' => $this->friendship->user_a_id,
                'user_b_id' => $this->friendship->user_b_id,
                'via_match' => $this->friendship->via_match,
                'created_at' => $this->friendship->created_at?->toISOString(),
            ],
            'other_user' => null,
        ];
    }

    public function broadcastWithUser(User $user): array
    {
        $other = $user->id === $this->userA->id ? $this->userB : $this->userA;

        return [
            'friendship' => [
                'id' => $this->friendship->id,
                'user_a_id' => $this->friendship->user_a_id,
                'user_b_id' => $this->friendship->user_b_id,
                'via_match' => $this->friendship->via_match,
                'created_at' => $this->friendship->created_at?->toISOString(),
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
