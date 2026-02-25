<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use League\Csv\Writer;

class ReportExportService
{
    /**
     * Export data to CSV format
     */
    public function exportToCSV(string $filename, array $headers, iterable $data): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $downloadName = $this->normalizeExportFilename($filename, 'csv');

        return response()->streamDownload(function () use ($headers, $data) {
            // UTF-8 BOM to ensure Excel (especially locale ID) reads characters correctly.
            echo "\xEF\xBB\xBF";

            $csv = Writer::createFromPath('php://output', 'w');
            $csv->setDelimiter(';');
            $csv->setEnclosure('"');
            $csv->setEscape('\\');

            $csv->insertOne($this->normalizeCsvRow($headers));

            foreach ($data as $row) {
                $csv->insertOne($this->normalizeCsvRow($row));
            }
        }, $downloadName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    /**
     * Export data to native XLSX format.
     */
    public function exportToXlsx(string $filename, array $headers, iterable $data, array $options = []): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $downloadName = $this->normalizeExportFilename($filename, 'xlsx');

        $rows = [];
        $rows[] = $this->normalizeXlsxRow($headers);

        foreach ($data as $row) {
            $rows[] = $this->normalizeXlsxRow($row);
        }

        $sheetName = $this->sanitizeSheetName($options['sheet_name'] ?? 'Laporan');
        $xlsxBinary = $this->buildSimpleZipBinary([
            '[Content_Types].xml' => $this->buildXlsxContentTypesXml(),
            '_rels/.rels' => $this->buildXlsxRootRelsXml(),
            'xl/workbook.xml' => $this->buildXlsxWorkbookXml($sheetName),
            'xl/_rels/workbook.xml.rels' => $this->buildXlsxWorkbookRelsXml(),
            'xl/styles.xml' => $this->buildXlsxStylesXml(),
            'xl/worksheets/sheet1.xml' => $this->buildXlsxSheetXml($rows),
        ]);

        return response()->streamDownload(function () use ($xlsxBinary) {
            echo $xlsxBinary;
        }, $downloadName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    /**
     * Export data to PDF format
     */
    public function exportToPDF(string $filename, string $viewPath, array $data, array $options = []): \Illuminate\Http\Response
    {
        $downloadName = $this->normalizeExportFilename($filename, 'pdf');
        $orientation = $options['orientation'] ?? 'portrait';
        $html = view($viewPath, $data)->render();
        $html = $this->injectProfessionalPdfStyles($html);

        $pdf = Pdf::loadHTML($html)
            ->setPaper('a4', $orientation)
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10)
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        return $pdf->download($downloadName);
    }

    /**
     * Format currency to string for export
     */
    public static function formatCurrency($value): string
    {
        $amount = (float) ($value ?? 0);
        $formatted = number_format(abs($amount), 0, ',', '.');

        if ($amount < 0) {
            return '(Rp '.$formatted.')';
        }

        return 'Rp '.$formatted;
    }

    /**
     * Format date for export
     */
    public static function formatDate($date, $format = 'd/m/Y'): string
    {
        return \Carbon\Carbon::parse($date)->format($format);
    }

    /**
     * Alias helper for accounting-style number presentation.
     */
    public static function formatAccountingAmount($value): string
    {
        return self::formatCurrency($value);
    }

    /**
     * Build export filename in stable format:
     * {base}_{YYYYMMDD_HHMMSSmmm}_{token}.{ext}
     */
    public function buildExportFilename(string $baseName, string $extension): string
    {
        $safeBase = $this->sanitizeFilenamePart($baseName);
        $safeBase = $this->stripTrailingTimestamp($safeBase);
        if ($safeBase === '') {
            $safeBase = 'export';
        }

        $safeExtension = $this->sanitizeFilenamePart($extension);
        if ($safeExtension === '') {
            $safeExtension = 'txt';
        }

        $timestamp = now(config('app.timezone', 'Asia/Jakarta'))->format('Ymd_Hisv');
        $token = strtolower(substr((string) Str::ulid(), -6));

        return "{$safeBase}_{$timestamp}_{$token}.{$safeExtension}";
    }

    /**
     * Normalize row values for CSV output.
     */
    private function normalizeCsvRow(iterable $row): array
    {
        $normalized = [];

        foreach ($row as $value) {
            $normalized[] = $this->normalizeCsvValue($value);
        }

        return $normalized;
    }

    /**
     * Normalize row values for XLSX output.
     */
    private function normalizeXlsxRow(iterable $row): array
    {
        $normalized = [];

        foreach ($row as $value) {
            $normalized[] = $this->normalizeCsvValue($value);
        }

        return $normalized;
    }

    /**
     * Ensure each CSV cell is safe and readable in spreadsheet apps.
     */
    private function normalizeCsvValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? 'Ya' : 'Tidak';
        }

