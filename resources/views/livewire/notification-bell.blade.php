@once
<style>
    .notification-bell-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .notification-bell-btn {
        position: relative;
        background: transparent;
        border: none;
        cursor: pointer;
        padding: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #5575a2;
        transition: all 0.3s ease;
    }

    .notification-bell-btn:hover {
        color: #3c5379;
        transform: scale(1.02);
    }

    .notification-bell-btn:active {
        transform: scale(0.95);
    }

    .bell-icon {
        width: 24px;
        height: 24px;
    }

    .notification-badge {
        position: absolute;
        top: -3px;
        right: -3px;
        background: #ff5757;
        color: white;
        border-radius: 50%;
        min-width: 20px;
        height: 20px;
        padding: 0 5px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        font-weight: 700;
        box-shadow: 0 2px 8px rgba(255, 87, 87, 0.3);
        transform: scale(1);
        transition: transform 0.2s ease;
    }

    .notification-badge.badge-bounce {
        animation: badge-pop 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }

    @keyframes badge-pop {
        0% { transform: scale(1); }
        50% { transform: scale(1.3); }
        100% { transform: scale(1); }
    }

    /* WebSocket Status Indicator */
    .ws-status {
        display: flex;
        align-items: center;
        gap: 4px;
        font-size: 0.7rem;
        padding: 2px 6px;
        border-radius: 10px;
        background: #f0f3f8;
        color: #64748b;
        cursor: help;
    }

    .ws-status .dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        animation: pulse-dot 2s infinite;
    }

    .ws-status.connected .dot {
        background: #10b981;
        box-shadow: 0 0 4px #10b981;
    }

    .ws-status.disconnected .dot {
        background: #ef4444;
        animation: none;
    }

    .ws-status.connecting .dot {
        background: #f59e0b;
        animation: blink 1s infinite;
    }

    @keyframes pulse-dot {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    @keyframes blink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
    }

    .ws-tooltip {
        position: absolute;
        bottom: -30px;
        right: 0;
        background: #1e293b;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.7rem;
        white-space: nowrap;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s;
    }

    .ws-status:hover .ws-tooltip {
        opacity: 1;
    }

    .notification-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        width: 360px;
        background: white;
        border-radius: 1rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12);
        z-index: 1050;
        margin-top: 0.5rem;
        max-height: 500px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-8px) scale(0.98);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .notification-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #f0f3f8;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
    }

    .notification-title {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .notification-title h4 {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 600;
        color: #1a1a1a;
    }

    .unread-badge {
        background: #5575a2;
        color: white;
        padding: 0.25rem 0.6rem;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .mark-all-btn {
        background: transparent;
        border: none;
        color: #5575a2;
        cursor: pointer;
        font-size: 0.8rem;
        font-weight: 600;
        transition: color 0.3s ease;
    }

    .mark-all-btn:hover {
        color: #3c5379;
    }

    .notification-header-actions {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .notif-close-btn {
        border: 1px solid #d8e0ee;
        background: #f8fafd;
        color: #64748b;
        border-radius: 0.55rem;
        width: 28px;
        height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .notif-close-btn:hover {
        background: #eef3f9;
        color: #334155;
    }

    .notification-list {
        flex: 1;
        overflow-y: auto;
        max-height: 350px;
    }

    .notification-list::-webkit-scrollbar {
        width: 4px;
    }

    .notification-list::-webkit-scrollbar-track {
        background: transparent;
    }

    .notification-list::-webkit-scrollbar-thumb {
        background: #d0d9e7;
        border-radius: 2px;
    }

    .notification-item {
        display: flex;
        align-items: center;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #f9f9fb;
        cursor: pointer;
        transition: background 0.3s ease;
        position: relative;
        gap: 0.75rem;
    }

    .notification-item:hover {
        background: #fafbfc;
    }

    .notification-item.unread {
        background: #f0f3f8;
    }

    .notification-item.unread:hover {
        background: #e6ecf5;
    }

    .notification-icon {
        flex-shrink: 0;
        width: 40px;
        height: 40px;
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f0f3f8;
        color: #5575a2;
    }

    .notification-icon svg {
        width: 20px;
        height: 20px;
    }

    .notification-content {
        flex: 1;
        min-width: 0;
    }

    .notification-text {
        display: flex;
        flex-direction: column;
        gap: 0.3rem;
    }

    .notif-title {
        margin: 0;
        font-size: 0.9rem;
        font-weight: 600;
        color: #1a1a1a;
        word-break: break-word;
    }

    .notif-message {
        margin: 0;
        font-size: 0.8rem;
        color: #666;
        word-break: break-word;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .notif-time {
        font-size: 0.75rem;
        color: #999;
    }

    .unread-indicator {
        flex-shrink: 0;
        width: 8px;
        height: 8px;
        background: #5575a2;
        border-radius: 50%;
    }

    .notification-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
        color: #999;
    }

    .notification-empty svg {
        width: 48px;
        height: 48px;
        margin-bottom: 0.5rem;
        opacity: 0.5;
    }

    .notification-empty p {
        margin: 0;
        font-size: 0.9rem;
    }

    .notification-footer {
        padding: 0.75rem 1.25rem;
        border-top: 1px solid #f0f3f8;
        background: #fafbfc;
    }

    .view-all-btn {
        display: block;
        text-align: center;
        color: #5575a2;
        text-decoration: none;
        font-size: 0.85rem;
        font-weight: 600;
        padding: 0.5rem;
        border-radius: 0.5rem;
        transition: background 0.3s ease;
    }

    .view-all-btn:hover {
        background: #f0f3f8;
    }

    @media (max-width: 480px) {
        .ws-status {
            display: none;
        }

        .notification-dropdown {
            width: 320px;
            right: -50px;
        }
    }
</style>
@endonce

<div class="notification-bell-wrapper" x-data="{
    open: false,
    wsConnected: false,
    wsStatusChecked: false,
    userId: {{ (int) auth()->id() }},
    unreadCount: {{ (int) $unreadCount }},
    autoCloseTimer: null,
    clearAutoCloseTimer() {
        if (this.autoCloseTimer) {
            clearTimeout(this.autoCloseTimer);
            this.autoCloseTimer = null;
        }
    },
    scheduleAutoClose(ms = 9000) {
        this.clearAutoCloseTimer();
        this.autoCloseTimer = setTimeout(() => {
            this.open = false;
            this.autoCloseTimer = null;
        }, ms);
    },
    openTemporarily(ms = 9000) {
        this.open = true;
        this.scheduleAutoClose(ms);
    },
    showUnreadOnStartup() {
        if (this.unreadCount <= 0) return;
        const key = `startup-notif-dropdown-${this.userId}`;
        if (sessionStorage.getItem(key) === '1') return;
        sessionStorage.setItem(key, '1');
        setTimeout(() => {
            this.openTemporarily(9000);
        }, 550);
    },
    registerAutoOpenHook() {
        window.addEventListener('open-notification-dropdown', () => {
            this.openTemporarily(9000);
        });
    },
    initWsStatus() {
        const check = () => {
            if (typeof Echo !== 'undefined' && Echo.connector) {
                try {
                    // Reverb uses Pusher connector under the hood
                    const pusher = Echo.connector.pusher;
                    if (pusher && pusher.connection) {
                        this.wsStatusChecked = true;
                        this.wsConnected = pusher.connection.state === 'connected';
                        
                        // Bind to state changes for real-time updates
                        pusher.connection.bind('state_change', (states) => {
                            this.wsConnected = states.current === 'connected';
                        });
                        return true;
                    }
                } catch (e) {
                    console.error('[WS] Error checking status:', e);
                }
            }
            return false;
        };

        // Try immediately
        if (!check()) {
            // If not ready, poll for a few seconds
            let attempts = 0;
            const maxAttempts = 10;
            const interval = setInterval(() => {
                attempts++;
                if (check() || attempts >= maxAttempts) {
                    clearInterval(interval);
                    this.wsStatusChecked = true;
                }
            }, 1000);
        }
    },
    init() {
        this.initWsStatus();
        this.showUnreadOnStartup();
        this.registerAutoOpenHook();
    }
}" x-init="init()" @click.away="open = false; clearAutoCloseTimer()">
    <!-- WebSocket Status Indicator - Hidden if Echo not available -->
    <template x-if="wsStatusChecked">
        <div class="ws-status" :class="wsConnected ? 'connected' : 'disconnected'">
            <span class="dot"></span>
            <span class="ws-tooltip" x-text="wsConnected ? 'Real-time aktif' : 'Offline - refresh halaman'"></span>
        </div>
    </template>

    <!-- Notification Bell Button -->
    <button type="button" @click="open = !open; if (!open) clearAutoCloseTimer();" class="notification-bell-btn" title="Notifikasi">
        <svg class="bell-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
        </svg>
        
        @if($unreadCount > 0)
            <span class="notification-badge {{ $unreadCount > 0 ? 'badge-bounce' : '' }}">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </button>

    <!-- Dropdown Content -->
    <div x-show="open"
         x-cloak
         x-transition:enter="transition ease-out duration-300 delay-75"
         x-transition:enter-start="opacity-0 transform scale-95 translate-y-[-5px]"
         x-transition:enter-end="opacity-100 transform scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 transform scale-95 translate-y-[-5px]"
         class="notification-dropdown"
         style="display: none;"
         @mouseenter="clearAutoCloseTimer()"
         @mouseleave="if (open) scheduleAutoClose(5000)"
         @click.away="setTimeout(() => { open = false; clearAutoCloseTimer(); }, 250)">
        
        <div class="notification-header">
            <div class="notification-title">
                <h4>Notifikasi</h4>
                @if($unreadCount > 0)
                    <span class="unread-badge">{{ $unreadCount }} Baru</span>
                @endif
            </div>
            <div class="notification-header-actions">
                @if($unreadCount > 0)
                    <button
                        type="button"
                        class="mark-all-btn"
                        x-on:click.prevent.stop="$wire.markAllAsRead()"
                        wire:loading.attr="disabled"
                        wire:target="markAllAsRead"
                    >
                        <span wire:loading.remove wire:target="markAllAsRead">Tandai Semua Dibaca</span>
                        <span wire:loading wire:target="markAllAsRead">Memproses...</span>
                    </button>
                @endif
                <button type="button" class="notif-close-btn" @click="open = false; clearAutoCloseTimer();" title="Tutup">&times;</button>
            </div>
        </div>

        <div class="notification-list">
            @forelse($notifications as $item)
                <div wire:click="markAsRead('{{ $item->notifikasi_id }}')" 
                     class="notification-item {{ !$item->is_read ? 'unread' : '' }}">
                    <div class="notification-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                    </div>
                    <div class="notification-content">
                        <div class="notification-text">
                            <p class="notif-title">{{ $item->judul }}</p>
                            <p class="notif-message">{{ $item->pesan }}</p>
                            <span class="notif-time">{{ $item->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    @if(!$item->is_read)
                        <div class="unread-indicator"></div>
                    @endif
                </div>
            @empty
                <div class="notification-empty">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                    <p>Tidak ada notifikasi baru</p>
                </div>
            @endforelse
        </div>

        <div class="notification-footer">
            <a href="{{ route(auth()->user()->role . '.notifikasi') }}" class="view-all-btn">
                Lihat Semua Notifikasi
            </a>
        </div>
    </div>
</div>
