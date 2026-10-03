<div class="qic-table-scroll" tabindex="0" role="region" aria-label="Tabel issue, geser horizontal pada layar kecil">
<table><caption class="qic-sr-only">Daftar issue simulasi Main Assy</caption><thead><tr><th scope="col">ID Issue</th><th scope="col">Material</th><th scope="col">Status</th><th scope="col">Prioritas</th><th scope="col">Diperbarui</th><th scope="col">Tindakan</th></tr></thead>
<tbody>
@foreach($demoIssues as $issue)
    <tr data-issue-row data-search="{{ mb_strtolower($issue['id'].' '.$issue['material']) }}" data-status="{{ $issue['status'] }}">
        <td><a href="{{ route('main-assy.issues.show', $issue['id']) }}">{{ $issue['id'] }}</a></td><td>{{ $issue['material'] }}</td><td><x-qic.status-badge :tone="$issue['tone']">{{ $issue['status'] }}</x-qic.status-badge></td><td>{{ $issue['priority'] }}</td><td>{{ $issue['updated'] }}</td><td><a href="{{ route('main-assy.issues.show', $issue['id']) }}" aria-label="Lihat {{ $issue['id'] }}">Lihat</a></td>
    </tr>
@endforeach
</tbody></table></div>
<p data-empty hidden role="status">Tidak ada issue yang cocok. Ubah kata pencarian atau filter.</p>
