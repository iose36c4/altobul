<?php

namespace App\Domain\Authorization;

use App\Domain\Relationship\RelationshipLevel;

enum VisibilityLevel: string
{
    case PUBLIC = 'PUBLIC';
    case MATCH = 'MATCH';
    case FRIENDS = 'FRIENDS';
    case PRIVATE = 'PRIVATE';

    public function satisfies(RelationshipLevel $relationship): bool
    {
        return match ($this) {
            self::PUBLIC => true,
            self::MATCH => $relationship->isAtLeastMatch(),
            self::FRIENDS => $relationship->isFriendship(),
            self::PRIVATE => false, // Requires explicit grant
        };
    }
}
