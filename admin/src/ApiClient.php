<?php

declare(strict_types=1);

class ApiClient
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct(string $baseUrl, string $apiKey)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
    }

    public function fromSession(): ?self
    {
        $baseUrl = $_SESSION['config']['backend_url'] ?? '';
        $apiKey = $_SESSION['config']['api_key'] ?? '';

        if ($baseUrl === '' || $apiKey === '') {
            return null;
        }

        return new self($baseUrl, $apiKey);
    }

    public function get(string $endpoint): array
    {
        return $this->request('GET', $endpoint);
    }

    public function post(string $endpoint, array $data = []): array
    {
        return $this->request('POST', $endpoint, $data);
    }

    public function put(string $endpoint, array $data = []): array
    {
        return $this->request('PUT', $endpoint, $data);
    }

    public function delete(string $endpoint): array
    {
        return $this->request('DELETE', $endpoint);
    }

    private function request(string $method, string $endpoint, array $data = []): array
    {
        $url = $this->baseUrl . '/api/admin' . $endpoint;

        $ch = curl_init();

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'X-API-Key: ' . $this->apiKey,
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_TIMEOUT => 30,
        ];

        switch ($method) {
            case 'POST':
                $options[CURLOPT_POST] = true;
                if ($data !== []) {
                    $options[CURLOPT_POSTFIELDS] = json_encode($data);
                }
                break;
            case 'PUT':
                $options[CURLOPT_CUSTOMREQUEST] = 'PUT';
                if ($data !== []) {
                    $options[CURLOPT_POSTFIELDS] = json_encode($data);
                }
                break;
            case 'DELETE':
                $options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
                break;
        }

        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return ['error' => 'Error de conexión: ' . $error, 'status' => 0];
        }

        $body = json_decode($response, true);

        return [
            'status' => $httpCode,
            'data' => $body ?? [],
        ];
    }

    public function testConnection(): array
    {
        $url = $this->baseUrl . '/api/admin/api-keys';

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'X-API-Key: ' . $this->apiKey,
                'Accept: application/json',
            ],
            CURLOPT_TIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return ['success' => false, 'message' => 'Error de conexión: ' . $error];
        }

        if ($httpCode === 200) {
            return ['success' => true, 'message' => 'Conexión exitosa'];
        }

        return ['success' => false, 'message' => 'Clave API inválida (HTTP ' . $httpCode . ')'];
    }
}
