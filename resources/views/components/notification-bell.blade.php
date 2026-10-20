@auth
    <div x-data="{ open: false }" class="relative" x-cloak>
        <!-- Bell Trigger -->
        <button type="button" @click="open = !open" @click.outside="open = false"
                class="relative p-2 rounded-full transition-colors text-slate-500 hover:text-slate-800 hover:bg-slate-100"
                :class="open ? 'text-blue-600 bg-slate-100' : ''"
                title="Notifikasi">
            <span class="material-symbols-outlined text-xl text-slate-500">notifications</span>
            @if ($unreadCount > 0)
                <span class="absolute -top-0.5 -right-0.5 inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full bg-rose-600 text-white font-mono text-[10px] font-bold">
                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                </span>
            @endif
        </button>

        <!-- Dropdown -->
        <div x-show="open"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             @click.outside="open = false"
             class="absolute right-0 mt-2 w-80 sm:w-96 max-w-[calc(100vw-2rem)] bg-surface-container-lowest border border-outline-variant rounded-xl shadow-xl z-50 overflow-hidden">

            <!-- Header -->
            <div class="px-4 py-3 border-b border-outline-variant bg-surface-container-low/50 flex items-center justify-between gap-2">
                <span class="font-title-sm text-title-sm text-on-surface font-semibold">Notifikasi</span>
                @if ($unreadCount > 0)
                    <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center gap-1 text-primary hover:underline font-label-md text-label-md">
                            <span class="material-symbols-outlined text-sm">done_all</span>
                            Tandai Semua Sudah Dibaca
                        </button>
                    </form>
                @endif
            </div>

            <!-- Items -->
            <div class="max-h-96 overflow-y-auto divide-y divide-outline-variant">
                @forelse ($recentNotifications as $notification)
                    @php
                        $data = $notification->data;
                        $isUnread = $notification->read_at === null;
                        $detailUrl = $data['url'] ?? null;

                        // Normalize any stored absolute URL (e.g. legacy tunnel
                        // domains) to a relative path so links survive domain
                        // changes and never leak stale external hosts.
                        if (is_string($detailUrl) && $detailUrl !== '') {
                            $parsedUrl = parse_url($detailUrl);
                            $detailUrl = ($parsedUrl['path'] ?? '/')
                                .(isset($parsedUrl['query']) ? '?'.$parsedUrl['query'] : '');
                        }
                    @endphp
                    <div class="flex items-start gap-3 px-4 py-3 {{ $isUnread ? 'bg-primary-container/30' : '' }}">
                        <span class="material-symbols-outlined text-xl mt-0.5 {{ $isUnread ? 'text-primary' : 'text-outline' }}"
                              style="font-variation-settings: 'FILL' {{ $isUnread ? 1 : 0 }};">
                            {{ ($data['type'] ?? '') === 'new_loan_request' ? 'assignment_add' : 'sync_alt' }}
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="font-label-md text-label-md font-semibold text-on-surface flex items-center gap-2">
                                {{ $data['title'] ?? 'Notifikasi' }}
                                @if ($isUnread)
                                    <span class="inline-block w-2 h-2 rounded-full bg-error flex-shrink-0"></span>
                                @endif
                            </p>
                            <p class="font-body-md text-body-md text-on-surface-variant mt-0.5">{{ $data['message'] ?? '' }}</p>
                            <div class="mt-1.5 flex items-center gap-3 flex-wrap">
                                <span class="font-caption-xs text-caption-xs text-outline">
                                    {{ $notification->created_at->diffForHumans() }}
                                </span>
                                @if ($detailUrl)
                                    <a href="{{ $detailUrl }}" @click="open = false"
                                       class="text-primary hover:underline font-label-md text-label-md inline-flex items-center gap-0.5">
                                        {{ ($data['type'] ?? '') === 'new_loan_request' ? 'Tinjau Tiket' : 'Lihat Detail' }}
                                        <span class="material-symbols-outlined text-sm">arrow_forward</span>
                                    </a>
                                @endif
                                @if ($isUnread)
                                    <form method="POST" action="{{ route('notifications.mark-read', $notification->id) }}">
                                        @csrf
                                        <button type="submit" class="text-on-surface-variant hover:text-primary font-caption-xs text-caption-xs inline-flex items-center gap-0.5"
                                                title="Tandai sudah dibaca">
                                            <span class="material-symbols-outlined text-[14px]">check</span>
                                            Tandai dibaca
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-10 text-center">
                        <span class="material-symbols-outlined text-4xl text-outline">notifications_off</span>
                        <p class="font-title-sm text-title-sm text-on-surface mt-2">Belum ada notifikasi</p>
                        <p class="font-caption-xs text-caption-xs text-on-surface-variant mt-1">Notifikasi akan muncul di sini.</p>
                    </div>
                @endforelse
            </div>

            <!-- Footer reminder -->
            <div class="px-4 py-2.5 border-t border-outline-variant bg-surface-container-low/50">
                <p class="font-caption-xs text-caption-xs text-on-surface-variant flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm text-primary">tips_and_updates</span>
                    Pesan pengingat: segera verifikasi tiket &amp; pantau status peminjaman agar tidak overdue.
                </p>
            </div>
        </div>
    </div>
@endauth
