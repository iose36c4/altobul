<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.admin-login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $baseUrl = rtrim(config('admin.api_base_url'), '/');
        $apiKey = config('admin.api_key');

        $response = Http::timeout(10)
            ->withHeaders([
                'X-API-Key' => $apiKey,
                'Accept' => 'application/json',
            ])
            ->post($baseUrl.'/api/admin/auth/login', [
                'email' => $request->email,
                'password' => $request->password,
            ]);

        if (! $response->ok()) {
            throw ValidationException::withMessages([
                'email' => 'Las credenciales no son correctas.',
            ]);
        }

        $data = $response->json();

        if (! isset($data['user']) || ($data['user']['role'] ?? '') !== 'admin') {
            throw ValidationException::withMessages([
                'email' => 'No tienes permisos de administrador.',
            ]);
        }

        $user = User::updateOrCreate(
            ['email' => $data['user']['email']],
            [
                'name' => $data['user']['name'] ?? $data['user']['email'],
                'role' => $data['user']['role'] ?? 'admin',
                'status' => $data['user']['status'] ?? 'active',
                'api_token' => $data['token'] ?? null,
                'backend_id' => $data['user']['id'] ?? null,
            ]
        );

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $baseUrl = rtrim(config('admin.api_base_url'), '/');
        $apiKey = config('admin.api_key');

        if ($user = Auth::user()) {
            Http::timeout(5)
                ->withHeaders([
                    'X-API-Key' => $apiKey,
                    'Authorization' => 'Bearer '.$user->api_token,
                    'Accept' => 'application/json',
                ])
                ->post($baseUrl.'/api/admin/auth/logout');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
