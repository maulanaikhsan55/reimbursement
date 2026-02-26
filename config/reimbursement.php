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
        'vendor_fuzzy_threshold' => env('AI_VENDOR_THRESHOLD', 75),
        'auto_reject_on_all_fail' => env('AI_AUTO_REJECT_FAIL', false),
    ],

    /* =====================================================
     * FEATURE FLAGS (SAFE ROLLOUT)
     * ===================================================== */
    'features' => [
        'realtime_notifications' => env('FEATURE_REALTIME_NOTIFICATIONS', true),
        'broadcast_notifications' => env('FEATURE_BROADCAST_NOTIFICATIONS', true),
        'echo_client' => env('FEATURE_ECHO_CLIENT', true),
    ],

    'notifications' => [
        // sync: langsung insert notifikasi (rekomendasi local/dev)
        // queue: pakai job queue (pastikan queue worker berjalan)
        'delivery' => env('NOTIFICATION_DELIVERY', 'sync'),
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
            'application/pdf',
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
        'duplicate_window_days' => env('DUPLICATE_WINDOW_DAYS', 15),
        'workday_start_hour' => env('WORKDAY_START_HOUR', 8),
        'workday_end_hour' => env('WORKDAY_END_HOUR', 18),
    ],
];
