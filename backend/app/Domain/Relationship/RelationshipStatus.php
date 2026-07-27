<?php

namespace App\Domain\Relationship;

use App\Models\Friendship;
use App\Models\UserMatch;

readonly class RelationshipStatus
{
    public function __construct(
        public RelationshipLevel $level,
        public ?UserMatch $matchModel = null,
        public ?Friendship $friendship = null,
        public bool $hasMutualToke = false,
    ) {}

    public function isAtLeastMatch(): bool
    {
        return $this->level->isAtLeastMatch();
    }

    public function isFriendship(): bool
    {
        return $this->level->isFriendship();
    }

    public function canChat(): bool
    {
        return $this->level->canChat();
    }

    public static function none(): self
    {
        return new self(RelationshipLevel::NONE);
    }

    public static function toked(): self
    {
        return new self(RelationshipLevel::TOKED);
    }

    public static function mutualToke(): self
    {
        return new self(RelationshipLevel::MUTUAL_TOKE, hasMutualToke: true);
    }

    public static function fromMatch(UserMatch $matchModel): self
    {
        return new self(RelationshipLevel::MATCH, matchModel: $matchModel);
    }

    public static function friendship(Friendship $friendship): self
    {
        return new self(RelationshipLevel::FRIENDSHIP, friendship: $friendship);
    }
}
