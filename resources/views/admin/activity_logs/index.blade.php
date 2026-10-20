@extends('layouts.admin', ['title' => 'Logbook Kegiatan Mahasiswa', 'pageTitle' => 'Logbook Mahasiswa'])

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="font-headline-sm text-headline-sm text-on-surface font-bold">Logbook Kegiatan Mahasiswa</h2>
            <p class="font-body-md text-body-md text-on-surface-variant">Tinjau dan verifikasi catatan kehadiran praktikum dan aktivitas kerja mandiri mahasiswa.</p>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="p-4 bg-surface-container-lowest rounded-xl border border-outline-variant shadow-xs">
        <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="flex flex-wrap items-center gap-3">
            @if (auth()->user()->isKepalaLab())
                <div class="w-full sm:w-64">
                    <select name="laboratory_id" onchange="this.form.submit()" 
                            class="w-full h-10 px-3 rounded-lg border border-outline-variant bg-surface-container-lowest focus:ring-2 focus:ring-blue-500 text-sm cursor-pointer font-body-md text-body-md">
                        <option value="">Semua Laboratorium</option>
                        @foreach ($laboratories as $lab)
                            <option value="{{ $lab->id }}" {{ request('laboratory_id') == $lab->id ? 'selected' : '' }}>
                                {{ $lab->name }} ({{ $lab->code }})
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="w-full sm:w-48">
                <select name="status" onchange="this.form.submit()"
                        class="w-full h-10 px-3 rounded-lg border border-outline-variant bg-surface-container-lowest focus:ring-2 focus:ring-blue-500 text-sm cursor-pointer font-body-md text-body-md">
                    <option value="">Semua Status</option>
                    <option value="PENDING" {{ request('status') === 'PENDING' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                    <option value="VERIFIED" {{ request('status') === 'VERIFIED' ? 'selected' : '' }}>Terverifikasi</option>
                    <option value="REJECTED" {{ request('status') === 'REJECTED' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>

            <div class="w-full sm:w-48">
                <select name="activity_type" onchange="this.form.submit()"
                        class="w-full h-10 px-3 rounded-lg border border-outline-variant bg-surface-container-lowest focus:ring-2 focus:ring-blue-500 text-sm cursor-pointer font-body-md text-body-md">
                    <option value="">Semua Jenis Kegiatan</option>
                    <option value="Praktikum" {{ request('activity_type') === 'Praktikum' ? 'selected' : '' }}>Praktikum</option>
                    <option value="Penelitian" {{ request('activity_type') === 'Penelitian' ? 'selected' : '' }}>Penelitian</option>
                    <option value="Tugas Akhir" {{ request('activity_type') === 'Tugas Akhir' ? 'selected' : '' }}>Tugas Akhir</option>
                    <option value="Mandiri" {{ request('activity_type') === 'Mandiri' ? 'selected' : '' }}>Mandiri</option>
                </select>
            </div>

            <div class="flex items-center gap-1.5 w-full sm:w-auto">
                <span class="text-xs text-on-surface-variant font-medium">Rentang:</span>
                <input type="date" name="from_date" value="{{ request('from_date') }}" onchange="this.form.submit()"
                       title="Dari tanggal kegiatan"
                       class="h-10 px-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-xs font-mono focus:ring-2 focus:ring-blue-500"/>
                <span class="text-xs text-on-surface-variant">s.d.</span>
                <input type="date" name="to_date" value="{{ request('to_date') }}" onchange="this.form.submit()"
                       title="Sampai tanggal kegiatan"
                       class="h-10 px-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-xs font-mono focus:ring-2 focus:ring-blue-500"/>
            </div>

            @if (request()->hasAny(['laboratory_id', 'status', 'activity_type', 'from_date', 'to_date']))
                <a href="{{ route('admin.activity-logs.index') }}" 
                   class="px-3 py-2 text-xs font-semibold text-rose-600 hover:text-rose-700 hover:underline">
                    Reset Filter
                </a>
            @endif
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container text-on-surface font-label-md text-label-md border-b border-outline-variant">
                        <th class="py-3.5 px-6 font-semibold w-12">#</th>
                        <th class="py-3.5 px-6 font-semibold">Mahasiswa</th>
                        <th class="py-3.5 px-6 font-semibold">Lab</th>
                        <th class="py-3.5 px-6 font-semibold">Waktu Kegiatan</th>
                        <th class="py-3.5 px-6 font-semibold">Jenis & Judul</th>
                        <th class="py-3.5 px-6 font-semibold text-center">Status</th>
                        <th class="py-3.5 px-6 font-semibold text-center w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant font-body-md text-body-md text-on-surface">
                    @forelse ($logs as $index => $log)
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="py-3.5 px-6 text-on-surface-variant font-mono text-xs">{{ $logs->firstItem() + $index }}</td>
                            <td class="py-3.5 px-6">
                                <div class="font-semibold text-on-surface">{{ $log->user->name }}</div>
                                <div class="font-mono text-xs text-on-surface-variant">{{ $log->user->identity_number ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-6">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-secondary-container text-on-secondary-container">
                                    <span class="material-symbols-outlined text-xs">science</span>
                                    {{ $log->laboratory->code ?? $log->laboratory->name }}
                                </span>
                            </td>
                            <td class="py-3.5 px-6">
                                <div class="font-mono text-xs font-medium">{{ $log->activity_date->format('d/m/Y') }}</div>
                                <div class="font-mono text-xs text-on-surface-variant mt-0.5">
                                    {{ substr($log->time_in, 0, 5) }} - {{ $log->time_out ? substr($log->time_out, 0, 5) : 'Selesai' }}
                                </div>
                            </td>
                            <td class="py-3.5 px-6 max-w-md">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                        {{ $log->activity_type }}
                                    </span>
                                    <span class="font-semibold text-sm text-on-surface">{{ $log->title }}</span>
                                </div>
                                @if ($log->description)
                                    <p class="text-xs text-on-surface-variant line-clamp-2">{{ $log->description }}</p>
                                @endif
                            </td>
                            <td class="py-3.5 px-6 text-center">
                                @if ($log->status === 'VERIFIED')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-300 dark:border-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Diverifikasi
                                    </span>
                                    @if ($log->verifier)
                                        <div class="text-[10px] text-on-surface-variant mt-1">oleh {{ $log->verifier->name }}</div>
                                    @endif
                                @elseif ($log->status === 'REJECTED')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 dark:bg-rose-950/40 dark:text-rose-400 border border-rose-300 dark:border-rose-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Ditolak
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-300 dark:border-amber-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Pending
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-6 text-center">
                                @if ($log->status === 'PENDING')
                                    <div class="flex items-center justify-center gap-1.5">
                                        <form method="POST" action="{{ route('admin.activity-logs.verify', $log) }}" onsubmit="return confirm('Verifikasi catatan kegiatan ini?');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    class="p-1.5 text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 rounded-lg transition-colors cursor-pointer"
                                                    title="Verifikasi Kegiatan">
                                                <span class="material-symbols-outlined text-base">check_circle</span>
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.activity-logs.reject', $log) }}" onsubmit="return confirm('Tolak catatan kegiatan ini?');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    class="p-1.5 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-lg transition-colors cursor-pointer"
                                                    title="Tolak Kegiatan">
                                                <span class="material-symbols-outlined text-base">cancel</span>
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-xs text-outline italic">Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-on-surface-variant">
                                <div class="w-12 h-12 rounded-full bg-surface-container flex items-center justify-center mx-auto mb-3 text-slate-400">
                                    <span class="material-symbols-outlined text-3xl">assignment</span>
                                </div>
                                <p class="font-title-sm text-title-sm text-on-surface">Tidak ada catatan kegiatan</p>
                                <p class="font-caption-xs text-caption-xs mt-1">Belum ada catatan aktivitas mahasiswa yang sesuai dengan filter.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="p-4 border-t border-outline-variant bg-surface-container-lowest">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
