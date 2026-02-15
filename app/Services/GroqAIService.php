<?php

namespace App\Services;

use Exception;
use GuzzleHttp\Client;

class GroqAIService
{
    protected Client $client;

    protected ?string $apiKey = null;

    public function __construct()
    {
        $this->apiKey = config('reimbursement.groq_api_key');
        $this->client = new Client([
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function processReceiptOCR(string $ocrText, ?string $jenisTransaksi = null): array
    {
        $maxRetries = 2;
        $retryDelay = 2000; // ms
        $attempt = 0;

        while ($attempt <= $maxRetries) {
            try {
                if (empty($this->apiKey)) {
                    throw new Exception('API Key Groq belum dikonfigurasi (reimbursement.groq_api_key).');
                }

                if ($attempt > 0) {
                    \Log::info("GroqAIService: Retry attempt $attempt after 429 error");
                    usleep($retryDelay * 1000);
                    $retryDelay *= 2; // Exponential backoff
                }

                \Log::info('GroqAIService: Starting OCR validation', ['jenis_transaksi' => $jenisTransaksi, 'attempt' => $attempt + 1]);

                // ULTRA SMART: Pre-clean OCR text to remove noise
                $ocrText = $this->preCleanOcrText($ocrText);

                $prompt = $this->buildOCRValidationPrompt($ocrText, $jenisTransaksi);

                $response = $this->client->post('https://api.groq.com/openai/v1/chat/completions', [
                    'json' => [
                        'model' => 'llama-3.3-70b-versatile',
                        'messages' => [
                            [
                                'role' => 'user',
                                'content' => $prompt,
                            ],
                        ],
                        'temperature' => 0,
                        'max_tokens' => 1000,
                    ],
                    'headers' => [
                        'Authorization' => 'Bearer '.$this->apiKey,
                    ],
                ]);

                $responseBody = $response->getBody()->getContents();
                $result = json_decode($responseBody, true);

                \Log::info('GroqAIService: API Response', ['status' => $response->getStatusCode(), 'response_preview' => substr($responseBody, 0, 500)]);

                if (! $result) {
                    throw new Exception('Groq API returned invalid JSON: '.json_last_error_msg());
                }

                if (isset($result['error'])) {
                    $errorMsg = $result['error']['message'] ?? json_encode($result['error']);

                    // If Rate Limit, retry
                    if (str_contains(strtolower($errorMsg), 'rate limit') || str_contains(strtolower($errorMsg), 'too many requests')) {
                        $attempt++;

                        continue;
                    }

                    throw new Exception('Groq API error: '.$errorMsg);
                }

                if (! isset($result['choices'][0]['message']['content'])) {
                    throw new Exception('Invalid response structure from Groq API: '.json_encode($result));
                }

                $content = $result['choices'][0]['message']['content'];
                \Log::info('GroqAIService: OCR validation completed', ['response_length' => strlen($content)]);

                $parsedResult = $this->parseOCRResponse($content, $ocrText);
                if ($parsedResult['success']) {
                    $parsedResult['data']['raw_text'] = $ocrText;
                }

                return $parsedResult;

            } catch (\GuzzleHttp\Exception\ClientException $e) {
                $response = $e->getResponse();
                $errorBody = $response ? $response->getBody()->getContents() : $e->getMessage();

                // Retry on 429 Too Many Requests
                if ($response && $response->getStatusCode() === 429 && $attempt < $maxRetries) {
                    \Log::warning('GroqAIService: 429 Rate Limit hit. Retrying...', ['attempt' => $attempt + 1]);
                    $attempt++;

                    continue;
                }

                \Log::error('GroqAIService HTTP Client Error: '.$errorBody);

                return [
                    'success' => false,
                    'error' => 'Gagal terhubung ke Groq API: '.$e->getMessage(),
                ];
            } catch (Exception $e) {
                \Log::error('GroqAIService OCR error: '.$e->getMessage());

                return [
                    'success' => false,
                    'error' => 'Gagal memproses gambar: '.$e->getMessage(),
                ];
            }
        }

        return [
            'success' => false,
            'error' => 'Gagal memproses OCR setelah beberapa kali mencoba akibat batasan limit API.',
        ];
    }

    private function buildOCRValidationPrompt(string $ocrText, ?string $jenisTransaksi = null): string
    {
        // Normalize Transaction Type (match values from create.blade.php)
        $input = strtolower($jenisTransaksi ?? '');
        $typeInstructions = '';

        // --- LOGIKA SPESIFIK BERDASARKAN TIPE TRANSAKSI ---

        if (str_contains($input, 'marketplace') || str_contains($input, 'tokopedia') || str_contains($input, 'shopee')) {
            // 1. MARKETPLACE
            $typeInstructions = <<<'EOT'
            **TIPE DOKUMEN: MARKETPLACE (Tokopedia, Shopee, Lazada, Blibli)**
            
            [ATURAN VENDOR]
            - **PRIORITAS 1**: Nama TOKO / PENJUAL (Merchant). Contoh: "Lex Garage", "onepoles24".
            - **PRIORITAS 2**: Nama PLATFORM (Tokopedia, Shopee) JIKA nama toko sulit ditemukan.
            - **LABEL**: 'Penjual:', 'Merchant:', 'Seller:', 'Diterbitkan atas nama:', 'Toko:'.
            - **KASUS GANDA**: Jika ada "Tokopedia" DAN "Lex Garage", ambil "Lex Garage" sebagai vendor utama.
            
            [ATURAN NOMINAL - MULTI INVOICE - SANGAT PENTING]
            - **DETEKSI MULTIPLE INVOICE**: Jika ada "Detail Pembayaran (N Invoice)" atau "(4 Invoice)" = ada N transaksi terpisah
            - **CARI SEMUA TOTAL** (PRIORITY TINGGI):
              1. **Total Per Invoice / Per Toko**: Jika invoice terpisah, list SEMUA total per invoice
              2. **SUBTOTAL HARGA BARANG**: Jumlah harga item saja (priority 2)
              3. **TOTAL BELANJA (Setelah Diskon)**: Total 1 invoice setelah diskon/tambahan
              4. **TOTAL BELANJA N INVOICE**: Jumlah SEMUA invoice (priority rendah, jangan di-ambil sebagai nominal utama)
              5. **TOTAL TAGIHAN**: Grand total (priority paling rendah)
            - **MASUKAN KE all_detected_totals**:
              - Setiap INVOICE INDIVIDUAL dengan total-nya
            - **NOMINAL UTAMA**: Ambil dari invoice PERTAMA atau INVOICE YANG PALING MENONJOL (bukan total semua invoice)
            EOT;

        } elseif (str_contains($input, 'transfer') || str_contains($input, 'bank') || str_contains($input, 'qris') || str_contains($input, 'ewallet') || str_contains($input, 'dana') || str_contains($input, 'ovo') || str_contains($input, 'gopay')) {
            // 2. TRANSFER / QRIS / E-WALLET
            $typeInstructions = <<<'EOT'
            **TIPE DOKUMEN: BUKTI TRANSFER / QRIS / E-WALLET (Dana, GoPay, OVO, M-Banking)**
            
            [ATURAN VENDOR - SANGAT KETAT]
            - **TARGET UTAMA**: Siapa PENERIMA uangnya? (Destination) / Nama LAYANAN yang dibayar.
            - **PRIORITAS 1**: Nama setelah kata 'Payment to:', 'Tujuan:', 'Penerima:', 'Ke:', 'Transfer ke:', 'Merchant:', 'Kirim Uang ke:'.
            - **DILARANG KERAS**: JANGAN ambil 'Acquirer' atau 'Pengakuisisi' (misal: GOPAY, SHOPEEPAY) sebagai vendor jika ada nama merchant lain.
            - **DILARANG KERAS**: JANGAN ambil teks header seperti "Transaksi Berhasil", "Pembayaran Berhasil", dll.
            - **CARI LABEL**: 'Payment to:', 'Tujuan:', 'Ke:', 'Penerima:', 'Nama Merchant:', 'Merchant:', 'Outlet:', 'Transfer ke:', 'Kirim Uang ke:', 'Layanan:'.
            
            [ATURAN NOMINAL - SANGAT PENTING]
            - **PRIORITAS UTAMA**: Cari 'Total Transaksi', 'Total', 'Total Bayar', 'Total Payment', 'Total Pembayaran', 'Jumlah Dibayar' (ANGKA TERAKHIR/FINAL yang user bayar).
            - **ADMIN FEE**: Biaya admin (misal: 2.500) HARUS ditambahkan ke nominal atau pastikan mengambil angka yang sudah termasuk admin.
            - **PERINGATAN KERAS - BRI CASE**: Pada struk BRI, abaikan label 'Nominal' (misal: 50.000) dan cari 'Total Transaksi' (misal: 52.500). **HASIL AKHIR HARUS 52.500**.
            - **DILARANG**: JANGAN mengambil angka dari label "Nominal" jika ada label "Total Transaksi" atau "Total Bayar".
            - **DILARANG**: JANGAN menandai nominal yang cocok dengan input user sebagai "Bukan Total Akhir" jika itu adalah angka terbesar/terakhir di struk.
            - **HATI-HATI**: Jangan ambil angka pertama (Subtotal, Jumlah Barang, Harga Satuan).
            - **PERHITUNGAN BENAR**: Ambil angka FINAL setelah ditambah biaya admin atau dikurangi diskon.

            [ATURAN TANGGAL - ULTRA SMART]
            - **PRIORITAS UTAMA**: Cari tanggal di baris paling atas (Header), biasanya tepat di bawah judul "Transaksi Berhasil" atau "Pembayaran Berhasil". 
            - **FORMAT**: Perhatikan format "DD Mon YYYY" (misal: 20 Jan 2026) atau "DD/MM/YYYY".
            - **SANGAT PENTING - EXCLUSION**: JANGAN mengambil angka dari NPWP (format 01.001.XXX... atau sejenisnya) sebagai tanggal. Bagian NPWP seperti cabang (misal: "093") seringkali salah terbaca sebagai tanggal lama (misal: "1993").
            - **POLA REFERENSI**: Jika tidak ada di header, cari di "No. Referensi" atau "Ref No". Banyak bank memasukkan tanggal di awal atau di tengah Ref No (misal: "9527120260102105..." mengandung "20260102" berarti 2026-01-02).
            - **EKSTRAKSI**: Jika Anda menemukan pola YYYYMMDD atau YYMMDD di dalam nomor panjang, gunakan itu sebagai tanggal.
            EOT;

        } elseif (str_contains($input, 'transport') || str_contains($input, 'ojek') || str_contains($input, 'gojek') || str_contains($input, 'grab') || str_contains($input, 'tiket') || str_contains($input, 'travel')) {
            // 3. TRANSPORT / TRAVEL / TIKET
            $typeInstructions = <<<'EOT'
            **TIPE DOKUMEN: TRANSPORTASI & TRAVEL (Gojek, Grab, Tiket Pesawat/Kereta, Traveloka)**
            
            [ATURAN VENDOR]
            - **PRIORITAS 1 (MAKANAN)**: Nama RESTORAN (misal: "McDonalds", "Sate Khas Senayan", "Ayam Geprek Pangeran").
            - **PRIORITAS 2 (TIKET)**: Nama Maskapai (Garuda, Lion Air) atau Nama Travel (Pahala Kencana, dll).
            - **PRIORITAS 3 (PERJALANAN)**: Gunakan "Gojek", "Grab", atau "Blue Bird".
            
            [ATURAN NOMINAL]
            - Cari 'Total Pembayaran', 'Total dibayar', 'Total'.
            - Untuk tiket pesawat/hotel, pastikan mengambil 'Total Bayar' termasuk pajak/fee.
            EOT;

        } elseif (str_contains($input, 'parkir') || str_contains($input, 'tol') || str_contains($input, 'parking')) {
            // 4. PARKIR / TOL
            $typeInstructions = <<<'EOT'
            **TIPE DOKUMEN: PARKIR & TOL (Karcis Parkir, E-Toll, Struk Gerbang Tol)**
            
            [ATURAN VENDOR]
            - **TARGET**: Nama Pengelola (misal: "Secure Parking", "Jasa Marga", "Sky Parking").
            - **LOKASI**: Nama gedung atau lokasi parkir (misal: "Grand Indonesia", "Soekarno-Hatta").
            
            [ATURAN NOMINAL]
            - Cari angka yang paling menonjol. Biasanya kecil (Rp 2.000, Rp 5.000, dst).
            - Abaikan angka jam masuk/keluar.
            EOT;

        } elseif (str_contains($input, 'hotel') || str_contains($input, 'penginapan')) {
            // 5. HOTEL / PENGINAPAN
            $typeInstructions = <<<'EOT'
            **TIPE DOKUMEN: HOTEL / AKOMODASI**
            
            [ATURAN VENDOR]
            - Nama Hotel yang tertera di header.
            - Cari kata "Check-in", "Check-out", "Stay Duration".
            
            [ATURAN NOMINAL]
            - Cari 'Grand Total', 'Amount Due', 'Balance'.
            - Hati-hati dengan 'Deposit' (biasanya dikurangi). Ambil nominal biaya menginap akhir.
            EOT;

        } elseif (str_contains($input, 'kesehatan') || str_contains($input, 'medis') || str_contains($input, 'obat') || str_contains($input, 'apotek')) {
            // 6. KESEHATAN / MEDIS
            $typeInstructions = <<<'EOT'
            **TIPE DOKUMEN: KESEHATAN (Rumah Sakit, Klinik, Apotek, Laboratorium)**
            
            [ATURAN VENDOR]
            - Nama Rumah Sakit, Klinik, atau Apotek (misal: "Apotek K-24", "RS Medika").
            - Cari nama pasien (jika ada) untuk verifikasi.
            
            [ATURAN NOMINAL]
            - Cari 'Total Biaya', 'Total Obat', 'Jumlah Bayar'.
            EOT;

        } else {
            // 6. UMUM / STRUK FISIK / ASET / ATK
            $typeInstructions = <<<'EOT'
            **TIPE DOKUMEN: UMUM / STRUK FISIK / ASET / ATK (Bon Kertas, Nota Toko, Invoice)**
            
            [ATURAN VENDOR]
            - **TARGET**: Nama Toko / Badan Usaha di HEADER struk.
            - **CONTOH**: "Indomaret", "Gramedia", "Ace Hardware", "Bhinneka".
            
            [ATURAN NOMINAL]
            - Cari 'Grand Total', 'Total', 'Jumlah Dibayar'.
            - Jika ini adalah pembelian aset (laptop, furnitur), pastikan rincian barang terbaca.
            EOT;
        }

        return <<<EOT
        Anda adalah Auditor Keuangan AI yang sangat teliti dan cerdas. Tugas Anda adalah menganalisis teks OCR dari struk/invoice dan mengekstrak informasi dengan akurasi tertinggi.

**TEKS OCR DARI STRUK:**
$ocrText

**ATURAN MAIN (WAJIB DIIKUTI):**

**1. IDENTIFIKASI VENDOR (SANGAT PENTING & KRITIS):**
   - **ATURAN WAJIB #1: CARI LABEL EKSPLISIT.** Ini adalah prioritas tertinggi. Cari kata kunci berikut dan ambil teks SETELAHNYA: "Penjual:", "Merchant:", "Toko:", "Seller:", "Diterbitkan atas nama:", "Diterbitkan Atas Nama:", "Tujuan:", "Ke:", "Penerima:". Jika ditemukan, ini HAMPIR PASTI adalah vendornya.
   - **ATURAN WAJIB #2: HINDARI "SAMPAH" DAN NOISE.** 
     - Teks-teks berikut **DILARANG KERAS** untuk dijadikan vendor: "Bukti Transaksi", "Rincian Pembayaran", "Success", "Transaksi Berhasil", "Struk", "Invoice", "Nota", "BCA", "Mandiri", "BRI", "BNI", "GoPay", "OVO", "DANA", "ShopeePay", "4G", "LTE", "WIFI", "10:56", "Battery 100%", "Acquirer", "Pengakuisisi", "Sumber Dana".
     - **DILARANG KERAS** mengambil teks yang terdiri dari banyak simbol (misal: "5.\" MO Em ...") atau teks yang tidak membentuk nama yang valid. Jika hanya ada noise, kembalikan null atau cari baris lain yang lebih bersih.
   - **ATURAN WAJIB #3: GUNAKAN KONTEKS.** Jika tidak ada label eksplisit, cari NAMA PERUSAHAAN atau NAMA ORANG yang paling mungkin (biasanya di baris tujuan/penerima).
   - **ATURAN WAJIB #4: LAKUKAN OCR HEALING.** Perbaiki kata yang rusak akibat OCR. Contoh: "1ndomaret" -> "Indomaret", "Alfamrt" -> "Alfamart".

**2. PANDUAN BERDASARKAN TIPE TRANSAKSI (INPUT USER):**
Gunakan ini sebagai panduan tambahan untuk memperkuat keputusan Anda.
$typeInstructions

**3. EKSTRAKSI NOMINAL (SANGAT KRITIS - Harus Tepat):**
   - **PRIORITAS 1 (TERTINGGI):** Cari "Total Transaksi", "Total Pembayaran", "Total Bayar", "Total Payment", "Jumlah Dibayar", "Nominal Pembayaran", "Total Tagihan".
   - **PENTING (BRI CASE):** Abaikan label "Nominal" (Subtotal). Gunakan angka dari label "Total Transaksi" (Grand Total). Contoh: Jika Nominal=50.000 dan Total Transaksi=52.500, ambil **52500**.
   - **PRIORITAS 2:** Cari "Total", "Grand Total", "Amount Due".
   - **HINDARI JEBAKAN:**
     - JANGAN ambil "Subtotal", "Jumlah Barang", "Harga Satuan", atau angka pertama yang ditemukan.
     - JANGAN ambil dari potongan NPWP (misal: "09-728-xxx" yang mengandung angka) - itu bukan tanggal atau nominal.
     - JANGAN ambil angka dari "No. Ref" atau "No. Referensi" kecuali jelas diakhiri dengan satuan mata uang (Rp/IDR).
   
   - **OCR HEALING NOMINAL:**
     - Bersihkan karakter sampah: "Rp10. 0 0 0" → "10000"
     - Hapus titik pemisah ribuan: "Rp10.000" → "10000"
     - Hapus spasi berlebih: "Rp 10 000" → "10000"
     - Perbaiki OCR salah: "Rp1O.OOO" → "10000" (O → 0)
   
   - **MULTIPLE TOTALS (SANGAT PENTING):**
     - Struk sering memiliki ANGKA BANYAK: harga per item, subtotal, pajak, total.
     - Anda HARUS mendeteksi SEMUA angka potensial yang diakhiri dengan Rp/IDR atau label "Total", "Bayar", "Tagihan".
     - Masukkan ke array "all_detected_totals" dengan format:
       ```
       [
         {"label": "Nama Label dari Struk", "amount": 25000, "priority": 1},
         {"label": "Subtotal", "amount": 20000, "priority": 2}
       ]
       ```
     - **PRIORITY:**
       - priority: 1 = FINAL/ACTUAL TOTAL yang user bayar (nominal utama)
       - priority: 2 = Subtotal (sebelum pajak/diskon)
       - priority: 3 = Pajak/Service Charge
       - priority: 4 = Harga per item tertinggi
   
   - **NOMINAL UTAMA:** Ambil dari priority 1 (final total). Jika tidak ada, gunakan priority 2 (subtotal).

**4. EKSTRAKSI TANGGAL (ULTRA SMART):**
   - **PENTING:** JANGAN mengambil angka NPWP sebagai tanggal meskipun mirip format tanggal.
   - **NPWP PATTERN:** Format NPWP: "XX.XXX.XXX.X-XXX.XXX" atau "09-07-08-xxx" → JANGAN dianggap tanggal.
   
   - **CARI DI HEADER STRUK:**
     - Format: "DD Mon YYYY" (misal: "02 Jan 2026", "14 Jan 2025")
     - Format: "DD/MM/YY" atau "DD-MM-YYYY"
     - Format: "YYYY-MM-DD"
   
   - **CARI DI NO. REFERENSI (PINTAR):**
     - Banyak bank menyisipkan tanggal di Ref No, contoh: "9527120260102105..." → "20260102" = 2026-01-02
     - Pola: Cari YYYYMMDD atau YYMMDD di dalam nomor panjang
     - Contoh: "20260102" → 2026-01-02, "260102" → 2026-01-02
   
   - **HINDARI JEBAKAN:**
     - JANGAN ambil angka dari "PAN Pelanggan" (misal: "93600002100579467") sebagai tanggal.
     - JANGAN ambil angka acak kecil (< 10) sebagai tanggal.
   
   - **FORMAT OUTPUT:** Selalu gunakan "YYYY-MM-DD" (contoh: "2025-01-14").

**5. EKSTRAKSI ITEM (ITEMIZED - WAJIB):**
   - **TUGAS KRITIS**: Ekstrak SEMUA barang/jasa yang dibeli ke dalam array "items".
   - **STRUKTUR**: Cari baris yang mengandung angka (qty) dan harga.
   - **DILARANG MELEWATKAN**: Meskipun OCR berantakan, coba tebak nama barang dari baris yang masuk akal.
   - **ARRAY items**: `{"name": "...", "qty": 1, "price": 0, "category": "...", "is_personal": false}`
   - **TOTAL/PAJAK**: JANGAN masukkan baris "Total", "Pajak", "PPN", atau "Diskon" ke dalam array `items`. Masukkan itu ke `all_detected_totals`.

**6. ANALISIS DETEKSI ANOMALI & FRAUD (SANGAT PENTING - SESUAI JUDUL SKRIPSI):**
   - Anda adalah mesin pendeteksi fraud berbasis LLM. Analisis struk untuk tanda-tanda ketidakwajaran.
   - **FRAUD RISK SCORE (0-100):**
     - **0-20**: Struk sangat standar, rapi, vendor jelas (Indomaret, SPBU, McDonalds).
     - **30-50**: Struk agak buram, atau barang yang dibeli agak aneh untuk urusan kantor (misal: camilan terlalu banyak).
     - **60-80**: Terdeteksi barang dilarang (Rokok, Alkohol, Pulsa, Diamond Game), atau struk terlihat seperti diedit secara digital.
     - **90-100**: Struk palsu, total 0 tapi diajukan, atau manipulasi angka yang terlihat jelas.
   
   - **SANITY CHECK NOTES (BAHASA INDONESIA):** Berikan alasan logis mengapa Anda memberikan skor tersebut. Contoh: "Ditemukan item Rokok (Pelanggaran Kebijakan)" atau "Struk terlihat valid dan sesuai dengan kategori Konsumsi."
   
   - **KLASIFIKASI KATEGORI:** Pilih: **Transport, Parkir, Konsumsi, Operasional, Asset, Tagihan, Tiket & Akomodasi, Lain-lain**.
   
   - **DETEKSI PELANGGARAN KEBIJAKAN (Policy Violations):** List barang dilarang: Rokok, Alkohol, Top-up Game, Pulsa, Kosmetik.
   
   - **CONFIDENCE SCORE (0-100):** Berapa persen Anda yakin dengan hasil ekstraksi ini? Jika teks sangat berantakan, berikan < 60. Jika sangat tajam, berikan > 90.
   
   - **AUTO-SPLIT ACCOUNTING:** Jika satu struk mengandung item dari kategori berbeda, identifikasi porsi nominal untuk masing-masing kategori.

   - **BREAKDOWN BIAYA:** Cari dan pisahkan: "subtotal", "tax_amount" (PPN/Pajak), "service_charge" (biaya layanan), dan "discount".
   
**HASIL AKHIR (OUTPUT HANYA DALAM FORMAT JSON):**
Berikan jawaban HANYA dalam format JSON yang valid.
{
  "vendor": "Nama Vendor yang sudah divalidasi",
  "address": "Alamat/Kota Vendor (jika ada)",
  "nominal": 25000,
  "currency": "IDR",
  "tanggal": "YYYY-MM-DD",
  "invoice_number": "NOMOR_INVOICE_ATAU_REF",
  "items": [
    {"name": "Nama Barang", "qty": 1, "price": 20000, "category": "Asset/Konsumsi/dll", "is_personal": false}
  ],
  "policy_violations": [],
  "accounting_split": [],
  "confidence_score": 95,
  "confidence_reason": "Alasan tingkat kepercayaan diri",
  "suggested_category": "Kategori yang disarankan",
  "fraud_risk_score": 5,
  "sanity_check_notes": "Catatan jika ada kejanggalan",
  "contains_sensitive_data": false,
  "all_detected_totals": [
    {"label": "Grand Total", "amount": 25000, "priority": 1}
  ]
}
EOT;
    }

    private function parseOCRResponse(string $content, string $originalOcrText): array
    {
        try {
            \Log::info('GroqAIService: parseOCRResponse input', ['content_length' => strlen($content), 'content_preview' => substr($content, 0, 300)]);

            $data = $this->extractJson($content);

            if (! $data) {
                \Log::error('GroqAIService: Failed to extract JSON from response', ['content' => $content]);
                throw new Exception('Tidak dapat mengekstrak JSON dari response AI. Response: '.substr($content, 0, 200));
            }

            \Log::info('GroqAIService: Successfully parsed OCR response', ['vendor' => $data['vendor'] ?? '', 'nominal' => $data['nominal'] ?? 0]);

            $detectedTotals = $data['all_detected_totals'] ?? [];

            // --- SANITIZATION & FALLBACK LOGIC ---
            $vendor = $data['vendor'] ?? '';
            // Clean up leading/trailing symbols and artifacts (e.g. -.-.-.- or |)
            $vendor = preg_replace('/^[^a-zA-Z0-9]+|[^a-zA-Z0-9]+$/', '', $vendor);
            
            // Remove trailing single letters separated by space (OCR artifacts like " n", " y")
            $vendor = preg_replace('/\s+[a-zA-Z]$/', '', $vendor);
            
            $vendor = trim($vendor);

            // Use original OCR text for fallback search instead of AI response
            $rawText = $originalOcrText;

            // 1. Blacklist Check for "Transaksi Berhasil" etc.
            // Expanded regex to catch more variations and OCR typos (e.g. Transaks| -> Transaksi)
            // Added \s* to handle leading/trailing spaces in strict matches
            $blacklistRegex = '/(Transaks[il1!]|Transfer|Pembayaran|Bukti|Rincian|Detail|Status)\s*(?:Berhasil|Sukses|Success|Completed|Processed)|Summary|Receipt|Merchant\s+(Location|ID|PAN|Terminal)|Acquirer|Pengakuisisi|^\s*Dana\s*$|^\s*OVO\s*$|^\s*GoPay\s*$|^\s*ShopeePay\s*$|^\s*LinkAja\s*$|^\s*Sumber\s*Dana\s*$|^\s*BRI\s*$|^\s*BCA\s*$|^\s*Mandiri\s*$|^\s*Blu\s*$|^\s*Jago\s*$|^\s*pedia\s*$|^\s*tokopedia\s*$/i';

            // Check if vendor matches blacklist OR contains "Transaksi Berhasil" anywhere (safer)
            // ALSO: If vendor is formatted like "- (Dana)", clean it
            if (preg_match('/^[\-\s]*\(Dana\)$/i', $vendor)) {
                $vendor = 'Dana'; // Normalize for blacklist check
            }

            // ADDED: If vendor is broken OCR (too short, too many symbols, like "- . GO")
            $vendorTrimmed = trim($vendor);
            $isBrokenVendor = (strlen($vendorTrimmed) <= 6) || (strlen(preg_replace('/[^a-z0-9]/i', '', $vendorTrimmed)) < 3);

            if (preg_match($blacklistRegex, $vendor) || stripos($vendor, 'Transaksi Berhasil') !== false || $isBrokenVendor) {
                \Log::warning('GroqAIService: Vendor blacklist detected: '.$vendor.'. Attempting fallback.');

                // Try to find "Tujuan: ..." or "Merchant: ..." in raw text
                // Enhanced regex: Handle both same-line and next-line values
                // e.g. "Tujuan: Nasi Uduk" OR "Tujuan\nNasi Uduk" OR "Nama Merchant Nasi kuning"
                if (preg_match('/(?:Nama Merchant|Merchant|Tujuan|Ke|Penerima|Outlet|Seller|Penjual|Diterbitkan Atas Nama)\s*[:]?\s*(?:([^\n\r]+)|[\r\n]+([^\r\n]+))/i', $rawText, $matches)) {
                    // matches[1] is same line, matches[2] is next line
                    $candidate = trim(! empty($matches[1]) ? $matches[1] : ($matches[2] ?? ''));

                    // Recursive blacklist check on the candidate
                    if (! empty($candidate) && ! preg_match($blacklistRegex, $candidate) && stripos($candidate, 'Transaksi Berhasil') === false) {
                        $vendor = $candidate;
                        \Log::info('GroqAIService: Fallback vendor found: '.$vendor);
                    } else {
                        $vendor = '';
                    }
                }

                // ENHANCED FALLBACK: If still not found, search for platform keywords
                // e.g. "Tiktok Local Service", "Shopee", "Gojek", etc.
                if (empty($vendor)) {
                    $platformKeywords = ['tiktok', 'shopee', 'tokopedia', 'pedia', 'lazada', 'bukalapak', 'gojek', 'grab', 'traveloka', 'blibli', 'dana', 'ovo', 'gopay', 'linkaja', 'shopeepay'];

                    foreach ($platformKeywords as $platform) {
                        // Search for platform name followed by words (e.g "Tiktok Local Service", "Shopee Official")
                        if (preg_match('/('.preg_quote($platform, '/').'\s+[^\n\r]*)/i', $rawText, $matches)) {
                            $candidate = trim($matches[1]);
                            // Clean up any trailing garbage
                            $candidate = preg_replace('/\s*[^\w\s]$/', '', $candidate);

                            if (! empty($candidate) && strlen($candidate) > 2) {
                                $vendor = $candidate;
                                \Log::info('GroqAIService: Platform fallback vendor found: '.$vendor);
                                break;
                            }
                        }
                    }
                }

                // Last resort: if still empty, clear it
                if (empty($vendor)) {
                    $vendor = '';
                }
            }

            // 2. Unicode Cleanup (e.g. u0026 -> &)
            if (str_contains($vendor, 'u0026')) {
                $vendor = str_replace('u0026', '&', $vendor);
            }
            $vendor = html_entity_decode($vendor);

            // 3. PATTERN CLEANING FOR E-WALLET RECEIPTS
            // Clean pattern "MERCHANT - SERVICE_NAME - ID" or "PREFIX - NAME - ID_NUMBER"
            // Example: "GobBills - PLN Token - 24122867682" → "PLN Token"
            //          "OVO - Starbucks - 1234567890" → "Starbucks"
            $vendor = $this->cleanEWalletVendorPattern($vendor);

            // 4. SMART NOMINAL SELECTION
            // If all_detected_totals exists, prioritize by priority (1=final/total amount)
            $finalNominal = (int) ($data['nominal'] ?? 0);
            $normalizedTotals = $this->normalizeDetectedTotals($detectedTotals);

            if (! empty($normalizedTotals)) {
                // Get the first one (lowest priority number = priority 1 = the actual total)
                $priorityTotal = $normalizedTotals[0]; // Should be sorted by priority
                if (isset($priorityTotal['amount']) && $priorityTotal['amount'] > 0) {
                    \Log::info('GroqAIService: Smart nominal selection', [
                        'original_nominal' => $finalNominal,
                        'priority_1_amount' => $priorityTotal['amount'],
                        'priority_1_label' => $priorityTotal['label'] ?? 'Unknown',
                    ]);
                    $finalNominal = (int) $priorityTotal['amount'];
                }
            }

            // 5. ULTRA SMART DATE EXTRACTION FALLBACK
            $finalDate = $data['tanggal'] ?? '';
            if (empty($finalDate) || strlen($finalDate) < 10) {
                // Try to extract from raw text if AI missed it
                // Look for Reference Numbers that often contain dates: YYYYMMDD or YYMMDD
                // e.g. 9527120260102105... -> 20260102
                if (preg_match('/(?:Ref|Reference|No|Nomor)\s*(?:No|Number)?[:\.\s]*(\d{10,25})/i', $rawText, $matches)) {
                    $refNo = $matches[1];
                    // Look for 202[4-9][01][0-9][0-3][0-9] (YYYYMMDD)
                    if (preg_match('/(202[4-9])(0[1-9]|1[0-2])(0[1-9]|[12][0-9]|3[01])/', $refNo, $dateParts)) {
                        $finalDate = "{$dateParts[1]}-{$dateParts[2]}-{$dateParts[3]}";
                        \Log::info('GroqAIService: Extracted date from Ref No (YYYYMMDD)', ['ref' => $refNo, 'date' => $finalDate]);
                    }
                    // Look for 2[4-9][01][0-9][0-3][0-9] (YYMMDD) - risky but often used
                    elseif (preg_match('/(2[4-9])(0[1-9]|1[0-2])(0[1-9]|[12][0-9]|3[01])/', substr($refNo, 0, 10), $dateParts)) {
                        $finalDate = "20{$dateParts[1]}-{$dateParts[2]}-{$dateParts[3]}";
                        \Log::info('GroqAIService: Extracted date from Ref No (YYMMDD)', ['ref' => $refNo, 'date' => $finalDate]);
                    }
                }

                // If still empty, try generic date regex in raw text
                if (empty($finalDate)) {
                    if (preg_match('/(\d{1,2})[\s\-\/](Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec|Januari|Februari|Maret|April|Mei|Juni|Juli|Agustus|September|Oktober|November|Desember|Des|Okt|Agu)[\s\-\/](\d{2,4})/i', $rawText, $matches)) {
                        // This will be handled by TesseractService/LocalParser too, but good to have here
                    }
                }
            }

            $items = $data['items'] ?? [];
            if (empty($items)) {
                \Log::warning('GroqAIService: No items extracted from OCR', ['vendor' => $vendor]);
            }

            return [
                'success' => true,
                'data' => [
                    'vendor' => $vendor,
                    'platform' => $data['platform'] ?? '',
                    'nominal' => $finalNominal,
                    'tanggal' => $finalDate,
                    'items' => $items,
                    'fraud_risk_score' => $data['fraud_risk_score'] ?? 0,
                    'sanity_check_notes' => $data['sanity_check_notes'] ?? '',
                    'policy_violations' => $data['policy_violations'] ?? [],
                    'suggested_category' => $data['suggested_category'] ?? null,
                    'accounting_split' => $data['accounting_split'] ?? [],
                    'confidence_score' => (int) ($data['confidence_score'] ?? 0),
                    'raw_text' => $rawText,
                    'all_detected_totals' => $normalizedTotals,
                ],
            ];

        } catch (Exception $e) {
            \Log::error('Failed to parse Groq OCR response: '.$e->getMessage(), ['content_preview' => substr($content, 0, 500)]);

            return [
                'success' => false,
                'error' => 'Gagal memproses response AI: '.$e->getMessage(),
            ];
        }
    }

    public function validateReceiptData(array $ocrData, array $userInput): array
    {
        try {
            if (empty($this->apiKey)) {
                throw new Exception('API Key Groq belum dikonfigurasi (reimbursement.groq_api_key).');
            }

            $prompt = $this->buildValidationPrompt($ocrData, $userInput);

            $response = $this->client->post('https://api.groq.com/openai/v1/chat/completions', [
                'json' => [
                    'model' => 'llama-3.3-70b-versatile',
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0,
                    'max_tokens' => 500,
                ],
                'headers' => [
                    'Authorization' => 'Bearer '.$this->apiKey,
                ],
            ]);

            $result = json_decode($response->getBody(), true);
            $content = $result['choices'][0]['message']['content'];

            // Log RAW content for debugging
            \Log::info('GroqAIService: Raw Validation Response', ['content' => $content]);

            $parsed = $this->parseValidationResponse($content);

            // If parse failed, return error
            if (! $parsed['success']) {
                \Log::error('GroqAIService: parseValidationResponse failed', [
                    'error' => $parsed['error'] ?? 'Unknown',
                    'content_preview' => substr($content, 0, 200),
                ]);

                return $parsed;
            }

            // Fallback if AI response is incomplete but successfully parsed
            if ($parsed['success']) {
                $data = $parsed['data'];

                // Ensure required fields exist
                if (! isset($data['notes'])) {
                    $data['notes'] = '';
                }
                if (! isset($data['vendor_match'])) {
                    $data['vendor_match'] = [];
                }
                if (! isset($data['nominal_match'])) {
                    $data['nominal_match'] = [];
                }
                if (! isset($data['tanggal_match'])) {
                    $data['tanggal_match'] = [];
                }

                // Fallback Vendor Match if missing
                if (! isset($data['vendor_match']['percentage']) || ! isset($data['vendor_match']['status'])) {
                    $ocrVendor = $ocrData['vendor'] ?? '';
                    $ocrPlatform = $ocrData['platform'] ?? '';
                    $inputVendor = $userInput['nama_vendor'] ?? '';

                    // Simple similarity check
                    similar_text(strtolower($ocrVendor), strtolower($inputVendor), $p1);
                    similar_text(strtolower($ocrPlatform), strtolower($inputVendor), $p2);

                    $bestMatch = max($p1, $p2);
                    $status = $bestMatch >= 80 ? 'pass' : 'fail';

                    $data['vendor_match'] = [
                        'percentage' => round($bestMatch),
                        'status' => $status,
                    ];
                    $data['notes'] .= ' (Fallback Vendor Match Used)';
                }

                // Fallback Nominal Match if missing
                if (! isset($data['nominal_match']['status'])) {
                    $ocrNominal = (float) ($ocrData['nominal'] ?? 0);
                    $inputNominal = (float) ($userInput['nominal'] ?? 0);
                    $diff = abs($ocrNominal - $inputNominal);

                    // Rule: Tolerance 1.0
                    $status = $diff <= 1.0 ? 'pass' : 'fail';

                    $data['nominal_match'] = [
                        'status' => $status,
                        'difference' => $diff,
                        'note' => $status === 'pass' ? 'Nominal cocok via fallback' : 'Nominal berbeda',
                    ];
                }

                // Fallback Tanggal Match if missing
                if (! isset($data['tanggal_match']['status'])) {
                    $ocrDate = $ocrData['tanggal'] ?? '';
                    $inputDate = $userInput['tanggal_transaksi'] ?? '';

                    $status = ($ocrDate === $inputDate) ? 'pass' : 'fail';

                    $data['tanggal_match'] = [
                        'status' => $status,
                    ];
                }

                return [
                    'success' => true,
                    'data' => $data,
                ];
            }

            return $parsed;

        } catch (Exception $e) {
            \Log::error('GroqAIService validation error: '.$e->getMessage());

            return [
                'success' => false,
                'error' => 'Gagal memvalidasi data: '.$e->getMessage(),
            ];
        }
    }

    private function buildValidationPrompt(array $ocrData, array $userInput): string
    {
        $ocrVendor = $ocrData['vendor'] ?? '-';
        $ocrPlatform = $ocrData['platform'] ?? '-';
        $ocrNominal = $ocrData['nominal'] ?? 0;
        $ocrDate = $ocrData['tanggal'] ?? '-';
        $ocrInvoiceNum = $ocrData['invoice_number'] ?? '-';
        $ocrRaw = $ocrData['raw_text'] ?? '-';
        $ocrRawPartial = substr($ocrRaw, 0, 500);

        $inputVendor = $userInput['nama_vendor'];
        $inputNominal = $userInput['nominal'];
        $inputDate = $userInput['tanggal_transaksi'];

        return <<<EOT
Validasi data input user vs hasil OCR struk.

        DATA OCR (Dari Struk):
        - Vendor: $ocrVendor
        - Platform: $ocrPlatform
        - Nominal: $ocrNominal
        - Tanggal: $ocrDate
        - No Invoice: $ocrInvoiceNum
        - Raw Text Partial: $ocrRawPartial

        DATA INPUT USER:
        - Vendor: $inputVendor
        - Nominal: $inputNominal
        - Tanggal: $inputDate

        TUGAS:
        1. Bandingkan Vendor. 'Tamiya Toys (Tokopedia)' vs 'Tamiya Toys' adalah MATCH (karena platform diabaikan). 
           **ATURAN KETAT VENDOR**: 
           - Jika ada nama orang/merchant yang jelas (misal: "HABIB IHSANUL AZHAR"), itulah vendornya. 
           - Abaikan fragmen noise seperti "MO Em ANY PA", "sa ... an", atau baris yang mengandung banyak simbol. 
           - Jika hasil OCR vendor adalah noise, jangan sarankan ke user.
        2. Bandingkan Nominal. Toleransi selisih <= 1 rupiah. 
           **SANGAT KRITIS - TOTAL AKHIR**: 
           - Jika nominal user (Rp 52.500) cocok dengan nominal OCR manapun di struk (terutama yang berlabel 'Total Transaksi'), maka status WAJIB "pass" dan catatan HARUS KOSONG. 
           - **DILARANG KERAS** menampilkan "Bukan Total Akhir" atau warning apapun jika angkanya sudah sama (52.500 == 52.500). Itu adalah kecocokan 100%.
        3. Bandingkan Tanggal. Format YYYY-MM-DD.
        4. Ekstrak No Invoice dari OCR (jika belum ada di input).

        Output JSON:
        {
          "vendor_match": {"status": "pass"/"fail"/"warning", "percentage": 0-100},
          "nominal_match": {"status": "pass"/"fail", "difference": 0, "note": "..."},
          "tanggal_match": {"status": "pass"/"fail"},
          "invoice_number": "...",
          "notes": "penjelasan singkat jika fail"
        }
        
        RESPOND JSON ONLY.
EOT;
    }

    private function parseValidationResponse(string $content): array
    {
        $data = $this->extractJson($content);
        if (! $data) {
            return ['success' => false, 'error' => 'Invalid JSON from AI'];
        }

        return ['success' => true, 'data' => $data];
    }

    private function extractJson(string $text): ?array
    {
        if (preg_match('/\{[\s\S]*\}/', $text, $matches)) {
            $json = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $json;
            }
        }

        return null;
    }

    /**
     * Ultra Smart Pre-cleaning for OCR Text
     */
    private function preCleanOcrText(string $text): string
    {
        // ============================================================
        // ULTRA SMART OCR PRE-CLEANING FOR MESSY RECEIPTS
        // ============================================================

        // IMPORTANT: First, extract and preserve date patterns to avoid corruption
        $datesFound = [];
        $monthPattern = 'Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec|Januari|Februari|Maret|April|Mei|Juni|Juli|Agustus|September|Oktober|November|Desember|Des|Okt|Agu';
        $sepPattern = '[\s\-\/\.\'\"]';
        if (preg_match_all("/(?:\d{1,2}$sepPattern*($monthPattern)$sepPattern*\d{2,4}|(?:19|20)\d{2}[-\/\.]\d{1,2}[-\/\.]\d{1,2}|\d{1,2}[-\/\.]\d{1,2}[-\/\.](?:19|20)\d{2})/i", $text, $matches)) {
            $datesFound = $matches[0];
        }

        // 1. Remove non-printable characters and excess control chars
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);

        // 2. Normalize whitespace (tabs, multiple newlines, etc.)
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        // 3. Remove common garbage characters/symbols from receipts (BUT preserve date separators)
        $text = preg_replace('/[|■□▪◆•~^\[\]{}<>]/', '', $text);
        $text = preg_replace('/[!]{3,}|[#]{3,}|[*]{3,}/', '', $text); // Excessive punctuation

        // 3.1 Fix common OCR 'O' vs '0' in dates/numbers context
        $text = preg_replace('/(\d)[Oo]\b/', '$10', $text); // 1O -> 10 at end of word
        $text = preg_replace('/\b[Oo](\d)/', '0$1', $text); // O1 -> 01 at start of word
        $text = preg_replace('/(\d)[Oo](\d)/', '$10$2', $text); // 1O0 -> 100
        $text = preg_replace('/([0-9.,]+)[Oo]([0-9.,]+)/', '$10$2', $text); // 10.OOO -> 10.000 (Partial)
        $text = preg_replace_callback('/(?<=[Rr]p|[Ii][Dd][Rr]|[Pp][Pp]|[Kk][Pp])\s*([0-9O.,]+)/i', function ($m) {
            return str_ireplace('O', '0', $m[0]);
        }, $text); // Handle Rp 1O.OOO and common misreads like Pp/Kp

        // 3.2 Remove specific Vendor/App Noise (BYOND/BRImo specific)
        $text = preg_replace('/^[Cc]i\s+PBV\s+/im', '', $text);
        $text = preg_replace('/^PBV\s+/im', '', $text);
        $text = preg_replace('/\b(?:BYOND|BRImo|BANK\s+BRI|BRI)\b/i', '', $text);
        $text = preg_replace('/\bby\s+BSI\b/i', '', $text);
        $text = preg_replace('/\bby\s+BRI\b/i', '', $text);
        $text = preg_replace('/^BSI\s+/im', '', $text);

        // 4. Fix common OCR character substitutions (CRITICAL FOR MESSY TEXT)
        $fixes = [
            // Numbers to letters
            '/\b1\b(?=[A-Za-z])/i' => 'I',    // 1ndomaret → Indomaret
            '/\b0\b(?=[A-Za-z])/i' => 'O',    // 0ffice → Office
            '/\b5\b(?=[A-Za-z])/i' => 'S',    // 5tore → Store
            '/\b8\b(?=[A-Za-z])/i' => 'B',    // 8lue → Blue

            // Common typos
            '/\b(1|l)\s*(n|I)/i' => 'In',   // l n → In
            '/\brn\b/' => 'm',               // rn → m (broken letter)
            '/\bvv\b/' => 'w',                // vv → w
            '/\bcl\b/' => 'd',                // cl → d
            '/\b[Pp][Pp]\b/' => 'Rp',          // Pp -> Rp
            '/\b[Kk][Pp]\b/' => 'Rp',          // Kp -> Rp
            '/\bTERBILANG\b.*$/im' => '',     // Remove Terbilang and everything after it on that line

            // Specific vendor name fixes (VERY IMPORTANT)
            '/\b1ndomaret\b/i' => 'Indomaret',
            '/\bAlfamrt\b/i' => 'Alfamart',
            '/\bAlfa\s*mart\b/i' => 'Alfamart',
            '/\bGo\s*P\s*ay\b/i' => 'GoPay',
            '/\bShopee\s*P\s*ay\b/i' => 'ShopeePay',
            '/\bAyam\s*Geprek\s*Jo\s*Pak\s*Dhe\b/i' => 'Ayam Geprek Jogja Pak Dhe',
            '/\bAyam\s*Geprek\s*Jo\b/i' => 'Ayam Geprek Jogja',
            '/\bA\s*ya\s*m\s*Geprek\b/i' => 'Ayam Geprek',
            '/\bNas\s*i\s*Uduk\b/i' => 'Nasi Uduk',
            '/\bGado\s*Ga\s*do\b/i' => 'Gado Gado',
            '/\bMie\s*Ga\s*coan\b/i' => 'Mie Gacoan',
            '/\bStar\s*buck\s*/i' => 'Starbucks',
            '/\bGrab\s*taxi\b/i' => 'Grab',
            '/\bGo\s*jek\b/i' => 'Gojek',
            '/\bBlue\s*bird\b/i' => 'Blue Bird',

            // Clean up merged words from OCR
            '/\b(Jenis|Total|Nominal)(Transaksi|Pembayaran)\b/i' => '$1 $2',
            '/\b(Jenis\s*)(Transaksi)\b/i' => 'Jenis Transaksi',

            // Fix broken payment text
            '/\b[O0]RlS\b/i' => 'QRIS',
            '/\b[O0]RIS\b/i' => 'QRIS',
            '/\bORIS\s*Bayar\b/i' => 'QRIS Bayar',
        ];

        foreach ($fixes as $pattern => $replacement) {
            $text = preg_replace($pattern, $replacement, $text);
        }

        // 5. Fix character splits in words (e.g., "A ya m Geprek" → "Ayam Geprek")
        $text = preg_replace('/\b([A-Za-z]+)\s+([A-Za-z])\b/', '$1$2', $text);

        // 6. Fix broken words with numbers in between (e.g., "Ayam123Geprek" → "Ayam Geprek")
        $text = preg_replace('/([A-Za-z]+)\d{2,}([A-Za-z]+)/', '$1 $2', $text);

        // 7. Fix single-letter words that should be part of larger word
        $text = preg_replace('/\s([A-Za-z])\s(?=[A-Za-z])/', '$1', $text);

        // 8. Fix common merchant name OCR errors (COMPREHENSIVE LIST)
        $merchantFixes = [
            '/\bAya.*Geprek.*Pak.*Dhe\b/i' => 'Ayam Geprek Jogja Pak Dhe',
            '/\bGeprek.*Pak.*Dhe\b/i' => 'Ayam Geprek Jogja Pak Dhe',
            '/\bMc.*Donalds?\b/i' => 'McDonalds',
            '/\bKFC\s*\d*\b/i' => 'KFC',
            '/\bPizza.*Hut\b/i' => 'Pizza Hut',
            '/\bDomino.*Pizza\b/i' => 'Domino Pizza',
            '/\bWarung.*Padang\b/i' => 'Warung Padang',
            '/\bSoto.*Ayar\b/i' => 'Soto Ayam',
            '/\bBakso.*Kuah\b/i' => 'Bakso Kuah',

            // Marketplaces
            '/\b(Toko)?\s*pedi\s*a\b/i' => 'Tokopedia',
            '/\bShop\s*ee\b/i' => 'Shopee',
            '/\bLaza\s*da\b/i' => 'Lazada',
            '/\bBli\s*bli\b/i' => 'Blibli',
            '/\bZalora\b/i' => 'Zalora',

            // Convenience stores
            '/\bIndo.*mart\b/i' => 'Indomaret',
            '/\bAlfa.*mart\b/i' => 'Alfamart',
            '/\bAlfa.*midi\b/i' => 'Alfamidi',
        ];

        foreach ($merchantFixes as $pattern => $replacement) {
            $text = preg_replace($pattern, $replacement, $text);
        }

        // 9. Process line-by-line to fix split words
        $lines = explode("\n", $text);
        $cleanedLines = [];
        foreach ($lines as $line) {
            $line = trim($line);

            // AGGRESSIVE NOISE REMOVAL: Strip common OCR artifacts from start/end of line
            // e.g. "|", "_", ".", ",", ":", "-", "=" at the edges
            $line = preg_replace('/^[\s\.\,\:\|\-\=_]+|[\s\.\,\:\|\-\=_]+$/', '', $line);
            $line = trim($line);

            // If a line is mostly single letters separated by spaces (e.g. "H E L L O")
            if (preg_match_all('/[A-Za-z]\s/', $line) > 8 && strlen($line) < 60) {
                $line = str_replace(' ', '', $line);
            }

            // Remove lines that are just garbage numbers/symbols or mostly symbols
            $symbolCount = preg_match_all('/[^a-zA-Z0-9\s]/', $line);
            $alphaCount = preg_match_all('/[a-zA-Z]/', $line);
            
            // If a line has more symbols than letters and is short, it's likely noise
            if ($symbolCount > $alphaCount && strlen($line) < 20) {
                continue;
            }

            // EXTRA NOISE FILTER: Catch patterns like "5." or "1." followed by symbols/random chars at the start
            if (preg_match('/^\d+[\s\.\,\:\|\"\'\-\=_]{1,}/', $line) && strlen($line) < 15) {
                continue;
            }

            // FILTER FRAGMENTED NOISE: e.g. "MO Em ANY PA", "sa ... an"
            if (preg_match('/([A-Z][a-z]?\s+){2,}/', $line) && strlen($line) < 30 && !preg_match('/(Bank|Fast|Biaya|Total|Habib|Ihsanul)/i', $line)) {
                continue;
            }

            // Remove BRI specific noise from watermarks
            if (preg_match('/(Alias Penerima|Sumber Dana|Informasi)\s*\d*\./i', $line) || preg_match('/\.\.\./', $line)) {
                continue;
            }

            if (preg_match('/^[\d\s\-\.\:\|]+$/', $line) && !preg_match('/\d{2,}/', $line)) {
                continue;
            }

            if (! empty($line)) {
                $cleanedLines[] = $line;
            }
        }
        $text = implode("\n", $cleanedLines);

        // 10. Remove obvious "status bar" noise lines (phone battery, network, etc.)
        $text = preg_replace('/^(LTE|4G|5G|100%|Battery|VoLTE|WIFI|\d{1,2}:\d{2}).*$/im', '', $text);

        // 11. Remove duplicate consecutive lines (OCR often reads same line twice)
        $lines = explode("\n", $text);
        $prevLine = '';
        $dedupedLines = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line !== $prevLine && ! empty($line)) {
                $dedupedLines[] = $line;
                $prevLine = $line;
            }
        }
        $text = implode("\n", $dedupedLines);

