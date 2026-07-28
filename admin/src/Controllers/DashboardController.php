<?php

declare(strict_types=1);

namespace App\Http\Controllers;

class DashboardController extends BaseController
{
    public function loginForm(): void
    {
        $error = '';
        require __DIR__ . '/../../views/login.php';
    }

    public function login(): void
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $error = 'Completá todos los campos';
            require __DIR__ . '/../../views/login.php';
            exit;
        }

        $client = $this->getClient();

        $result = $client->post('/auth/login', [
            'email' => $email,
            'password' => $password,
            'guard' => 'admin',
        ]);

        if (($result['status'] ?? 0) === 200 && isset($result['data']['token'])) {
            $_SESSION['admin_token'] = $result['data']['token'];
            $_SESSION['admin_email'] = $email;
            header('Location: /');
            exit;
        }

        $error = $result['data']['message'] ?? 'Credenciales inválidas';
        require __DIR__ . '/../../views/login.php';
    }

    public function logout(): void
    {
        session_destroy();
        header('Location: /login');
        exit;
    }

    public function index(): void
    {
        $client = $this->getClient();

        $keys = $client->get('/api-keys');
        $users = $client->get('/users');
        $zones = $client->get('/geo-zones');
        $verifications = $client->get('/verification-requests');

        $stats = [
            'total_keys' => count($keys['data']['data'] ?? $keys['data'] ?? []),
            'total_users' => count($users['data']['data'] ?? $users['data'] ?? []),
            'total_zones' => count($zones['data']['data'] ?? $zones['data'] ?? []),
            'pending_verifications' => count($verifications['data']['data'] ?? $verifications['data'] ?? []),
        ];

        require __DIR__ . '/../../views/dashboard.php';
    }
}
