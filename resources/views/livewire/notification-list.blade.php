<div>
    <!-- Quick Stats -->
    <div class="notif-stats">
        <div class="stat-card">
            <div class="stat-left">
                <div class="stat-value">{{ \App\Models\Notifikasi::where('user_id', auth()->id())->where('is_read', false)->count() }}</div>
                <div class="stat-label">Belum Dibaca</div>
            </div>
            <div class="stat-icon warning-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-left">
                <div class="stat-value">{{ \App\Models\Notifikasi::where('user_id', auth()->id())->count() }}</div>
                <div class="stat-label">Total Notifikasi</div>
            </div>
            <div class="stat-icon primary-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="notif-section">
        <div class="notif-header">
            <h2>Aktivitas Terbaru</h2>
            @if(\App\Models\Notifikasi::where('user_id', auth()->id())->where('is_read', false)->count() > 0)
                <button wire:click="markAllAsRead" class="btn-modern btn-modern-secondary btn-modern-sm">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px; margin-right: 6px;"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <span>Tandai Semua Dibaca</span>
                </button>
            @endif
        </div>

        @if($notifikasi->count() > 0)
            <div class="notif-items">
                @foreach($notifikasi as $item)
                    <div class="notif-card {{ !$item->is_read ? 'unread' : '' }}" wire:key="notif-{{ $item->notifikasi_id }}">
                        <div class="notif-content" wire:click="markAsRead('{{ $item->notifikasi_id }}')" style="cursor: pointer;">
                            <div class="notif-title">{{ $item->judul }}</div>
                            <div class="notif-message">{{ $item->pesan }}</div>
                            <div class="notif-meta">
                                <span class="notif-type">{{ ucfirst(str_replace('_', ' ', $item->tipe)) }}</span>
                                <span class="notif-time">{{ $item->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        @if(!$item->is_read)
                            <button wire:click="markAsRead('{{ $item->notifikasi_id }}')" class="btn-check" title="Tandai dibaca">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            </button>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="notif-pagination">
                {{ $notifikasi->links('components.pagination') }}
            </div>
        @else
            <div class="notif-empty">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
                <div class="notif-empty-title">Tidak Ada Notifikasi</div>
                <p>Semua notifikasi telah ditandai dibaca</p>
            </div>
        @endif
    </div>
</div>