        // 12. Final cleanup - normalize spacing again (BUT preserve date separators)
        $text = preg_replace('/\s{2,}/', ' ', $text);
        $text = preg_replace('/\n\s*\n\s*\n/', "\n\n", $text);

        // 13. Restore any preserved dates if they were corrupted
        if (! empty($datesFound)) {
            foreach ($datesFound as $date) {
                // Check if this date pattern exists in cleaned text and fix it
                if (preg_match('/(\d{1,2})\s{2,}([A-Za-z]+)\s{2,}(\d{2,4})/', $text, $m)) {
                    // Date has too many spaces, fix it
                    $fixedDate = "{$m[1]} {$m[2]} {$m[3]}";
                    $text = preg_replace('/\d{1,2}\s{2,}[A-Za-z]+\s{2,}\d{2,4}/', $fixedDate, $text, 1);
                }
                // Check if date separators were removed
                if (preg_match('/(\d{1,2})([A-Za-z]+)(\d{2,4})/', $text, $m) && ! stripos($date, $m[1].$m[2].$m[3])) {
                    // Original had spaces, restore proper format
                    $text = preg_replace('/(\d{1,2})([A-Za-z]+)(\d{2,4})/', '$1 $2 $3', $text, 1);
                }
            }
        }

        return trim($text);
    }

    private function normalizeDetectedTotals(array $totals): array
    {
        $normalized = [];
        foreach ($totals as $total) {
            if (isset($total['amount']) && isset($total['label'])) {
                $normalized[] = [
                    'label' => $total['label'],
                    'amount' => (int) $total['amount'],
                    'priority' => (int) ($total['priority'] ?? 999),
                ];
            }
        }

        // Sort by priority (asc)
        usort($normalized, fn ($a, $b) => $a['priority'] <=> $b['priority']);

        return $normalized;
    }

    private function cleanEWalletVendorPattern(string $vendor): string
    {
        if (empty($vendor)) {
            return $vendor;
        }

        $originalVendor = $vendor;

        // Pattern: "MERCHANT - SERVICE_NAME - ID" or "PREFIX - NAME - ID_NUMBER"
        // Match 3+ parts separated by " - " where the last parts look like ID/number
        $patterns = [
            // Pattern 1: "MERCHANT - SERVICE_NAME - NUMBER" → extract SERVICE_NAME
            '/^(.+?)\s*-\s*(.+?)\s*-\s*(\d+.*)$/i',
            // Pattern 2: "MERCHANT - SERVICE_NAME - ALPHANUMERIC_ID" → extract SERVICE_NAME
            '/^(.+?)\s*-\s*(.+?)\s*-\s*([A-Z0-9#]{6,}.*)$/i',
            // Pattern 3: More flexible - any 3 parts with last part containing numbers/IDs
            '/^(.+?)\s*-\s*(.+?)\s*-\s*(.{6,})$/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, trim($vendor), $matches)) {
                $prefix = trim($matches[1]);
                $serviceName = trim($matches[2]);
                $id = trim($matches[3] ?? '');

                \Log::info('GroqAIService: Detected E-Wallet pattern', [
                    'original' => $originalVendor,
                    'prefix' => $prefix,
                    'service_name' => $serviceName,
                    'id_part' => $id,
                ]);

                // Validation: serviceName should be meaningful (not just a number)
                // AND should NOT end with numbers/IDs/hyphens (which means it was mis-parsed)
                if (preg_match('/[a-zA-Z]/', $serviceName) && ! preg_match('/[\d#]$/', $serviceName)) {
                    // serviceName has letters and doesn't end with number/ID
                    $vendorToReturn = $serviceName;

                    $cleanedVendor = trim($vendorToReturn);
                    \Log::info('GroqAIService: Cleaned vendor from pattern', ['result' => $cleanedVendor]);

                    return $cleanedVendor;
                } elseif (preg_match('/[a-zA-Z]/', $serviceName)) {
                    // Service name has letters but might have trailing numbers
                    // Try to strip trailing number/ID parts
                    $serviceName = preg_replace('/[\s\-#\d]+$/', '', $serviceName);
                    $serviceName = trim($serviceName);

                    if (! empty($serviceName)) {
                        \Log::info('GroqAIService: Cleaned vendor (stripped trailing)', ['result' => $serviceName]);

                        return $serviceName;
                    }
                }
            }
        }

        // Fallback: If vendor contains 3+ hyphens and likely pattern, try more aggressive extraction
        if (substr_count($vendor, '-') >= 2) {
            $parts = array_map('trim', explode('-', $vendor));

            // If we have at least 3 parts, and last part is numeric-heavy
            if (count($parts) >= 3) {
                $lastPart = end($parts);

                // Check if last part is mostly numbers/IDs
                if (preg_match('/^\d/', $lastPart) || strlen(preg_replace('/[^0-9]/', '', $lastPart)) > 5) {
                    // Extract middle parts (skip first and last)
                    $middleParts = array_slice($parts, 1, -1);
                    if (! empty($middleParts)) {
                        $serviceName = trim(implode(' - ', $middleParts));

                        if (preg_match('/[a-zA-Z]/', $serviceName) && strlen($serviceName) > 2) {
                            \Log::info('GroqAIService: Cleaned vendor via fallback split', [
                                'original' => $originalVendor,
                                'result' => $serviceName,
                            ]);

                            return $serviceName;
                        }
                    }
                }
            }
        }

        // If no pattern matches, return vendor as-is
        return $vendor;
    }
}
