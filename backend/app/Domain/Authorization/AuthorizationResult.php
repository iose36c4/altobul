<?php

namespace App\Domain\Authorization;

readonly class AuthorizationResult
{
    public function __construct(
        public bool $allowed,
        public ?AuthorizationReason $reason = null,
        public ?string $detail = null,
    ) {}
    
    public static function allowed(?AuthorizationReason $reason = null): self
    {
        return new self(true, $reason);
    }
    
    public static function denied(AuthorizationReason $reason, ?string $detail = null): self
    {
        return new self(false, $reason, $detail);
    }
}