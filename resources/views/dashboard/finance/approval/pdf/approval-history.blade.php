<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Riwayat Approval Finance</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 24px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .summary {
            margin-bottom: 16px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .summary p {
            margin: 4px 0;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .amount {
            text-align: right;
        }
        .footer {
            margin-top: 24px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Riwayat Approval Finance</h1>
        <p>Periode: {{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}</p>
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="summary">
        <p>Total Nominal: Rp {{ number_format($totalNominal, 0, ',', '.') }}</p>
        <p>Jumlah Pengajuan: {{ $pengajuans->count() }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No. Pengajuan</th>
                <th>Pegawai</th>
                <th>Departemen</th>
                <th>Vendor</th>
                <th>Nominal</th>
                <th>Status</th>
                <th>Tgl Diproses</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pengajuans as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->nomor_pengajuan }}</td>
                    <td>{{ $item->user->name ?? '-' }}</td>
                    <td>{{ $item->departemen->nama_departemen ?? '-' }}</td>
                    <td>{{ $item->nama_vendor ?? '-' }}</td>
                    <td class="amount">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                    <td>{{ $item->status->label() }}</td>
                    <td>{{ $item->tanggal_disetujui_finance ? $item->tanggal_disetujui_finance->format('d/m/Y') : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Laporan ini dihasilkan otomatis oleh sistem Reimbursement</p>
    </div>
</body>
</html>
