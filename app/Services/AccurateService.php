<?php

namespace App\Services;

use App\Models\LogTransaksiAccurate;
use App\Models\Pengajuan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AccurateService
{
    private string $apiHost;

    private string $apiToken;

    private string $apiSecret;

    private string $databaseId;

    private int $timeout;

    public function __construct()
    {
        $this->apiHost = trim(config('reimbursement.accurate.api_host') ?? '');
        // Ensure host doesn't have trailing slash
        $this->apiHost = rtrim($this->apiHost, '/');

        $this->apiToken = trim(config('reimbursement.accurate.api_token') ?? '');
        $this->apiSecret = trim(config('reimbursement.accurate.api_secret') ?? '');
        $this->databaseId = trim(config('reimbursement.accurate.database_id') ?? '');
        $this->timeout = (int) config('reimbursement.accurate.timeout', 30);
    }

    /**
     * Get the correct base path for the API.
     * Most database hosts require '/accurate' prefix.
     */
    private function getBasePath(string $path): string
    {
        // For zeus.accurate.id, /accurate prefix IS required in the URL
        if (strpos($this->apiHost, 'zeus.accurate.id') !== false) {
            return '/accurate'.$path;
        }

        return $path;
    }

    public function sendTransaction(Pengajuan $pengajuan, string $coaCode, string $kasBankCode): array
    {
        $payload = [];
        try {
            // 1. Pre-flight Validation
            $validation = $this->validatePengajuanForTransfer($pengajuan);
            if (! $validation['success']) {
                return [
                    'success' => false,
                    'message' => $validation['message'],
                ];
            }

            // 2. CRITICAL: Prevent Duplicates
            // Check if transaction with this number already exists in Accurate
            // This handles cases where previous request timed out but actually succeeded in Accurate
            $check = $this->checkTransactionExists($pengajuan->nomor_pengajuan);
            if ($check['success'] && $check['exists']) {
                $existingData = $check['data'];
                $transactionId = $existingData['number'] ?? $existingData['id'] ?? 'EXISTING';

                Log::warning('AccurateService: Transaction already exists in Accurate. Preventing duplicate.', [
                    'nomor_pengajuan' => $pengajuan->nomor_pengajuan,
                    'transaction_id' => $transactionId,
                ]);

                return [
                    'success' => true,
                    'transaction_id' => $transactionId,
                    'message' => 'Transaksi sudah ada di Accurate (Mencegah Duplikasi)',
                    'is_duplicate' => true,
                ];
            }

            $payload = $this->prepareJournalVoucherPayload($pengajuan, $coaCode, $kasBankCode);

            Log::info('Sending Journal Voucher to Accurate', [
                'pengajuan_id' => $pengajuan->pengajuan_id,
                'nomor_pengajuan' => $pengajuan->nomor_pengajuan,
                'payload' => $payload,
            ]);

            // Path for saving journal voucher
            $path = $this->getBasePath('/api/journal-voucher/save.do');
            $response = Http::timeout($this->timeout)
                ->withHeaders($this->getAuthHeaders('POST', $path))
                ->post($this->apiHost.$path, $payload);

            return $this->handleResponse($pengajuan, $payload, $response);
        } catch (\Exception $e) {
            Log::error('Failed to send journal to Accurate', [
                'pengajuan_id' => $pengajuan->pengajuan_id,
                'error' => $e->getMessage(),
            ]);

            return $this->logFailedTransaction($pengajuan, $payload, null, $e->getMessage());
        }
    }

    /**
     * Pre-flight validation before sending to Accurate
     */
    public function validatePengajuanForTransfer(Pengajuan $pengajuan): array
    {
        // 1. Cek Nominal
        if ($pengajuan->nominal <= 0) {
            return ['success' => false, 'message' => 'Nominal pengajuan harus lebih dari 0.'];
        }

        // 2. Cek COA dan Accurate ID-nya
        if (! $pengajuan->coa_id) {
            return ['success' => false, 'message' => 'Akun biaya (COA) belum dipilih.'];
        }

        $coa = $pengajuan->coa;
        if (! $coa || ! $coa->accurate_id) {
            return ['success' => false, 'message' => 'Akun biaya (COA) belum tersinkronisasi dengan Accurate.'];
        }

        // 3. Cek Kas/Bank dan Accurate ID-nya
        if (! $pengajuan->kas_bank_id) {
            return ['success' => false, 'message' => 'Kas/Bank pembayaran belum dipilih.'];
        }

        $kasBank = $pengajuan->kasBank;
        if (! $kasBank || ! $kasBank->accurate_id) {
            return ['success' => false, 'message' => 'Kas/Bank belum tersinkronisasi dengan Accurate.'];
        }

        // 4. Cek Tanggal Transaksi
        if (! $pengajuan->tanggal_transaksi) {
            return ['success' => false, 'message' => 'Tanggal transaksi tidak valid.'];
        }

        // 5. Cek Koneksi (Ping ke Accurate)
        if (! $this->apiToken || ! $this->databaseId) {
            return ['success' => false, 'message' => 'Konfigurasi Accurate belum lengkap (Token/Database ID kosong).'];
        }

        return ['success' => true];
    }

    private function getAuthHeaders(string $method, string $path): array
    {
        // DOCUMENTATION: For API Token method, timestamp can be Unix Timestamp Millisecond
        $timestamp = (string) (int) (microtime(true) * 1000);

        // DOCUMENTATION: X-Api-Signature is ONLY the HMAC-SHA256 of the X-Api-Timestamp
        // using Signature Secret as Key. Method and Path are NOT included for API Token.
        $signature = base64_encode(
            hash_hmac('sha256', $timestamp, $this->apiSecret, true)
        );

        $headers = [
            'Authorization' => 'Bearer '.$this->apiToken,
            'X-Api-Signature' => $signature,
            'X-Api-Timestamp' => $timestamp,
            'Accept' => 'application/json',
        ];

        if (! empty($this->databaseId)) {
            $headers['X-Database-Id'] = $this->databaseId;
        }

        return $headers;
    }

    private function getTokenInfo(): array
    {
        try {
            // DOCUMENTATION: getTokenInfo path is /api/api-token.do
            $path = '/api/api-token.do';
            $host = 'https://account.accurate.id';

            // DOCUMENTATION: getTokenInfo must use POST
            $response = Http::timeout($this->timeout)
                ->withHeaders($this->getAuthHeaders('POST', $path))
                ->post($host.$path);

            if ($response->successful()) {
                $data = $response->json();
                if ($data === null) {
                    return [
                        'success' => false,
                        'message' => 'Token info response bukan JSON. Gagal verifikasi.',
                    ];
                }
                if (isset($data['s']) && $data['s'] === true) {
                    return [
                        'success' => true,
                        'data' => $data['d'],
                        'message' => 'Token valid',
                    ];
                }

                return [
                    'success' => false,
                    'message' => 'Token tidak valid: '.($data['d'][0] ?? 'Unknown error'),
                ];
            }

            return [
                'success' => false,
                'message' => 'Gagal verifikasi token: '.$response->status().' - '.$response->body(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error verifikasi token: '.$e->getMessage(),
            ];
        }
    }

    public function fetchCOAsFromAccurate(?string $lastModified = null): array
    {
        try {
            $path = $this->getBasePath('/api/glaccount/list.do');
            $allCoas = [];
            $page = 1;
            $pageSize = 1000;
            $hasMore = true;

            while ($hasMore) {
                $params = [
                    'fields' => 'id,no,name,accountType,suspended,currencyCode,balance,asOf,notes,description,memo,note,remarks,remark,parentAccount,parent.id,parent.no',
                    'sp.pageSize' => $pageSize,
                    'sp.page' => $page,
                ];

                if ($lastModified) {
                    $params['filter.lastModified.val[0]'] = $lastModified;
                    $params['filter.lastModified.op'] = 'GREATER_THAN';
                }

                $response = Http::timeout($this->timeout)
                    ->withHeaders($this->getAuthHeaders('GET', $path))
                    ->get($this->apiHost.$path, $params);

                if (! $response->successful()) {
                    return [
                        'success' => false,
                        'data' => [],
                        'message' => 'Gagal mengambil data COA: '.$response->status(),
                    ];
                }

                $responseData = $response->json();
                if ($responseData === null || ! isset($responseData['s']) || $responseData['s'] === false) {
                    $errorMsg = $responseData['d'][0] ?? 'Unknown Accurate error';

                    return [
                        'success' => false,
                        'data' => [],
                        'message' => 'Error dari Accurate: '.$errorMsg,
                    ];
                }

                $coas = $responseData['d'] ?? [];
                $allCoas = array_merge($allCoas, $coas);

                // Check if we need to fetch more pages
                if (count($coas) < $pageSize) {
                    $hasMore = false;
                } else {
                    $page++;
                }
            }

            $processedCoas = array_filter(array_map(function ($coa) {
                $number = $coa['no'] ?? $coa['number'] ?? null;
                $name = $coa['name'] ?? null;

                if (! empty($number) && ! empty($name)) {
                    return [
                        'kode_coa' => trim((string) $number),
                        'nama_coa' => trim((string) $name),
                        'tipe_akun' => $this->mapAccountType($coa['accountType'] ?? null),
                        'raw_account_type' => $coa['accountType'] ?? null,
                        'is_active' => ! ($coa['suspended'] ?? false),
                        'currency_code' => $coa['currencyCode'] ?? 'IDR',
                        'saldo' => $coa['balance'] ?? 0,
                        'as_of_date' => isset($coa['asOf']) ? \Carbon\Carbon::createFromFormat('d/m/Y', $coa['asOf'])->format('Y-m-d') : null,
                        'deskripsi' => ($coa['memo'] ?? '') ?: ($coa['notes'] ?? '') ?: ($coa['description'] ?? '') ?: ($coa['note'] ?? '') ?: ($coa['remarks'] ?? '') ?: ($coa['remark'] ?? '') ?: null,
                        'accurate_id' => $coa['id'] ?? null,
                        'parent_accurate_id' => $coa['parentAccount']['id'] ?? $coa['parent']['id'] ?? null,
                        'parent_code' => (strlen($number) > 4) ? substr($number, 0, -2) : null,
                    ];
                }

                return null;
            }, $allCoas), fn ($item) => $item !== null);

            return [
                'success' => true,
                'data' => $processedCoas,
                'message' => 'Berhasil mengambil data COA dari Accurate',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => [],
                'message' => 'Error: '.$e->getMessage(),
            ];
        }
    }

    private function mapAccountType(?string $accountType = null): string
    {
        if (! $accountType) {
            return 'expense';
        }

        $mapping = [
            'CASH_BANK' => 'asset',
            'ACCOUNT_RECEIVABLE' => 'asset',
            'INVENTORY' => 'asset',
            'OTHER_CURRENT_ASSET' => 'asset',
            'FIXED_ASSET' => 'asset',
            'OTHER_ASSET' => 'asset',
            'ACCUMULATED_DEPRECIATION' => 'asset',
            'ACCOUNT_PAYABLE' => 'liability',
            'OTHER_CURRENT_LIABILITY' => 'liability',
            'LONG_TERM_LIABILITY' => 'liability',
            'EQUITY' => 'equity',
            'REVENUE' => 'revenue',
            'COST_OF_GOODS_SOLD' => 'expense',
            'COGS' => 'expense',
            'EXPENSE' => 'expense',
            'OTHER_EXPENSE' => 'expense',
            'OTHER_INCOME' => 'revenue',
        ];

        return $mapping[strtoupper($accountType)] ?? 'expense';
    }

    public function fetchKasBankFromAccurate(?string $lastModified = null): array
    {
        try {
            $path = $this->getBasePath('/api/glaccount/list.do');
            $allAccounts = [];
            $page = 1;
            $pageSize = 1000;
            $hasMore = true;

            while ($hasMore) {
                $params = [
                    'fields' => 'id,no,name,accountType,suspended,currencyCode,balance,asOf,notes,description,memo,note,remarks,remark,parentAccount,parent.id,parent.no',
                    'filter.accountType.val[0]' => 'CASH_BANK',
                    'sp.pageSize' => $pageSize,
                    'sp.page' => $page,
                ];

                if ($lastModified) {
                    $params['filter.lastModified.val[0]'] = $lastModified;
                    $params['filter.lastModified.op'] = 'GREATER_THAN';
                }

                $response = Http::timeout($this->timeout)
                    ->withHeaders($this->getAuthHeaders('GET', $path))
                    ->get($this->apiHost.$path, $params);

                if (! $response->successful()) {
                    return [
                        'success' => false,
                        'data' => [],
                        'message' => 'Gagal mengambil data Kas/Bank: '.$response->status(),
                    ];
                }

                $responseData = $response->json();
                if ($responseData === null || ! isset($responseData['s']) || $responseData['s'] === false) {
                    $errorMsg = $responseData['d'][0] ?? 'Unknown Accurate error';

                    return [
                        'success' => false,
                        'data' => [],
                        'message' => 'Error dari Accurate: '.$errorMsg,
                    ];
                }

                $accounts = $responseData['d'] ?? [];
                $allAccounts = array_merge($allAccounts, $accounts);

                if (count($accounts) < $pageSize) {
                    $hasMore = false;
                } else {
                    $page++;
                }
            }

            $processed = array_filter(array_map(function ($acc) {
                $number = $acc['no'] ?? $acc['number'] ?? null;
                $name = $acc['name'] ?? null;

                if (! empty($number) && ! empty($name)) {
                    return [
                        'kode_kas_bank' => trim((string) $number),
                        'nama_kas_bank' => trim((string) $name),
                        'nomor_rekening' => $number,
                        'nama_bank' => $name,
                        'tipe_akun' => $acc['accountType'] ?? 'CASH_BANK',
                        'is_active' => ! ($acc['suspended'] ?? false),
                        'currency_code' => $acc['currencyCode'] ?? 'IDR',
                        'saldo' => $acc['balance'] ?? 0,
                        'as_of_date' => isset($acc['asOf']) ? \Carbon\Carbon::createFromFormat('d/m/Y', $acc['asOf'])->format('Y-m-d') : null,
                        'deskripsi' => ($acc['memo'] ?? '') ?: ($acc['notes'] ?? '') ?: ($acc['description'] ?? '') ?: ($acc['note'] ?? '') ?: ($acc['remarks'] ?? '') ?: ($acc['remark'] ?? '') ?: null,
                        'accurate_id' => $acc['id'] ?? null,
                        'parent_accurate_id' => $acc['parentAccount']['id'] ?? $acc['parent']['id'] ?? null,
                        'parent_code' => (strlen($number) > 4) ? substr($number, 0, -2) : null,
                    ];
                }

                return null;
            }, $allAccounts), fn ($item) => $item !== null);

            return [
                'success' => true,
                'data' => $processed,
                'message' => 'Berhasil mengambil data Kas/Bank dari Accurate',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => [],
                'message' => 'Error: '.$e->getMessage(),
            ];
        }
    }

    public function fetchDepartmentsFromAccurate(?string $lastModified = null): array
    {
        try {
            $path = $this->getBasePath('/api/department/list.do');
            $allDepartments = [];
            $page = 1;
            $pageSize = 1000;
            $hasMore = true;

            while ($hasMore) {
                $params = [
                    'fields' => 'id,name,description,notes,memo,note,remarks,remark,suspended',
                    'sp.pageSize' => $pageSize,
                    'sp.page' => $page,
                ];

                if ($lastModified) {
                    $params['filter.lastModified.val[0]'] = $lastModified;
                    $params['filter.lastModified.op'] = 'GREATER_THAN';
                }

                $response = Http::timeout($this->timeout)
                    ->withHeaders($this->getAuthHeaders('GET', $path))
                    ->get($this->apiHost.$path, $params);

                if (! $response->successful()) {
                    return [
                        'success' => false,
                        'data' => [],
                        'message' => 'Gagal mengambil data Departemen: '.$response->status(),
                    ];
                }

                $responseData = $response->json();
                if ($responseData === null || ! isset($responseData['s']) || $responseData['s'] === false) {
                    $errorMsg = $responseData['d'][0] ?? 'Unknown Accurate error';

                    return [
                        'success' => false,
                        'data' => [],
                        'message' => 'Error dari Accurate: '.$errorMsg,
                    ];
                }

                $departments = $responseData['d'] ?? [];
                $allDepartments = array_merge($allDepartments, $departments);

                if (count($departments) < $pageSize) {
                    $hasMore = false;
                } else {
                    $page++;
                }
            }

            $processed = array_map(function ($dept) {
                return [
                    'accurate_id' => $dept['id'] ?? null,
                    'nama_departemen' => $dept['name'] ?? null,
                    'deskripsi' => ($dept['memo'] ?? '') ?: ($dept['description'] ?? '') ?: ($dept['notes'] ?? '') ?: ($dept['note'] ?? '') ?: ($dept['remarks'] ?? '') ?: ($dept['remark'] ?? '') ?: null,
                    'is_active' => ! ($dept['suspended'] ?? false),
                ];
            }, $allDepartments);

            return [
                'success' => true,
                'data' => $processed,
                'message' => 'Berhasil mengambil data Departemen dari Accurate',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => [],
                'message' => 'Error: '.$e->getMessage(),
            ];
        }
    }

    private function prepareJournalVoucherPayload(Pengajuan $pengajuan, string $coaCode, string $kasBankCode): array
    {
        // Format tanggal ke dd/mm/yyyy (WAJIB format ini!)
        $date = \Carbon\Carbon::parse($pengajuan->tanggal_transaksi)->format('d/m/Y');
        $nominal = (float) $pengajuan->nominal;

        // Deskripsi lengkap untuk Keterangan (Header)
        $userDisplay = $pengajuan->user->name ?? 'Unknown';
        $deptDisplay = $pengajuan->departemen->nama_departemen ?? 'Unknown';
        $description = sprintf(
            'Reimbursement %s - %s (%s)',
            $pengajuan->nomor_pengajuan,
            $userDisplay,
            $deptDisplay
        );

        // Deskripsi untuk Memo (Line Items)
        $vendorPart = $pengajuan->nama_vendor ? " [Vendor: {$pengajuan->nama_vendor}]" : '';
        $memoDetail = ($pengajuan->deskripsi ?? 'Biaya reimbursement')."{$vendorPart} - {$userDisplay} ({$deptDisplay})";

        $payload = [
            'transDate' => $date,
            'description' => $description,

            // Detail jurnal: WAJIB balance (Debit = Kredit)
            'detailJournalVoucher' => [
                // Baris 1: DEBIT (Beban/Expense)
                [
                    'accountNo' => $coaCode,
                    'amount' => $nominal,
                    'amountType' => 'DEBIT',
                    'memo' => $memoDetail,
                ],
                // Baris 2: KREDIT (Kas/Bank)
                [
                    'accountNo' => $kasBankCode,
                    'amount' => $nominal,
                    'amountType' => 'CREDIT',
                    'memo' => 'Pembayaran '.$pengajuan->nomor_pengajuan.' ke '.$userDisplay.$vendorPart,
                ],
            ],
        ];

        // Add Department if synced
        if ($pengajuan->departemen && $pengajuan->departemen->accurate_id) {
            $payload['detailJournalVoucher'][0]['departmentId'] = $pengajuan->departemen->accurate_id;
        }

        return $payload;
    }

    private function handleResponse(Pengajuan $pengajuan, array $payload, $response): array
    {
        $statusCode = $response->status();

        if ($statusCode >= 200 && $statusCode < 300) {
            $responseData = $response->json();

            // Accurate Online typically returns the saved object in the 'r' key
            // and success status in 's' key
            $transactionId = $responseData['r']['number'] ?? $responseData['r']['id'] ?? $responseData['id'] ?? 'ACC-'.time();

            $this->logSuccessfulTransaction($pengajuan, $payload, $responseData, (string) $transactionId);

            return [
                'success' => true,
                'transaction_id' => $transactionId,
                'message' => 'Berhasil dikirim ke Accurate',
            ];
        } else {
            $errorMessage = $response->json()['message'] ?? $response->body();

            $this->logFailedTransaction($pengajuan, $payload, $response->json(), $errorMessage);

            return [
                'success' => false,
                'error_message' => $errorMessage,
                'message' => 'Gagal dikirim ke Accurate: '.$errorMessage,
            ];
        }
    }

    private function logSuccessfulTransaction(Pengajuan $pengajuan, array $payload, array $response, string $transactionId): void
    {
        LogTransaksiAccurate::create([
            'pengajuan_id' => $pengajuan->pengajuan_id,
            'request_payload' => json_encode($payload),
            'response_payload' => json_encode($response),
            'status' => 'success',
            'accurate_transaction_id' => $transactionId,
            'error_message' => null,
            'sent_at' => now(),
        ]);

        Log::info('Transaction successfully sent to Accurate', [
            'pengajuan_id' => $pengajuan->pengajuan_id,
            'transaction_id' => $transactionId,
        ]);
    }

    private function logFailedTransaction(Pengajuan $pengajuan, array $payload, ?array $response, string $errorMessage): array
    {
        LogTransaksiAccurate::create([
            'pengajuan_id' => $pengajuan->pengajuan_id,
            'request_payload' => ! empty($payload) ? json_encode($payload) : null,
            'response_payload' => $response ? json_encode($response) : null,
            'status' => 'failed',
            'accurate_transaction_id' => null,
            'error_message' => $errorMessage,
            'sent_at' => now(),
        ]);

        Log::error('Transaction failed to send to Accurate', [
            'pengajuan_id' => $pengajuan->pengajuan_id,
            'error' => $errorMessage,
        ]);

        return [
            'success' => false,
            'error_message' => $errorMessage,
            'message' => 'Gagal dikirim ke Accurate: '.$errorMessage,
        ];
    }

    public function testConnection(): array
    {
        try {
            // First check token info
            $tokenInfo = $this->getTokenInfo();
            if (! $tokenInfo['success']) {
                return $tokenInfo;
            }

            $path = $this->getBasePath('/api/glaccount/list.do');
            $response = Http::timeout($this->timeout)
                ->withHeaders($this->getAuthHeaders('GET', $path))
                ->get($this->apiHost.$path, [
                    'limit' => 1,
                    'offset' => 0,
                ]);

            if ($response->successful()) {
                $responseData = $response->json();
                if ($responseData === null || ! isset($responseData['s'])) {
                    return [
                        'success' => false,
                        'message' => 'Koneksi ke host database berhasil tapi response bukan JSON yang valid.',
                    ];
                }

                return [
                    'success' => true,
                    'message' => 'Koneksi ke Accurate berhasil. Host: '.$this->apiHost,
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Koneksi ke host database gagal: '.$response->status().' - '.$response->body(),
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Fetch current balance for a specific Accurate account ID
     */
    public function getAccountBalance(string $accurateId): array
    {
        try {
            $path = $this->getBasePath('/api/glaccount/detail.do');
            $response = Http::timeout($this->timeout)
                ->withHeaders($this->getAuthHeaders('GET', $path))
                ->get($this->apiHost.$path, [
                    'id' => $accurateId,
                ]);

            if ($response->successful()) {
                $responseData = $response->json();
                if ($responseData && isset($responseData['s']) && $responseData['s'] === true) {
                    return [
                        'success' => true,
                        'balance' => $responseData['d']['balance'] ?? 0,
                        'name' => $responseData['d']['name'] ?? 'Unknown',
                    ];
                }

                return [
                    'success' => false,
                    'message' => $responseData['d'][0] ?? 'Gagal mengambil detail akun',
                ];
            }

            return ['success' => false, 'message' => 'Gagal koneksi ke Accurate: '.$response->status()];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error: '.$e->getMessage()];
        }
    }

    /**
     * Check if a transaction exists in Accurate
     */
    public function checkTransactionExists(string $number, string $type = 'JOURNAL_VOUCHER'): array
    {
        try {
            // Mapping type to correct list API
            $pathMap = [
                'JOURNAL_VOUCHER' => '/api/journal-voucher/list.do',
                // Add more if needed
            ];

            $path = $this->getBasePath($pathMap[$type] ?? $pathMap['JOURNAL_VOUCHER']);
            $response = Http::timeout($this->timeout)
                ->withHeaders($this->getAuthHeaders('GET', $path))
                ->get($this->apiHost.$path, [
                    'filter.number.val[0]' => $number,
                    'fields' => 'id,number,transDate',
                ]);

            if ($response->successful()) {
                $responseData = $response->json();
                if ($responseData && isset($responseData['s']) && $responseData['s'] === true) {
                    $exists = ! empty($responseData['d']);

                    return [
                        'success' => true,
                        'exists' => $exists,
                        'data' => $exists ? $responseData['d'][0] : null,
                    ];
                }
            }

            return ['success' => false, 'message' => 'Gagal verifikasi transaksi'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error: '.$e->getMessage()];
        }
    }

    /**
     * Fetch a single GL Account by its number
     */
    public function getAccountByNumber(string $no): array
    {
        try {
            $path = $this->getBasePath('/api/glaccount/list.do');
            $response = Http::timeout($this->timeout)
                ->withHeaders($this->getAuthHeaders('GET', $path))
                ->get($this->apiHost.$path, [
                    'fields' => 'id,no,name,accountType,suspended,currencyCode,balance,asOf,notes',
                    'filter.no.op' => 'EQUAL',
                    'filter.no.val[0]' => $no,
                ]);

            if ($response->successful()) {
                $responseData = $response->json();
                if ($responseData && isset($responseData['s']) && $responseData['s'] === true && ! empty($responseData['d'])) {
                    $acc = $responseData['d'][0];

                    return [
                        'success' => true,
                        'data' => [
                            'kode_coa' => trim((string) $acc['no']),
                            'nama_coa' => trim((string) $acc['name']),
                            'tipe_akun' => $this->mapAccountType($acc['accountType'] ?? null),
                            'is_active' => ! ($acc['suspended'] ?? false),
                            'currency_code' => $acc['currencyCode'] ?? 'IDR',
                            'saldo' => $acc['balance'] ?? 0,
                            'as_of_date' => isset($acc['asOf']) ? \Carbon\Carbon::createFromFormat('d/m/Y', $acc['asOf'])->format('Y-m-d') : null,
                            'deskripsi' => ($acc['memo'] ?? '') ?: ($acc['notes'] ?? '') ?: ($acc['description'] ?? '') ?: ($acc['note'] ?? '') ?: ($acc['remarks'] ?? '') ?: ($acc['remark'] ?? '') ?: null,
                            'accurate_id' => $acc['id'] ?? null,
                        ],
                    ];
                }

                return ['success' => false, 'message' => 'Akun tidak ditemukan di Accurate'];
            }

            return ['success' => false, 'message' => 'Gagal koneksi ke Accurate: '.$response->status()];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error: '.$e->getMessage()];
        }
    }

    /**
     * Fetch transactions for a specific account and period
     */
    public function getAccountTransactions(string $coaAccurateId, string $startDate, string $endDate): array
    {
        $allTransactions = [];

        // 1. Try GL History (covers all types)
        try {
            $path = $this->getBasePath('/api/gl-history/list.do');
            $response = Http::timeout($this->timeout)
                ->withHeaders($this->getAuthHeaders('GET', $path))
                ->get($this->apiHost.$path, [
                    'id' => $coaAccurateId,
                    'fromDate' => \Carbon\Carbon::parse($startDate)->format('d/m/Y'),
                    'toDate' => \Carbon\Carbon::parse($endDate)->format('d/m/Y'),
                    'fields' => 'id,no,number,transNo,date,transDate,description,amount,totalAmount,transType',
                ]);

            if ($response->successful()) {
                $responseData = $response->json();
                if ($responseData && isset($responseData['s']) && $responseData['s'] === true) {
                    foreach ($responseData['d'] ?? [] as $item) {
                        $allTransactions[] = [
                            'id' => $item['id'],
                            'number' => $item['number'] ?? $item['no'] ?? $item['transNo'] ?? 'Unknown',
                            'transDate' => $item['transDate'] ?? $item['date'] ?? '01/01/2000',
                            'description' => $item['description'] ?? '',
                            'totalAmount' => abs($item['amount'] ?? $item['totalAmount'] ?? 0),
                            'transType' => $item['transType'] ?? 'JOURNAL_VOUCHER',
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning('GL History fetch failed: '.$e->getMessage());
        }

        // 2. If empty or as extra insurance, try Journal Voucher list (which we know works for JVs)
        if (empty($allTransactions)) {
            try {
                $path = $this->getBasePath('/api/journal-voucher/list.do');
                $response = Http::timeout($this->timeout)
                    ->withHeaders($this->getAuthHeaders('GET', $path))
                    ->get($this->apiHost.$path, [
                        'fields' => 'id,number,transDate,description,totalAmount',
                        'filter.transDate.op' => 'BETWEEN',
                        'filter.transDate.val' => [\Carbon\Carbon::parse($startDate)->format('d/m/Y'), \Carbon\Carbon::parse($endDate)->format('d/m/Y')],
                    ]);

                if ($response->successful()) {
                    $responseData = $response->json();
                    if ($responseData && isset($responseData['s']) && $responseData['s'] === true) {
                        foreach ($responseData['d'] ?? [] as $item) {
                            $allTransactions[] = [
                                'id' => $item['id'],
                                'number' => $item['number'],
                                'transDate' => $item['transDate'],
                                'description' => $item['description'] ?? '',
                                'totalAmount' => $item['totalAmount'] ?? 0,
                                'transType' => 'JOURNAL_VOUCHER',
                            ];
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('JV list fallback failed: '.$e->getMessage());
            }
        }

        return [
            'success' => true,
            'data' => $allTransactions,
        ];
    }

    /**
     * Get detail of any transaction by its type and ID
     */
    public function getTransactionDetail(string $id, string $type): array
    {
        try {
            $pathMap = [
                'JOURNAL_VOUCHER' => '/api/journal-voucher/detail.do',
                'CASH_OUT' => '/api/cash-out/detail.do',
                'CASH_IN' => '/api/cash-in/detail.do',
                'BANK_TRANSFER' => '/api/bank-transfer/detail.do',
                'OTHER_DEPOSIT' => '/api/other-deposit/detail.do',
                'OTHER_PAYMENT' => '/api/other-payment/detail.do',
                'SALES_INVOICE' => '/api/sales-invoice/detail.do',
                'PURCHASE_INVOICE' => '/api/purchase-invoice/detail.do',
            ];

            $path = $this->getBasePath($pathMap[$type] ?? $pathMap['JOURNAL_VOUCHER']);
            $response = Http::timeout($this->timeout)
                ->withHeaders($this->getAuthHeaders('GET', $path))
                ->get($this->apiHost.$path, [
                    'id' => $id,
                ]);

            if ($response->successful()) {
                $responseData = $response->json();
                if ($responseData && isset($responseData['s']) && $responseData['s'] === true) {
                    return [
                        'success' => true,
                        'data' => $responseData['d'],
                    ];
                }
            }

            return ['success' => false, 'message' => "Gagal mengambil detail transaksi tipe $type"];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error: '.$e->getMessage()];
        }
    }

    /**
     * REKONSILIASI (ULTRA SMART)
     * Verifikasi apakah data di Accurate sama persis dengan di database kita
     */
    public function reconcileTransaction(Pengajuan $pengajuan): array
    {
        if (! $pengajuan->accurate_transaction_id) {
            return ['success' => false, 'message' => 'Transaksi belum terintegrasi dengan Accurate'];
        }

        $detail = $this->getTransactionDetail($pengajuan->accurate_transaction_id, 'JOURNAL_VOUCHER');
        if (! $detail['success']) {
            return ['success' => false, 'message' => 'Gagal mengambil data dari Accurate untuk rekonsiliasi'];
        }

        $accurateData = $detail['data'];
        $accurateNominal = (float) ($accurateData['totalAmount'] ?? 0);
        $dbNominal = (float) $pengajuan->nominal;

        $isMatch = abs($accurateNominal - $dbNominal) < 0.01;

        if ($isMatch) {
            return [
                'success' => true,
                'is_match' => true,
                'message' => 'Data sinkron: Nominal di Accurate (Rp '.number_format($accurateNominal).') sesuai dengan sistem.',
                'accurate_data' => $accurateData,
            ];
        } else {
            Log::error('RECONCILIATION FAILED', [
                'pengajuan_id' => $pengajuan->pengajuan_id,
                'db_nominal' => $dbNominal,
                'accurate_nominal' => $accurateNominal,
            ]);

            return [
                'success' => true,
                'is_match' => false,
                'message' => 'Data TIDAK SINKRON! Nominal di Accurate: Rp '.number_format($accurateNominal).', di Sistem: Rp '.number_format($dbNominal),
                'difference' => $accurateNominal - $dbNominal,
            ];
        }
    }
}
