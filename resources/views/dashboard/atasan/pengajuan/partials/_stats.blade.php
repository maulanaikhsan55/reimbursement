<div class="stats-grid">
    <div class="stat-card modern">
        <div class="stat-left">
            <div class="stat-value">Rp {{ number_format($stats->total_nominal ?? 0, 0, ',', '.') }}</div>
            <div class="stat-label">Total Pengajuan</div>
            <div class="stat-sub-label">{{ $stats->total ?? 0 }} Pengajuan</div>
        </div>
        <div class="stat-icon primary-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
            </svg>
        </div>
    </div>

    <div class="stat-card modern">
        <div class="stat-left">
            <div class="stat-value">Rp {{ number_format($stats->nominal_pending ?? 0, 0, ',', '.') }}</div>
            <div class="stat-label">Sedang Diproses</div>
            <div class="stat-sub-label">{{ $stats->pending ?? 0 }} Pengajuan</div>
        </div>
        <div class="stat-icon primary-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
        </div>
    </div>

    <div class="stat-card modern">
        <div class="stat-left">
            <div class="stat-value">Rp {{ number_format($stats->nominal_approved ?? 0, 0, ',', '.') }}</div>
            <div class="stat-label">Total Dicairkan</div>
            <div class="stat-sub-label">{{ $stats->approved ?? 0 }} Pengajuan</div>
        </div>
        <div class="stat-icon success-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
        </div>
    </div>

    <div class="stat-card modern">
        <div class="stat-left">
            <div class="stat-value">{{ $stats->rejected ?? 0 }}</div>
            <div class="stat-label">Ditolak</div>
        </div>
        <div class="stat-icon danger-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
        </div>
    </div>
</div>
