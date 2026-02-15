@extends('layouts.app')

@section('title', 'Notifikasi - Reimbursement System')

@section('content')
<div class="notifikasi-wrapper">
    <x-page-header title="Notifikasi" subtitle="Pantau semua notifikasi sistem reimbursement Anda" :showNotification="true" :showProfile="true" />

    <!-- Stats Cards -->
    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-number">{{ $notifikasi->total() }}</div>
                <div class="stat-label">Total Notifikasi</div>
            </div>
            <div class="stat-icon-box primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-number">{{ $notifikasi->where('is_read', false)->count() }}</div>
                <div class="stat-label">Belum Dibaca</div>
            </div>
            <div class="stat-icon-box warning">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M12 6v6l4 2"></path>
                </svg>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-number">{{ $notifikasi->where('is_read', true)->count() }}</div>
                <div class="stat-label">Sudah Dibaca</div>
            </div>
            <div class="stat-icon-box success">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
        </div>
    </div>

    <!-- Daftar Notifikasi Section -->
    <div class="notifikasi-section">
        <div class="section-title-bar">
            <div>
                <h2 class="section-title">Daftar Notifikasi</h2>
                <p class="section-info">Total: {{ $notifikasi->total() }} notifikasi</p>
            </div>
            @if($notifikasi->total() > 0 && $notifikasi->where('is_read', false)->count() > 0)
                <form action="{{ route('notifikasi.mark-all-read') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-mark-all">Tandai Semua Dibaca</button>
                </form>
            @endif
        </div>

        @if($notifikasi->count() > 0)
            <div class="notifikasi-list">
                @foreach($notifikasi as $item)
                    <div class="notifikasi-item {{ $item->is_read ? 'read' : 'unread' }}">
                        <div class="item-header">
                            <div class="item-title-group">
                                @if(!$item->is_read)
                                    <span class="unread-dot"></span>
                                @endif
                                <h3 class="item-title">{{ $item->judul }}</h3>
                            </div>
                            <span class="item-badge">{{ ucfirst(str_replace('_', ' ', $item->tipe)) }}</span>
                        </div>
                        <p class="item-message">
                            {{ $item->pesan }}
                            
                            @if($item->pengajuan && $item->pengajuan->user)
                                <div class="audit-info">
                                    <span class="audit-badge" title="Data User Saat Ini">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="12" cy="7" r="4"></circle>
                                        </svg>
                                        {{ $item->pengajuan->user->name }} (Sekarang)
                                    </span>
                                    @if($item->pengajuan->departemen)
                                        <span class="audit-badge" title="Departemen Saat Ini">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                            </svg>
                                            {{ $item->pengajuan->departemen->nama_departemen }}
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </p>
                        <div class="item-footer">
                            <span class="item-date">{{ $item->created_at->format('d M Y, H:i') }}</span>
                            @if(!$item->is_read)
                                <form action="{{ route('notifikasi.mark-read', ['notifikasi_id' => $item->notifikasi_id]) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn-read">Tandai Dibaca</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="pagination-container">
                {{ $notifikasi->links('components.pagination') }}
            </div>
        @else
            <div class="empty-notif">
                <div class="empty-icon">
                    <x-icon name="bell" class="w-16 h-16 text-muted" style="opacity: 0.2; color: #5575a2;" />
                </div>
                <h3>Tidak Ada Notifikasi</h3>
                <p>Anda tidak memiliki notifikasi saat ini. Semua notifikasi akan ditampilkan di sini.</p>
            </div>
        @endif
    </div>
</div>

