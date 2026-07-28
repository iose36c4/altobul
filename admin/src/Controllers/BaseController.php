<?php

declare(strict_types=1);

namespace App\Http\Controllers;

class BaseController
{
    protected function getClient(): \ApiClient
    {
        $config = require __DIR__ . '/../../config.php';

        return new \ApiClient($config['backend_url'], $config['api_key']);
    }

    protected function render(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../../views/' . $view . '.php';
    }

    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }
}
