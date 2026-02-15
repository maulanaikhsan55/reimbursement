<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use League\Csv\Writer;

class ReportExportService
{
    /**
     * Export data to CSV format
     */
    public function exportToCSV(string $filename, array $headers, iterable $data): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $data) {
            $csv = Writer::createFromPath('php://output', 'w');
            $csv->insertOne($headers);

            foreach ($data as $row) {
                $csv->insertOne($row);
            }
        }, $filename, ['Content-Type' => 'text/csv; charset=utf-8']);
    }

    /**
     * Export data to PDF format
     */
    public function exportToPDF(string $filename, string $viewPath, array $data): \Illuminate\Http\Response
    {
        $pdf = Pdf::loadView($viewPath, $data)
            ->setPaper('a4')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10);

        return $pdf->download($filename);
    }

    /**
     * Format currency to string for export
     */
    public static function formatCurrency($value): string
    {
        return 'Rp '.number_format($value, 0, ',', '.');
    }

    /**
     * Format date for export
     */
    public static function formatDate($date, $format = 'd/m/Y'): string
    {
        return \Carbon\Carbon::parse($date)->format($format);
    }
}