        if (is_scalar($value)) {
            $stringValue = (string) $value;
        } else {
            $stringValue = json_encode($value, JSON_UNESCAPED_UNICODE) ?: '';
        }

        // Keep one-line rows (prevents broken lines in CSV consumers).
        $stringValue = preg_replace('/\R+/u', ' ', $stringValue) ?? $stringValue;
        $stringValue = trim($stringValue);

        // Spreadsheet formula injection hardening.
        if ($stringValue !== '' && preg_match('/^[=\-+@]/', $stringValue)) {
            $stringValue = "'".$stringValue;
        }

        return $stringValue;
    }

    private function normalizeExportFilename(string $filename, string $defaultExtension): string
    {
        $base = pathinfo($filename, PATHINFO_FILENAME);
        $extension = pathinfo($filename, PATHINFO_EXTENSION) ?: $defaultExtension;

        return $this->buildExportFilename($base, $extension);
    }

    private function sanitizeFilenamePart(string $value): string
    {
        $normalized = preg_replace('/[^a-zA-Z0-9._-]+/', '_', trim($value)) ?? '';
        $normalized = preg_replace('/_+/', '_', $normalized) ?? '';
        $normalized = trim($normalized, '._-');

        return substr($normalized, 0, 120);
    }

    private function stripTrailingTimestamp(string $base): string
    {
        // old pattern: *_YYYY-mm-dd_HHiiSS
        $base = preg_replace('/[_-]\d{4}-\d{2}-\d{2}_\d{6}$/', '', $base) ?? $base;
        // old/new compact pattern: *_YYYYmmdd_HHiiss[mmm]
        $base = preg_replace('/[_-]\d{8}_\d{6,9}$/', '', $base) ?? $base;

        return trim($base, '_-');
    }

    /**
     * Inject a consistent professional report style for all PDF exports.
     */
    private function injectProfessionalPdfStyles(string $html): string
    {
        $style = <<<'CSS'
<style>
@page { margin: 22px 18px; }
body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 10.5px;
    color: #1f2937;
}
.header {
    text-align: center;
    margin-bottom: 16px;
    padding-bottom: 10px;
    border-bottom: 2px solid #425d87;
}
.header h1, .header h2 {
    margin: 0;
    color: #1f2e46;
    font-size: 16px;
    letter-spacing: 0.02em;
}
.header p, .subtitle {
    margin: 4px 0 0;
    color: #6b7280;
    font-size: 9.5px;
}
.summary {
    margin: 0 0 12px;
    padding: 8px 10px;
    border: 1px solid #d8e1ef;
    background: #f8fbff;
}
.summary p {
    margin: 2px 0;
    font-size: 10px;
}
.summary-item {
    display: inline-block;
    width: 48%;
    vertical-align: top;
    margin-bottom: 6px;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}
