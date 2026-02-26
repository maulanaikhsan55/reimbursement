<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class SendToAccurateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'coa_id' => ['required', 'exists:coa,coa_id'],
            'kas_bank_id' => ['required', 'exists:kas_bank,kas_bank_id'],
            'catatan_finance' => ['nullable', 'string', 'max:500'],
        ];
    }
}

