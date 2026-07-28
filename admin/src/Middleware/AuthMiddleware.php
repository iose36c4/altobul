<?php

declare(strict_types=1);

class AuthMiddleware
{
    public function handle(): bool
    {
        if (! file_exists(__DIR__ . '/../../config.php')) {
            header('Location: /install');
            exit;
        }

        return true;
    }
}
