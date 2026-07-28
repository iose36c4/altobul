<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\User;
use App\Services\ApiKeyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminPanelController extends Controller
{
    public function __construct(
        private ApiKeyService $apiKeyService,
    ) {}

    public function showLogin(): View
    {
        return view('admin.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Auth::getProvider()->retrieveByCredentials($credentials)) {
            return back()->withErrors(['email' => 'Credenciales inválidas'])->withInput();
        }

        if (! $user->isAdmin()) {
            return back()->withErrors(['email' => 'No tenés permisos de administrador'])->withInput();
        }

        if ($user->status !== 'active') {
            return back()->withErrors(['email' => 'Tu cuenta está deshabilitada'])->withInput();
        }

        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    public function dashboard(): View
    {
        $user = Auth::user();
        $keys = ApiKey::where('created_by', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.dashboard', compact('keys'));
    }

    public function createKey(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:CLIENT,ADMIN'],
            'expires_in_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
        ]);

        $this->apiKeyService->createApiKey(
            Auth::user(),
            $validated['name'],
            $validated['type'],
            $validated['expires_in_days'] ?? null,
        );

        return redirect()->route('admin.dashboard')->with('success', 'Clave API creada. No olvides copiarla.');
    }

    public function createKeyShow(): View
    {
        return view('admin.create-key');
    }

    public function destroyKey(ApiKey $apiKey): RedirectResponse
    {
        if ($apiKey->created_by !== Auth::id()) {
            abort(403);
        }

        $this->apiKeyService->revokeApiKey($apiKey);

        return redirect()->route('admin.dashboard')->with('success', 'Clave revocada.');
    }
}
