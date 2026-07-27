<?php

namespace App\Domain\Authorization;

use Illuminate\Auth\Access\AuthorizationException;

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

    public function throwIfDenied(): void
    {
        if (! $this->allowed) {
            throw new AuthorizationException(
                $this->detail ?? 'This action is unauthorized.',
                $this->reason ? $this->reason->value : 'FORBIDDEN'
            );
        }
    }
}
