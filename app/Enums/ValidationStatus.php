<?php

namespace App\Enums;

enum ValidationStatus: string
{
    case PENDING = 'pending';
    case VALID = 'valid';
    case INVALID = 'invalid';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Validasi',
            self::VALID => 'Valid',
            self::INVALID => 'Peringatan/Tidak Valid',
        };
    }
}