th, td {
    border: 1px solid #d7dee9;
    padding: 6px 7px;
    vertical-align: top;
}
th {
    background: #eef3fb;
    color: #2f3f5d;
    font-weight: 700;
    font-size: 9.5px;
}
tbody tr:nth-child(even) td {
    background: #fcfdff;
}
.amount, .text-right {
    text-align: right;
}
.text-mono {
    font-family: DejaVu Sans Mono, monospace;
}
.total-row {
    background: #eaf0fb !important;
    font-weight: 700;
}
.footer {
    margin-top: 14px;
    text-align: center;
    font-size: 9px;
    color: #6b7280;
}
</style>
CSS;

        if (stripos($html, '</head>') !== false) {
            return preg_replace('/<\/head>/i', $style.'</head>', $html, 1) ?? ($html.$style);
        }

        return $style.$html;
    }

    /**
     * Build [Content_Types].xml for XLSX package.
     */
    private function buildXlsxContentTypesXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
    <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
    <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>
XML;
    }

    /**
     * Build _rels/.rels for XLSX package.
     */
    private function buildXlsxRootRelsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>
XML;
    }

    /**
     * Build xl/workbook.xml for XLSX package.
     */
    private function buildXlsxWorkbookXml(string $sheetName): string
    {
        $escapedName = $this->escapeXml($sheetName);

        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheets>
        <sheet name="{$escapedName}" sheetId="1" r:id="rId1"/>
    </sheets>
</workbook>
XML;
    }

    /**
     * Build xl/_rels/workbook.xml.rels for XLSX package.
     */
    private function buildXlsxWorkbookRelsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
    <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>
XML;
    }

    /**
     * Build minimal style set for XLSX package.
     */
    private function buildXlsxStylesXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <fonts count="2">
        <font>
            <sz val="11"/>
            <name val="Calibri"/>
            <family val="2"/>
        </font>
        <font>
            <b/>
            <sz val="11"/>
            <name val="Calibri"/>
            <family val="2"/>
        </font>
    </fonts>
    <fills count="3">
        <fill><patternFill patternType="none"/></fill>
        <fill><patternFill patternType="gray125"/></fill>
        <fill>
            <patternFill patternType="solid">
                <fgColor rgb="FFEAF1FB"/>
                <bgColor indexed="64"/>
            </patternFill>
        </fill>
    </fills>
    <borders count="1">
        <border>
            <left/><right/><top/><bottom/><diagonal/>
        </border>
    </borders>
    <cellStyleXfs count="1">
        <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
    </cellStyleXfs>
    <cellXfs count="3">
        <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
        <xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1">
            <alignment horizontal="center" vertical="center"/>
        </xf>
        <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyAlignment="1">
            <alignment vertical="top" wrapText="1"/>
        </xf>
    </cellXfs>
    <cellStyles count="1">
        <cellStyle name="Normal" xfId="0" builtinId="0"/>
    </cellStyles>
