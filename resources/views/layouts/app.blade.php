<!DOCTYPE html>
<html class="dark" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', config('app.name','SMM Elite'))</title>
<script src="https://cdn.tailwindcss.com/3.4.17?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&family=Space+Grotesk:wght@600&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#0b1326">
<script>
tailwind.config = {
  darkMode: "class",
  theme: {
    extend: {
      colors: {
        "background":               "#0b1326",
        "surface":                  "#0b1326",
        "surface-container":        "#171f33",
        "surface-container-low":    "#131b2e",
        "surface-container-high":   "#222a3d",
        "surface-container-highest":"#2d3449",
        "surface-variant":          "#2d3449",
        "surface-bright":           "#31394d",
        "on-background":            "#dae2fd",
        "on-surface":               "#dae2fd",
        "on-surface-variant":       "#c2c6d6",
        "primary":                  "#adc6ff",
        "primary-container":        "#4d8eff",
        "on-primary":               "#002e6a",
        "secondary":                "#d0bcff",
        "secondary-container":      "#571bc1",
        "tertiary":                 "#4edea3",
        "tertiary-container":       "#00a572",
        "error":                    "#ffb4ab",
        "error-container":          "#93000a",
        "outline":                  "#8c909f",
        "outline-variant":          "#424754",
      },
      fontFamily: {
        "inter":       ["Inter","sans-serif"],
        "body-md":     ["Inter"],
        "body-sm":     ["Inter"],
        "body-lg":     ["Inter"],
        "h1":          ["Inter"],
        "h2":          ["Inter"],
        "h3":          ["Inter"],
        "label-caps":  ["Space Grotesk"],
      },
      fontSize: {
        "body-lg":    ["18px",{lineHeight:"1.6",fontWeight:"400"}],
        "body-md":    ["16px",{lineHeight:"1.6",fontWeight:"400"}],
        "body-sm":    ["14px",{lineHeight:"1.5",fontWeight:"400"}],
        "h1":         ["48px",{lineHeight:"1.2",letterSpacing:"-0.02em",fontWeight:"700"}],
        "h2":         ["32px",{lineHeight:"1.3",letterSpacing:"-0.01em",fontWeight:"600"}],
        "h3":         ["24px",{lineHeight:"1.4",fontWeight:"600"}],
        "label-caps": ["12px",{lineHeight:"1",letterSpacing:"0.1em",fontWeight:"600"}],
      },
      spacing: {
        "xs":"0.5rem","sm":"1rem","md":"1.5rem","lg":"2rem","xl":"3rem",
        "gutter":"24px","base":"4px","container-max":"1440px",
      },
      borderRadius: { DEFAULT:"0.25rem","lg":"0.5rem","xl":"0.75rem","full":"9999px" },
    }
  }
}
</script>
<style>
.glass-panel{background-color:rgba(11,19,38,0.4);backdrop-filter:blur(12px);border:1px solid rgba(140,144,159,0.2)}
.glass-card{background-color:rgba(23,31,51,0.5);backdrop-filter:blur(16px);border:1px solid rgba(173,198,255,0.1);box-shadow:0 8px 32px 0 rgba(0,0,0,0.3)}
.neon-glow-primary{box-shadow:0 0 15px rgba(173,198,255,0.2),inset 0 1px 0 rgba(255,255,255,0.1)}
.neon-text-primary{text-shadow:0 0 8px rgba(173,198,255,0.5)}
.bg-gradient-primary{background:linear-gradient(135deg,#4d8eff 0%,#adc6ff 100%)}
.glass-input{background:transparent;border:none;border-bottom:1px solid rgba(173,198,255,0.3);color:#dae2fd}
.glass-input:focus{outline:none;border-bottom:1px solid #adc6ff;box-shadow:0 4px 15px -3px rgba(173,198,255,0.3)}
.nav-active{background:rgba(59,130,246,0.1);color:#93c5fd;border-right:4px solid #3b82f6}
#toast-container{position:fixed;top:1rem;right:1rem;z-index:9999;display:flex;flex-direction:column;gap:8px;pointer-events:none}
.toast{background:rgba(23,31,51,0.95);border:1px solid rgba(173,198,255,0.2);border-radius:10px;padding:10px 16px;font-size:13px;color:#dae2fd;display:flex;align-items:center;gap:10px;pointer-events:all;opacity:0;transition:opacity 0.25s;min-width:240px;backdrop-filter:blur(12px)}
.toast.show{opacity:1}
@keyframes fadeUp{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
.fade-up{animation:fadeUp 0.3s ease forwards}
</style>
@yield('css')
</head>
<body class="bg-background text-on-background font-body-md antialiased min-h-screen flex flex-col md:flex-row relative overflow-x-hidden">

{{-- Ambient background lighting --}}
<div class="fixed inset-0 z-0 pointer-events-none opacity-30">
    <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] rounded-full bg-primary-container blur-[150px]"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[30%] h-[30%] rounded-full bg-secondary-container blur-[120px]"></div>
</div>

{{-- Mobile overlay --}}
<div id="sidebar-overlay" class="fixed inset-0 z-40 bg-black/60 hidden md:hidden" onclick="closeSidebar()"></div>

{{-- Sidebar Desktop + Mobile --}}
<nav id="sidebar" class="hidden md:flex flex-col fixed left-0 top-0 h-full py-6 w-64 border-r border-white/5 bg-slate-950/80 backdrop-blur-md z-50 transition-transform duration-300">
    <div class="px-6 mb-8">
        <div class="flex items-center gap-3">
            <span class="text-2xl font-black italic text-blue-500">{{ config('app.name','SMM Elite') }}</span>
        </div>
        <p class="text-xs text-slate-500 mt-1 uppercase tracking-widest font-label-caps">Elite Control</p>
    </div>

    <div class="flex flex-col gap-1 mt-4 flex-grow font-inter text-sm font-medium px-3">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-4 px-4 py-3 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-blue-500/10 text-blue-400' : 'text-slate-500 hover:text-slate-300 hover:bg-white/5' }} transition-all hover:translate-x-1">
            <span class="material-symbols-outlined text-[20px]" {{ request()->routeIs('dashboard') ? "style=font-variation-settings:'FILL'_1" : "" }}>dashboard</span> Dashboard
        </a>
        <a href="{{ route('orders.create') }}" class="flex items-center gap-4 px-4 py-3 rounded-lg {{ request()->routeIs('orders.create') ? 'bg-blue-500/10 text-blue-400' : 'text-slate-500 hover:text-slate-300 hover:bg-white/5' }} transition-all hover:translate-x-1">
            <span class="material-symbols-outlined text-[20px]">add_circle</span> New Order
        </a>
        <a href="{{ route('orders.index') }}" class="flex items-center gap-4 px-4 py-3 rounded-lg {{ request()->routeIs('orders.index') ? 'bg-blue-500/10 text-blue-400' : 'text-slate-500 hover:text-slate-300 hover:bg-white/5' }} transition-all hover:translate-x-1">
            <span class="material-symbols-outlined text-[20px]">shopping_cart</span> Orders
        </a>
        <a href="{{ route('services.index') }}" class="flex items-center gap-4 px-4 py-3 rounded-lg {{ request()->routeIs('services.*') ? 'bg-blue-500/10 text-blue-400' : 'text-slate-500 hover:text-slate-300 hover:bg-white/5' }} transition-all hover:translate-x-1">
            <span class="material-symbols-outlined text-[20px]">list_alt</span> Services
        </a>
        <a href="{{ route('analytics.index') }}" class="flex items-center gap-4 px-4 py-3 rounded-lg {{ request()->routeIs('analytics.*') ? 'bg-blue-500/10 text-blue-400' : 'text-slate-500 hover:text-slate-300 hover:bg-white/5' }} transition-all hover:translate-x-1">
            <span class="material-symbols-outlined text-[20px]">analytics</span> Analytics
        </a>
        <a href="{{ route('referral.index') }}" class="flex items-center gap-4 px-4 py-3 rounded-lg {{ request()->routeIs('referral.*') ? 'bg-blue-500/10 text-blue-400' : 'text-slate-500 hover:text-slate-300 hover:bg-white/5' }} transition-all hover:translate-x-1">
            <span class="material-symbols-outlined text-[20px]">group_add</span> Referrals
        </a>
        <a href="{{ route('funds.index') }}" class="flex items-center gap-4 px-4 py-3 rounded-lg {{ request()->routeIs('funds.*') ? 'bg-blue-500/10 text-blue-400' : 'text-slate-500 hover:text-slate-300 hover:bg-white/5' }} transition-all hover:translate-x-1">
            <span class="material-symbols-outlined text-[20px]">account_balance_wallet</span> Add Funds
        </a>
        <a href="{{ route('tickets.index') }}" class="flex items-center gap-4 px-4 py-3 rounded-lg {{ request()->routeIs('tickets.*') ? 'bg-blue-500/10 text-blue-400' : 'text-slate-500 hover:text-slate-300 hover:bg-white/5' }} transition-all hover:translate-x-1">
            <span class="material-symbols-outlined text-[20px]">help_outline</span> Support
        </a>

        @if(auth()->user()->is_admin ?? false)
        <div class="mt-4 pt-4 border-t border-white/5">
            <p class="text-[10px] text-slate-600 uppercase tracking-widest px-4 mb-2 font-label-caps">Admin</p>
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-4 px-4 py-3 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-purple-500/10 text-purple-400' : 'text-slate-500 hover:text-slate-300 hover:bg-white/5' }} transition-all hover:translate-x-1">
                <span class="material-symbols-outlined text-[20px]">admin_panel_settings</span> Command Center
            </a>
            <a href="{{ route('admin.fund_accounts.index') }}" class="flex items-center gap-4 px-4 py-3 rounded-lg {{ request()->routeIs('admin.fund_accounts.*') ? 'bg-purple-500/10 text-purple-400' : 'text-slate-500 hover:text-slate-300 hover:bg-white/5' }} transition-all hover:translate-x-1">
                <span class="material-symbols-outlined text-[20px]">account_balance</span> Payment Accounts
            </a>
            <a href="{{ route('admin.fund-requests.index') }}" class="flex items-center gap-4 px-4 py-3 rounded-lg {{ request()->routeIs('admin.fund-requests.*') ? 'bg-purple-500/10 text-purple-400' : 'text-slate-500 hover:text-slate-300 hover:bg-white/5' }} transition-all hover:translate-x-1">
                <span class="material-symbols-outlined text-[20px]">payments</span> Fund Requests
            </a>
        </div>
        @endif
    </div>

    {{-- Balance widget --}}
    <div class="px-4 mt-auto">
        <div class="glass-card rounded-xl p-3 mb-3">
            <p class="text-[10px] text-outline uppercase tracking-widest font-label-caps mb-1">Wallet</p>
            <p class="text-lg font-bold text-on-surface neon-text-primary">${{ number_format(auth()->user()->funds ?? 0, 2) }}</p>
            <p class="text-xs text-outline">₨{{ number_format((auth()->user()->funds ?? 0) * session('usd_pkr_rate', 280), 0) }}</p>
            <a href="{{ route('funds.index') }}" class="mt-2 w-full text-center text-xs bg-gradient-primary text-white py-1.5 rounded-lg block font-semibold hover:brightness-110 transition-all">+ Add Funds</a>
        </div>
        <div class="flex items-center gap-3 px-1">
            <div class="w-8 h-8 rounded-full bg-gradient-primary flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-on-surface truncate">{{ auth()->user()->name ?? 'User' }}</p>
                <p class="text-xs text-outline truncate">{{ auth()->user()->email ?? '' }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-slate-500 hover:text-red-400 transition-colors p-1" title="Logout">
                    <span class="material-symbols-outlined text-[18px]">logout</span>
                </button>
            </form>
        </div>
    </div>
</nav>

{{-- Main content --}}
<main class="flex-1 md:ml-64 relative z-10 flex flex-col min-h-screen">

    {{-- Top header --}}
    <header class="flex justify-between items-center px-6 h-14 w-full sticky top-0 z-40 bg-slate-950/60 backdrop-blur-2xl border-b border-blue-500/20 shadow-[0_0_15px_rgba(59,130,246,0.1)] transition-all duration-300">
        <div class="flex items-center gap-4">
            <button class="md:hidden text-slate-400 hover:text-white" onclick="toggleSidebar()">
                <span class="material-symbols-outlined">menu</span>
            </button>
            <h1 class="text-lg font-semibold text-white font-inter hidden md:block">@yield('page-title','Dashboard')</h1>
        </div>
        <div class="flex items-center gap-3">
            {{-- Live PKR rate --}}
            <div class="hidden sm:flex items-center gap-1.5 text-xs text-outline bg-surface-container rounded-lg px-3 py-1.5 border border-outline-variant/30">
                <span class="text-tertiary font-bold">₨</span>{{ number_format(session('usd_pkr_rate', 280), 1) }}/$
            </div>
            <a href="{{ route('tickets.index') }}" class="text-slate-400 hover:text-blue-300 transition-colors hover:bg-white/5 p-2 rounded-full">
                <span class="material-symbols-outlined">notifications</span>
            </a>
            <a href="{{ route('funds.index') }}" class="text-slate-400 hover:text-blue-300 transition-colors hover:bg-white/5 p-2 rounded-full">
                <span class="material-symbols-outlined">account_balance_wallet</span>
            </a>
            <div class="w-8 h-8 rounded-full bg-gradient-primary flex items-center justify-center text-white font-bold text-sm border border-blue-500/30">
                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
            </div>
        </div>
    </header>

    {{-- Flash messages --}}
    @if(session('success') || session('error') || $errors->any())
    <div class="px-6 pt-4">
        @if(session('success'))
        <div class="flex items-center gap-3 bg-tertiary/10 border border-tertiary/30 rounded-xl px-4 py-3 text-tertiary text-sm mb-3">
            <span class="material-symbols-outlined text-[18px]">check_circle</span> {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="flex items-center gap-3 bg-error/10 border border-error/30 rounded-xl px-4 py-3 text-error text-sm mb-3">
            <span class="material-symbols-outlined text-[18px]">error</span> {{ session('error') }}
        </div>
        @endif
        @if($errors->any())
        <div class="bg-error/10 border border-error/30 rounded-xl px-4 py-3 text-error text-sm mb-3">
            @foreach($errors->all() as $e)
            <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[14px]">cancel</span> {{ $e }}</div>
            @endforeach
        </div>
        @endif
    </div>
    @endif

    {{-- Page content --}}
    <div class="p-6 md:p-gutter flex-1 w-full max-w-container-max mx-auto">
        @yield('content')
    </div>

    {{-- Footer --}}
    <footer class="w-full py-6 px-4 flex flex-col items-center gap-3 bg-slate-950 border-t border-white/5 mt-auto z-10">
        <div class="flex gap-4 font-inter text-xs text-slate-600">
            <a class="hover:text-white transition-colors" href="#">Terms</a>
            <a class="hover:text-white transition-colors" href="#">Privacy</a>
            <a class="hover:text-white transition-colors" href="#">Status</a>
            <a class="hover:text-white transition-colors" href="{{ route('tickets.create') }}">Support</a>
        </div>
        <p class="text-xs font-inter text-slate-600">© {{ date('Y') }} {{ config('app.name','SMM Elite') }}. All rights reserved.</p>
    </footer>
</main>

{{-- Mobile bottom nav --}}
<nav class="fixed bottom-0 w-full z-50 flex justify-around items-center h-16 px-4 md:hidden bg-slate-900/90 backdrop-blur-lg border-t border-blue-500/30 rounded-t-2xl shadow-[0_-5px_20px_rgba(59,130,246,0.2)]">
    <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center {{ request()->routeIs('dashboard') ? 'text-blue-400 drop-shadow-[0_0_8px_rgba(59,130,246,0.8)]' : 'text-slate-500' }} p-2 rounded-lg">
        <span class="material-symbols-outlined" {{ request()->routeIs('dashboard') ? "style=font-variation-settings:'FILL'_1" : "" }}>home</span>
        <span class="text-[10px] uppercase font-bold mt-0.5 font-label-caps">Home</span>
    </a>
    <a href="{{ route('orders.create') }}" class="flex flex-col items-center justify-center {{ request()->routeIs('orders.create') ? 'text-blue-400' : 'text-slate-500' }} p-2 rounded-lg">
        <span class="material-symbols-outlined">add_circle</span>
        <span class="text-[10px] uppercase font-bold mt-0.5 font-label-caps">Order</span>
    </a>
    <a href="{{ route('orders.index') }}" class="flex flex-col items-center justify-center {{ request()->routeIs('orders.index') ? 'text-blue-400' : 'text-slate-500' }} p-2 rounded-lg">
        <span class="material-symbols-outlined">history</span>
        <span class="text-[10px] uppercase font-bold mt-0.5 font-label-caps">Orders</span>
    </a>
    <a href="{{ route('funds.index') }}" class="flex flex-col items-center justify-center {{ request()->routeIs('funds.*') ? 'text-blue-400' : 'text-slate-500' }} p-2 rounded-lg">
        <span class="material-symbols-outlined">payments</span>
        <span class="text-[10px] uppercase font-bold mt-0.5 font-label-caps">Wallet</span>
    </a>
    <button onclick="toggleSidebar()" class="flex flex-col items-center justify-center text-slate-500 p-2 rounded-lg">
        <span class="material-symbols-outlined">menu</span>
        <span class="text-[10px] uppercase font-bold mt-0.5 font-label-caps">Menu</span>
    </button>
</nav>

{{-- Toast container --}}
<div id="toast-container"></div>

<script>
function toggleSidebar(){
    const s=document.getElementById('sidebar');
    const o=document.getElementById('sidebar-overlay');
    s.classList.toggle('hidden');
    s.classList.toggle('flex');
    o.classList.toggle('hidden');
}
function closeSidebar(){
    document.getElementById('sidebar').classList.add('hidden');
    document.getElementById('sidebar').classList.remove('flex');
    document.getElementById('sidebar-overlay').classList.add('hidden');
}
function showToast(msg,type='info'){
    const c={success:'#4edea3',danger:'#ffb4ab',info:'#adc6ff',warning:'#fcd34d'};
    const i={success:'check_circle',danger:'cancel',info:'info',warning:'warning'};
    const t=document.createElement('div');
    t.className='toast';
    t.style.borderLeft=`3px solid ${c[type]}`;
    t.innerHTML=`<span class="material-symbols-outlined" style="color:${c[type]};font-size:18px;flex-shrink:0">${i[type]}</span><span>${msg}</span>`;
    document.getElementById('toast-container').appendChild(t);
    requestAnimationFrame(()=>t.classList.add('show'));
    setTimeout(()=>{t.classList.remove('show');setTimeout(()=>t.remove(),300);},4000);
}
@if(session('success')) showToast("{{ addslashes(session('success')) }}",'success'); @endif
@if(session('error'))   showToast("{{ addslashes(session('error')) }}",'danger');  @endif
</script>
@yield('scripts')
</body>
</html>
