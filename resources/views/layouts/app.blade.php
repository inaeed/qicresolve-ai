<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'QICResolve-AI' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
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

<body class="min-h-screen bg-slate-100 text-slate-800">
    <div class="flex min-h-screen">
        <aside class="w-64 bg-slate-900 px-5 py-6 text-white">
            <h1 class="text-xl font-bold">QICResolve-AI</h1>
            <p class="mt-1 text-xs text-slate-400">
                Quality Issue Management
            </p>

            <nav class="mt-8 space-y-2">
                <a href="{{ route('dashboard') }}"
                   class="block rounded-lg px-4 py-3 hover:bg-slate-800">
                    Dashboard
                </a>

                <a href="{{ route('issues.index') }}"
                   class="block rounded-lg px-4 py-3 hover:bg-slate-800">
                    Quality Issues
                </a>
            </nav>
        </aside>

        <main class="flex-1">
            <header class="border-b bg-white px-8 py-4">
                <h2 class="font-semibold">
                    {{ $header ?? 'Dashboard' }}
                </h2>
            </header>

            <section class="p-8">
                {{ $slot }}
            </section>
        </main>
    </div>

    @livewireScripts
</body>
</html>