<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\User;
use App\Services\ApiKeyService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
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

        if (! $user || ! Hash::check($credentials['password'], $user->getAuthPassword())) {
            if ($user) {
                $user->recordFailedLogin();
            }

            return back()->withErrors(['email' => 'Credenciales inválidas'])->withInput();
        }

        if ($user->isLocked()) {
            return back()->withErrors(['email' => 'Tu cuenta está bloqueada temporalmente por demasiados intentos fallidos. Intentá de nuevo en unos minutos.'])->withInput();
        }

        if (! $user->isAdmin()) {
            return back()->withErrors(['email' => 'No tenés permisos de administrador'])->withInput();
        }

        if ($user->status !== 'active') {
            return back()->withErrors(['email' => 'Tu cuenta está deshabilitada'])->withInput();
        }

        $user->resetFailedLoginAttempts();

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

        $result = $this->apiKeyService->createApiKey(
            Auth::user(),
            $validated['name'],
            $validated['type'],
            $validated['expires_in_days'] ?? null,
        );

        return redirect()->route('admin.keys.show-created')
            ->with('new_key', $result['raw_key'])
            ->with('new_key_name', $validated['name'])
            ->with('new_key_type', $validated['type']);
    }

    public function showCreated(): View
    {
        $rawKey = session('new_key');

        if (! $rawKey) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.key-created', [
            'rawKey' => $rawKey,
            'keyName' => session('new_key_name'),
            'keyType' => session('new_key_type'),
        ]);
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

    public function showForgotPassword(): View
    {
        return view('admin.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! $user->isAdmin()) {
            return back()->with('status', 'Si el email existe en el sistema, recibirás un enlace para restablecer tu contraseña.');
        }

        $token = app(PasswordBroker::class)->createToken($user);

        $resetUrl = route('admin.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);

        return back()->with('status', 'Si el email existe en el sistema, recibirás un enlace para restablecer tu contraseña.');
    }

    public function showResetPassword(Request $request): View
    {
        abort_unless($request->filled('token') && $request->filled('email'), 404);

        return view('admin.reset-password', [
            'token' => $request->token,
            'email' => $request->email,
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password_hash' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('admin.login')->with('status', 'Contraseña restablecida. Iniciá sesión.')
            : back()->withErrors(['email' => __($status)]);
    }
}
