<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $qicTitle ?? 'Workspace' }} · QICResolve</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
    <a class="qic-brand" href="{{ route($qicRole.'.dashboard') }}"><span class="qic-brand-mark">Q</span><span>QICResolve<small>QUALITY ISSUE WORKSPACE</small></span></a>
    <div class="qic-role-label">{{ $definition['label'] }}<small>{{ $definition['subtitle'] }}</small></div>
    <nav>
        @foreach($definition['pages'] as $key => $page)
            @if($page['nav'])
                <a href="{{ route($qicRole.'.'.$key) }}" @if(($qicPage ?? '') === $key) aria-current="page" @endif><span class="qic-nav-dot" aria-hidden="true"></span>{{ $page['title'] }}</a>
            @endif
        @endforeach
    </nav>
    <div class="qic-sidebar-end"><a href="{{ route('dashboard') }}">Ganti workspace demo</a><form action="{{ route('logout') }}" method="POST">@csrf<button type="submit">Keluar akun</button></form></div>
</aside>
<div class="qic-shell">
    <header class="qic-topbar"><button type="button" class="qic-button qic-secondary qic-menu" data-menu aria-controls="qic-sidebar" aria-expanded="false">Menu</button><div><span class="qic-muted qic-small">{{ $definition['label'] }}</span><strong>{{ $qicTitle ?? 'Workspace' }}</strong></div><a class="qic-user" href="{{ route($qicRole.'.profile') }}">{{ auth()->user()->name }}</a></header>
    <main id="workspace" tabindex="-1">
        <div class="qic-demo"><strong>Mode demonstrasi</strong><span>Data contoh disimpan sementara pada tab browser. Foto, SLA, dan AI belum terhubung ke server.</span><button type="button" data-reset class="qic-link-button">Reset demo</button></div>
        <div class="qic-page-heading"><div><span class="qic-eyebrow">QICRESOLVE / {{ $definition['label'] }}</span><h1>{{ $qicTitle ?? 'Workspace' }}</h1></div><div id="qic-page-actions"></div></div>
        <p id="qic-storage-warning" class="qic-warning" role="status" hidden></p>
        <div id="qic-toast" class="qic-toast" role="status" tabindex="-1" hidden></div>
        @yield('workspace')
    </main>
</div>
<script type="application/json" id="qic-boot">{!! json_encode($boot, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
</body>
</html>
