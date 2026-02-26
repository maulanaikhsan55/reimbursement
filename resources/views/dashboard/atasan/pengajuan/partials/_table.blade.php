@if($pengajuanList->total() === 0 && !request('search') && !request('status') && !request('tanggal_from') && !request('tanggal_to'))
    <div class="empty-state">
        <div class="empty-state-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="12" y1="18" x2="12" y2="12"></line>
                <line x1="9" y1="15" x2="15" y2="15"></line>
            </svg>
        </div>
        <div class="empty-state-title">Belum Ada Pengajuan</div>
        <p>Anda belum pernah membuat pengajuan reimbursement.</p>
        <div class="empty-state-actions">
            <a href="{{ route('atasan.pengajuan.create') }}" class="btn-modern btn-modern-primary">
                Buat Pengajuan Baru
            </a>
        </div>
    </div>
@elseif($pengajuanList->isEmpty())
    <div class="empty-state">
        <div class="empty-state-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
            </svg>
        </div>
        <div class="empty-state-title">Tidak ada hasil</div>
        <p>Coba ubah filter pencarian Anda</p>
    </div>
@else
    <div class="data-table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="col-no-pengajuan">No. Pengajuan</th>
                    <th class="col-vendor">Vendor</th>
                    <th class="col-tanggal">Tanggal</th>
                    <th class="col-nominal">Nominal</th>
                    <th class="col-status">Status</th>
                    <th class="col-ai">Validasi AI</th>
                    <th class="col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pengajuanList as $pengajuan)
                    <tr>
                        <td data-label="No. Pengajuan">
                            <span class="code-badge">
                                {{ $pengajuan->nomor_pengajuan }}
                            </span>
                        </td>
                        <td data-label="Vendor">
                            <div class="vendor-name">{{ $pengajuan->nama_vendor }}</div>
                        </td>
                        <td data-label="Tanggal" class="col-tanggal">
                            <span class="text-secondary">{{ $pengajuan->tanggal_pengajuan->format('d M Y') }}</span>
                        </td>
                        <td data-label="Nominal" class="col-nominal">
                            <span class="amount-text amount-text-strong">{{ format_rupiah($pengajuan->nominal) }}</span>
                        </td>
                        <td data-label="Status" class="col-status">
                            @if($pengajuan->status->value == 'validasi_ai')
                                <x-status-badge status="validasi_ai" />
                            @else
                                <x-status-badge :status="$pengajuan->status" :transactionId="$pengajuan->accurate_transaction_id" />
                            @endif
                        </td>
                        <td data-label="Validasi AI" class="col-ai">
                            @php $validasi = $pengajuan->validasiAi->where('jenis_validasi', 'ocr')->first(); @endphp
                            <x-ai-validation-status :status="$validasi?->status" />
                        </td>
                        <td data-label="Aksi" class="col-aksi">
                            <div class="action-buttons-centered">
                                <x-action-icon :href="route('atasan.pengajuan.show', $pengajuan->pengajuan_id)" title="Lihat detail" />
                                <x-action-icon
                                    :href="route('atasan.pengajuan.create', ['duplicate_id' => $pengajuan->pengajuan_id])"
                                    variant="duplicate"
                                    title="Ajukan lagi (Duplikat)"
                                />
                                @if(in_array($pengajuan->status->value, ['validasi_ai', 'menunggu_finance', 'ditolak_finance']))
                                <form action="{{ route('atasan.pengajuan.destroy', $pengajuan->pengajuan_id) }}" method="POST" class="inline-action-form">
                                    @csrf @method('DELETE')
                                    <x-action-icon
                                        variant="delete"
                                        title="Batalkan pengajuan"
                                        onclick="openConfirmModal(() => this.closest('form').submit(), 'Batalkan Pengajuan', 'Yakin ingin membatalkan pengajuan ini?')"
                                    />
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        {{ $pengajuanList->links('components.pagination') }}
    </div>
@endif
