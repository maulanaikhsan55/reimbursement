<?php

namespace App\Enums;

enum PengajuanStatus: string
{
    case VALIDASI_AI = 'validasi_ai';
    case MENUNGGU_ATASAN = 'menunggu_atasan';
    case DITOLAK_ATASAN = 'ditolak_atasan';
    case MENUNGGU_FINANCE = 'menunggu_finance';
    case DITOLAK_FINANCE = 'ditolak_finance';
    case TERKIRIM_ACCURATE = 'terkirim_accurate';
    case DICAIRKAN = 'dicairkan';
    case SELESAI = 'selesai';

    public function label(): string
    {
        return match ($this) {
            self::VALIDASI_AI => 'Validasi AI',
            self::MENUNGGU_ATASAN => 'Menunggu Atasan',
            self::DITOLAK_ATASAN => 'Ditolak Atasan',
            self::MENUNGGU_FINANCE => 'Menunggu Finance',
            self::DITOLAK_FINANCE => 'Ditolak Finance',
            self::TERKIRIM_ACCURATE => 'Terkirim ke Accurate',
            self::DICAIRKAN => 'Dicairkan',
            self::SELESAI => 'Selesai',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::VALIDASI_AI => 'info',
            self::MENUNGGU_ATASAN, self::MENUNGGU_FINANCE => 'warning',
            self::DITOLAK_ATASAN, self::DITOLAK_FINANCE => 'danger',
            self::TERKIRIM_ACCURATE, self::DICAIRKAN, self::SELESAI => 'success',
        };
    }
}