<style>
    .notifikasi-wrapper {
        padding: 1.5rem;
        background: #f8f9fb;
        min-height: 100vh;
    }

    /* Stats Container */
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin: 2rem 0;
    }

    .stat-card {
        background: white;
        padding: 1.5rem;
        border-radius: 1rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1.5rem;
    }

    .stat-content {
        flex: 1;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 800;
        color: #1a1a1a;
        margin-bottom: 0.5rem;
    }

    .stat-label {
        font-size: 0.9rem;
        color: #666;
        font-weight: 500;
    }

    .stat-icon-box {
        width: 60px;
        height: 60px;
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .stat-icon-box svg {
        width: 28px;
        height: 28px;
        stroke-width: 1.5;
    }

    .stat-icon-box.primary {
        background: rgba(66, 93, 135, 0.15);
        color: #5575a2;
    }

    .stat-icon-box.warning {
        background: rgba(251, 146, 60, 0.15);
        color: #fb923c;
    }

    .stat-icon-box.success {
        background: rgba(34, 197, 94, 0.15);
        color: #22c55e;
    }

    /* Notifikasi Section */
    .notifikasi-section {
        background: white;
        border-radius: 1rem;
        padding: 2rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .section-title-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        gap: 1rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #e8ecf1;
    }

    .section-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: #1a1a1a;
        margin: 0;
    }

    .section-info {
        font-size: 0.85rem;
        color: #999;
        margin: 0.25rem 0 0 0;
    }

    .btn-mark-all {
        background: #5575a2;
        color: white;
        border: none;
        padding: 0.6rem 1.5rem;
        border-radius: 0.75rem;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-mark-all:hover {
        background: #425d87;
        box-shadow: 0 2px 8px rgba(85, 117, 162, 0.2);
    }

    /* Notifikasi List */
    .notifikasi-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .notifikasi-item {
        padding: 1.25rem;
        border: 1px solid #e8ecf1;
        border-radius: 0.75rem;
        background: #fafbfc;
        transition: all 0.3s ease;
    }

    .notifikasi-item.unread {
        background: #f0f4ff;
        border-left: 4px solid #5575a2;
    }

    .notifikasi-item.read {
        opacity: 0.7;
    }

    .notifikasi-item:hover {
        box-shadow: 0 2px 8px rgba(85, 117, 162, 0.08);
    }

    .item-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
        gap: 1rem;
    }

    .item-title-group {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex: 1;
    }

    .unread-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #5575a2;
        flex-shrink: 0;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    .item-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1a1a1a;
        margin: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .item-badge {
        padding: 0.35rem 0.75rem;
        background: rgba(66, 93, 135, 0.1);
        color: #5575a2;
        font-size: 0.7rem;
        font-weight: 700;
        border-radius: 0.5rem;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .item-message {
        font-size: 0.9rem;
        color: #555;
        margin: 0.5rem 0 1rem 0;
        line-height: 1.5;
    }

    .item-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
    }

    .item-date {
        font-size: 0.8rem;
        color: #999;
    }

    .btn-read {
        background: none;
        border: none;
        color: #5575a2;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        padding: 0.25rem 0.75rem;
        text-decoration: none;
    }

    .btn-read:hover {
        color: #3d4a68;
        background: rgba(85, 117, 162, 0.1);
        border-radius: 0.4rem;
    }

    /* Audit Info Styles */
    .audit-info {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 0.75rem;
        padding-top: 0.75rem;
        border-top: 1px dashed #e8ecf1;
    }

    .audit-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: #f1f5f9;
        color: #475569;
        padding: 0.25rem 0.6rem;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
        border: 1px solid #e2e8f0;
    }

    .audit-badge svg {
        opacity: 0.7;
    }

    /* Empty State */
    .empty-notif {
        text-align: center;
        padding: 3rem 2rem;
    }

    .empty-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
    }

    .empty-notif h3 {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1a1a1a;
        margin: 0 0 0.5rem 0;
    }

    .empty-notif p {
        font-size: 0.9rem;
        color: #999;
        margin: 0;
    }

    .pagination-container {
        margin-top: 2rem;
        display: flex;
        justify-content: center;
    }

    @media (max-width: 1024px) {
        .stats-container {
            grid-template-columns: repeat(2, 1fr);
        }

        .section-title-bar {
            flex-direction: column;
            align-items: flex-start;
        }
    }

    @media (max-width: 768px) {
        .notifikasi-wrapper {
            padding: 1rem;
        }

        .stats-container {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .stat-card {
            padding: 1rem;
        }

        .stat-number {
            font-size: 1.5rem;
        }

        .notifikasi-section {
            padding: 1.5rem;
        }

        .item-header {
            flex-wrap: wrap;
        }

        .item-footer {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>
@endsection
