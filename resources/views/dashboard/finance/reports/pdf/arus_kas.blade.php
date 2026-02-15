<!DOCTYPE html>
<html>
<head>
    <title>Laporan Arus Kas</title>
    <style>
        body { 
            font-family: sans-serif; 
            font-size: 9pt; 
            margin: 20px;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px; 
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 6px; 
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
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Arus Kas</h2>
        <div class="subtitle">Periode: {{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}</div>
        <div class="subtitle">Total Transaksi: {{ $entries->count() }} entri</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 80px;">Tanggal</th>
                <th style="width: 100px;">No. Referensi</th>
                <th style="width: 100px;">Pengajuan</th>
                <th style="width: 90px;">Departemen</th>
                <th style="width: 90px;">Kategori</th>
                <th class="text-right" style="width: 100px;">Penerimaan</th>
                <th class="text-right" style="width: 100px;">Pengeluaran</th>
            </tr>
        </thead>
        <tbody>
            @foreach($entries as $entry)
                <tr>
                    <td>{{ $entry->tanggal_posting->format('d/m/Y') }}</td>
                    <td>{{ $entry->nomor_ref }}</td>
                    <td>{{ $entry->pengajuan ? $entry->pengajuan->nomor_pengajuan : '-' }}</td>
                    <td>{{ $entry->pengajuan && $entry->pengajuan->departemen ? $entry->pengajuan->departemen->nama_departemen : '-' }}</td>
                    <td>{{ $entry->pengajuan && $entry->pengajuan->kategori ? $entry->pengajuan->kategori->nama_kategori : '-' }}</td>
                    <td class="text-right">
                        {{ $entry->tipe_posting == 'debit' ? 'Rp ' . number_format($entry->nominal, 0, ',', '.') : '-' }}
                    </td>
                    <td class="text-right">
                        {{ $entry->tipe_posting == 'credit' ? 'Rp ' . number_format($entry->nominal, 0, ',', '.') : '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" class="text-right">Total</th>
                <th class="text-right">Rp {{ number_format($totalInflow, 0, ',', '.') }}</th>
                <th class="text-right">Rp {{ number_format($totalOutflow, 0, ',', '.') }}</th>
            </tr>
            <tr>
                <th colspan="5" class="text-right">Kenaikan/(Penurunan) Kas</th>
                <th colspan="2" class="text-right" style="background-color: {{ $totalInflow - $totalOutflow >= 0 ? '#f0fdf4' : '#fef2f2' }};">
                    Rp {{ number_format($totalInflow - $totalOutflow, 0, ',', '.') }}
                </th>
            </tr>
        </tfoot>
    </table>
</body>
</html>
