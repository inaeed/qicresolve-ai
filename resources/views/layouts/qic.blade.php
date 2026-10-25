<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $qicTitle ?? 'Workspace' }} · QICResolve</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
    /* Warna sidebar senada dengan dashboard Master. */
    .qic-app .qic-sidebar {
        background: linear-gradient(
            165deg,
            #0b263b 0%,
            #071827 45%,
            #040c19 100%
        );
        color: #edf9ff;
        border-right: 1px solid rgba(71, 221, 255, 0.22);
        box-shadow: 8px 0 30px rgba(4, 12, 25, 0.18);
        scrollbar-width: thin;
        scrollbar-color: #28566d #071827;
    }

    .qic-app .qic-sidebar .qic-brand {
        color: #edf9ff;
        border-bottom-color: rgba(71, 221, 255, 0.16);
    }

    .qic-app .qic-sidebar .qic-brand-mark {
        background: linear-gradient(145deg, #153e57, #081b2d);
        color: #86eaff;
        border: 1px solid rgba(104, 228, 255, 0.45);
        box-shadow: 0 0 20px rgba(32, 197, 255, 0.16);
        text-shadow: 0 0 12px rgba(104, 228, 255, 0.55);
    }

    .qic-app .qic-sidebar .qic-brand small {
        color: #83adc1;
    }

    .qic-app .qic-sidebar .qic-role-label {
        color: #edf9ff;
        border-color: rgba(71, 221, 255, 0.16);
    }

    .qic-app .qic-sidebar .qic-role-label small {
        color: #a6bfce;
    }

    .qic-app .qic-sidebar nav a {
        color: #bfd4e0;
        background: transparent;
        border: 1px solid transparent;
        border-radius: 12px;
        transition:
            background 180ms ease,
            border-color 180ms ease,
            color 180ms ease,
            box-shadow 180ms ease;
    }

    .qic-app .qic-sidebar nav a:hover {
        color: #f0fcff;
        background: rgba(56, 189, 248, 0.09);
        border-color: rgba(104, 228, 255, 0.2);
    }

    .qic-app .qic-sidebar nav a[aria-current="page"] {
        color: #baf4ff;
        background: linear-gradient(
            110deg,
            rgba(16, 103, 139, 0.55),
            rgba(13, 57, 81, 0.75)
        );
        border-color: rgba(104, 228, 255, 0.55);
        box-shadow:
            inset 3px 0 0 #68e4ff,
            0 0 20px rgba(32, 197, 255, 0.12);
    }

    .qic-app .qic-sidebar .qic-nav-dot {
        background: transparent;
        border: 1px solid #719aaf;
    }

    .qic-app .qic-sidebar
    a[aria-current="page"] .qic-nav-dot {
        background: #68e4ff;
        border-color: #68e4ff;
        box-shadow: 0 0 10px rgba(104, 228, 255, 0.7);
    }

    .qic-app .qic-sidebar .qic-sidebar-end {
        border-top-color: rgba(71, 221, 255, 0.16);
    }

    .qic-app .qic-sidebar .qic-sidebar-end a,
    .qic-app .qic-sidebar .qic-sidebar-end button {
        color: #a6bfce;
    }

    .qic-app .qic-sidebar .qic-sidebar-end a:hover,
    .qic-app .qic-sidebar .qic-sidebar-end button:hover {
        color: #86eaff;
    }

    .qic-app .qic-sidebar a:focus-visible,
    .qic-app .qic-sidebar button:focus-visible {
        outline: 2px solid #86eaff;
        outline-offset: 3px;
    }

    @media (prefers-reduced-motion: reduce) {
        .qic-app .qic-sidebar nav a {
            transition: none;
        }
    }

    /* Sidebar ringkas: berlaku untuk seluruh role. */
.qic-app .qic-sidebar {
    height: 100vh;
    height: 100dvh;
    padding: 16px 14px;
    display: flex;
    flex-direction: column;
    gap: 0;
    overflow-y: auto;
}

.qic-app .qic-sidebar .qic-brand {
    flex-shrink: 0;
    margin: 0 0 12px;
    padding: 0 0 12px;
    gap: 10px;
    font-size: 20px;
}

.qic-app .qic-sidebar .qic-brand-mark {
    width: 36px;
    height: 36px;
    font-size: 24px;
}

.qic-app .qic-sidebar .qic-brand small {
    margin-top: 3px;
    font-size: 9px;
    line-height: 1.4;
}

.qic-app .qic-sidebar .qic-role-label {
    flex-shrink: 0;
    margin: 0 0 10px;
    padding: 0 0 10px;
    font-size: 15px;
    line-height: 1.4;
}

.qic-app .qic-sidebar .qic-role-label small {
    margin-top: 3px;
    font-size: 11px;
    line-height: 1.4;
}

.qic-app .qic-sidebar nav {
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
    gap: 3px;
    margin: 0;
    padding: 0;
}

.qic-app .qic-sidebar nav a {
    display: flex;
    align-items: center;
    gap: 10px;
    min-height: 36px;
    margin: 0;
    padding: 8px 11px;
    font-size: 12px;
    line-height: 1.4;
    border-radius: 9px;
}

.qic-app .qic-sidebar .qic-nav-dot {
    width: 6px;
    height: 6px;
    flex-shrink: 0;
}

.qic-app .qic-sidebar .qic-sidebar-end {
    flex-shrink: 0;
    margin-top: auto;
    padding-top: 12px;
}

.qic-app .qic-sidebar .qic-sidebar-end a,
.qic-app .qic-sidebar .qic-sidebar-end button {
    display: block;
    margin: 0;
    padding: 7px 10px;
    font-size: 12px;
    line-height: 1.4;
}

/* Tambahan penyesuaian untuk layar laptop yang pendek. */
@media (max-height: 740px) and (min-width: 769px) {
    .qic-app .qic-sidebar {
        padding-top: 12px;
        padding-bottom: 12px;
    }

    .qic-app .qic-sidebar .qic-brand,
    .qic-app .qic-sidebar .qic-role-label {
        margin-bottom: 8px;
        padding-bottom: 8px;
    }

    .qic-app .qic-sidebar nav {
        gap: 2px;
    }

    .qic-app .qic-sidebar nav a {
        min-height: 32px;
        padding-top: 6px;
        padding-bottom: 6px;
    }
}

/* Area sentuh menu tetap nyaman di HP. */
@media (pointer: coarse) {
    .qic-app .qic-sidebar nav a,
    .qic-app .qic-sidebar .qic-sidebar-end a,
    .qic-app .qic-sidebar .qic-sidebar-end button {
        min-height: 44px;
    }
}

/* Identitas role: terpisah dari logo dan daftar menu. */
.qic-app .qic-sidebar .qic-role-label {
    position: relative;
    isolation: isolate;
    overflow: hidden;
    flex-shrink: 0;

    margin: 8px 0 18px;
    padding: 20px 10px;
    text-align: center;

    font-size: 17px;
    font-weight: 700;
    line-height: 1.4;
    color: #edfaff;

    border: 1px solid rgba(104, 228, 255, 0.16);
    border-radius: 14px;
    background: radial-gradient(
        ellipse at center,
        rgba(27, 137, 174, 0.18),
        rgba(7, 24, 39, 0.3) 75%
    );
}

.qic-app .qic-sidebar .qic-role-label small {
    display: block;
    margin-top: 6px;
    color: #a6c9da;
    font-size: 11px;
    font-weight: 400;
    line-height: 1.5;
}

/* Titik cahaya menyebar di belakang tulisan. */
.qic-app .qic-sidebar .qic-role-label::before,
.qic-app .qic-sidebar .qic-role-label::after {
    content: "";
    position: absolute;
    z-index: -1;
    inset: 0;
    pointer-events: none;

    background:
        radial-gradient(circle at 15% 30%, #8cefff 0 1px, transparent 3px),
        radial-gradient(circle at 80% 25%, #68e4ff 0 1px, transparent 3px),
        radial-gradient(circle at 25% 80%, #68e4ff 0 1px, transparent 3px),
        radial-gradient(circle at 88% 72%, #a5f4ff 0 1px, transparent 3px),
        radial-gradient(circle at 50% 50%, #38cfff22, transparent 65%);

    animation: qic-role-bloom 5s ease-in-out infinite;
}

.qic-app .qic-sidebar .qic-role-label::after {
    transform: rotate(180deg);
    animation-delay: -2.5s;
}

@keyframes qic-role-bloom {
    0%, 100% {
        opacity: 0.25;
        scale: 0.85;
    }

    50% {
        opacity: 0.85;
        scale: 1.12;
    }
}

/* Ruang lebih hemat pada layar pendek. */
@media (max-height: 740px) {
    .qic-app .qic-sidebar .qic-role-label {
        margin: 5px 0 12px;
        padding: 14px 8px;
        font-size: 16px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .qic-app .qic-sidebar .qic-role-label::before,
    .qic-app .qic-sidebar .qic-role-label::after {
        animation: none;
        opacity: 0.5;
    }
}
</style>
{{-- Tema bersama dimuat setelah gaya halaman. --}}
<style>
/* Tema bersama QICResolve — navy–teal. */
:root{--qic-navy:#12384b;--qic-teal:#008da6;--qic-gradient:linear-gradient(100deg,#12384b,#008da6);--qic-gradient-light:linear-gradient(100deg,#c7f2f0,#65d8dd)}
.qic-app{--q-primary:#08758a;--q-ink:#081d3b}
.qic-app .qic-button:not(.qic-secondary){background:var(--qic-gradient);border-color:#08758a;color:white!important}
.qic-app .qic-button:not(.qic-secondary):hover{background:linear-gradient(100deg,#0b2c3c,#00798e)}
.qic-app .qic-secondary{color:#08758a!important;border-color:#08758a}
.qic-app .qic-secondary:hover{background:#edf8f8}
.qic-app .qic-badge.info{color:#08677a;background:#e0f3f5}
.qic-app .qic-timeline li:before{background:var(--qic-gradient)}
.qic-app progress{accent-color:#08758a}
.qic-app progress::-webkit-progress-value{background:var(--qic-gradient)}
.qic-app progress::-moz-progress-bar{background:var(--qic-gradient)}
/* Gradasi hanya untuk teks; warna SVG tetap memakai currentColor yang terbaca. */
@supports(background-clip:text){
.qic-app #workspace :is(h1,h2,h3),
.qic-app #workspace a:not(.qic-button):not(.qic-badge),
.qic-app :is(.qic-demo strong,.qic-link-button,.qic-workflow strong),
.ql-card h1,.ql-brand span,.ql-link,.ql-home,
.lp :is(.lp-accent,.lp-brand span,.lp-number,.lp-title,.lp-roles-heading h2,.lp-role-card h3,.lp-feature h3){
color:#08758a!important;background-image:var(--qic-gradient)!important;background-clip:text!important;-webkit-background-clip:text!important;-webkit-text-fill-color:transparent!important}
/* Variante claire sur les surfaces sombres. */
.qic-app .qic-sidebar :is(.qic-brand-mark,.qic-role-label),
.master-page :is(.master-brand span,.master-intro h1 span,.master-enter,.master-eyebrow,.master-hub p),
.ql-visual .ql-eyebrow,.lp-panel h2 span{
color:#8bdcde!important;background-image:var(--qic-gradient-light)!important;background-clip:text!important;-webkit-background-clip:text!important;-webkit-text-fill-color:transparent!important}
}
.qic-app .qic-sidebar nav a[aria-current]{background:linear-gradient(100deg,#12384bcc,#008da655)!important;color:#d7f7f5!important;border-color:#3bc3ca}
.ql-submit{background:var(--qic-gradient)!important;border-color:#08758a!important}
.ql-lock,.ql-input>svg,.ql-toggle{color:#426e7c!important}
.ql-link,.ql-home{color:#08758a}
.lp-btn:not(.secondary){background:var(--qic-gradient)!important;border-color:#08758a!important}
.lp-btn.secondary{color:#08758a!important;border-color:#3a95a5!important}
.lp-icon{color:#126477!important}
.lp-panel h2{color:#f5fbfc}
.lp-metric>svg{color:#83d6dc}
.lp-panel-label{color:#a4d8dd}
.lp .lp-site-footer .lp-brand span{color:#8bdce0!important;background-image:none!important;-webkit-text-fill-color:#8bdce0!important}
.master-page .master-card small{color:#9ed7d8!important}
.master-page .master-link{color:#addcdb!important}
@media(forced-colors:active){[class]{-webkit-text-fill-color:currentColor!important;background-image:none!important}}

</style>
<style>
/* Nama merek pada sidebar gelap: navy–teal dengan pangkal yang tetap terbaca. */
.qic-app .qic-sidebar .qic-brand-name{display:inline-block;color:#6bd6dc;background-image:linear-gradient(100deg,#85afc0 0%,#44c1ca 60%,#08a4b8 100%);background-clip:text;-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.qic-app .qic-badge{background-image:none!important;-webkit-text-fill-color:currentColor!important}
.qic-app :is(.qic-badge.warning,.qic-badge-warning){color:#854d0e;background:#fef3c7;border:1px solid #fde68a}
.qic-app :is(.qic-badge.success,.qic-badge-success){color:#166534;background:#dcfce7;border:1px solid #bbf7d0}
.qic-app :is(.qic-badge.danger,.qic-badge-danger){color:#991b1b;background:#fee2e2;border:1px solid #fecaca}
.qic-app :is(.qic-badge.info,.qic-badge-info){color:#075985;background:#e0f2fe;border:1px solid #bae6fd}
.qic-app :is(.qic-badge.neutral,.qic-badge-neutral){color:#475569;background:#f1f5f9;border:1px solid #cbd5e1}
</style>
<style>.qic-server-evidence{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,240px),1fr));gap:20px}.qic-server-file{padding:16px;border:1px solid #d6e6ec;border-radius:10px;min-width:0}.qic-server-file strong{display:block;margin-top:12px}</style>
</head>
<body class="qic-app">
@php
    $definition = config('qic_ui.'.$qicRole);
    $links = [];
    foreach (config('qic_ui') as $roleKey => $roleDefinition) {
        foreach ($roleDefinition['pages'] as $pageKey => $pageDefinition) {
            $links[$roleKey][$pageKey] = route($roleKey.'.'.$pageKey, str_contains($pageDefinition['path'], '{issue}') ? ['issue' => '__ISSUE__'] : []);
        }
    }
    $boot = [
        'role' => $qicRole, 'page' => $qicPage ?? 'dashboard', 'screen' => $qicScreen ?? 'dashboard',
        'issue' => request()->route('issue'), 'links' => $links,
        'user' => ['id' => (string) auth()->id(), 'name' => auth()->user()->name, 'email' => auth()->user()->email],
        'accountUrl' => route('profile.edit'),
    ];
@endphp
<a href="#workspace" class="qic-skip">Lewati navigasi</a>
<aside id="qic-sidebar" class="qic-sidebar" aria-label="Navigasi {{ $definition['label'] }}">
    <a class="qic-brand" href="{{ route($qicRole.'.dashboard') }}"><span class="qic-brand-mark">Q</span><span><span class="qic-brand-name">QICResolve</span><small>QUALITY ISSUE WORKSPACE</small></span></a>
    <div class="qic-role-label">{{ $definition['label'] }}<small>{{ $definition['subtitle'] }}</small></div>
    <nav>
        @foreach($definition['pages'] as $key => $page)
            @if($page['nav'])
                <a href="{{ route($qicRole.'.'.$key) }}" @if(($qicPage ?? '') === $key) aria-current="page" @endif><span class="qic-nav-dot" aria-hidden="true"></span>{{ $page['title'] }}</a>
            @endif
        @endforeach
    </nav>
    <div class="qic-sidebar-end"><a href="{{ route('dashboard') }}? view=workspaces">Ganti workspace demo</a><form action="{{ route('logout') }}" method="POST">@csrf<button type="submit">Keluar akun</button></form></div>
</aside>
<div class="qic-shell">
    <header class="qic-topbar"><button type="button" class="qic-button qic-secondary qic-menu" data-menu aria-controls="qic-sidebar" aria-expanded="false">Menu</button><div><span class="qic-muted qic-small">{{ $definition['label'] }}</span><strong>{{ $qicTitle ?? 'Workspace' }}</strong></div><a class="qic-user" href="{{ route($qicRole.'.profile') }}">{{ auth()->user()->name }}</a></header>
    <main id="workspace" tabindex="-1">
        <div class="qic-demo"><strong>Database aktif</strong><span>Issue dan evidence tersimpan di server. Metadata impor ditandai sebagai simulasi.</span></div>
        <div class="qic-page-heading"><div><span class="qic-eyebrow">QICRESOLVE / {{ $definition['label'] }}</span><h1>{{ $qicTitle ?? 'Workspace' }}</h1></div><div id="qic-page-actions"></div></div>
        <p id="qic-storage-warning" class="qic-warning" role="status" hidden></p>
        <div id="qic-toast" class="qic-toast" role="status" tabindex="-1" hidden></div>
        @yield('workspace')
    </main>
</div>
<script type="application/json" id="qic-boot">{!! json_encode($boot, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
</body>
</html>
