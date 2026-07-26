<?php

namespace App\Domain\Relationship;

enum RelationshipLevel: int
{
    case NONE = 0;
    case TOKED = 1;
    case MUTUAL_TOKE = 2;
    case MATCH = 3;
    case FRIENDSHIP = 4;
    
    public function isAtLeastMatch(): bool
    {
        return $this->value >= self::MATCH->value;
    }
    
    public function isFriendship(): bool
    {
        return $this === self::FRIENDSHIP;
    }
    
    public function canChat(): bool
    {
        return $this->isAtLeastMatch();
    }
}