</styleSheet>
XML;
    }

    /**
     * Build xl/worksheets/sheet1.xml with inline string cells.
     */
    private function buildXlsxSheetXml(array $rows): string
    {
        $rowCount = max(count($rows), 1);
        $maxColumns = 1;

        foreach ($rows as $row) {
            $maxColumns = max($maxColumns, count($row));
        }

        $lastColumn = $this->toExcelColumnName($maxColumns);
        $dimensionRef = 'A1:'.$lastColumn.$rowCount;
        $sheetRowsXml = '';

        foreach ($rows as $rowIndex => $row) {
            $rowNumber = $rowIndex + 1;
            $sheetRowsXml .= '<row r="'.$rowNumber.'">';

            $cellCount = max(count($row), $maxColumns);
            for ($colIndex = 0; $colIndex < $cellCount; $colIndex++) {
                $cellRef = $this->toExcelColumnName($colIndex + 1).$rowNumber;
                $cellStyle = $rowNumber === 1 ? 1 : 2;
                $value = $row[$colIndex] ?? '';
                $escapedValue = $this->escapeXml((string) $value);

                $sheetRowsXml .= '<c r="'.$cellRef.'" t="inlineStr" s="'.$cellStyle.'"><is><t xml:space="preserve">'.$escapedValue.'</t></is></c>';
            }

            $sheetRowsXml .= '</row>';
        }

        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <dimension ref="{$dimensionRef}"/>
    <sheetViews>
        <sheetView workbookViewId="0">
            <pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/>
        </sheetView>
    </sheetViews>
    <sheetFormatPr defaultRowHeight="15"/>
    <sheetData>{$sheetRowsXml}</sheetData>
    <autoFilter ref="A1:{$lastColumn}1"/>
</worksheet>
XML;
    }

    /**
     * Convert 1-based column index to Excel column letters.
     */
    private function toExcelColumnName(int $index): string
    {
        $name = '';
        while ($index > 0) {
            $index--;
            $name = chr(($index % 26) + 65).$name;
            $index = (int) floor($index / 26);
        }

        return $name ?: 'A';
    }

    /**
     * Escape value for XML output.
     */
    private function escapeXml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    /**
     * Sanitize worksheet name for XLSX limits.
     */
    private function sanitizeSheetName(string $name): string
    {
        $name = preg_replace('/[\\\[\]\*\/\?:]+/u', ' ', $name) ?? 'Sheet1';
        $name = trim($name);
        if ($name === '') {
            $name = 'Sheet1';
        }

        return substr($name, 0, 31);
    }

    /**
     * Build ZIP binary without requiring ZipArchive extension.
     */
    private function buildSimpleZipBinary(array $files): string
    {
        $localFileSection = '';
        $centralDirectory = '';
        $offset = 0;
        [$dosTime, $dosDate] = $this->toDosDateTime(time());

        foreach ($files as $path => $content) {
            $path = str_replace('\\', '/', $path);
            $content = (string) $content;
            $nameLength = strlen($path);
            $contentLength = strlen($content);
            $crc = (int) sprintf('%u', crc32($content));

            $localHeader = pack(
                'VvvvvvVVVvv',
                0x04034b50,
                20,
                0,
                0,
                $dosTime,
                $dosDate,
                $crc,
                $contentLength,
                $contentLength,
                $nameLength,
                0
            );

            $localRecord = $localHeader.$path.$content;
            $localFileSection .= $localRecord;

            $centralDirectory .= pack(
                'VvvvvvvVVVvvvvvVV',
                0x02014b50,
                20,
                20,
                0,
                0,
                $dosTime,
                $dosDate,
                $crc,
                $contentLength,
                $contentLength,
                $nameLength,
                0,
                0,
                0,
                0,
                0,
                $offset
            ).$path;

            $offset += strlen($localRecord);
        }

        $entries = count($files);
        $centralSize = strlen($centralDirectory);
        $centralOffset = strlen($localFileSection);

        $endOfCentralDirectory = pack(
            'VvvvvVVv',
            0x06054b50,
            0,
            0,
            $entries,
            $entries,
            $centralSize,
            $centralOffset,
            0
        );

        return $localFileSection.$centralDirectory.$endOfCentralDirectory;
    }

    /**
     * Convert unix timestamp to DOS date/time values used in ZIP headers.
     *
     * @return array{0:int,1:int}
     */
    private function toDosDateTime(int $timestamp): array
    {
        $year = (int) date('Y', $timestamp);
        $month = (int) date('n', $timestamp);
        $day = (int) date('j', $timestamp);
        $hour = (int) date('G', $timestamp);
        $minute = (int) date('i', $timestamp);
        $second = (int) date('s', $timestamp);

        if ($year < 1980) {
            $year = 1980;
            $month = 1;
            $day = 1;
            $hour = 0;
            $minute = 0;
            $second = 0;
        }

        $dosTime = ($hour << 11) | ($minute << 5) | intdiv($second, 2);
        $dosDate = (($year - 1980) << 9) | ($month << 5) | $day;

        return [$dosTime, $dosDate];
    }
}
