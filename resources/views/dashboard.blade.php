@component('layouts.app', [
    'title' => 'Dashboard Main Assy | QICResolve-AI',
    'header' => 'Dashboard Main Assy',
])
    <div class="space-y-6">
        {{-- Judul halaman --}}
        <div>
            <p class="text-sm font-medium text-blue-600">
                Main Assy · Internal Customer
            </p>

            <h1 class="mt-2 text-2xl font-bold text-slate-900">
                Dashboard
            </h1>

            <p class="mt-2 text-sm text-slate-500">
                Pantau complaint dan perkembangan penanganan quality issue.
            </p>
        </div>

        {{-- Data sementara untuk pratinjau frontend --}}
        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800">
            Pratinjau frontend: angka berikut merupakan data simulasi.
        </div>

        {{-- Ringkasan complaint --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                ['label' => 'Open Issues', 'value' => 12],
                ['label' => 'Pending Verification', 'value' => 4],
                ['label' => 'Need Revision', 'value' => 2],
                ['label' => 'Closed', 'value' => 37],
            ] as $stat)
                <div class="rounded-xl border border-slate-200 bg-white p-5">
                    <p class="text-sm text-slate-500">
                        {{ $stat['label'] }}
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-slate-900">
                        {{ $stat['value'] }}
                    </p>
                </div>
            @endforeach
        </div>

        {{-- Daftar complaint --}}
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="font-semibold text-slate-900">
                    My Recent Issues
                </h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Issue ID</th>
                            <th class="px-5 py-3">Concern</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Process Owner</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        <tr>
                            <td class="whitespace-nowrap px-5 py-4 font-medium">
                                QIC-DEMO-001
                            </td>
                            <td class="px-5 py-4">Terminal bengkok</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex whitespace-nowrap rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-700">
                                    Menunggu verifikasi
                                </span>
                            </td>
                            <td class="px-5 py-4 text-slate-500">
                                Belum ditetapkan
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endcomponent