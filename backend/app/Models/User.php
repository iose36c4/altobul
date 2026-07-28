<?php

namespace App\Models;

use App\Models\UserMatch as MatchModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['email', 'password_hash', 'role', 'status', 'verification_status', 'verified_at', 'email_verified_at', 'last_seen_at', 'failed_login_attempts', 'locked_until'])]
#[Hidden(['password_hash', 'remember_token'])]
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'verified_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
            'password_hash' => 'hashed',
        ];
    }

    public function profile()
    {
        return $this->hasOne(Profile::class, 'user_id', 'id');
    }

    public function photos()
    {
        return $this->hasMany(Photo::class, 'user_id', 'id');
    }

    public function posts()
    {
        return $this->hasMany(Post::class, 'user_id', 'id');
    }

    public function sentTokes()
    {
        return $this->hasMany(Toke::class, 'sender_id', 'id');
    }

    public function receivedTokes()
    {
        return $this->hasMany(Toke::class, 'receiver_id', 'id');
    }

    public function matchesAsA()
    {
        return $this->hasMany(MatchModel::class, 'user_a_id', 'id');
    }

    public function matchesAsB()
    {
        return $this->hasMany(MatchModel::class, 'user_b_id', 'id');
    }

    public function friendshipsAsA()
    {
        return $this->hasMany(Friendship::class, 'user_a_id', 'id');
    }

    public function friendshipsAsB()
    {
        return $this->hasMany(Friendship::class, 'user_b_id', 'id');
    }

    public function blocksAsBlocker()
    {
        return $this->hasMany(Block::class, 'blocker_id', 'id');
    }

    public function blocksAsBlocked()
    {
        return $this->hasMany(Block::class, 'blocked_id', 'id');
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class, 'user_a_id', 'id')
            ->orWhere('user_b_id', $this->id);
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id', 'id');
    }

    public function verificationRequests()
    {
        return $this->hasMany(VerificationRequest::class, 'user_id', 'id');
    }

    public function discoveryPreferences()
    {
        return $this->hasMany(DiscoveryPreference::class, 'user_id', 'id');
    }

    public function grantedFieldAccess()
    {
        return $this->hasMany(ProfileFieldValueAccess::class, 'grantee_id', 'id');
    }

    public function grantedPhotoAccess()
    {
        return $this->hasMany(PhotoAccess::class, 'grantee_id', 'id');
    }

    public function grantedPostAccess()
    {
        return $this->hasMany(PostAccess::class, 'grantee_id', 'id');
    }

    public function isOnline(): bool
    {
        return $this->last_seen_at
            && $this->last_seen_at->diffInMinutes(now()) < (config('app.online_threshold_minutes', 2));
    }

    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    public function recordFailedLogin(): void
    {
        $attempts = $this->failed_login_attempts + 1;
        $lockMinutes = match (true) {
            $attempts >= 10 => 60,
            $attempts >= 5 => 15,
            $attempts >= 3 => 5,
            default => 0,
        };

        $this->update([
            'failed_login_attempts' => $attempts,
            'locked_until' => $lockMinutes > 0 ? now()->addMinutes($lockMinutes) : null,
        ]);
    }

    public function resetFailedLoginAttempts(): void
    {
        $this->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ]);
    }
}
