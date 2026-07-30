<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isSelf = $request->user() && $request->user()->id === $this->id;
        $isAdmin = $request->user() && $request->user()->isAdmin();
        $isAdminApiRequest = $request->attributes->get('api_key_type') === 'ADMIN';
        $canViewAll = $isSelf || $isAdmin || $isAdminApiRequest;

        $data = [
            'id' => $this->id,
            'role' => $this->role,
            'status' => $this->status,
            'verification_status' => $this->verification_status,
            'verified_at' => $this->verified_at?->toISOString(),
            'email_verified_at' => $this->email_verified_at?->toISOString(),
            'last_seen_at' => $this->last_seen_at?->toISOString(),
            'is_online' => $this->isOnline(),
            'is_verified' => $this->isVerified(),
            'created_at' => $this->created_at?->toISOString(),
            'profile' => $this->whenLoaded('profile', fn () => new ProfileResource($this->profile)),
        ];

        if ($canViewAll) {
            $data['email'] = $this->email;
        }

        if ($isAdminApiRequest) {
            $data['photos'] = PhotoResource::collection($this->whenLoaded('photos'));
            $data['posts'] = PostResource::collection($this->whenLoaded('posts'));
            $data['sent_tokes'] = TokeResource::collection($this->whenLoaded('sentTokes'));
            $data['received_tokes'] = TokeResource::collection($this->whenLoaded('receivedTokes'));

            $matches = collect();
            if ($this->relationLoaded('matchesAsA')) {
                $matches = $matches->merge($this->matchesAsA);
            }
            if ($this->relationLoaded('matchesAsB')) {
                $matches = $matches->merge($this->matchesAsB);
            }
            $data['matches'] = UserMatchResource::collection($matches);

            $friendships = collect();
            if ($this->relationLoaded('friendshipsAsA')) {
                $friendships = $friendships->merge($this->friendshipsAsA);
            }
            if ($this->relationLoaded('friendshipsAsB')) {
                $friendships = $friendships->merge($this->friendshipsAsB);
            }
            $data['friendships'] = FriendshipResource::collection($friendships);

            $blocks = collect();
            if ($this->relationLoaded('blocksAsBlocker')) {
                $blocks = $blocks->merge($this->blocksAsBlocker);
            }
            if ($this->relationLoaded('blocksAsBlocked')) {
                $blocks = $blocks->merge($this->blocksAsBlocked);
            }
            $data['blocks'] = BlockResource::collection($blocks);

            $data['verification_requests'] = $this->whenLoaded('verificationRequests');
            $data['conversations'] = ConversationResource::collection($this->whenLoaded('conversations'));
            $data['friendship_requests_sent'] = FriendshipRequestResource::collection($this->whenLoaded('friendshipRequestsSent'));
            $data['friendship_requests_received'] = FriendshipRequestResource::collection($this->whenLoaded('friendshipRequestsReceived'));
        }

        return $data;
    }
}
