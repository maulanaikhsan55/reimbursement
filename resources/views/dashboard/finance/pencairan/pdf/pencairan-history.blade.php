<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Riwayat Pencairan Dana</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
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
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .summary p {
            margin: 5px 0;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
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
        .total-row {
            background-color: #e9ecef;
            font-weight: bold;
        }
        .amount {
            text-align: right;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Riwayat Pencairan Dana</h1>
        <p>Periode: {{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}</p>
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="summary">
        <p>Total Pencairan: {{ number_format($totalNominal, 0, ',', '.') }}</p>
        <p>Jumlah Record: {{ $pengajuan->count() }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nomor Pengajuan</th>
                <th>Pegawai</th>
                <th>Departemen</th>
                <th>Kategori Biaya</th>
                <th>Deskripsi</th>
                <th>Nominal</th>
                <th>Tanggal Pengajuan</th>
                <th>Tanggal Pencairan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pengajuan as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->nomor_pengajuan }}</td>
                <td>{{ $item->user->name }}</td>
                <td>{{ $item->departemen->nama_departemen }}</td>
                <td>{{ $item->kategori ? $item->kategori->nama_kategori : '-' }}</td>
                <td>{{ $item->deskripsi }}</td>
                <td class="amount">{{ number_format($item->nominal, 0, ',', '.') }}</td>
                <td>{{ $item->tanggal_pengajuan->format('d/m/Y') }}</td>
                <td>{{ $item->tanggal_pencairan->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="6" style="text-align: right; font-weight: bold;">TOTAL</td>
                <td class="amount">{{ number_format($totalNominal, 0, ',', '.') }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Laporan ini dihasilkan secara otomatis oleh sistem Reimbursement</p>
    </div>
</body>
</html>
