<!DOCTYPE html>
<html class="dark" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Register — {{ config('app.name','SMM Elite') }}</title>
<script src="https://cdn.tailwindcss.com/3.4.17?plugins=forms"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&family=Space+Grotesk:wght@600&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<style>
.glass-card{background:rgba(23,31,51,0.6);backdrop-filter:blur(20px);border:1px solid rgba(173,198,255,0.1);box-shadow:0 8px 32px rgba(0,0,0,0.4)}
.glass-input{background:rgba(255,255,255,0.04);border:1px solid rgba(173,198,255,0.15);color:#dae2fd;border-radius:12px;padding:12px 16px;width:100%;font-size:14px;transition:all 0.2s;outline:none;font-family:'Inter',sans-serif}
.glass-input:focus{border-color:#adc6ff;box-shadow:0 0 0 3px rgba(173,198,255,0.1)}
.glass-input::placeholder{color:#8c909f}
@keyframes fadeUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
.fade-up{animation:fadeUp 0.4s ease forwards}
</style>
</head>
<body style="background:#0b1326;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1rem;font-family:'Inter',sans-serif;">

<div style="position:fixed;inset:0;pointer-events:none;overflow:hidden;">
    <div style="position:absolute;top:-15%;right:-10%;width:45%;height:45%;border-radius:50%;background:radial-gradient(circle,rgba(87,27,193,0.12),transparent 70%);filter:blur(40px)"></div>
    <div style="position:absolute;bottom:-10%;left:-10%;width:35%;height:35%;border-radius:50%;background:radial-gradient(circle,rgba(77,142,255,0.1),transparent 70%);filter:blur(40px)"></div>
    <div style="position:absolute;inset:0;background-image:linear-gradient(rgba(173,198,255,0.03) 1px,transparent 1px),linear-gradient(90deg,rgba(173,198,255,0.03) 1px,transparent 1px);background-size:40px 40px;"></div>
</div>

<div style="width:100%;max-width:440px;position:relative;z-index:1;" class="fade-up">

    <div style="text-align:center;margin-bottom:2rem;">
        <div style="display:inline-flex;align-items:center;justify-content:center;width:52px;height:52px;border-radius:14px;background:linear-gradient(135deg,#4d8eff,#adc6ff);margin-bottom:14px;box-shadow:0 0 20px rgba(173,198,255,0.3);">
            <span style="font-family:'Inter',sans-serif;font-weight:900;font-size:22px;color:#002e6a;font-style:italic;">S</span>
        </div>
        <h1 style="font-size:28px;font-weight:900;color:#dae2fd;letter-spacing:-0.02em;font-style:italic;">Create Account</h1>
        <p style="font-size:12px;color:#8c909f;margin-top:4px;text-transform:uppercase;letter-spacing:.1em;font-family:'Space Grotesk',sans-serif;">Start growing today — it's free</p>
    </div>

    <div class="glass-card" style="border-radius:20px;padding:2rem;">

        @if($errors->any())
        <div style="background:rgba(255,180,171,0.1);border:1px solid rgba(255,180,171,0.25);border-radius:10px;padding:10px 14px;margin-bottom:16px;font-size:13px;color:#ffb4ab;">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf
            @if(request('ref'))
            <input type="hidden" name="referral_code" value="{{ request('ref') }}">
            @endif

            @if(request('ref'))
            <div style="background:rgba(78,222,163,0.1);border:1px solid rgba(78,222,163,0.2);border-radius:10px;padding:10px 14px;margin-bottom:16px;font-size:13px;color:#4edea3;display:flex;align-items:center;gap:8px;">
                <span class="material-symbols-outlined" style="font-size:16px;">card_giftcard</span>
                You were referred! You'll both get bonuses.
            </div>
            @endif

            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:11px;font-weight:600;color:#8c909f;text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px;font-family:'Space Grotesk',sans-serif;">Full Name</label>
                <input type="text" name="name" class="glass-input" value="{{ old('name') }}" placeholder="Your name" required autofocus>
            </div>
            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:11px;font-weight:600;color:#8c909f;text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px;font-family:'Space Grotesk',sans-serif;">Email Address</label>
                <input type="email" name="email" class="glass-input" value="{{ old('email') }}" placeholder="you@example.com" required>
            </div>
            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:11px;font-weight:600;color:#8c909f;text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px;font-family:'Space Grotesk',sans-serif;">Password</label>
                <input type="password" name="password" class="glass-input" placeholder="Minimum 8 characters" required>
            </div>
            <div style="margin-bottom:24px;">
                <label style="display:block;font-size:11px;font-weight:600;color:#8c909f;text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px;font-family:'Space Grotesk',sans-serif;">Confirm Password</label>
                <input type="password" name="password_confirmation" class="glass-input" placeholder="Repeat password" required>
            </div>

            <button type="submit" style="width:100%;background:linear-gradient(135deg,#4d8eff,#adc6ff);color:#002e6a;border:none;border-radius:12px;padding:14px;font-size:14px;font-weight:700;cursor:pointer;font-family:'Inter',sans-serif;display:flex;align-items:center;justify-content:center;gap:8px;box-shadow:0 0 20px rgba(173,198,255,0.25);transition:all 0.2s;" onmouseover="this.style.filter='brightness(1.1)'" onmouseout="this.style.filter='brightness(1)'">
                <span class="material-symbols-outlined" style="font-size:18px;">person_add</span> Create Account
            </button>
        </form>

        <div style="text-align:center;margin-top:20px;font-size:13px;color:#8c909f;">
            Already have an account?
            <a href="{{ route('login') }}" style="color:#adc6ff;font-weight:600;text-decoration:none;"> Sign in →</a>
        </div>
    </div>
</div>
</body>
</html>
