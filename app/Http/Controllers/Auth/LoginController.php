<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * LoginController
 *
 * FIXES:
 * - CRITICAL-4: Open redirect via redirect_to parameter — now validates to local paths only
 * - Added proper session regeneration
 * - Rate limiting is applied via route definition (throttle:login)
 */
class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email'    => 'required|email|max:255',
            'password' => 'required|min:8|max:128',
        ]);

        $email = Str::lower(trim($validated['email']));
        $password = $validated['password'];

        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

        if ($user?->status === 'banned') {
            Log::warning('Banned user attempted login', ['email' => $user->email, 'ip' => $request->ip()]);

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'This account has been suspended. Contact support.']);
        }

        if ($user?->isLocked()) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Your account is temporarily locked due to too many failed login attempts. Please try again later.']);
        }

        if (! $user || ! Hash::check($password, $user->password)) {
            $user?->recordFailedLogin();
            Log::warning('Failed login attempt', ['email' => $validated['email'], 'ip' => $request->ip()]);

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Invalid email or password.']);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        $user->recordSuccessfulLogin($request->ip());

        Log::info('User logged in', ['user_id' => $user->id, 'ip' => $request->ip()]);

        // FIXED: Validate redirect_to is a relative local path only — prevents open redirect
        $intended = $request->input('redirect_to', '');
        if (! $this->isSafeRedirect($intended)) {
            $intended = route('dashboard');
        }

        return redirect()->intended($intended);
    }

    public function logout(Request $request)
    {
        Log::info('User logged out', ['user_id' => Auth::id()]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Only allow same-origin relative redirects.
     * Blocks: https://evil.com, //evil.com, javascript:alert(1)
     */
    private function isSafeRedirect(string $url): bool
    {
        if (empty($url)) {
            return false;
        }
        // Must start with a single slash (relative path), no protocol or double-slash
        return (bool) preg_match('#^/(?!/)#', $url);
    }
}
