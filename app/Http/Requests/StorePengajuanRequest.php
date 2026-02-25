<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StorePengajuanRequest - Validation rules for creating new reimbursement requests
 *
 * Implements strict validation for:
 * - Receipt age (max 15 days old)
 * - Transaction amount (Rp 1.000 - Rp 100.000.000)
 * - Vendor name format (alphanumeric + common symbols)
 * - File requirements (PDF/JPG/PNG, max 5MB)
 * - Description length (5-500 characters)
 */
class StorePengajuanRequest extends FormRequest
{
    /**
     * Determine if user is authorized to make this request
     */
    public function authorize(): bool
    {
        return $this->user() && in_array($this->user()->role, ['pegawai', 'atasan', 'finance']);
    }

    /**
     * Get the validation rules that apply to the request
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'tanggal_transaksi' => [
                'required',
                'date',
                'after_or_equal:'.now()->subDays(15)->toDateString(),
                'before_or_equal:'.now()->toDateString(),
            ],
            'kategori_id' => [
                'required',
                'integer',
                'exists:kategori_biaya,kategori_id',
            ],
            'judul' => [
                'required',
                'string',
                'min:3',
                'max:120',
            ],
            'nama_vendor' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[a-zA-Z0-9\s\-\&\.\,]+$/u',
            ],
            'jenis_transaksi' => [
                'nullable',
                'in:marketplace,transfer_direct,transport,other',
            ],
            'deskripsi' => [
                'required',
                'string',
                'min:10',
                'max:1000',
            ],
            'nominal' => [
                'required',
                'numeric',
                'min:1000',
                'max:100000000',
            ],
            'file_bukti' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png,webp',
                'max:5120',
            ],
            'catatan_pegawai' => [
                'nullable',
                'string',
                'max:500',
            ],
            'ocr_data_json' => [
                'nullable',
                'json',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tanggal_transaksi.required' => 'Tanggal transaksi harus diisi.',
            'tanggal_transaksi.date' => 'Format tanggal transaksi tidak valid.',
            'tanggal_transaksi.after_or_equal' => 'Bukti transaksi harus berumur maksimal 15 hari.',
            'tanggal_transaksi.before_or_equal' => 'Tanggal transaksi tidak boleh di masa depan.',

            'kategori_id.required' => 'Kategori biaya harus dipilih.',
            'kategori_id.exists' => 'Kategori biaya tidak ditemukan.',
            'kategori_id.integer' => 'Kategori biaya tidak valid.',

            'judul.required' => 'Judul / deskripsi singkat harus diisi.',
            'judul.min' => 'Judul / deskripsi singkat minimal 3 karakter.',
            'judul.max' => 'Judul / deskripsi singkat maksimal 120 karakter.',

            'nama_vendor.required' => 'Nama vendor/toko harus diisi.',
            'nama_vendor.min' => 'Nama vendor minimal 2 karakter.',
            'nama_vendor.max' => 'Nama vendor maksimal 100 karakter.',
            'nama_vendor.regex' => 'Nama vendor hanya boleh mengandung huruf, angka, dan tanda (-&.,).',

            'jenis_transaksi.in' => 'Jenis transaksi tidak valid.',

            'deskripsi.required' => 'Deskripsi pengajuan harus diisi.',
            'deskripsi.min' => 'Catatan detail minimal 10 karakter.',
            'deskripsi.max' => 'Catatan detail maksimal 1000 karakter.',

            'nominal.required' => 'Nominal pengajuan harus diisi.',
            'nominal.numeric' => 'Nominal harus berupa angka.',
            'nominal.min' => 'Nominal minimal Rp 1.000.',
            'nominal.max' => 'Nominal maksimal Rp 100.000.000 per pengajuan.',

            'file_bukti.required' => 'File bukti transaksi harus diunggah.',
            'file_bukti.file' => 'File bukti harus berupa file yang valid.',
            'file_bukti.mimes' => 'Format file hanya boleh PDF, JPG, PNG, atau WebP.',
            'file_bukti.max' => 'Ukuran file maksimal 5MB.',

            'catatan_pegawai.max' => 'Catatan pegawai maksimal 500 karakter.',
            'ocr_data_json.json' => 'Data OCR harus berupa JSON valid.',
        ];
    }

    /**
     * Prepare input for validation
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'judul' => trim((string) $this->input('judul', '')),
            'deskripsi' => trim((string) $this->input('deskripsi', '')),
            'nama_vendor' => trim((string) $this->input('nama_vendor', '')),
        ]);

        if ($this->has('nominal')) {
            $this->merge([
                'nominal' => $this->normalizeNominalInput($this->input('nominal')),
            ]);
        }
    }

    /**
     * Normalize nominal value from UI input to numeric float.
     *
     * Handles mixed separators (ID/US) and applies a safety fallback:
     * if parsed value is suspiciously small while digit-only value is large,
     * use digit-only value to avoid false "minimal Rp 1.000" errors.
     */
    private function normalizeNominalInput(mixed $value): float
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return 0.0;
        }

        $nominal = preg_replace('/[^\d.,]/', '', $raw) ?? '';
        if ($nominal === '') {
            return 0.0;
        }

        $digitsOnly = preg_replace('/\D+/', '', $nominal) ?? '';
        $digitsOnlyValue = $digitsOnly !== '' ? (float) $digitsOnly : 0.0;

        $hasDot = str_contains($nominal, '.');
        $hasComma = str_contains($nominal, ',');

        if ($hasDot && $hasComma) {
            $lastDot = strrpos($nominal, '.');
            $lastComma = strrpos($nominal, ',');

            if ($lastComma !== false && $lastDot !== false && $lastComma > $lastDot) {
                // Indonesian decimal format: 10.000,50
                $nominal = str_replace('.', '', $nominal);
                $nominal = str_replace(',', '.', $nominal);
            } else {
                // US decimal format: 10,000.50
                $nominal = str_replace(',', '', $nominal);
            }
        } elseif ($hasComma) {
            $parts = explode(',', $nominal);
            $lastPart = end($parts) ?: '';

            // Single comma + 1-2 trailing digits => decimal, otherwise thousands separator
            if (count($parts) === 2 && strlen($lastPart) <= 2) {
                $nominal = str_replace(',', '.', $nominal);
            } else {
                $nominal = str_replace(',', '', $nominal);
            }
        } elseif ($hasDot) {
            $parts = explode('.', $nominal);
            $lastPart = end($parts) ?: '';

            // Single dot + 1-2 trailing digits => decimal, otherwise thousands separator
            if (!(count($parts) === 2 && strlen($lastPart) <= 2)) {
                $nominal = str_replace('.', '', $nominal);
            }
        }

        $parsedValue = is_numeric($nominal) ? (float) $nominal : 0.0;

        // Safety net for malformed locale parsing: prefer strong digit-only value.
        if ($parsedValue < 1000 && $digitsOnlyValue >= 1000) {
            return $digitsOnlyValue;
        }

        return $parsedValue;
    }
}
