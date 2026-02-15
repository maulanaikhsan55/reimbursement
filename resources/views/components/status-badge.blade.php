@props(['status', 'transactionId' => null])

@php
    $statusValue = $status instanceof \UnitEnum ? $status->value : $status;
    $normalizedStatus = str_replace('_', '-', $statusValue);
    
    $labels = [
        'draft' => 'Draft',
        'validasi_ai' => 'Validasi AI',
        'menunggu_atasan' => 'Menunggu Atasan',
        'ditolak_atasan' => 'Ditolak Atasan',
        'menunggu_finance' => 'Menunggu Finance',
        'ditolak_finance' => 'Ditolak Finance',
        'terkirim_accurate' => 'Disetujui',
        'dicairkan' => 'Dicairkan',
        'void_accurate' => 'Void',
        'selesai' => 'Selesai'
    ];

    $label = $labels[$statusValue] ?? ucfirst(str_replace('_', ' ', $statusValue));
    $colorClass = 'status-' . $normalizedStatus;
@endphp

<div class="status-badge-wrapper" style="display: inline-flex; flex-direction: column; align-items: center; gap: 4px;">
    <span {{ $attributes->merge(['class' => 'status-badge ' . $colorClass]) }}>
        {{ $label }}
    </span>
    @if ($transactionId && in_array($statusValue, ['terkirim_accurate', 'dicairkan']))
        <span class="status-transaction-id" style="font-size: 0.65rem; color: #94a3b8; font-weight: 600; font-family: 'JetBrains Mono', monospace; letter-spacing: 0.02em;">
            {{ $transactionId }}
        </span>
    @endif
</div>
