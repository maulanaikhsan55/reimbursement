<?php

if (! function_exists('format_rupiah')) {
    /**
     * Format number to Rupiah
     *
     * @param  float|int  $amount
     * @return string
     */
    function format_rupiah($amount)
    {
        return 'Rp '.number_format($amount, 0, ',', '.');
    }
}
