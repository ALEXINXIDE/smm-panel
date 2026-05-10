@extends('layouts.app')
@section('title', 'New Order')
@section('page-title', 'New Order Wizard')

@section('css')
<style>
/* ── Step bar ── */
.step-wrap{display:flex;align-items:center;margin-bottom:2rem}
.step-item{display:flex;flex-direction:column;align-items:center;gap:4px;min-width:56px}
.step-circle{width:36px;height:36px;border-radius:50%;border:2px solid #424754;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#8c909f;transition:all .3s}
.step-circle.active{border-color:#adc6ff;color:#adc6ff;background:rgba(173,198,255,.1);box-shadow:0 0 12px rgba(173,198,255,.25)}
.step-circle.done{border-color:#4edea3;background:#4edea3;color:#003824}
.step-label{font-size:10px;color:#8c909f;font-weight:600;letter-spacing:.08em;text-transform:uppercase}
.step-circle.active + .step-label{color:#adc6ff}
.step-line{flex:1;height:1px;background:#424754;margin-bottom:18px}
/* ── Cards ── */
.platform-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}
@media(max-width:480px){.platform-grid{grid-template-columns:repeat(3,1fr)}}
.pf-card{background:rgba(23,31,51,.6);border:1.5px solid rgba(173,198,255,.1);border-radius:14px;padding:14px 8px;display:flex;flex-direction:column;align-items:center;gap:8px;cursor:pointer;transition:all .2s}
.pf-card:hover{border-color:#adc6ff;transform:translateY(-2px)}
.pf-card.selected{border-color:#adc6ff;background:rgba(173,198,255,.09);box-shadow:0 0 16px rgba(173,198,255,.2)}
.pf-icon{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:22px;color:#fff}
.pf-name{font-size:11px;font-weight:600;color:#8c909f;text-align:center}
.pf-card.selected .pf-name{color:#adc6ff}
/* ── Service type buttons ── */
.type-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:16px}
@media(max-width:480px){.type-grid{grid-template-columns:repeat(2,1fr)}}
.type-btn{background:rgba(23,31,51,.5);border:1.5px solid rgba(173,198,255,.1);border-radius:12px;padding:14px 8px;display:flex;flex-direction:column;align-items:center;gap:6px;cursor:pointer;transition:all .2s}
.type-btn:hover{border-color:#adc6ff}
.type-btn.selected{border-color:#adc6ff;background:rgba(173,198,255,.09)}
.type-btn .ti{font-size:22px;color:#8c909f}
.type-btn.selected .ti{color:#adc6ff}
.type-btn span:last-child{font-size:11px;font-weight:600;color:#8c909f;text-align:center}
.type-btn.selected span:last-child{color:#adc6ff}
/* ── Service rows ── */
.svc-row{padding:12px 14px;border-radius:10px;border:1.5px solid rgba(173,198,255,.1);cursor:pointer;transition:all .15s;margin-bottom:6px;background:rgba(23,31,51,.3);display:block;width:100%;text-align:left}
.svc-row:hover{border-color:#adc6ff;background:rgba(173,198,255,.05)}
.svc-row.selected{border-color:#adc6ff;background:rgba(173,198,255,.09)}
/* ── Steps ── */
.step-content{display:none}.step-content.active{display:block}
/* ── Tier badges ── */
.tier-eco{background:rgba(78,222,163,.1);color:#4edea3;border:1px solid rgba(78,222,163,.3)}
.tier-std{background:rgba(173,198,255,.1);color:#adc6ff;border:1px solid rgba(173,198,255,.3)}
.tier-pre{background:rgba(224,168,255,.1);color:#e0a8ff;border:1px solid rgba(224,168,255,.3)}
</style>
@endsection

@section('content')
<div class="max-w-2xl mx-auto px-2">

{{-- Step bar --}}
<div class="step-wrap">
    @foreach(['Platform','Type','Service','Details','Confirm'] as $i => $lbl)
    <div class="step-item">
        <div class="step-circle {{ $i===0?'active':'' }}" id="sc-{{ $i+1 }}">{{ $i+1 }}</div>
        <p class="step-label">{{ $lbl }}</p>
    </div>
    @if($i<4)<div class="step-line"></div>@endif
    @endforeach
</div>

{{-- ── STEP 1: Platform ── --}}
<div class="step-content active" id="step-1">
<div class="glass-card rounded-xl p-6">
    <h2 class="text-xl font-bold text-on-surface mb-1">New Order</h2>
    <p class="text-on-surface-variant text-sm mb-5">Which platform do you want to grow?</p>
    <div class="platform-grid">
        @php
        $platforms = [
            ['Instagram','linear-gradient(135deg,#833ab4,#fd1d1d,#fcb045)','fab fa-instagram',['instagram','ig']],
            ['TikTok',   'linear-gradient(135deg,#010101,#69C9D0)',         'fab fa-tiktok',   ['tiktok','tik tok']],
            ['YouTube',  'linear-gradient(135deg,#FF0000,#cc0000)',          'fab fa-youtube',  ['youtube','yt']],
            ['Facebook', 'linear-gradient(135deg,#1877F2,#0A66C2)',          'fab fa-facebook-f',['facebook','fb']],
            ['Twitter',  'linear-gradient(135deg,#1DA1F2,#0d8bd9)',          'fab fa-twitter',  ['twitter','x.com','tweet']],
            ['Telegram', 'linear-gradient(135deg,#0088cc,#005a96)',          'fab fa-telegram', ['telegram']],
            ['Spotify',  'linear-gradient(135deg,#1DB954,#0f8c3a)',          'fab fa-spotify',  ['spotify']],
            ['Discord',  'linear-gradient(135deg,#5865F2,#3b4fd4)',          'fab fa-discord',  ['discord']],
        ];
        @endphp
        @foreach($platforms as [$name,$grad,$icon,$keywords])
        <div class="pf-card" onclick="selectPlatform('{{ $name }}',{{ json_encode($keywords) }},this)">
            <div class="pf-icon" style="background:{{ $grad }}"><i class="{{ $icon }}"></i></div>
            <span class="pf-name">{{ $name }}</span>
        </div>
        @endforeach
    </div>
</div>
</div>

{{-- ── STEP 2: Service Type ── --}}
<div class="step-content" id="step-2">
<div class="glass-card rounded-xl p-6">
    <div class="flex items-center gap-3 mb-5">
        <button onclick="goStep(1)" class="text-outline hover:text-on-surface p-1"><span class="material-symbols-outlined">arrow_back</span></button>
        <div>
            <h3 class="text-lg font-bold text-on-surface">What do you want?</h3>
            <p class="text-xs text-outline">Platform: <span id="lbl-platform" class="text-primary font-semibold"></span></p>
        </div>
    </div>
    <div class="type-grid" id="type-grid">
        {{-- Filled dynamically --}}
        <div class="text-center py-8 col-span-3 text-outline" id="type-loading">
            <span class="material-symbols-outlined animate-spin text-[28px] block mb-2">progress_activity</span>
            Loading...
        </div>
    </div>
</div>
</div>

{{-- ── STEP 3: Pick Service ── --}}
<div class="step-content" id="step-3">
<div class="glass-card rounded-xl p-6">
    <div class="flex items-center gap-3 mb-4">
        <button onclick="goStep(2)" class="text-outline hover:text-on-surface p-1"><span class="material-symbols-outlined">arrow_back</span></button>
        <div>
            <h3 class="text-lg font-bold text-on-surface">Choose a package</h3>
            <p class="text-xs text-outline"><span id="lbl-platform2" class="text-primary font-semibold"></span> · <span id="lbl-type" class="text-primary font-semibold"></span></p>
        </div>
    </div>
    <input type="text" id="svc-search" placeholder="Search packages..." oninput="filterSvc()"
        class="w-full glass-input py-2.5 px-3 mb-4 text-sm bg-transparent rounded-lg border border-outline-variant/40 focus:border-primary transition-colors">
    <div class="max-h-80 overflow-y-auto pr-1" id="svc-list">
        <div class="text-center py-8 text-outline" id="svc-loading" style="display:none">
            <span class="material-symbols-outlined animate-spin text-[28px] block mb-2">progress_activity</span>Loading...
        </div>
        <div class="text-center py-8 text-outline" id="svc-empty" style="display:none">
            <span class="material-symbols-outlined text-[36px] block mb-2 opacity-30">inventory_2</span>No packages found.
        </div>
    </div>
</div>
</div>

{{-- ── STEP 4: Details ── --}}
<div class="step-content" id="step-4">
<div class="glass-card rounded-xl p-6">
    <div class="flex items-center gap-3 mb-5">
        <button onclick="goStep(3)" class="text-outline hover:text-on-surface p-1"><span class="material-symbols-outlined">arrow_back</span></button>
        <div>
            <h3 class="text-lg font-bold text-on-surface">Order Details</h3>
            <p class="text-xs text-outline truncate max-w-xs" id="lbl-service"></p>
        </div>
    </div>
    <div class="space-y-5">
        <div>
            <label class="block text-xs font-semibold uppercase tracking-widest text-outline mb-2">Target Link *</label>
            <input type="url" id="order-link" class="w-full glass-input py-2.5 px-3 text-sm bg-transparent" placeholder="https://...">
            <p class="text-xs text-outline mt-1">Must be a public URL</p>
        </div>
        <div>
            <div class="flex justify-between items-center mb-2">
                <label class="text-xs font-semibold uppercase tracking-widest text-outline">Quantity</label>
                <input type="number" id="qty-num" class="w-24 glass-input py-1.5 px-3 text-sm text-center bg-transparent" oninput="syncSlider()">
            </div>
            <input type="range" id="qty-range" min="100" max="10000" step="100" value="1000"
                class="w-full accent-blue-400" oninput="syncNum();calcPrice()">
            <div class="flex justify-between text-xs text-outline mt-1">
                <span id="qty-min-lbl">Min: 100</span><span id="qty-max-lbl">Max: 10,000</span>
            </div>
        </div>
        {{-- Price card --}}
        <div class="bg-surface-container-low rounded-xl p-4 border border-outline-variant/30">
            <p class="text-xs font-semibold uppercase tracking-widest text-outline mb-3">Price Summary</p>
            <div class="grid grid-cols-2 gap-3">
                <div><p class="text-xs text-outline">Rate / 1K</p><p class="text-on-surface font-semibold text-sm" id="d-rate">$0.0000</p></div>
                <div><p class="text-xs text-outline">Quantity</p><p class="text-on-surface font-semibold text-sm" id="d-qty">1,000</p></div>
                <div><p class="text-xs text-outline">Total USD</p><p class="text-2xl font-bold text-primary" id="d-usd">$0.0000</p></div>
                <div><p class="text-xs text-outline">Total PKR</p><p class="text-2xl font-bold text-tertiary" id="d-pkr">₨0</p></div>
            </div>
            <div class="mt-3 pt-3 border-t border-outline-variant/30 flex justify-between text-sm">
                <span class="text-outline">Balance after</span>
                <span id="d-after" class="font-semibold text-on-surface">${{ number_format(auth()->user()->funds ?? 0,2) }}</span>
            </div>
        </div>
        <button onclick="goStep(5)" class="w-full bg-gradient-primary text-white font-semibold py-3 rounded-xl hover:brightness-110 transition-all text-sm">
            Continue → Confirm
        </button>
    </div>
</div>
</div>

{{-- ── STEP 5: Confirm ── --}}
<div class="step-content" id="step-5">
<div class="glass-card rounded-xl p-6">
    <div class="flex items-center gap-3 mb-5">
        <button onclick="goStep(4)" class="text-outline hover:text-on-surface p-1"><span class="material-symbols-outlined">arrow_back</span></button>
        <h3 class="text-lg font-bold text-on-surface">Confirm Order</h3>
    </div>
    @foreach([['Platform','r-platform'],['Type','r-type'],['Service','r-service'],['Link','r-link'],['Quantity','r-qty'],['Total (USD)','r-usd'],['Total (PKR)','r-pkr']] as [$lbl,$id])
    <div class="flex justify-between items-center py-3 border-b border-outline-variant/20">
        <span class="text-outline text-sm">{{ $lbl }}</span>
        <span id="{{ $id }}" class="text-on-surface font-medium text-sm text-right max-w-[55%] truncate">—</span>
    </div>
    @endforeach
    <div class="flex items-start gap-3 bg-yellow-500/10 border border-yellow-500/30 rounded-xl p-3 my-5 text-yellow-400 text-xs">
        <span class="material-symbols-outlined text-[16px] flex-shrink-0 mt-0.5">warning</span>
        Verify the link is correct. Orders cannot be cancelled once processing starts.
    </div>
    <form method="POST" action="{{ route('orders.store') }}" id="order-form">
        @csrf
        <input type="hidden" name="service_id" id="f-service">
        <input type="hidden" name="link" id="f-link">
        <input type="hidden" name="quantity" id="f-quantity">
        <button type="submit" onclick="return prepareSubmit()"
            class="w-full bg-gradient-primary text-white font-semibold py-3.5 rounded-xl hover:brightness-110 transition-all text-sm flex items-center justify-center gap-2">
            <span class="material-symbols-outlined text-[18px]">check_circle</span> Place Order
        </button>
    </form>
</div>
</div>

</div>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
<script>
const PKR          = {{ session('usd_pkr_rate', 280) }};
const BAL          = {{ auth()->user()->funds ?? 0 }};
const SVC_URL      = '{{ route("orders.services_by_category") }}';
const ALL_CATS     = @json($categories);

// State
let selPlatform='', selKeywords=[], selType='', selCatIds=[], selSvcId=null, selRate=0, selMin=100, selMax=10000, selName='';
let svcCache = {}; // categoryId -> services[]

// ── Service types with icons and keywords ──────────────────────────────────
const SERVICE_TYPES = [
    { label:'Followers',  icon:'group',          keywords:['follower','follow','sub'] },
    { label:'Likes',      icon:'favorite',       keywords:['like','heart','react'] },
    { label:'Views',      icon:'visibility',     keywords:['view','watch','impression','play'] },
    { label:'Comments',   icon:'chat_bubble',    keywords:['comment','reply','review'] },
    { label:'Shares',     icon:'share',          keywords:['share','repost','retweet','rt'] },
    { label:'Saves',      icon:'bookmark',       keywords:['save','bookmark','pin'] },
    { label:'Streams',    icon:'music_note',     keywords:['stream','listen','play'] },
    { label:'Members',    icon:'group_add',      keywords:['member','join','subscriber'] },
    { label:'Mentions',   icon:'alternate_email',keywords:['mention','tag'] },
    { label:'Everything', icon:'apps',           keywords:[] },
];

function selectPlatform(name, keywords, el) {
    document.querySelectorAll('.pf-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    selPlatform  = name;
    selKeywords  = keywords;
    document.getElementById('lbl-platform').textContent  = name;
    document.getElementById('lbl-platform2').textContent = name;
    buildTypeGrid(name, keywords);
    setTimeout(() => goStep(2), 200);
}

function buildTypeGrid(platform, platformKws) {
    const grid    = document.getElementById('type-grid');
    const loading = document.getElementById('type-loading');
    loading.style.display = 'none';

    // Filter categories that match this platform
    const platformStr = [platform, ...platformKws].join(' ').toLowerCase();
    const matchingCats = ALL_CATS.filter(c =>
        platformKws.some(kw => c.name.toLowerCase().includes(kw)) ||
        c.name.toLowerCase().includes(platform.toLowerCase())
    );

    // Collect all category IDs for "Everything"
    selCatIds = matchingCats.map(c => c.id);

    // Get types that exist for this platform
    const availableTypes = SERVICE_TYPES.filter(t => {
        if (t.label === 'Everything') return matchingCats.length > 0;
        return matchingCats.some(c =>
            t.keywords.some(kw => c.name.toLowerCase().includes(kw))
        );
    });

    // Remove old buttons
    grid.querySelectorAll('.type-btn').forEach(b => b.remove());

    if (!availableTypes.length) {
        grid.innerHTML = '<p class="col-span-3 text-center text-outline text-sm py-6">No services found for this platform.</p>';
        return;
    }

    availableTypes.forEach(type => {
        const btn = document.createElement('button');
        btn.className  = 'type-btn';
        btn.innerHTML  = `<span class="material-symbols-outlined ti">${type.icon}</span><span>${type.label}</span>`;
        btn.onclick    = () => selectType(type, btn, matchingCats);
        grid.appendChild(btn);
    });
}

function selectType(type, el, matchingCats) {
    document.querySelectorAll('.type-btn').forEach(b => b.classList.remove('selected'));
    el.classList.add('selected');
    selType = type.label;
    document.getElementById('lbl-type').textContent = type.label;

    // Find categories matching this type
    let targetCats;
    if (type.label === 'Everything') {
        targetCats = matchingCats;
    } else {
        targetCats = matchingCats.filter(c =>
            type.keywords.some(kw => c.name.toLowerCase().includes(kw))
        );
        if (!targetCats.length) targetCats = matchingCats; // fallback
    }

    selCatIds = targetCats.map(c => c.id);
    loadServicesForCats(targetCats, type.keywords);
    setTimeout(() => goStep(3), 200);
}

function loadServicesForCats(cats, typeKeywords) {
    const list    = document.getElementById('svc-list');
    const loading = document.getElementById('svc-loading');
    const empty   = document.getElementById('svc-empty');

    list.querySelectorAll('.svc-row').forEach(r => r.remove());
    loading.style.display = 'block';
    empty.style.display   = 'none';

    // Fetch all categories in parallel, use cache when available
    const fetches = cats.map(cat => {
        if (svcCache[cat.id]) return Promise.resolve(svcCache[cat.id]);
        return fetch(SVC_URL + '?category_id=' + cat.id, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(r => r.json()).then(svcs => { svcCache[cat.id] = svcs; return svcs; });
    });

    Promise.all(fetches).then(results => {
        loading.style.display = 'none';
        let all = results.flat();

        // Sort cheapest first
        all.sort((a,b) => parseFloat(a.rate) - parseFloat(b.rate));

        if (!all.length) { empty.style.display = 'block'; return; }

        all.forEach(svc => {
            const rate = parseFloat(svc.rate);
            const tier = rate < 0.5 ? ['Economy','tier-eco'] : rate < 2 ? ['Standard','tier-std'] : ['Premium','tier-pre'];
            const row  = document.createElement('button');
            row.className      = 'svc-row';
            row.dataset.name   = svc.name.toLowerCase();
            row.dataset.rate   = svc.rate;
            row.innerHTML = `
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="text-on-surface font-medium text-sm leading-snug">${escHtml(svc.name)}</p>
                        <p class="text-outline text-xs mt-1">
                            Min: ${Number(svc.min).toLocaleString()} &nbsp;·&nbsp; Max: ${Number(svc.max).toLocaleString()}
                            ${svc.min_time ? '&nbsp;·&nbsp; ' + svc.min_time + '–' + (svc.max_time||'?') + ' hrs' : ''}
                        </p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-primary font-bold text-sm">$${rate.toFixed(4)}</p>
                        <p class="text-outline text-[10px]">per 1K</p>
                        <span class="inline-block mt-1 px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider ${tier[1]}">${tier[0]}</span>
                    </div>
                </div>`;
            row.onclick = () => selectService(svc, row);
            list.insertBefore(row, loading);
        });
    }).catch(() => {
        loading.style.display = 'none';
        empty.style.display   = 'block';
        empty.querySelector('p').textContent = 'Failed to load. Please try again.';
    });
}

function filterSvc() {
    const q = document.getElementById('svc-search').value.toLowerCase();
    // Also sort by price when searching
    const rows = [...document.querySelectorAll('#svc-list .svc-row')];
    rows.forEach(r => r.style.display = (!q || r.dataset.name.includes(q)) ? '' : 'none');
}

function selectService(svc, el) {
    document.querySelectorAll('#svc-list .svc-row').forEach(r => r.classList.remove('selected'));
    el.classList.add('selected');
    selSvcId = svc.id; selRate = parseFloat(svc.rate);
    selMin   = parseInt(svc.min); selMax = parseInt(svc.max); selName = svc.name;
    document.getElementById('lbl-service').textContent = svc.name;
    document.getElementById('d-rate').textContent = '$' + selRate.toFixed(4);
    const sl = document.getElementById('qty-range');
    sl.min = selMin; sl.max = selMax; sl.step = Math.max(1, Math.ceil(selMin / 10));
    sl.value = selMin;
    document.getElementById('qty-num').value = selMin;
    document.getElementById('qty-min-lbl').textContent = 'Min: ' + selMin.toLocaleString();
    document.getElementById('qty-max-lbl').textContent = 'Max: ' + selMax.toLocaleString();
    calcPrice();
    setTimeout(() => goStep(4), 200);
}

function syncNum()    { document.getElementById('qty-num').value = document.getElementById('qty-range').value; }
function syncSlider() {
    let v = Math.min(Math.max(parseInt(document.getElementById('qty-num').value)||selMin, selMin), selMax);
    document.getElementById('qty-range').value = v; calcPrice();
}
function calcPrice() {
    const qty   = parseInt(document.getElementById('qty-range').value) || selMin;
    const total = (qty / 1000) * selRate;
    const after = BAL - total;
    document.getElementById('d-qty').textContent  = qty.toLocaleString();
    document.getElementById('d-usd').textContent  = '$' + total.toFixed(4);
    document.getElementById('d-pkr').textContent  = '₨' + Math.round(total * PKR).toLocaleString();
    const el = document.getElementById('d-after');
    el.textContent = '$' + Math.max(0, after).toFixed(2);
    el.className   = 'font-semibold ' + (after < 0 ? 'text-error' : 'text-on-surface');
}

function goStep(n) {
    if (n === 4 && !selSvcId) { return; }
    if (n === 5) {
        const link = document.getElementById('order-link').value.trim();
        if (!link) { document.getElementById('order-link').focus(); return; }
        const qty   = parseInt(document.getElementById('qty-range').value);
        const total = (qty / 1000) * selRate;
        document.getElementById('r-platform').textContent = selPlatform;
        document.getElementById('r-type').textContent     = selType;
        document.getElementById('r-service').textContent  = selName;
        document.getElementById('r-link').textContent     = link;
        document.getElementById('r-qty').textContent      = qty.toLocaleString();
        document.getElementById('r-usd').textContent      = '$' + total.toFixed(4);
        document.getElementById('r-pkr').textContent      = '₨' + Math.round(total * PKR).toLocaleString();
    }
    for (let i = 1; i <= 5; i++) {
        document.getElementById('step-' + i).classList.toggle('active', i === n);
        const sc = document.getElementById('sc-' + i);
        sc.className = 'step-circle' + (i === n ? ' active' : i < n ? ' done' : '');
        sc.innerHTML = i < n ? '<span class="material-symbols-outlined text-[14px]">check</span>' : i;
    }
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function prepareSubmit() {
    const link = document.getElementById('order-link').value.trim();
    if (!selSvcId || !link) return false;
    document.getElementById('f-service').value  = selSvcId;
    document.getElementById('f-link').value     = link;
    document.getElementById('f-quantity').value = document.getElementById('qty-range').value;
    return true;
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
@endsection
