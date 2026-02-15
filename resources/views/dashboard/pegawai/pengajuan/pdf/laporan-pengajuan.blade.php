<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pengajuan Reimbursement</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 20px;
            font-size: 11px;
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
        .summary-item {
            display: inline-block;
            width: 48%;
            margin-bottom: 8px;
            margin-right: 2%;
        }
        .summary-item strong {
            display: block;
            margin-bottom: 3px;
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
        .amount {
            text-align: right;
        }
        .total-row {
            background-color: #e9ecef;
            font-weight: bold;
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
        <h1>Laporan Pengajuan Reimbursement</h1>
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="summary">
        <div class="summary-item">
            <strong>Pegawai:</strong>
            {{ $user->name }}
        </div>
        <div class="summary-item">
            <strong>Email:</strong>
            {{ $user->email }}
        </div>
        <div class="summary-item">
            <strong>Departemen:</strong>
            {{ $user->departemen->nama_departemen ?? '-' }}
        </div>
        <div class="summary-item">
            <strong>Total Pengajuan:</strong>
            {{ $pengajuanList->count() }} item(s)
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nomor Pengajuan</th>
                <th>Vendor</th>
                <th>Tanggal</th>
                <th>Kategori Biaya</th>
                <th>Deskripsi</th>
                <th class="amount">Nominal</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @forelse($pengajuanList as $index => $pengajuan)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $pengajuan->nomor_pengajuan }}</td>
                <td>{{ $pengajuan->nama_vendor }}</td>
                <td>{{ $pengajuan->tanggal_pengajuan->format('d/m/Y') }}</td>
                <td>{{ $pengajuan->kategori ? $pengajuan->kategori->nama_kategori : '-' }}</td>
                <td>{{ Str::limit($pengajuan->deskripsi, 50) }}</td>
                <td class="amount">{{ number_format($pengajuan->nominal, 0, ',', '.') }}</td>
                <td>{{ $pengajuan->status->label() }}</td>
            </tr>
                @php $total += $pengajuan->nominal; @endphp
            @empty
            <tr>
                <td colspan="8" style="text-align: center;">Tidak ada data pengajuan</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="6" style="text-align: right;">TOTAL</td>
                <td class="amount">{{ number_format($total, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Laporan ini dihasilkan secara otomatis oleh sistem Reimbursement</p>
    </div>
</body>
</html>
