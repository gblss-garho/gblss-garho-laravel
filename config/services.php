<?php

return [
    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'whatsapp' => [
        'token' => env('WHATSAPP_TOKEN'),
        'phone_id' => env('WHATSAPP_PHONE_ID'),'dry_run' => env('WHATSAPP_DRY_RUN', true),
        'auto_absence' => env('WHATSAPP_AUTO_ABSENCE', true),
    ],
];
