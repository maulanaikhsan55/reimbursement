<?php

return [

    /* =====================================================
     * AI VALIDATION CONFIG
     * ===================================================== */
    'groq_api_key' => env('GROQ_API_KEY'),

    'ai_validation' => [
        'ocr_enabled' => env('AI_OCR_ENABLED', true),
        'ocr_confidence_threshold' => env('AI_OCR_THRESHOLD', 85),
        'duplicate_check_enabled' => env('AI_DUPLICATE_ENABLED', true),
        'vendor_verification_enabled' => env('AI_VENDOR_ENABLED', true),
        'vendor_fuzzy_threshold' => env('AI_VENDOR_THRESHOLD', 80),
        'auto_reject_on_all_fail' => env('AI_AUTO_REJECT_FAIL', false),
    ],

    /* =====================================================
     * ACCURATE ONLINE (API TOKEN METHOD – FINAL)
     * ===================================================== */
    'accurate' => [
        'api_host' => env('ACCURATE_API_HOST', 'https://public.accurate.id'),
        'api_token' => env('ACCURATE_API_TOKEN', env('ACCURATE_API_KEY')),
        'api_secret' => env('ACCURATE_API_SECRET'),
        'database_id' => env('ACCURATE_DATABASE_ID'),
        'timeout' => env('ACCURATE_API_TIMEOUT', 30),
    ],

    /* =====================================================
     * STORAGE CONFIG
     * ===================================================== */
    'storage' => [
        'bukti_path' => 'reimbursement/bukti',
        'max_file_size' => 5120, // KB (5MB)
        'allowed_mimes' => [
            'image/jpeg',
            'image/png',
            'image/webp',
        ],
    ],

    /* =====================================================
     * SECURITY CONFIG
     * ===================================================== */
    'security' => [
        'allowed_domains' => explode(',', env('ALLOWED_EMAIL_DOMAINS', 'humplus.id,gmail.com,yahoo.com,outlook.com')),
        'force_official_email' => env('FORCE_OFFICIAL_EMAIL', false),
    ],

    /* =====================================================
     * POLICY CONFIG
     * ===================================================== */
    'policy' => [
        'max_receipt_age_days' => env('MAX_RECEIPT_AGE_DAYS', 15),
    ],
];
