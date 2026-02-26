<?php

namespace App\Http\Requests\Atasan;

use Illuminate\Foundation\Http\FormRequest;

class ApprovePengajuanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'catatan_atasan' => ['nullable', 'string'],
        ];
    }
}

