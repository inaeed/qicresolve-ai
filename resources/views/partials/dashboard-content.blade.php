@php($demoIssues = collect(config('qic_demo')))
<div class="qic-page-heading"><div><h1>Dashboard</h1><p>Pantau laporan dan tindak lanjut dari Quality.</p></div><a class="qic-btn" href="{{ route('main-assy.issues.create') }}">+ Buat Issue</a></div>
<section class="qic-stats" aria-label="Ringkasan issue simulasi">
    <x-qic.stat-card label="Issue Terbuka" :value="3" help="Masih dalam penanganan" />
    <x-qic.stat-card label="Menunggu Verifikasi" :value="1" help="Menunggu tinjauan Quality" />
    <x-qic.stat-card label="Perlu Informasi" :value="1" help="Memerlukan kelengkapan" />
    <x-qic.stat-card label="Selesai" :value="1" help="Telah diverifikasi Quality" />
</section>
<section class="qic-card"><div class="qic-section-heading"><h2>Issue Terbaru</h2><a href="{{ route('main-assy.issues.index') }}">Lihat semua issue</a></div>
    @include('main-assy.partials.issue-table')
</section>
<section class="qic-card"><h2>Aktivitas Terbaru</h2><p>Quality meminta foto label material yang lebih jelas untuk QI-002.</p><a href="{{ route('main-assy.issues.show', 'QI-002') }}">Lihat permintaan informasi →</a></section>
