<?php

namespace App\Services;

use Illuminate\Support\Str;

class LocalReceiptParser
{
    /**
     * Parse raw OCR text to extract structural data
     */
    public function parse(string $rawText, ?string $transactionType = null): array
    {
        $lines = $this->splitLines($rawText);
        
        $vendor = $this->extractVendor($lines, $transactionType);
        $nominal = $this->extractNominal($lines);
        $date = $this->extractDate($lines);
        $time = $this->extractTime($lines);
        $invoiceNumber = $this->extractInvoiceNumber($lines);

        // Standardize vendor name
        $vendor = $this->standardizeVendorName($vendor);

        return [
            'vendor' => $vendor,
            'nominal' => $nominal,
            'date' => $date,
            'time' => $time,
            'invoice_number' => $invoiceNumber,
            'raw_lines' => $lines,
            'suggested_category' => $this->suggestCategory($vendor, $rawText)
        ];
    }

    private function extractInvoiceNumber(array $lines): ?string
    {
        $patterns = [
            '/(?:invoice|inv|no|ref|kuitansi|nota|transaksi)\s*(?:no|number|num)?[:.\-\s]*([A-Z0-9\-\/]{4,})/i',
            '/(?:no\.\s*)([A-Z0-9\-\/]{4,})/i',
            '/\b([A-Z]{2,}\d{4,})\b/', // e.g. ABC12345
            '/(?:ref)\s*[:.\-\s]*(\d{10,})/i', // Specifically for long bank ref numbers
        ];

        foreach ($lines as $line) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $line, $matches)) {
                    $val = trim($matches[1]);
                    // Clean up trailing punctuation
                    $val = rtrim($val, '.,:');
                    if (strlen($val) >= 4) return $val;
                }
            }
        }
        return null;
    }

    /**
     * Standardize Vendor Name (e.g. Go-Jek -> Gojek)
     * FIXED: Preserve proper brand casing (iPhone, eBay, etc.)
     */
    public function standardizeVendorName(?string $vendor): ?string
    {
        if (empty($vendor)) return null;

        $vendor = trim($vendor);
        
        // Remove common labels if they were captured
        $vendor = preg_replace('/^(merchant|penjual|toko|seller|outlet|vendor|supplier|penerima|tujuan)\b[:\s\-]*/i', '', $vendor);
        
        // Handle multi-column OCR (e.g. Tokopedia: Penjual: A [spaces] Pembeli: B)
        if (preg_match('/(.*)(?:pembeli|untuk|kepada|alamat pengiriman|customer)[:\s\-]+/i', $vendor, $parts)) {
            $vendor = trim($parts[1]);
        }

        $lower = strtolower($vendor);

        // FIXED: Preserve proper casing for brands
        $standardMap = [
            'Gojek' => ['go-jek', 'go jek', 'gojek indonesia'],
            'Grab' => ['grab indonesia', 'grabtaxi', 'grabfood', 'grab car', 'grab bike'],
            'TikTok' => ['tiktok shop', 'tiktok', 'tiktok local'], // Proper TikTok casing
            'Shopee' => ['shopee pay', 'shopeepay', 'shopee indonesia'],
            'Tokopedia' => ['tokopedia.com', 'pt tokopedia'],
            'Indomaret' => ['indomaret point', 'indomaret fresh'],
            'Alfamart' => ['alfamidi', 'alfa mart'],
            'Starbucks' => ['starbucks coffee', 'pt sari coffee indonesia'],
            'McDonald\'s' => ['mcdonalds', 'mcdonald', 'mcd'], // Proper McDonald's
            'KFC' => ['kfc', 'kentucky fried chicken'],
            'Kopi Kenangan' => ['kenangan coffee', 'kenangan'],
            'Pertamina' => ['spbu pertamina', 'pertamax'],
            'Blue Bird' => ['bluebird', 'pt blue bird'],
            'iPhone' => ['iphone', 'i phone'],
            'iPad' => ['ipad', 'i pad'],
            'BCA' => ['bank central asia', 'bca digital'],
            'BRI' => ['bank rakyat indonesia', 'bank bri'],
        ];

        foreach ($standardMap as $standard => $aliases) {
            // Check exact match with case-insensitive
            if ($lower === strtolower($standard)) return $standard;
            
            // Check if any alias matches
            foreach ($aliases as $alias) {
                if (str_contains($lower, $alias)) {
                    return $standard; // Return with proper casing
                }
            }
        }

        // If no match found, return with Title Case (but preserve known brands)
        return $vendor;
    }

    /**
     * Suggest Kategori Biaya based on vendor or keywords
     */
    public function suggestCategory(?string $vendor, string $rawText): ?string
    {
        $text = strtolower($vendor . ' ' . $rawText);
        
        $rules = [
            'Transport' => [
                'gojek', 'grab', 'blue bird', 'bluebird', 'taxi', 'ojek', 'pertamina', 'spbu', 'bensin', 'fuel', 'kereta', 'kai', 
                'penerbangan', 'flight', 'travel', 'pertamax', 'pertalite', 'shell', 'bp akr'
            ],
            'Parkir' => [
                'parkir', 'parking', 'karcis', 'secure parking', 'sky parking', 'mall', 'tol', 'gerbang tol', 'e-toll', 'jasa marga'
            ],
            'Konsumsi' => [
                'makan', 'minum', 'restoran', 'restaurant', 'cafe', 'kopi', 'coffee', 'starbucks', 
                'kenangan', 'janji jiwa', 'warung', 'food', 'bakmie', 'nasi', 'kuliner', 'catering',
                'gofood', 'grabfood', 'shopeefood', 'mcdonald', 'kfc', 'burger', 'pizza'
            ],
            'Operasional' => [
                'indomaret', 'alfamart', 'alfamidi', 'atk', 'kertas', 'tinta', 'printer', 'fotocopy', 
                'office', 'kantor', 'listrik', 'pln', 'pulsa', 'kuota', 'telkomsel', 'xl', 'indosat',
                'fotokopi', 'lakban', 'map', 'amplop', 'elektronik', 'computer', 'komputer', 'gadget'
            ],
            'Lain-lain' => [
                'admin', 'biaya transfer', 'biaya layanan'
            ]
        ];

        foreach ($rules as $category => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($text, $kw)) {
                    return $category;
                }
            }
        }

        return null;
    }

    private function splitLines(string $text): array
    {
        $lines = explode("\n", $text);
        return array_values(array_filter(array_map('trim', $lines), fn($l) => strlen($l) >= 2));
    }

    /**
     * Extract Vendor Name with Context Awareness
     * FIXED: Improved validation and greedy regex handling
     */
    private function extractVendor(array $lines, ?string $transactionType = null): ?string
    {
        // 0. HIGHEST PRIORITY: Look for "Pay to" or "Pembayaran ke" (Common in DANA/QRIS/Bank)
        foreach ($lines as $i => $line) {
            if (preg_match('/(pay to|pembayaran ke|bayar ke|tujuan|penerima|transfer ke|kirim ke|ke rekening|nama penerima|nama tujuan)\s*[:]?\s*(.{3,})/i', $line, $matches)) {
                $value = trim($matches[2]);
                
                // FIXED: Skip if value is just symbols/whitespace
                if (preg_match('/^[\s\-\:\|\.\*\#]+$/', $value)) {
                    if (isset($lines[$i + 1])) {
                        $value = $lines[$i + 1];
                    } else {
                        continue;
                    }
                }
                
                // FIXED: Minimum length check
                if (strlen($value) < 3) {
                    continue;
                }
                
                if (!empty($value) && $this->isValidVendorName($value, true)) {
                    // Remove leading account numbers or IDs (Common in BRI/Bank transfers)
                    // e.g. "1234567890 IKHSAN" -> "IKHSAN"
                    $value = preg_replace('/^\d{5,}\s+/', '', $value);
                    
                    // Clean up leading/trailing symbols and artifacts (e.g. -.-.-.- or |)
                    $value = preg_replace('/^[^a-zA-Z0-9]+|[^a-zA-Z0-9]+$/', '', $value);
                    
                    // Remove trailing single letters separated by space (OCR artifacts like " n", " y")
                    $value = preg_replace('/\s+[a-zA-Z]$/', '', $value);
                    
                    $value = trim($value);

                    // FIXED: Better continuation check
                    if (isset($lines[$i+1]) && $lines[$i+1] !== $value) {
                        $nextLine = trim($lines[$i+1]);
                        // Only append if next line is meaningful (3+ chars, not numeric-only, not header)
                        if (strlen($nextLine) >= 3 && 
                            strlen($nextLine) < 30 && 
                            !preg_match('/^\d+$/', $nextLine) &&
                            !$this->isHeaderOrPlatform($nextLine)) {
                            $value .= ' ' . $nextLine;
                        }
                    }
                    
                    return $value;
                }
            }
        }

        // 1. Transaction Type Specific Logic (Legacy, kept for compatibility)
        if ($transactionType === 'transfer_direct' || $transactionType === 'qris') {
            // Already covered by improved Step 0
        }

        // 2. Generic "Merchant" or "Penjual" search
        foreach ($lines as $i => $line) {
            // Block "Sumber Dana" which often appears before Vendor in E-Wallets
            if (stripos($line, 'sumber dana') !== false) {
                continue;
            }

            // EXCLUSION: Skip "Merchant Location", "Merchant ID", "Merchant PAN", "Acquirer", "Terminal"
            if (preg_match('/(merchant\s+(location|id|pan|terminal|address)|acquirer|pengakuisisi|terminal\s*id)/i', $line)) {
                continue;
            }

            if (preg_match('/(merchant|penjual|toko|seller|outlet|vendor|supplier|diterbitkan oleh|diterbitkan atas nama)\b\s*[:]?\s*(.{3,})/i', $line, $matches)) {
                 $value = trim($matches[2]);
                 
                 // FIXED: Skip if just symbols
                 if (preg_match('/^[\s\-\:\|\.]+$/', $value)) {
                     if (isset($lines[$i + 1])) {
                         $value = $lines[$i + 1];
                     } else {
                         continue;
                     }
                 }
                 
                 if (empty($value) || strlen($value) < 3) {
                     continue;
                 }
                 
                 if ($this->isValidVendorName($value)) {
                     return $value;
                 }
            }
        }

        // 3. Fallback: Top of receipt, but skip known Headers/Banks/Platforms
        foreach ($lines as $line) {
            if ($this->isValidVendorName($line) && !$this->isHeaderOrPlatform($line)) {
                $line = preg_replace('/^[^a-zA-Z0-9]+|[^a-zA-Z0-9]+$/', '', $line);
                $line = preg_replace('/\s+[a-zA-Z]$/', '', $line);
                return trim($line);
            }
        }

        return null;
    }

    private function isValidVendorName(string $name, bool $allowPlatform = false): bool
    {
        $name = trim($name);
        if (strlen($name) < 3) return false;
        
        // Filter out noise with high symbol density
        $symbolCount = preg_match_all('/[^a-zA-Z0-9\s]/', $name);
        $alphaCount = preg_match_all('/[a-zA-Z]/', $name);
        if ($symbolCount > $alphaCount && $alphaCount < 5) return false;

        // Filter out fragmented noise (many short words typical of background logo OCR)
        $words = explode(' ', $name);
        $shortWords = array_filter($words, fn($w) => strlen($w) <= 2);
        if (count($words) >= 3 && count($shortWords) / count($words) > 0.6) return false;

        // FIXED: More precise invalid patterns with word boundaries
        $invalidPatterns = [
            '/^Rp\.?\s*\d+/i', // Nominal
            '/^IDR/i',
            '/^\d+$/', // Numbers only
            '/^transaksi\s+(berhasil|gagal|sukses)/i', // Specific transaction status
            '/^(berhasil|gagal|sukses)$/i', // Status only
            '/^total/i',
            '/^jumlah/i',
            '/^untuk(\s|$)/i',
            '/^pembeli(\s|$)/i',
            '/^kepada(\s|$)/i',
            '/^penerima(\s|$)/i',
            '/^diterbitkan\s+(atas\s+nama|oleh)/i',
            '/^alamat\s+pengiriman/i',
            '/^sumber dana/i', 
            '/^(dana|gopay|ovo|shopeepay|linkaja)$/i', // Platform only (exact match)
            '/^(bca|mandiri|bri|bni|cimb)$/i', // Bank only (exact match)
            '/^bank\s+/i',
            '/^metode pembayaran/i',
            '/^rincian/i',
            '/^invoice/i',
            '/^struk/i',
            '/^bukti/i',
            '/^acquirer/i',
            '/^pengakuisisi/i',
            '/^terminal id/i',
            '/^merchant pan/i',
            '/^customer pan/i',
            '/^ref/i',
            '/^no\s*ref/i',
            '/^nomor/i',
            '/^\d{2}:\d{2}/', // Time (10:56)
            '/^\d{1,2}\s+(jan|feb|mar|apr|mei|jun|jul|agu|aug|sep|okt|oct|nov|des|dec)/i', // Date starts
            '/^(lte|4g|5g|volte|3g|wifi)/i', // Status bar
        ];

        foreach ($invalidPatterns as $pattern) {
            if (preg_match($pattern, $name)) {
                return false;
            }
        }

        // If it's a known header/platform, it's NOT a specific vendor (unless allowPlatform is true)
        if (!$allowPlatform && $this->isHeaderOrPlatform($name)) {
            return false;
        }

        return true;
    }

    private function isHeaderOrPlatform(string $line): bool
    {
        $headers = [
            'transaksi berhasil', 'pembayaran berhasil', 'transfer berhasil',
            'rincian transaksi', 'detail transaksi', 'bukti transaksi',
            'tokopedia', 'shopee', 'gojek', 'grab', 'dana', 'ovo', 'gopay', 'linkaja',
            'bca', 'mandiri', 'bri', 'bni', 'cimb', 'jago', 'seabank',
            'info produk', 'jumlah harga', 'harga satuan', 'total harga',
            'struk pembayaran', 'bukti pembayaran', 'receipt', 'invoice'
        ];

        $lower = strtolower($line);
        foreach ($headers as $h) {
            if (str_contains($lower, $h)) return true;
        }
        return false;
    }

    /**
     * Extract Nominal with IMPROVED PRIORITY ORDER
     * FIXED: Final Payment > Total Tagihan > Total Transaksi > Total
     */
    private function extractNominal(array $lines): ?float
    {
        // PRIORITY 1: "Nominal Pembayaran" / "Total Payment" (FINAL amount user pays)
        foreach ($lines as $i => $line) {
            // Skip Ref lines
            if (preg_match('/(ref|reference|no\.\s*ref)/i', $line)) {
                continue;
            }

            // HIGHEST PRIORITY: Final payment labels
            if (preg_match('/(nominal\s*pembayaran|nominal\s*bayar|total\s*payment|total\s*pembayaran|total\s*tagihan|grand\s*total|amount\s*due|jumlah\s*dibayar)/i', $line)) {
                $val = $this->parseCurrency($line);
                
                if (!$val && isset($lines[$i+1])) {
                    $val = $this->parseCurrency($lines[$i+1]);
                }

                if ($val && $this->isValidAmount($val)) {
                    \Log::info('LocalReceiptParser: Found PRIORITY 1 Nominal (Final Payment)', [
                        'line' => $line,
                        'value' => $val
                    ]);
                    return $val;
                }
            }
        }

        // PRIORITY 2: "Total Transaksi" (common in BRI, but might be subtotal)
        foreach ($lines as $i => $line) {
            // Enhanced regex for common OCR errors in "Total Transaksi"
            if (preg_match('/t[o0!][ta][ta][l|i]\s*tr[a4]ns[a4]ks[i1]/i', $line)) {
                $val = $this->parseCurrency($line);
                if (!$val && isset($lines[$i+1])) {
                    $val = $this->parseCurrency($lines[$i+1]);
                }
                if ($val && $this->isValidAmount($val)) {
                    \Log::info('LocalReceiptParser: Found PRIORITY 2 Nominal (Total Transaksi)', ['value' => $val]);
                    return $val;
                }
            }
        }

        // PRIORITY 3: Generic "Total" - collect ALL matches, take LAST one
        $totalMatches = [];
        foreach ($lines as $i => $line) {
            // Skip Ref lines
            if (preg_match('/(ref|reference|no\.\s*ref)/i', $line)) {
                continue;
            }

            if (preg_match('/(^|\s)total(\s|:|$)/i', $line)) {
                $val = $this->parseCurrency($line);
                if (!$val && isset($lines[$i+1])) {
                    $val = $this->parseCurrency($lines[$i+1]);
                }

                if ($val && $this->isValidAmount($val)) {
                    $totalMatches[] = ['index' => $i, 'value' => $val, 'line' => $line];
                }
            }
        }
        
        // Return LAST total match (most accurate in receipts)
        if (!empty($totalMatches)) {
            $lastMatch = end($totalMatches);
            \Log::info('LocalReceiptParser: Found PRIORITY 3 Total', [
                'line' => $lastMatch['line'],
                'value' => $lastMatch['value'],
                'position' => 'last match of ' . count($totalMatches)
            ]);
            return $lastMatch['value'];
        }

        // PRIORITY 4: "Bayar" / "Jumlah" (but NOT "Jumlah Barang")
        foreach (array_reverse($lines) as $line) {
            if (preg_match('/(^|\s)(bayar|jumlah)(\s|:|$)/i', $line) && 
                !preg_match('/jumlah\s*(barang|item|qty)/i', $line)) {
                $val = $this->parseCurrency($line);
                if ($val && $this->isValidAmount($val)) {
                    \Log::info('LocalReceiptParser: Found PRIORITY 4 Bayar/Jumlah', [
                        'line' => $line,
                        'value' => $val
                    ]);
                    return $val;
                }
            }
        }

        // PRIORITY 5: Last resort - largest valid amount (likely the total)
        $candidates = [];
        foreach ($lines as $line) {
            // Skip lines that are NOT amounts
            if (preg_match('/(jumlah\s*barang|qty|harga|satuan|diskon|tip|admin|pajak|ppn|biaya|ref|reference|nomor)/i', $line)) {
                continue;
            }
            $val = $this->parseCurrency($line);
            if ($val && $this->isValidAmount($val)) {
                $candidates[] = ['value' => $val, 'line' => $line];
            }
        }

        if (!empty($candidates)) {
            usort($candidates, fn($a, $b) => $b['value'] <=> $a['value']);
            $largest = $candidates[0];
            \Log::info('LocalReceiptParser: Found PRIORITY 5 nominal (largest)', [
                'line' => $largest['line'],
                'value' => $largest['value'],
                'candidates_count' => count($candidates)
            ]);
            return $largest['value'];
        }

        return null;
    }

    private function isValidAmount(float $val): bool
    {
        // Reasonable limits for reimbursement
        return $val >= 100 && $val <= 10000000000;
    }

    /**
     * Parse Currency with IMPROVED decimal/thousand separator logic
     */
    private function parseCurrency(string $text): ?float
    {
        // OCR HEALING: Fix common OCR errors in numbers before parsing
        $text = preg_replace_callback('/(?:Rp\.?|IDR|[1l]DR|1DR)\s*([0-9A-Za-z.,\s]+)/i', function($m) {
            $val = $m[1];
            if (preg_match('/[0-9]/', $val) || preg_match_all('/[SOIl]/i', $val) > 1) {
                $val = str_ireplace(['S', 'O', 'I', 'l'], ['5', '0', '1', '1'], $val);
            }
            return 'Rp ' . $val;
        }, $text);

        // Extract numbers - ENHANCED pattern
        if (preg_match_all('/(?:Rp\.?|IDR|[1l]DR|1DR)\s*([0-9]{1,3}(?:[.,\s]*[0-9]{3})*|[0-9]+)|(?:\s|^)([0-9]{1,3}(?:[.,\s]*[0-9]{3})+(?:[.,\s]*[0-9]{1,2})?)(?:\s|$)/i', $text, $matches)) {
            $allMatches = array_filter(array_merge($matches[1], $matches[2]));
            
            foreach ($allMatches as $match) {
                if (empty($match)) continue;
                $clean = preg_replace('/\s+/', '', $match);
                $hasDot = str_contains($clean, '.');
                $hasComma = str_contains($clean, ',');

                // IMPROVED: Handle dot/comma ambiguity
                if ($hasDot && $hasComma) {
                     // Determine which is decimal separator by position
                     $dotPos = strrpos($clean, '.');
                     $commaPos = strrpos($clean, ',');
                     
                     if ($dotPos > $commaPos) {
                         // US Format: 10,000.00 -> comma=thousand, dot=decimal
                         $clean = str_replace(',', '', $clean);
                     } else {
                         // Indonesian: 10.000,00 -> dot=thousand, comma=decimal
                         $clean = str_replace('.', '', $clean);
                         $clean = str_replace(',', '.', $clean);
                     }
                } elseif ($hasDot) {
                    // IMPROVED: Context-aware dot handling
                    $dotCount = substr_count($clean, '.');
                    
                    if ($dotCount > 1) {
                        // Multiple dots = thousand separator (e.g. 10.000.000)
                        $clean = str_replace('.', '', $clean);
                    } elseif (preg_match('/\.\d{3}($|\D)/', $clean)) {
                        // Dot followed by exactly 3 digits = thousand (e.g. 10.000)
                        $clean = str_replace('.', '', $clean);
                    } elseif (preg_match('/\.\d{1,2}$/', $clean)) {
                        // Dot followed by 1-2 digits at END = decimal (e.g. 10.50)
                        // Keep as-is
                    } else {
                        // Ambiguous: assume thousand if value > 999
                        $temp = (float) str_replace('.', '', $clean);
                        if ($temp > 999) {
                            $clean = str_replace('.', '', $clean);
                        }
                    }
                } elseif ($hasComma) {
                    // Similar logic for comma
                    $commaCount = substr_count($clean, ',');
                    
                    if ($commaCount > 1) {
                        // Multiple commas = thousand separator
                        $clean = str_replace(',', '', $clean);
                    } elseif (preg_match('/,\d{3}($|\D)/', $clean)) {
                        // Comma + 3 digits = thousand
                        $clean = str_replace(',', '', $clean);
                    } elseif (preg_match('/,\d{1,2}$/', $clean)) {
                        // Comma + 1-2 digits at END = decimal
                        $clean = str_replace(',', '.', $clean);
                    } else {
                        // Ambiguous
                        $temp = (float) str_replace(',', '', $clean);
                        if ($temp > 999) {
                            $clean = str_replace(',', '', $clean);
                        } else {
                            $clean = str_replace(',', '.', $clean);
                        }
                    }
                }

                $val = (float) $clean;
                if ($val > 100 && $val < 10000000000) {
                    return $val;
                }
            }
        }
        return null;
    }

    private function extractDate(array $lines): ?string
    {
        // Added: Des, Okt, Agu for Indonesian short format
        $months = 'Januari|Februari|Maret|April|Mei|Juni|Juli|Agustus|September|Oktober|November|Desember|Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec|Des|Okt|Agu';
        
        foreach ($lines as $line) {
            // 1. Format: 25 Des 2025 OR 25-Des-2025 OR 25/Des/2025 OR 25'Des 2025
            if (preg_match("/(\d{1,2})[\s\-\/\.\'\"]*($months)[\s\-\/\.\'\"]*(\d{2,4})/i", $line, $matches)) {
                $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                $month = $this->monthToNum($matches[2]);
                $year = $matches[3];
                if (strlen($year) === 2) $year = '20' . $year;
                return "$year-$month-$day";
            }
            
            // 2. Format: YYYY-MM-DD
            if (preg_match('/(\d{4})[-\/\.](\d{1,2})[-\/\.](\d{1,2})/', $line, $matches)) {
                $day = str_pad($matches[3], 2, '0', STR_PAD_LEFT);
                $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
                return "{$matches[1]}-{$month}-{$day}";
            }
            
            // 3. Format: DD-MM-YYYY
            if (preg_match('/(\d{1,2})[-\/\.](\d{1,2})[-\/\.](\d{2,4})/', $line, $matches)) {
                $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
                $year = $matches[3];
                if (strlen($year) === 2) $year = '20' . $year;
                return "$year-$month-$day";
            }
        }

        // 4. SMART: Extract date from Reference Numbers (YYYYMMDD or YYMMDD)
        foreach ($lines as $line) {
            if (preg_match('/(?:Ref|Reference|No|Nomor)\s*(?:No|Number)?[:\.\s]*(\d{10,25})/i', $line, $matches)) {
                $refNo = $matches[1];
                // YYYYMMDD
                if (preg_match('/(202[4-9])(0[1-9]|1[0-2])(0[1-9]|[12][0-9]|3[01])/', $refNo, $parts)) {
                    return "{$parts[1]}-{$parts[2]}-{$parts[3]}";
                }
                // YYMMDD (at start of ref)
                if (preg_match('/^(2[4-9])(0[1-9]|1[0-2])(0[1-9]|[12][0-9]|3[01])/', $refNo, $parts)) {
                    return "20{$parts[1]}-{$parts[2]}-{$parts[3]}";
                }
            }
        }

        return null;
    }

    private function monthToNum(string $name): string
    {
        $name = strtolower(substr($name, 0, 3));
        $map = [
            'jan' => '01', 'feb' => '02', 'mar' => '03', 'apr' => '04',
            'mei' => '05', 'may' => '05', 'jun' => '06', 'jul' => '07',
            'agu' => '08', 'aug' => '08', 'sep' => '09', 'okt' => '10', 'oct' => '10',
            'nov' => '11', 'des' => '12', 'dec' => '12'
        ];
        return $map[$name] ?? '01';
    }

    private function extractTime(array $lines): ?string
    {
        foreach ($lines as $line) {
            // HH:MM:SS or HH:MM
            if (preg_match('/([01]?[0-9]|2[0-3])[:.]([0-5][0-9])(?:[:.]([0-5][0-9]))?/', $line, $matches)) {
                $hour = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                $min = $matches[2];
                $sec = $matches[3] ?? '00';
                return "{$hour}:{$min}:{$sec}";
            }
        }
        return null;
    }
}