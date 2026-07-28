<?php

declare(strict_types=1);

class InstallerMiddleware
{
    public function handle(): bool
    {
        if (! file_exists(__DIR__ . '/../../config.php')) {
            return true;
        }

        return false;
    }
}
