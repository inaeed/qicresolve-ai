<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="dark">
    <title>Master Workspace · QICResolve</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Seluruh gaya dibatasi pada halaman Master Workspace. */
        .master-page { margin:0; background:#040c19; color:#edf9ff; font-family:Inter,ui-sans-serif,system-ui,sans-serif; }
        .master-page * { box-sizing:border-box; }
        .master-page [hidden] { display:none!important; }
        .master-shell { min-height:100svh; position:relative; overflow:hidden; background:radial-gradient(ellipse at 50% 48%,#063b59 0, #081c30 37%,#040c19 76%); }
        .master-shell::before { content:""; position:absolute; inset:0; pointer-events:none; opacity:.23; background-image:linear-gradient(#2787ab22 1px,transparent 1px),linear-gradient(90deg,#2787ab22 1px,transparent 1px); background-size:48px 48px; mask-image:radial-gradient(ellipse,#000,transparent 78%); }
        .master-nav { position:relative; z-index:2; max-width:1440px; margin:auto; padding:26px 5%; display:flex; align-items:center; justify-content:space-between; gap:20px; }
        .master-brand { color:#f4fbff; text-decoration:none; font-size:21px; font-weight:800; letter-spacing:-.7px; }
        .master-brand span { color:#39d7ff; }
        .master-nav form { margin:0; }
        .master-button { display:inline-flex; align-items:center; justify-content:center; gap:18px; border:1px solid #51dfff77; border-radius:10px; background:#0c263a; color:#e8faff; padding:12px 20px; font:inherit; font-size:14px; cursor:pointer; text-decoration:none; transition:background .2s,transform .2s; }
        .master-button:hover { background:#144159; transform:translateY(-2px); }
        .master-button:focus-visible,.master-card:focus-visible,.master-link:focus-visible { outline:3px solid #a3efff; outline-offset:5px; }
        .master-primary { background:#68e4ff; color:#032231; border-color:#68e4ff; font-weight:750; box-shadow:0 0 32px #20c5ff33; }
        .master-primary:hover { background:#a3efff; }
        .master-stage { position:relative; z-index:1; max-width:1300px; margin:auto; padding:24px 5% 40px; }
        .master-intro { min-height:calc(100svh - 150px); display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; }
        .master-eyebrow { color:#71d7f0; text-transform:uppercase; font-size:11px; letter-spacing:3px; font-weight:700; margin:0 0 16px; }
        .master-intro h1 { font-size:clamp(27px,4vw,48px); letter-spacing:-1.6px; line-height:1.2; margin:26px 0 12px; }
        .master-intro h1 span { display:block; color:#74e2ff; margin-top:8px; }
        .master-copy { color:#a6bfce; font-size:15px; line-height:1.8; max-width:530px; margin:0 auto 28px; }
        .master-core { width:160px; height:180px; position:relative; display:grid; place-items:center; flex-shrink:0; isolation:isolate; }
        .master-core::before,.master-core::after { content:""; position:absolute; inset:0; clip-path:polygon(50% 0,94% 25%,94% 75%,50% 100%,6% 75%,6% 25%); background:#47ddff; }
        .master-core::before { filter:drop-shadow(0 0 18px #31d7ff); animation:master-pulse 3s ease-in-out infinite; }
        .master-core::after { inset:3px; background:linear-gradient(145deg,#11435d,#061b2d 65%); }
        .master-core strong { position:relative; z-index:1; font-size:32px; letter-spacing:3px; color:#baf4ff; text-shadow:0 0 24px #36d6ff; }
        .master-orbit { position:absolute; inset:-32px; border:1px solid #36d6ff33; border-radius:50%; animation:master-spin 25s linear infinite; }
        .master-orbit::after { content:""; position:absolute; top:28px; left:24px; width:7px; height:7px; border-radius:50%; background:#83edff; box-shadow:0 0 18px #4fddff; }
        .master-circuits { position:absolute; inset:-100px -170px; z-index:-1; pointer-events:none; }
        .master-circuits i { position:absolute; top:50%; left:50%; height:2px; width:230px; transform-origin:left; transform:rotate(calc(var(--i)*60deg)); background:linear-gradient(90deg,#43dfffbb,transparent); }
        .master-circuits i::after { content:""; position:absolute; width:30px; height:2px; background:#b5f7ff; box-shadow:0 0 12px #25cdff; animation:master-flow 2.8s linear infinite; animation-delay:calc(var(--i)*-.4s); }
        .master-step { color:#6e94a9; font-size:11px; letter-spacing:2px; margin-top:28px; }
        .master-heading { text-align:center; margin:12px 0 20px; }
        .master-heading h1 { font-size:clamp(26px,3.3vw,40px); margin:0 0 10px; letter-spacing:-1px; }
        .master-heading p { color:#a6bfce; margin:0; line-height:1.7; }
        .master-network { display:grid; grid-template-columns:1fr 190px 1fr; align-items:center; gap:28px; min-height:390px; position:relative; }
        .master-network::before { content:""; position:absolute; top:50%; left:15%; right:15%; height:1px; background:#36cfff66; box-shadow:0 0 12px #25cdff; }
        .master-hub { grid-column:2; grid-row:1 / span 2; justify-self:center; text-align:center; z-index:1; }
        .master-hub .master-core { width:145px; height:165px; }
        .master-hub p { color:#73cee8; font-size:10px; letter-spacing:2px; margin-top:26px; }
        .master-card { position:relative; display:block; color:inherit; text-decoration:none; padding:25px; border:1px solid #3da7c85c; border-radius:16px; background:linear-gradient(135deg,#103047ee,#071827f5); box-shadow:0 14px 40px #0003; transition:transform .25s,border-color .25s,box-shadow .25s; }
        .master-card:hover { transform:translateY(-5px); border-color:#73e4ff; box-shadow:0 0 30px #22c6ff22; }
        .master-card small { display:block; color:#76cce3; font-size:10px; letter-spacing:2px; text-transform:uppercase; }
        .master-card h2 { font-size:25px; margin:12px 0 10px; }
        .master-card p { color:#afc4d2; line-height:1.7; font-size:14px; margin:0 0 18px; }
        .master-card .master-enter { color:#87eaff; font-size:13px; font-weight:700; }
        .master-card .master-number { position:absolute; right:22px; top:20px; color:#4c8199; font-size:13px; }
        .master-card-main-assy { grid-column:1; grid-row:1 / span 2; }
        .master-card-auto-line { grid-column:3; grid-row:1; }
        .master-card-quality { grid-column:3; grid-row:2; }
        .master-footer { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; padding-top:24px; border-top:1px solid #4382a033; }
        .master-link { color:#9bc5d8; font-size:13px; }
        .master-note { color:#88a5b6; font-size:12px; line-height:1.7; max-width:650px; margin:18px auto 0; text-align:center; }
        .master-panel { animation:master-reveal .6s ease both; }
        @keyframes master-pulse { 50% { opacity:.55; } }
        @keyframes master-spin { to { transform:rotate(360deg); } }
        @keyframes master-flow { from { left:100%; opacity:0; } 30% { opacity:1; } to { left:0; opacity:0; } }
        @keyframes master-reveal { from { opacity:0; transform:translateY(14px); } to { opacity:1; transform:translateY(0); } }
        @media (max-width:700px) and (orientation:portrait) {
            .master-nav { padding:20px 6%; }
            .master-brand { font-size:18px; }
            .master-nav .master-button { font-size:12px; padding:10px 13px; }
            .master-stage { padding:24px 6% 32px; }
            .master-intro { min-height:calc(100svh - 140px); }
            .master-core { width:130px; height:150px; }
            .master-intro h1 { letter-spacing:-.8px; }
            .master-network { grid-template-columns:1fr; gap:18px; min-height:0; }
            .master-network::before { display:none; }
            .master-hub { grid-column:1; grid-row:auto; margin:24px 0 16px; }
            .master-hub .master-core { width:95px; height:110px; }
            .master-hub .master-core strong { font-size:24px; }
            .master-card-main-assy,.master-card-auto-line,.master-card-quality { grid-column:1; grid-row:auto; }
            .master-card { padding:22px; }
            .master-card h2 { font-size:23px; }
            .master-footer { margin-top:24px; }
        }
        @media (max-height:560px) and (orientation:landscape) {
            .master-nav { padding:14px 5%; }
            .master-intro { min-height:0; padding:20px 0; }
            .master-intro .master-core { width:85px; height:95px; }
            .master-intro h1 { font-size:28px; margin-top:16px; }
            .master-copy { margin-bottom:14px; }
            .master-network { grid-template-columns:1fr 100px 1fr; gap:14px; min-height:0; }
            .master-hub .master-core { width:85px; height:100px; }
            .master-card { padding:16px; }
            .master-card h2 { font-size:21px; }
            .master-card p { font-size:12px; }
        }
        @media (prefers-reduced-motion:reduce) {
            .master-page *, .master-page *::before, .master-page *::after { animation:none!important; transition:none!important; }
        }
        /* Dashboard Master lebih ringkas pada layar landscape. */
@media (min-width: 701px) and (min-height: 500px) {
    .master-shell {
        min-height: 100dvh;
    }

    .master-nav {
        padding: 14px 5%;
    }

    .master-stage {
        padding: 8px 5% 16px;
    }

    .master-intro {
        min-height: calc(100dvh - 100px);
        padding: 10px 0;
    }

    .master-intro > .master-eyebrow {
        margin-bottom: 12px;
    }

    .master-intro .master-core {
        width: 110px;
        height: 125px;
    }

    .master-intro h1 {
        margin: 20px 0 10px;
        font-size: clamp(28px, 3.2vw, 40px);
    }

    .master-intro h1 span {
        margin-top: 4px;
    }

    .master-copy {
        margin-bottom: 18px;
        font-size: 14px;
        line-height: 1.6;
    }

    .master-step {
        margin-top: 16px;
    }

    .master-heading {
        margin: 4px 0 14px;
    }

    .master-heading .master-eyebrow {
        margin-bottom: 8px;
    }

    .master-heading h1 {
        font-size: 30px;
        margin-bottom: 6px;
    }

    .master-heading p {
        font-size: 13px;
    }

    .master-network {
        min-height: 0;
        gap: 16px 24px;
        grid-template-columns: minmax(0, 1fr) 150px minmax(0, 1fr);
    }

    .master-hub .master-core {
        width: 110px;
        height: 125px;
    }

    .master-hub p {
        margin-top: 18px;
    }

    .master-card {
        min-width: 0;
        padding: 18px 20px;
    }

    .master-card h2 {
        font-size: 22px;
        margin: 8px 0;
    }

    .master-card p {
        font-size: 13px;
        line-height: 1.5;
        margin-bottom: 12px;
    }

    .master-card small {
        padding-right: 25px;
        font-size: 9px;
        letter-spacing: 1.5px;
    }

    .master-footer {
        margin-top: 14px;
        padding-top: 12px;
    }

    .master-note {
        margin-top: 10px;
        font-size: 11px;
        line-height: 1.5;
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
</head>
<body class="master-page">
  @php
    $showWorkspaces = request('view') === 'workspaces';
@endphp
<div class="master-shell">
    <header class="master-nav">
        <a class="master-brand" href="{{ route('home') }}">QIC<span>Resolve</span></a>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="master-button" type="submit">Keluar akun</button>
        </form>
    </header>
    <main class="master-stage">
        <section
    id="master-welcome"
    class="master-intro master-panel"
    aria-labelledby="welcome-title"
    @if($showWorkspaces) hidden @endif
>
            <p class="master-eyebrow">Master Access · Project Owner</p>
            <div class="master-core" aria-hidden="true">
                <div class="master-orbit"></div>
                <div class="master-circuits">
                    @for($i = 0; $i < 6; $i++)
                        <i style="--i:{{ $i }}"></i>
                    @endfor
                </div>
                <strong>QIC</strong>
            </div>
            <h1 id="welcome-title" tabindex="-1">Selamat datang, Master<span>{{ auth()->user()->name }}</span></h1>
            <p class="master-copy">Satu pusat kendali untuk memantau quality issue dan menjelajahi alur penanganan complaint.</p>
            <button id="master-next" class="master-button master-primary" type="button" hidden>Lanjut ke Workspace <span aria-hidden="true">→</span></button>
            <noscript><p><a class="master-button master-primary" href="#master-workspaces">Pilih Workspace ↓</a></p></noscript>
            <p class="master-step">01 / SAMBUTAN</p>
        </section>
        <section
    id="master-workspaces"
    class="master-panel"
    aria-labelledby="workspace-title"
    @unless($showWorkspaces) hidden @endunless
>
            <div class="master-heading">
                <p class="master-eyebrow">Connected Workspaces</p>
                <h1 id="workspace-title" tabindex="-1">Pilih workspace Anda</h1>
                <p>Tiga peran, satu alur penanganan quality issue.</p>
            </div>
            <div class="master-network">
                <div class="master-hub" aria-hidden="true">
                    <div class="master-core"><div class="master-orbit"></div><strong>QIC</strong></div>
                    <p>CONTROL CENTER</p>
                </div>
                @foreach(config('qic_ui') as $role => $definition)
                    <a class="master-card master-card-{{ $role }}" href="{{ route($role.'.dashboard') }}">
                        <span class="master-number" aria-hidden="true">0{{ $loop->iteration }}</span>
                        <small>{{ $definition['subtitle'] }}</small>
                        <h2>{{ $definition['label'] }}</h2>
                        <p>{{ ['main-assy' => 'Buat complaint, lampirkan bukti, dan pantau penanganan.', 'auto-line' => 'Investigasi, susun respons 3C, dan dokumentasikan tindakan korektif.', 'quality' => 'Verifikasi, tugaskan, tinjau 3C, dan putuskan penyelesaian.'][$role] }}</p>
                        <span class="master-enter">Buka workspace <span aria-hidden="true">↗</span></span>
                    </a>
                @endforeach
            </div>
            <footer class="master-footer">
                <button id="master-back" class="master-button" type="button" hidden>← Kembali</button>
                <a class="master-link" href="{{ route('profile.edit') }}">Pengaturan akun</a>
            </footer>
            <p class="master-note">Mode demonstrasi · Data simulasi pada tab ini. Pemilihan workspace tetap mengikuti hak akses akun yang berlaku.</p>
        </section>
    </main>
</div>
<script>
    // Inisialisasi ulang aman ketika halaman dimuat melalui Livewire navigate.
    (() => {
        function initMasterDashboard() {
            const welcome = document.getElementById('master-welcome');
            const workspaces = document.getElementById('master-workspaces');
            const next = document.getElementById('master-next');
            const back = document.getElementById('master-back');
            if (!welcome || !workspaces || next.dataset.ready) return;
            next.dataset.ready = 'true';
            next.hidden = false;
            back.hidden = false;
            const openWorkspaces = @json($showWorkspaces);

welcome.hidden = openWorkspaces;
workspaces.hidden = !openWorkspaces;
            function showWorkspace(show) {
                welcome.hidden = show;
                workspaces.hidden = !show;
                document.getElementById(show ? 'workspace-title' : 'welcome-title').focus({ preventScroll:true });
                window.scrollTo({ top:0, behavior:'instant' });
            }
            next.addEventListener('click', () => showWorkspace(true));
            back.addEventListener('click', () => showWorkspace(false));

            // Langsung tampilkan pilihan workspace jika datang dari sidebar.
const params = new URLSearchParams(window.location.search);

if (params.get('view') === 'workspaces') {
    showWorkspace(true);
}
        }
        initMasterDashboard();
        document.addEventListener('livewire:navigated', initMasterDashboard);
    })();
</script>
</body>
</html>
