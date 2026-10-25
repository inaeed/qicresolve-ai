<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Masuk · QICResolve</title>
@vite(['resources/css/app.css','resources/js/app.js'])
<style>
body.qic-orbit-login{margin:0;background:#f3f9ff;color:#081d3b;font-family:Arial,Helvetica,sans-serif}.qic-orbit-login *{box-sizing:border-box}.qic-orbit-login [hidden]{display:none!important}.ql-page{min-height:100svh;display:flex;flex-direction:column;background:radial-gradient(ellipse at 20% 45%,#bceef52a,transparent 60%),radial-gradient(#8ed5e755 1px,transparent 1px);background-size:auto,24px 24px}.ql-header{width:min(1280px,calc(100% - 64px));margin:auto;padding:24px 0;display:flex;justify-content:space-between;align-items:center;gap:18px}.ql-brand{font-size:29px;font-weight:800;letter-spacing:-1px;color:#081d3b;text-decoration:none}.ql-brand span{color:#087f98}.ql-home{font-size:14px;color:#173c56;text-decoration:none;padding:12px 0}.ql-main{width:min(1200px,calc(100% - 64px));margin:auto;display:grid;grid-template-columns:1.08fr 1fr;gap:20px;padding:10px 0 24px;align-items:stretch}.ql-visual{position:relative;overflow:hidden;border:1px solid #23c7de;border-radius:22px;background:radial-gradient(ellipse at 80% 15%,#087f9844,transparent 65%),linear-gradient(145deg,#103047,#071827);color:#edfaff;padding:28px 30px 24px;text-align:center;display:flex;flex-direction:column;align-items:center;justify-content:center;box-shadow:0 0 22px #25c9e31a}.ql-eyebrow{align-self:flex-start;font-size:10px;letter-spacing:2.5px;color:#b7e5f2;margin:0 0 10px;line-height:1.7}.ql-eyebrow:after{content:"";display:block;width:48px;height:2px;background:#33d9ee;margin-top:10px}.ql-orbit{width:min(100%,350px);height:auto;aspect-ratio:1.25;display:block;overflow:visible;margin:0 auto 14px}.ql-visual h2{color:#f1fbff;font-size:clamp(25px,2.5vw,35px);line-height:1.2;letter-spacing:-.8px;margin:0 0 14px;font-weight:750}.ql-description{font-size:14px;line-height:1.8;max-width:450px;color:#c0d8e6;margin:0}.ql-roles{width:100%;display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-top:25px}.ql-role{font-size:12px;font-weight:600;line-height:1.5;color:#e7faff}.ql-role svg{width:32px;height:32px;display:block;margin:0 auto 10px;color:#5fe4f6}.ql-note{margin:24px 0 0;color:#91b5cb;font-size:11px;line-height:1.7}.ql-card{min-width:0;padding:32px 36px;background:#fff;border:1px solid #dcebf5;border-radius:22px;box-shadow:0 12px 40px #24749d0b;display:flex;flex-direction:column;justify-content:center}.ql-lock{width:56px;height:56px;border-radius:50%;background:#eff8ff;color:#082f4b;display:grid;place-items:center;margin:0 auto 18px}.ql-lock svg{width:29px;height:29px}.ql-card h1{font-size:32px;color:#081d3b;line-height:1.2;font-weight:750;letter-spacing:-.8px;margin:0 0 10px;text-align:center}.ql-subtitle{color:#647e98;font-size:14px;line-height:1.7;margin:0 0 26px;text-align:center}.ql-form{margin:0}.ql-field{margin-bottom:20px}.ql-field>label{display:block;font-size:14px;font-weight:700;color:#102a43;margin-bottom:9px}.ql-input{position:relative;display:flex;align-items:center}.ql-input>svg{position:absolute;left:15px;width:21px;height:21px;color:#617d96;pointer-events:none}.ql-input input{width:100%;min-height:50px;border:1px solid #cedfea;border-radius:10px;background:#fff;color:#102a43;padding:13px 46px;font:inherit;font-size:16px;line-height:1.4}.ql-input input::placeholder{color:#8297ac}.ql-input input:focus{outline:3px solid #26badb22;border-color:#168fa8}.ql-input input[aria-invalid=true]{border-color:#af3042}.ql-toggle{position:absolute;right:4px;width:44px;height:44px;display:grid;place-items:center;background:transparent;color:#637f97;border:0;border-radius:8px;cursor:pointer}.ql-toggle svg{width:22px;height:22px}.ql-toggle:hover{background:#edf8fc}.ql-options{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin:0 0 20px}.ql-check{display:flex;align-items:center;gap:9px;font-size:13px;color:#58718b;min-height:32px;cursor:pointer}.ql-check input{width:17px;height:17px;accent-color:#087f98;margin:0}.ql-link{font-size:13px;color:#08748d;text-underline-offset:3px}.ql-submit{width:100%;min-height:50px;border:1px solid #087c96;border-radius:10px;color:white;background:linear-gradient(105deg,#0b3048,#009db6);font:inherit;font-size:16px;font-weight:700;cursor:pointer;box-shadow:0 6px 18px #087f981c}.ql-submit:hover{background:linear-gradient(105deg,#123c55,#00849c)}.ql-register{display:flex;justify-content:center;align-items:center;flex-wrap:wrap;gap:5px;border-top:1px solid #dfebf3;padding-top:20px;margin-top:24px;font-size:13px;color:#657f96;line-height:1.7}.ql-error{font-size:13px;line-height:1.6;color:#a52638;margin:8px 0 0}.ql-success{background:#eefaf3;color:#166746;border:1px solid #c5ead6;border-radius:9px;padding:12px;font-size:13px;line-height:1.6;margin:0 0 18px}.ql-footer{text-align:center;font-size:12px;line-height:1.7;color:#68839b;padding:12px 20px 22px}.qic-orbit-login a:focus-visible,.qic-orbit-login button:focus-visible{outline:3px solid #149cb7;outline-offset:4px}@media(max-width:900px){.ql-card{padding:28px 24px}.ql-visual{padding:26px 22px}.ql-orbit{width:280px}.ql-card h1{font-size:29px}}@media(max-width:700px){.ql-main,.ql-header{width:calc(100% - 36px)}.ql-header{padding:16px 0}.ql-brand{font-size:24px}.ql-home{font-size:12px}.ql-main{grid-template-columns:minmax(0,1fr);margin:0 auto}.ql-card{grid-row:1;padding:28px 24px}.ql-orbit{width:min(100%,280px)}.ql-visual h2{font-size:27px}.ql-visual{padding:25px 22px}}@media(max-height:750px) and (min-width:701px){.ql-header{padding:15px 0}.ql-main{padding:6px 0 14px}.ql-orbit{width:245px;margin-bottom:8px}.ql-visual,.ql-card{padding-top:24px;padding-bottom:22px}.ql-roles{margin-top:18px}.ql-note{margin-top:16px}.ql-subtitle{margin-bottom:20px}.ql-lock{width:44px;height:44px;margin-bottom:12px}.ql-card h1{font-size:29px}.ql-field{margin-bottom:16px}.ql-footer{padding-bottom:14px}}

/* Heading terpusat dan penghubung ketiga peran. */
.ql-eyebrow{align-self:center;text-align:center}
.ql-eyebrow:after{margin-left:auto;margin-right:auto}
.ql-role{position:relative}
.ql-role:not(:last-child):after{content:"";position:absolute;top:16px;left:calc(50% + 24px);width:calc(100% - 32px);height:1px;background:linear-gradient(90deg,#39daf244,#39daf2,#39daf244);box-shadow:0 0 7px #39daf266;pointer-events:none}
.ql-visual{background:radial-gradient(ellipse at 80% 15%,#087f9844,transparent 65%),radial-gradient(circle at 18% 75%,#67e8f917 1px,transparent 1.8px),linear-gradient(145deg,#103047,#071827);background-size:auto,26px 26px,auto}
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
<body class="qic-orbit-login">
<div class="ql-page">
<header class="ql-header"><a class="ql-brand" href="{{ route('home') }}">QIC<span>Resolve</span></a><a class="ql-home" href="{{ route('home') }}">← Kembali ke beranda</a></header>
<main class="ql-main">
<aside class="ql-visual" aria-labelledby="ql-visual-title">
<p class="ql-eyebrow">QUALITY ISSUE WORKSPACE</p>
{{-- Sembilan cahaya dari tepi panel; ekor terputus mengorbit di depan/belakang QIC. --}}
<svg class="ql-orbit" viewBox="0 0 400 320" aria-hidden="true" data-qic-orbits>
<defs>
  <filter id="ql-glow" x="-100%" y="-100%" width="300%" height="300%">
    <feGaussianBlur stdDeviation="2.4" result="blur"/>
    <feMerge><feMergeNode in="blur"/><feMergeNode in="SourceGraphic"/></feMerge>
  </filter>
  <linearGradient id="ql-core" x1="0" y1="0" x2="1" y2="1">
    <stop stop-color="#103f58"/><stop offset="1" stop-color="#071827"/>
  </linearGradient>
</defs>
<g data-orbit-back></g>
<g data-orbit-core>
  <path d="M200 88 263 124V196L200 232 137 196V124Z" fill="url(#ql-core)" stroke="#6ceaff" stroke-width="3" filter="url(#ql-glow)"/>
  <text x="200" y="177" text-anchor="middle" fill="white" font-family="Arial,Helvetica,sans-serif" font-size="49" font-weight="800" letter-spacing="-2">QIC</text>
</g>
<g data-orbit-front></g>
</svg>
<h2 id="ql-visual-title">Terhubung dalam satu proses.</h2>
<p class="ql-description">Main Assy melapor. Auto Line menindaklanjuti.<br>Quality memverifikasi dan mengambil keputusan.</p>
<div class="ql-roles">
<div class="ql-role"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" aria-hidden="true"><path d="m12 2 8 4-8 4-8-4 8-4ZM4 6v8l8 4 8-4V6M12 10v8M2 13v6l6 3 4-2m10-7v6l-6 3-4-2"/></svg>Main Assy</div>
<div class="ql-role"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="18" width="20" height="4" rx="2"/><path d="M6 18v-6l5-6 4 3-4 7m4-7 5 4-3 3"/><circle cx="11" cy="5" r="2"/></svg>Auto Line</div>
<div class="ql-role"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><path d="M13 22H5a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v9M7 7h10M7 12h7M7 17h4m3 3 3 3 6-7"/></svg>Quality</div>
</div><p class="ql-note">Prototipe akademik · Data simulasi</p>
</aside>
<section class="ql-card" aria-labelledby="ql-title">
<div class="ql-lock" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="5" y="10" width="14" height="12" rx="2"/><path d="M8 10V6a4 4 0 0 1 8 0v4M12 15v3"/></svg></div>
<h1 id="ql-title">Masuk ke akun</h1><p class="ql-subtitle">Lanjutkan pekerjaan dan pantau quality issue.</p>
@if(session('status'))<p class="ql-success" role="status">{{ session('status') }}</p>@endif
<form class="ql-form" method="POST" action="{{ route('login.store') }}">@csrf
<div class="ql-field"><label for="ql-email">Email</label><div class="ql-input"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="3"/><path d="m3 6 9 7 9-7"/></svg><input id="ql-email" name="email" type="email" value="{{ old('email') }}" placeholder="nama@email.com" required autofocus autocomplete="username" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror></div>@error('email')<p id="email-error" class="ql-error" role="alert">{{ $message }}</p>@enderror</div>
<div class="ql-field"><label for="ql-password">Password</label><div class="ql-input"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="5" y="10" width="14" height="12" rx="2"/><path d="M8 10V6a4 4 0 0 1 8 0v4M12 15v3"/></svg><input id="ql-password" name="password" type="password" placeholder="Masukkan password" required autocomplete="current-password" @error('password') aria-invalid="true" aria-describedby="password-error" @enderror><button class="ql-toggle" id="ql-toggle" type="button" aria-label="Tampilkan password" aria-controls="ql-password" aria-pressed="false" hidden><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg></button></div>@error('password')<p id="password-error" class="ql-error" role="alert">{{ $message }}</p>@enderror</div>
<div class="ql-options"><label class="ql-check"><input type="checkbox" name="remember" value="1" @checked(old('remember'))> Ingat saya</label>@if(Route::has('password.request'))<a class="ql-link" href="{{ route('password.request') }}">Lupa password?</a>@endif</div>
<button class="ql-submit" type="submit">Masuk <span aria-hidden="true">→</span></button>
</form>
@if(Route::has('register'))<div class="ql-register"><span>Belum punya akun?</span> <a class="ql-link" href="{{ route('register') }}">Buat akun</a></div>@endif
</section></main>
<footer class="ql-footer">QICResolve · Proyek PBL · Politeknik Negeri Batam</footer>
</div>
<script>
(() => {
    function initOrbitLogin() {
        const input = document.getElementById('ql-password');
        const toggle = document.getElementById('ql-toggle');
        if (!input || !toggle || toggle.dataset.ready) return;
        toggle.dataset.ready = 'true';
        toggle.hidden = false;
        toggle.addEventListener('click', () => {
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            toggle.setAttribute('aria-pressed', String(show));
            toggle.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
        });
        const svg = document.querySelector('[data-qic-orbits]');
        if (!svg) return;
        const ns = 'http://www.w3.org/2000/svg';
        const back = svg.querySelector('[data-orbit-back]');
        const front = svg.querySelector('[data-orbit-front]');
        const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
        const TAU = Math.PI * 2;
        const introSeconds = 6;
        let bounds;
        function measure() {
            const panel = svg.closest(".ql-visual").getBoundingClientRect();
            const box = svg.getBoundingClientRect();
            const scale = 400 / box.width;
            bounds = {left:(panel.left-box.left)*scale+8, right:(panel.right-box.left)*scale-8,
                top:(panel.top-box.top)*scale+8, bottom:(panel.bottom-box.top)*scale-8};
        }
        measure();
        const resizeObserver = new ResizeObserver(measure);
        resizeObserver.observe(svg.closest(".ql-visual"));
        let elapsed = 0, previous = null, frame = 0, disposed = false;

        function element(tag, attrs, parent) {
            const node = document.createElementNS(ns, tag);
            for (const [key, value] of Object.entries(attrs)) node.setAttribute(key, value);
            parent.append(node);
            return node;
        }

        const orbits = Array.from({length:9}, (_, i) => -55 + (i % 3) * 55).map((angle, i) => {
            const parts = {};
            for (const [side, parent] of [['back', back], ['front', front]]) {
                parts[side] = {
                    line: element('path', {fill:'none', stroke:'#58d9ee', 'stroke-width':1, opacity:side === 'back' ? .36 : .65}, parent),
                    trail: element('path', {fill:'none', stroke:'#82f4ff', 'stroke-width':1.6, 'stroke-linecap':'round', filter:'url(#ql-glow)'}, parent),
                    dot: element('circle', {r:2.8, fill:'#ddffff', filter:'url(#ql-glow)'}, parent)
                };
            }
            return {angle:angle * Math.PI / 180, phase:i * TAU / 9, speed:TAU / (8 + (i % 3) * .65),
                index:i, parts};
        });

        // Proyeksi orbit miring: z menentukan depan/belakang hexagon.
        function point(orbit, theta, progress, seconds) {
            const angle = orbit.angle;
            const x = (145 + (orbit.index % 3) * 7) * Math.cos(theta);
            const y = (48 + (orbit.index % 3) * 3) * Math.sin(theta);
            const finalX = 200 + x * Math.cos(angle) - y * Math.sin(angle);
            const finalY = 160 + x * Math.sin(angle) + y * Math.cos(angle);
            const fraction = (orbit.index % 3 + 1) / 4;
            const starts = [
                [bounds.left, bounds.top + (bounds.bottom-bounds.top)*fraction],
                [bounds.right, bounds.top + (bounds.bottom-bounds.top)*fraction],
                [bounds.left + (bounds.right-bounds.left)*fraction, bounds.top],
                [bounds.left + (bounds.right-bounds.left)*fraction, bounds.bottom]
            ];
            const start = starts[orbit.index % 4];
            return {
                x:start[0] * (1-progress) + finalX * progress,
                y:start[1] * (1-progress) + finalY * progress,
                z:Math.sin(theta)
            };
        }

        function pathFor(points, side) {
            let d = '', drawing = false;
            for (const p of points) {
                const visible = side === 'front' ? p.z >= 0 : p.z < 0;
                if (!visible) { drawing = false; continue; }
                d += `${drawing ? 'L' : 'M'}${p.x.toFixed(2)},${p.y.toFixed(2)} `;
                drawing = true;
            }
            return d;
        }

        function draw(seconds) {
            const raw = reduced.matches ? 1 : Math.min(seconds / introSeconds, 1);
            const progress = 1 - Math.pow(1 - raw, 3);
            // Ekor terputus tetap mengejar titik; tidak pernah menjadi elips penuh.
            const length = .32 + .70 * progress;
            orbits.forEach(orbit => {
                const head = orbit.phase + seconds * orbit.speed;
                const linePoints = [], trailPoints = [];
                for (let i = 0; i <= 240; i++) {
                    linePoints.push(point(orbit, head - length + length * i / 240, progress, seconds));
                }
                for (let i = 0; i <= 30; i++) {
                    trailPoints.push(point(orbit, head - .24 + .24 * i / 30, progress, seconds));
                }
                const dot = point(orbit, head, progress, seconds);
                for (const side of ['back', 'front']) {
                    const nodes = orbit.parts[side];
                    nodes.line.setAttribute('d', pathFor(linePoints, side));
                    nodes.line.setAttribute('opacity', side === 'back' ? '.25' : '.48');
                    nodes.trail.setAttribute('d', pathFor(trailPoints, side));
                    nodes.dot.setAttribute('cx', dot.x);
                    nodes.dot.setAttribute('cy', dot.y);
                    nodes.dot.setAttribute('visibility', (side === 'front' ? dot.z >= 0 : dot.z < 0) ? 'visible' : 'hidden');
                }
            });
        }

        function tick(now) {
            frame = 0;
            if (disposed || !svg.isConnected) return;
            if (previous !== null) elapsed += Math.min((now - previous) / 1000, .05);
            previous = now;
            draw(elapsed);
            frame = requestAnimationFrame(tick);
        }
        function syncMotion() {
            cancelAnimationFrame(frame);
            frame = 0;
            previous = null;
            if (disposed) return;
            if (reduced.matches) { draw(introSeconds); return; }
            if (!document.hidden) frame = requestAnimationFrame(tick);
        }
        function dispose() {
            disposed = true;
            resizeObserver.disconnect();
            cancelAnimationFrame(frame);
            reduced.removeEventListener('change', syncMotion);
            document.removeEventListener('visibilitychange', syncMotion);
            document.removeEventListener('livewire:navigating', dispose);
            window.removeEventListener('pagehide', pause);
            window.removeEventListener('pageshow', syncMotion);
        }
        function pause() { cancelAnimationFrame(frame); previous = null; }
        reduced.addEventListener('change', syncMotion);
        document.addEventListener('visibilitychange', syncMotion);
        document.addEventListener('livewire:navigating', dispose, {once:true});
        window.addEventListener('pagehide', pause);
        window.addEventListener('pageshow', syncMotion);
        draw(0);
        syncMotion();
    }
    initOrbitLogin();
    document.addEventListener('livewire:navigated', initOrbitLogin);
})();
</script>
</body>
</html>
