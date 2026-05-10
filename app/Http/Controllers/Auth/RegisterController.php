<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Show the registration form.
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Handle a registration request.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255|min:2',
            'email'    => 'required|email|unique:users,email|max:255',
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
        ]);

        // Handle referral code if provided
        $referrer = null;
        if ($request->filled('referral_code')) {
            $referrer = User::where('referral_code', $request->referral_code)->first();
        }

        // Create the user
        $user = User::create([
            'name'        => $validated['name'],
            // FIX: Using native PHP function to avoid "Str class not found" error
            'email'       => strtolower(trim($validated['email'])),
            'password'    => Hash::make($validated['password']),
            'referred_by' => $referrer?->id,
        ]);

        // Initialize user defaults
        $user->funds  = 0;
        $user->status = 'active';
        $user->save();

        // Log successful registration
        Log::info('New user registered', [
            'user_id'     => $user->id,
            'referred_by' => $referrer?->id,
            'ip'          => $request->ip(),
        ]);

        // Log the user in
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('success', 'Welcome! Your account has been created.');
    }
}
