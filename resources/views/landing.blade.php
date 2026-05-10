<!DOCTYPE html>
<html class="dark" lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>{{ config('app.name','SMM Elite') }} — #1 SMM Panel Pakistan</title>
<script src="https://cdn.tailwindcss.com/3.4.17?plugins=forms"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&family=Space+Grotesk:wght@600&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<style>
body { background:#0b1326; color:#dae2fd; font-family:'Inter',sans-serif; overflow-x:hidden; }
.glass { background:rgba(23,31,51,0.5); backdrop-filter:blur(16px); border:1px solid rgba(173,198,255,0.1); }
.gradient-text { background:linear-gradient(135deg,#adc6ff,#4edea3); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
.btn-primary { background:linear-gradient(135deg,#4d8eff,#adc6ff); color:#002e6a; font-weight:700; border-radius:12px; padding:14px 32px; font-size:16px; border:none; cursor:pointer; transition:all 0.2s; box-shadow:0 0 30px rgba(173,198,255,0.25); display:inline-flex; align-items:center; gap:8px; text-decoration:none; }
.btn-primary:hover { filter:brightness(1.1); transform:translateY(-2px); }
.btn-ghost { background:transparent; border:1px solid rgba(173,198,255,0.2); color:#dae2fd; font-weight:600; border-radius:12px; padding:13px 28px; font-size:15px; cursor:pointer; transition:all 0.2s; display:inline-flex; align-items:center; gap:8px; text-decoration:none; }
.btn-ghost:hover { border-color:#adc6ff; background:rgba(173,198,255,0.05); }
.feature-card { background:rgba(23,31,51,0.4); border:1px solid rgba(173,198,255,0.08); border-radius:16px; padding:28px; transition:all 0.3s; }
.feature-card:hover { border-color:rgba(173,198,255,0.25); transform:translateY(-4px); box-shadow:0 20px 40px rgba(0,0,0,0.3); }
@keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-12px)} }
.float { animation:float 4s ease-in-out infinite; }
@keyframes fadeUp { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
.fade-up { animation:fadeUp 0.5s ease forwards; }
.counter { font-variant-numeric:tabular-nums; }
</style>
</head>
<body>

{{-- Background --}}
<div style="position:fixed;inset:0;z-index:0;pointer-events:none;">
    <div style="position:absolute;top:-20%;left:-10%;width:50%;height:50%;border-radius:50%;background:radial-gradient(circle,rgba(77,142,255,0.1),transparent 70%);filter:blur(60px)"></div>
    <div style="position:absolute;bottom:-20%;right:-10%;width:40%;height:40%;border-radius:50%;background:radial-gradient(circle,rgba(87,27,193,0.08),transparent 70%);filter:blur(60px)"></div>
    <div style="position:absolute;inset:0;background-image:linear-gradient(rgba(173,198,255,0.025) 1px,transparent 1px),linear-gradient(90deg,rgba(173,198,255,0.025) 1px,transparent 1px);background-size:40px 40px;"></div>
</div>

{{-- Nav --}}
<nav style="position:sticky;top:0;z-index:50;backdrop-filter:blur(20px);border-bottom:1px solid rgba(173,198,255,0.1);background:rgba(11,19,38,0.8);">
    <div style="max-width:1200px;margin:0 auto;padding:0 24px;height:64px;display:flex;align-items:center;justify-content:space-between;">
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#4d8eff,#adc6ff);display:flex;align-items:center;justify-content:center;font-weight:900;font-size:16px;color:#002e6a;font-style:italic;">S</div>
            <span style="font-weight:900;font-size:18px;font-style:italic;color:#dae2fd;">{{ config('app.name','SMM Elite') }}</span>
        </div>
        <div style="display:flex;align-items:center;gap:12px;">
            <a href="{{ route('login') }}" class="btn-ghost" style="padding:9px 20px;font-size:14px;">Sign In</a>
            <a href="{{ route('register') }}" class="btn-primary" style="padding:9px 20px;font-size:14px;">Get Started</a>
        </div>
    </div>
</nav>

{{-- Hero --}}
<section style="max-width:1200px;margin:0 auto;padding:100px 24px 80px;text-align:center;position:relative;z-index:1;">
    <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(173,198,255,0.08);border:1px solid rgba(173,198,255,0.2);border-radius:99px;padding:6px 16px;font-size:12px;color:#adc6ff;font-weight:600;margin-bottom:24px;letter-spacing:.06em;text-transform:uppercase;" class="fade-up">
        <span class="material-symbols-outlined" style="font-size:14px;font-variation-settings:'FILL' 1">bolt</span>
        Pakistan's #1 SMM Panel — Instant Delivery
    </div>
    <h1 style="font-size:clamp(40px,7vw,80px);font-weight:900;line-height:1.1;letter-spacing:-0.03em;margin-bottom:24px;" class="fade-up">
        Grow Your Social<br><span class="gradient-text">Media Presence</span>
    </h1>
    <p style="font-size:18px;color:#8c909f;max-width:560px;margin:0 auto 40px;line-height:1.6;" class="fade-up">
        The most affordable SMM panel in Pakistan. Followers, likes, views and more — with live PKR pricing, EasyPaisa &amp; JazzCash support.
    </p>
    <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;" class="fade-up">
        <a href="{{ route('register') }}" class="btn-primary" style="font-size:16px;">
            <span class="material-symbols-outlined" style="font-size:20px;">rocket_launch</span>
            Start Free Now
        </a>
        <a href="{{ route('services.index') }}" class="btn-ghost" style="font-size:16px;">
            <span class="material-symbols-outlined" style="font-size:20px;">list_alt</span>
            Browse Services
        </a>
    </div>

    {{-- Live counter --}}
    <div style="display:flex;justify-content:center;gap:40px;flex-wrap:wrap;margin-top:60px;">
        @foreach([['500K+','Orders Delivered'],['99.8%','Success Rate'],['₨280','Per $1 USD'],['24/7','Support']] as [$num,$label])
        <div style="text-align:center;">
            <p style="font-size:32px;font-weight:900;color:#dae2fd;letter-spacing:-0.02em;" class="counter">{{ $num }}</p>
            <p style="font-size:12px;color:#8c909f;text-transform:uppercase;letter-spacing:.08em;font-family:'Space Grotesk',sans-serif;font-weight:600;margin-top:4px;">{{ $label }}</p>
        </div>
        @endforeach
    </div>
</section>

{{-- Features --}}
<section style="max-width:1200px;margin:0 auto;padding:60px 24px;position:relative;z-index:1;">
    <h2 style="text-align:center;font-size:36px;font-weight:900;margin-bottom:12px;letter-spacing:-0.02em;">Why Choose Us</h2>
    <p style="text-align:center;color:#8c909f;margin-bottom:50px;font-size:16px;">Features no other Pakistani panel offers</p>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:20px;">
        @foreach([
            ['bolt','Instant Delivery','Orders start within seconds. No waiting, no delays.','#adc6ff'],
            ['currency_rupee','PKR Pricing','See all prices in rupees with live exchange rate updated daily.','#4edea3'],
            ['smartphone','EasyPaisa & JazzCash','Pay directly from your Pakistani mobile wallet — no card needed.','#d0bcff'],
            ['autorenew','Auto Order Sync','Your order status updates automatically — no manual refreshing.','#fcd34d'],
            ['group_add','Referral Program','Earn 5% on every deposit your referrals make — forever.','#f9a8d4'],
            ['verified_user','Secure & Private','Your data is encrypted. We never share your information.','#6ee7b7'],
        ] as [$icon,$title,$desc,$color])
        <div class="feature-card">
            <div style="width:44px;height:44px;border-radius:12px;background:{{ $color }}1a;border:1px solid {{ $color }}40;display:flex;align-items:center;justify-content:center;margin-bottom:16px;">
                <span class="material-symbols-outlined" style="color:{{ $color }};font-size:22px;font-variation-settings:'FILL' 1">{{ $icon }}</span>
            </div>
            <h3 style="font-weight:700;font-size:16px;margin-bottom:8px;color:#dae2fd;">{{ $title }}</h3>
            <p style="color:#8c909f;font-size:14px;line-height:1.6;">{{ $desc }}</p>
        </div>
        @endforeach
    </div>
</section>

{{-- Platforms --}}
<section style="max-width:1200px;margin:0 auto;padding:40px 24px 80px;position:relative;z-index:1;">
    <h2 style="text-align:center;font-size:32px;font-weight:900;margin-bottom:40px;letter-spacing:-0.02em;">All Major Platforms</h2>
    <div style="display:flex;justify-content:center;flex-wrap:wrap;gap:16px;">
        @foreach([
            ['Instagram','fab fa-instagram','linear-gradient(135deg,#833ab4,#fd1d1d,#fcb045)'],
            ['TikTok','fab fa-tiktok','linear-gradient(135deg,#010101,#69C9D0)'],
            ['YouTube','fab fa-youtube','#FF0000'],
            ['Facebook','fab fa-facebook-f','#1877F2'],
            ['Twitter','fab fa-twitter','#1DA1F2'],
            ['Telegram','fab fa-telegram','#0088cc'],
            ['Spotify','fab fa-spotify','#1DB954'],
            ['Discord','fab fa-discord','#5865F2'],
        ] as [$name,$icon,$bg])
        <div style="display:flex;flex-direction:column;align-items:center;gap:8px;padding:16px 12px;background:rgba(23,31,51,0.4);border:1px solid rgba(173,198,255,0.08);border-radius:14px;min-width:80px;transition:all 0.2s;cursor:default;" onmouseover="this.style.borderColor='rgba(173,198,255,0.3)';this.style.transform='translateY(-3px)'" onmouseout="this.style.borderColor='rgba(173,198,255,0.08)';this.style.transform='translateY(0)'">
            <div style="width:40px;height:40px;border-radius:10px;background:{{ $bg }};display:flex;align-items:center;justify-content:center;font-size:18px;color:#fff;">
                <i class="{{ $icon }}"></i>
            </div>
            <span style="font-size:11px;color:#8c909f;font-weight:600;font-family:'Space Grotesk',sans-serif;">{{ $name }}</span>
        </div>
        @endforeach
    </div>
</section>

{{-- CTA --}}
<section style="max-width:900px;margin:0 auto 80px;padding:0 24px;position:relative;z-index:1;">
    <div class="glass" style="border-radius:24px;padding:60px 40px;text-align:center;position:relative;overflow:hidden;">
        <div style="position:absolute;top:-50%;left:-20%;width:60%;height:60%;border-radius:50%;background:radial-gradient(circle,rgba(77,142,255,0.15),transparent 70%);filter:blur(40px);pointer-events:none;"></div>
        <div style="position:relative;z-index:1;">
            <h2 style="font-size:36px;font-weight:900;letter-spacing:-0.02em;margin-bottom:12px;">Ready to grow?</h2>
            <p style="color:#8c909f;font-size:16px;margin-bottom:32px;">Join thousands of creators and businesses growing with us every day.</p>
            <a href="{{ route('register') }}" class="btn-primary" style="font-size:16px;">
                <span class="material-symbols-outlined">rocket_launch</span>
                Create Free Account
            </a>
        </div>
    </div>
</section>

{{-- Footer --}}
<footer style="border-top:1px solid rgba(173,198,255,0.08);padding:32px 24px;text-align:center;position:relative;z-index:1;">
    <p style="color:#424754;font-size:13px;">© {{ date('Y') }} {{ config('app.name','SMM Elite') }}. Built for Pakistan. All rights reserved.</p>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
