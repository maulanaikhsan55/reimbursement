<!DOCTYPE html>
<html>
<head>
    <title>Laporan Buku Besar</title>
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
        <h2>Laporan Buku Besar</h2>
        <div class="subtitle">Periode: {{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Kode Akun</th>
                <th>Nama Akun</th>
                <th>Debit</th>
                <th>Kredit</th>
                <th>Saldo</th>
                <th>Transaksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ledger as $data)
                <tr>
                    <td class="text-mono">{{ $data['coa']->kode_coa }}</td>
                    <td>{{ $data['coa']->nama_coa }}</td>
                    <td class="text-right">Rp {{ number_format($data['debit'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($data['credit'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($data['saldo'], 0, ',', '.') }}</td>
                    <td style="text-align: center;">{{ $data['count'] }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2" class="text-right">Total</th>
                <th class="text-right">Rp {{ number_format($totalDebit, 0, ',', '.') }}</th>
                <th class="text-right">Rp {{ number_format($totalCredit, 0, ',', '.') }}</th>
                <th class="text-right">Rp {{ number_format($totalDebit - $totalCredit, 0, ',', '.') }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>
</body>
</html>