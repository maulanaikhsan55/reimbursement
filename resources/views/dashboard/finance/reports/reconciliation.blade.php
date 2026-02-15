@extends('layouts.app')

@section('title', 'Auto-Reconciliation Dashboard')

@push('styles')
<style>
    .recon-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        margin-top: 1rem;
    }
    @media (max-width: 992px) {
        .recon-grid {
            grid-template-columns: 1fr;
        }
    }
    .recon-card {
        background: white;
        border-radius: 1.25rem;
        padding: 1.25rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        border: 1px solid #eef2f7;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .recon-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 30px rgba(66, 93, 135, 0.1);
    }
    .recon-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }
    .recon-info h3 {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0 0 0.25rem 0;
    }
    .recon-info p {
        font-size: 0.85rem;
        color: #64748b;
        margin: 0;
    }
    .recon-status {
        padding: 0.4rem 0.75rem;
        border-radius: 2rem;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    .status-match {
        background: #f0fdf4;
        color: #16a34a;
        border: 1px solid #bbf7d0;
    }
    .status-mismatch {
        background: #fff1f2;
        color: #e11d48;
        border: 1px solid #fecdd3;
    }
    .status-unknown {
        background: #f8fafc;
        color: #64748b;
        border: 1px solid #e2e8f0;
    }
    .balance-comparison {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-top: 1rem;
        padding: 1rem;
        background: #f8fafc;
        border-radius: 0.75rem;
    }
    .balance-item label {
        display: block;
        font-size: 0.75rem;
        color: #64748b;
        margin-bottom: 0.25rem;
    }
    .balance-item .value {
        font-size: 1rem;
        font-weight: 700;
        color: #1e293b;
    }
    .diff-section {
        margin-top: 1rem;
        padding: 0.75rem 1rem;
        border-radius: 0.75rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .diff-match { background: #f0fdf4; color: #16a34a; font-size: 0.85rem; }
    .diff-mismatch { background: #fff1f2; color: #e11d48; font-size: 0.85rem; }
    
    .recon-actions {
        margin-top: 1.5rem;
        display: flex;
        gap: 0.75rem;
    }
    .btn-recon {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.6rem;
        border-radius: 0.75rem;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        border: 1px solid transparent;
    }
    .btn-check { background: #425d87; color: white; }
    .btn-check:hover { background: #3c5379; }
    .btn-details { background: white; color: #425d87; border-color: #d0d9e7; }
    .btn-details:hover { background: #f8fafc; }

    /* Skeleton Loader */
    .skeleton {
        background: linear-gradient(90deg, #f0f3f8 25%, #e6ecf5 50%, #f0f3f8 75%);
        background-size: 200% 100%;
        animation: loading-shimmer 1.5s infinite;
    }
    @keyframes loading-shimmer {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
</style>
@endpush

@section('content')
<div class="dashboard-wrapper">
    <div class="dashboard-container">
        <x-page-header title="Auto-Reconciliation" subtitle="Sinkronisasi saldo Kas/Bank dengan Accurate Online" :showNotification="true" :showProfile="true" />

        <div class="dashboard-content">
            <div class="filter-container" style="margin-bottom: 2rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; gap: 2rem; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 300px;">
                        <div class="search-group">
                            <div class="search-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                            </div>
                            <input type="text" id="reconSearch" class="filter-input-pegawai search-input" placeholder="Cari Nama Akun atau Kode..." onkeyup="filterRecon()" autocomplete="off">
                        </div>
                    </div>
                    <div class="header-actions">
                        <div class="coa-summary-badge" style="background: #425d87; color: white; padding: 10px 20px; border-radius: 50px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                            {{ $reconData->count() }} Akun Terhubung
                        </div>
                    </div>
                </div>
            </div>

            <div class="recon-grid" id="reconGrid">
            @foreach($reconData as $data)
                <div class="recon-card" id="card-{{ $data['kas_bank_id'] }}">
                    <div class="recon-header">
                        <div class="recon-info">
                            <h3>{{ $data['nama'] }}</h3>
                            <p>{{ $data['kode'] }}</p>
                        </div>
                        <span class="recon-status status-unknown" id="status-{{ $data['kas_bank_id'] }}">Menunggu Cek</span>
                    </div>

                    <div class="balance-comparison">
                        <div class="balance-item">
                            <label>Saldo Lokal (Web)</label>
                            <div class="value" id="local-val-{{ $data['kas_bank_id'] }}">Rp {{ number_format($data['local_balance'], 0, ',', '.') }}</div>
                        </div>
                        <div class="balance-item">
                            <label>Saldo Accurate</label>
                            <div class="value" id="accurate-val-{{ $data['kas_bank_id'] }}">-</div>
                        </div>
                    </div>

                    <div class="diff-section" id="diff-section-{{ $data['kas_bank_id'] }}" style="display: none;">
                        <span id="diff-text-{{ $data['kas_bank_id'] }}"></span>
                        <span style="font-weight: 700;" id="diff-val-{{ $data['kas_bank_id'] }}"></span>
                    </div>

                    <div class="recon-actions">
                        <button type="button" class="btn-recon btn-check" onclick="checkReconciliation('{{ $data['kas_bank_id'] }}', '{{ $data['coa_id'] }}', {{ $data['local_balance'] }})">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 11a8.1 8.1 0 0 0-15.5-2m-.5 5v-5h5"></path>
                                <path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5-5v5h-5"></path>
                            </svg>
                            Cek Sekarang
                        </button>
                        <a href="{{ route('finance.report.buku_besar', ['coa_id' => $data['coa_id']]) }}" class="btn-recon btn-details">
                            Buku Besar
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
        </div>
    </div>
</div>

<x-proof-modal />

@push('scripts')
<script>
(function() {
    const initReconciliation = () => {
        window.filterRecon = function() {
            const input = document.getElementById('reconSearch');
            if (!input) return;
            const filter = input.value.toLowerCase();
            const grid = document.getElementById('reconGrid');
            if (!grid) return;
            const cards = grid.getElementsByClassName('recon-card');

            for (let i = 0; i < cards.length; i++) {
                const card = cards[i];
                const name = card.querySelector('.recon-info h3').innerText.toLowerCase();
                const code = card.querySelector('.recon-info p').innerText.toLowerCase();
                
                if (name.indexOf(filter) > -1 || code.indexOf(filter) > -1) {
                    card.style.display = "";
                } else {
                    card.style.display = "none";
                }
            }
        };

        window.checkReconciliation = function(kbId, coaId, localBalance) {
            const btn = document.querySelector(`#card-${kbId} .btn-check`);
            const statusBadge = document.getElementById(`status-${kbId}`);
            const accurateVal = document.getElementById(`accurate-val-${kbId}`);
            const diffSection = document.getElementById(`diff-section-${kbId}`);
            const diffText = document.getElementById(`diff-text-${kbId}`);
            const diffVal = document.getElementById(`diff-val-${kbId}`);
            
            if (!btn) return;

            // Loading state
            btn.disabled = true;
            btn.innerHTML = '<span class="clip-loader" style="width: 14px; height: 14px; border-width: 2px;"></span> Memeriksa...';
            if (statusBadge) {
                statusBadge.className = 'recon-status status-unknown';
                statusBadge.innerText = 'Memeriksa...';
            }
            
            fetch(`{{ route('finance.report.buku_besar.reconcile') }}?coa_id=${coaId}&local_balance=${localBalance}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (accurateVal) accurateVal.innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(data.accurate_balance);
                        
                        // Update tampilan saldo lokal jika berubah setelah sinkronisasi otomatis
                        const localValElem = document.getElementById(`local-val-${kbId}`);
                        if (localValElem) localValElem.innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(data.local_balance);
                        
                        // Update attribute onclick tombol agar menggunakan saldo terbaru untuk pengecekan berikutnya
                        btn.setAttribute('onclick', `checkReconciliation('${kbId}', '${coaId}', ${data.local_balance})`);

                        if (diffSection) {
                            diffSection.style.display = 'flex';
                            if (data.is_match) {
                                if (statusBadge) {
                                    statusBadge.className = 'recon-status status-match';
                                    statusBadge.innerText = 'Match';
                                }
                                diffSection.className = 'diff-section diff-match';
                                if (diffText) diffText.innerText = 'Sesuai';
                                if (diffVal) diffVal.innerText = 'Selisih: Rp 0';
                            } else {
                                if (statusBadge) {
                                    statusBadge.className = 'recon-status status-mismatch';
                                    statusBadge.innerText = 'Mismatch';
                                }
                                diffSection.className = 'diff-section diff-mismatch';
                                if (diffText) diffText.innerText = 'Selisih';
                                if (diffVal) diffVal.innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(data.diff);
                                
                                if (data.discrepancies && data.discrepancies.length > 0) {
                                    showDiscrepancies(coaId, data.discrepancies);
                                }
                            }
                        }
                    } else {
                        if (window.showNotification) window.showNotification('error', 'Gagal', data.message);
                        if (statusBadge) statusBadge.innerText = 'Error';
                    }
                })
                .catch(err => {
                    console.error(err);
                    if (window.showNotification) window.showNotification('error', 'Error', 'Terjadi kesalahan saat menghubungi server.');
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = `
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 11a8.1 8.1 0 0 0-15.5-2m-.5 5v-5h5"></path>
                            <path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5-5v5h-5"></path>
                        </svg>
                        Cek Lagi
                    `;
                });
        };

        window.showDiscrepancies = function(coaId, list) {
            const container = document.getElementById('discrepancyList');
            if (!container) return;
            container.innerHTML = '';
            
            list.forEach(item => {
                const row = document.createElement('div');
                row.style = 'display: flex; justify-content: space-between; align-items: center; padding: 1rem; border: 1px solid #e2e8f0; border-radius: 0.75rem; margin-bottom: 0.75rem;';
                row.innerHTML = `
                    <div>
                        <div style="font-weight: 700; color: #1e293b;">${item.number}</div>
                        <div style="font-size: 0.8rem; color: #64748b;">${item.date} • ${item.description || 'Tanpa keterangan'}</div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="font-weight: 700; color: #1e293b;">Rp ${new Intl.NumberFormat('id-ID').format(item.amount)}</div>
                        <button onclick="syncTransaction(this, '${coaId}', '${item.accurate_id}', '${item.trans_type}')" class="btn-modern btn-modern-primary btn-modern-sm">
                            Sinkronkan
                        </button>
                    </div>
                `;
                container.appendChild(row);
            });
            
            const modal = document.getElementById('discrepancyModal');
            if (modal) modal.style.display = 'flex';
        };

        window.syncTransaction = function(btn, coaId, accurateId, transType) {
            const originalContent = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="clip-loader" style="width: 12px; height: 12px; border-width: 2px; border-color: white; border-bottom-color: transparent;"></span> Syncing...';
            
            fetch('{{ route('finance.report.buku_besar.sync_missing') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    coa_id: coaId,
                    accurate_id: accurateId,
                    trans_type: transType
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    btn.parentElement.parentElement.style.opacity = '0.5';
                    btn.parentElement.innerHTML = '<span style="color: #16a34a; font-weight: 700;">Berhasil</span>';
                    if (window.showNotification) window.showNotification('success', 'Berhasil', 'Transaksi berhasil disinkronkan.');
                } else {
                    if (window.showNotification) window.showNotification('error', 'Gagal', data.message);
                    btn.disabled = false;
                    btn.innerHTML = originalContent;
                }
            })
            .catch(err => {
                console.error(err);
                if (window.showNotification) window.showNotification('error', 'Error', 'Gagal sinkronisasi data.');
                btn.disabled = false;
                btn.innerHTML = originalContent;
            });
        };

        window.closeDiscrepancyModal = function() {
            const modal = document.getElementById('discrepancyModal');
            if (modal) modal.style.display = 'none';
        };
    };

    // Initialize on load
    initReconciliation();

    // Re-initialize on Livewire navigation
    document.addEventListener('livewire:navigated', initReconciliation);
})();
</script>
@endpush
@endsection
