<!DOCTYPE html>
<html>
<head>
    <title>Laporan Pengajuan Reimbursement</title>
    <style>
        body { 
            font-family: sans-serif; 
            font-size: 10pt; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px; 
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 8px; 
            text-align: left; 
        }
        th { 
            background-color: #f2f2f2; 
            font-weight: bold;
        }
        .text-right { 
            text-align: right; 
        }
        .header { 
            text-align: center; 
            margin-bottom: 20px; 
        }
        .subtitle { 
            font-size: 0.9em; 
            color: #666; 
        }
        .summary {
            margin-bottom: 20px;
            background-color: #f9f9f9;
            padding: 10px;
            border: 1px solid #ddd;
        }
        .summary-item {
            display: inline-block;
            width: 45%;
            margin-bottom: 5px;
        }
        .text-mono {
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Pengajuan Reimbursement</h2>
        <div class="subtitle">
            Periode: 
            @if(request('tanggal_from') && request('tanggal_to'))
                {{ \Carbon\Carbon::parse(request('tanggal_from'))->format('d/m/Y') }} - {{ \Carbon\Carbon::parse(request('tanggal_to'))->format('d/m/Y') }}
            @else
                Semua Periode
            @endif
        </div>
    </div>

    <div class="summary">
        <div class="summary-item">
            <strong>Pegawai:</strong> {{ $user->name }}
        </div>
        <div class="summary-item">
            <strong>NIP:</strong> {{ $user->nip ?? '-' }}
        </div>
        <div class="summary-item">
            <strong>Email:</strong> {{ $user->email }}
        </div>
        <div class="summary-item">
            <strong>Departemen:</strong> {{ $user->departemen->nama_departemen ?? '-' }}
        </div>
        <div class="summary-item">
            <strong>Tanggal Cetak:</strong> {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No. Pengajuan</th>
                <th>Tanggal</th>
                <th>Vendor</th>
                <th>Kategori</th>
                <th>Keterangan</th>
                <th class="text-right">Nominal</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @forelse($pengajuanList as $index => $pengajuan)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="text-mono">{{ $pengajuan->nomor_pengajuan }}</td>
                    <td>{{ \Carbon\Carbon::parse($pengajuan->tanggal_pengajuan)->format('d/m/Y') }}</td>
                    <td>{{ $pengajuan->nama_vendor }}</td>
                    <td>{{ $pengajuan->kategori->nama_kategori ?? '-' }}</td>
                    <td>{{ Str::limit($pengajuan->deskripsi, 50) }}</td>
                    <td class="text-right">Rp {{ number_format($pengajuan->nominal, 0, ',', '.') }}</td>
                    <td>
                        {{ $pengajuan->status->label() }}
                    </td>
                </tr>
                @php $total += $pengajuan->nominal; @endphp
            @empty
                <tr>
                    <td colspan="8" style="text-align: center;">Tidak ada data pengajuan</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="6" class="text-right">Total</th>
                <th class="text-right">Rp {{ number_format($total, 0, ',', '.') }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>
</body>
</html>