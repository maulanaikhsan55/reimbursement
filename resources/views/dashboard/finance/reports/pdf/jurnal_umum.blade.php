<!DOCTYPE html>
<html>
<head>
    <title>Laporan Jurnal Umum</title>
    <style>
        body { font-family: sans-serif; font-size: 10pt; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .header { text-align: center; margin-bottom: 20px; }
        .subtitle { font-size: 0.9em; color: #666; }
        .text-mono { font-family: monospace; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Jurnal Umum</h2>
        <div class="subtitle">Periode: {{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>No. Ref</th>
                <th>Kode COA</th>
                <th>Nama Akun</th>
                <th>Deskripsi</th>
                <th>Debit</th>
                <th>Kredit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($groupedJournal as $group)
                @foreach($group['entries'] as $index => $entry)
                    <tr>
                        <td>{{ $index === 0 ? $entry->tanggal_posting->format('d/m/Y') : '' }}</td>
                        <td>{{ $index === 0 ? $entry->nomor_ref : '' }}</td>
                        <td class="text-mono">{{ $entry->coa->kode_coa }}</td>
                        <td style="{{ $entry->tipe_posting === 'credit' ? 'padding-left: 20px;' : '' }}">
                            {{ $entry->coa->nama_coa }}
                        </td>
                        <td>{{ $index === 0 ? $entry->deskripsi : '' }}</td>
                        <td class="text-right">
                            {{ $entry->tipe_posting === 'debit' ? 'Rp ' . number_format($entry->nominal, 0, ',', '.') : '-' }}
                        </td>
                        <td class="text-right">
                            {{ $entry->tipe_posting === 'credit' ? 'Rp ' . number_format($entry->nominal, 0, ',', '.') : '-' }}
                        </td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" class="text-right">Total</th>
                <th class="text-right">Rp {{ number_format($totalDebit, 0, ',', '.') }}</th>
                <th class="text-right">Rp {{ number_format($totalCredit, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>
</body>
</html>