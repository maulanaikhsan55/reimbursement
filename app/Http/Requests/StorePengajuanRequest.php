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
                'min:5',
                'max:500',
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

            'nama_vendor.required' => 'Nama vendor/toko harus diisi.',
            'nama_vendor.min' => 'Nama vendor minimal 2 karakter.',
            'nama_vendor.max' => 'Nama vendor maksimal 100 karakter.',
            'nama_vendor.regex' => 'Nama vendor hanya boleh mengandung huruf, angka, dan tanda (-&.,).',

            'jenis_transaksi.in' => 'Jenis transaksi tidak valid.',

            'deskripsi.required' => 'Deskripsi pengajuan harus diisi.',
            'deskripsi.min' => 'Deskripsi minimal 5 karakter.',
            'deskripsi.max' => 'Deskripsi maksimal 500 karakter.',

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
        // Sanitize nominal input (remove symbols, handle ID/US formats)
        if ($this->has('nominal')) {
            $nominal = $this->input('nominal');
            $nominal = preg_replace('/[^\d,.]/', '', $nominal);

            // Handle Indonesian format (10.000,00) vs US format (10,000.00)
            if (str_contains($nominal, '.') && str_contains($nominal, ',')) {
                $lastDot = strrpos($nominal, '.');
                $lastComma = strrpos($nominal, ',');

                if ($lastComma > $lastDot) {
                    // ID Format: 10.000,00 → remove dots, replace comma
                    $nominal = str_replace('.', '', $nominal);
                    $nominal = str_replace(',', '.', $nominal);
                }
                // else: US Format already correct
            } elseif (str_contains($nominal, ',')) {
                // Only comma: assume ID thousands separator 10,000 → 10000
                if (strlen(explode(',', $nominal)[1] ?? '') <= 2) {
                    $nominal = str_replace(',', '.', $nominal); // Decimal separator
                } else {
                    $nominal = str_replace(',', '', $nominal); // Thousands separator
                }
            }

            $this->merge(['nominal' => (float) $nominal]);
        }
    }
}
