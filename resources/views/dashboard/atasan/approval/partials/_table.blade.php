@if($pengajuanList->isEmpty())
    <div class="empty-state">
        <div class="empty-state-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
            </svg>
        </div>
        @if(request()->anyFilled(['search', 'tanggal_from', 'tanggal_to', 'status']))
            <div class="empty-state-title">Tidak ada hasil</div>
            <p>Coba ubah filter pencarian Anda</p>
        @else
            <div class="empty-state-title">Belum ada pengajuan</div>
            <p>Tidak ada pengajuan yang menunggu persetujuan saat ini</p>
        @endif
    </div>
@else
    <div class="data-table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="col-no-pengajuan">No. Pengajuan</th>
                    <th class="col-tanggal">Tanggal</th>
                    <th class="col-staff">Staff</th>
                    <th class="col-vendor">Vendor</th>
                    <th class="col-nominal">Nominal</th>
                    <th class="col-status">Status</th>
                    <th class="col-ai">Validasi AI</th>
                    <th class="col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pengajuanList as $pengajuan)
                    <tr>
                        <td data-label="No. Pengajuan">
                            <span class="code-badge">{{ $pengajuan->nomor_pengajuan }}</span>
                        </td>
                        <td data-label="Tanggal" class="col-tanggal">
                            <span class="text-secondary">{{ $pengajuan->tanggal_pengajuan->format('d M Y') }}</span>
                        </td>
                        <td data-label="Staff">
                            <div class="table-text-strong">{{ $pengajuan->user->name }}</div>
                        </td>
                        <td data-label="Vendor">
                            <div class="table-text-strong">{{ $pengajuan->nama_vendor }}</div>
                        </td>
                        <td data-label="Nominal" class="col-nominal">
                            <span class="amount-text amount-text-strong">{{ format_rupiah($pengajuan->nominal) }}</span>
                        </td>
                        <td data-label="Status" class="col-status">
                            <x-status-badge :status="$pengajuan->status" :transactionId="$pengajuan->accurate_transaction_id" />
                        </td>
                        <td data-label="Validasi AI" class="col-ai">
                            @php
                                // Get any validation record for this pengajuan to determine status
                                $validasi = $pengajuan->validasiAi->first();
                            @endphp
                            <x-ai-validation-status :status="$validasi?->status" />
                        </td>
                        <td data-label="Aksi" class="col-aksi">
                            <div class="action-buttons-centered">
                                <x-action-icon
                                    :href="route('atasan.approval.show', $pengajuan->pengajuan_id)"
                                    :variant="$pengajuan->status->value === 'menunggu_atasan' ? 'approve' : 'view'"
                                    :title="$pengajuan->status->value === 'menunggu_atasan' ? 'Persetujuan' : 'Lihat Detail'"
                                />
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
