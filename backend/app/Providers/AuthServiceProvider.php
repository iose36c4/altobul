<?php

namespace App\Providers;

use App\Models\Block;
use App\Models\Conversation;
use App\Models\Friendship;
use App\Models\FriendshipRequest;
use App\Models\Photo;
use App\Models\Post;
use App\Models\Profile;
use App\Models\ProfileFieldValueAccess;
use App\Models\Toke;
use App\Models\User;
use App\Models\UserMatch;
use App\Models\VerificationRequest;
use App\Policies\BlockPolicy;
use App\Policies\ConversationPolicy;
use App\Policies\FriendshipPolicy;
use App\Policies\FriendshipRequestPolicy;
use App\Policies\PhotoPolicy;
use App\Policies\PostPolicy;
use App\Policies\ProfileFieldValueAccessPolicy;
use App\Policies\ProfilePolicy;
use App\Policies\TokePolicy;
use App\Policies\UserMatchPolicy;
use App\Policies\UserPolicy;
use App\Policies\VerificationRequestPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class => UserPolicy::class,
        Profile::class => ProfilePolicy::class,
        Photo::class => PhotoPolicy::class,
        Post::class => PostPolicy::class,
        Toke::class => TokePolicy::class,
        UserMatch::class => UserMatchPolicy::class,
        Friendship::class => FriendshipPolicy::class,
        FriendshipRequest::class => FriendshipRequestPolicy::class,
        Block::class => BlockPolicy::class,
        Conversation::class => ConversationPolicy::class,
        VerificationRequest::class => VerificationRequestPolicy::class,
        ProfileFieldValueAccess::class => ProfileFieldValueAccessPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
