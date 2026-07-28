<?php

declare(strict_types=1);

namespace App\Http\Controllers;

class InstallController
{
    public function show(): void
    {
        require __DIR__ . '/../../views/install/step1.php';
    }

    public function testConnection(): void
    {
        $baseUrl = trim($_POST['backend_url'] ?? '');
        $apiKey = trim($_POST['api_key'] ?? '');

        if ($baseUrl === '' || $apiKey === '') {
            echo json_encode(['success' => false, 'message' => 'Completá todos los campos']);
            exit;
        }

        $client = new \ApiClient($baseUrl, $apiKey);
        $result = $client->testConnection();

        echo json_encode($result);
        exit;
    }

    public function save(): void
    {
        $baseUrl = trim($_POST['backend_url'] ?? '');
        $apiKey = trim($_POST['api_key'] ?? '');

        if ($baseUrl === '' || $apiKey === '') {
            header('Location: /install');
            exit;
        }

        $client = new \ApiClient($baseUrl, $apiKey);
        $result = $client->testConnection();

        if (! $result['success']) {
            require __DIR__ . '/../../views/install/step1.php';
            exit;
        }

        $config = '<?php' . PHP_EOL
            . 'return [' . PHP_EOL
            . '    \'backend_url\' => ' . var_export($baseUrl, true) . ',' . PHP_EOL
            . '    \'api_key\' => ' . var_export($apiKey, true) . ',' . PHP_EOL
            . '];' . PHP_EOL;

        file_put_contents(__DIR__ . '/../../config.php', $config);

        $_SESSION['config'] = [
            'backend_url' => $baseUrl,
            'api_key' => $apiKey,
        ];

        require __DIR__ . '/../../views/install/success.php';
    }
}
