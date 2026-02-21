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
        <p>Periode: <?php echo e($startDate->format('d/m/Y')); ?> - <?php echo e($endDate->format('d/m/Y')); ?></p>
        <p>Dicetak pada: <?php echo e(now()->format('d/m/Y H:i:s')); ?></p>
    </div>

    <div class="summary">
        <p>Total Nominal: Rp <?php echo e(number_format($totalNominal, 0, ',', '.')); ?></p>
        <p>Jumlah Pengajuan: <?php echo e($pengajuans->count()); ?></p>
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
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $pengajuans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($index + 1); ?></td>
                    <td><?php echo e($item->nomor_pengajuan); ?></td>
                    <td><?php echo e($item->user->name ?? '-'); ?></td>
                    <td><?php echo e($item->departemen->nama_departemen ?? '-'); ?></td>
                    <td><?php echo e($item->nama_vendor ?? '-'); ?></td>
                    <td class="amount">Rp <?php echo e(number_format($item->nominal, 0, ',', '.')); ?></td>
                    <td><?php echo e($item->status->label()); ?></td>
                    <td><?php echo e($item->tanggal_disetujui_finance ? $item->tanggal_disetujui_finance->format('d/m/Y') : '-'); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        <p>Laporan ini dihasilkan otomatis oleh sistem Reimbursement</p>
    </div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\reimbursement_ikhsansblm dipush\resources\views/dashboard/finance/approval/pdf/approval-history.blade.php ENDPATH**/ ?>