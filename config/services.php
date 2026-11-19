<?php

return [
    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'whatsapp' => [
        'token' => env('WHATSAPP_TOKEN'),
        'phone_id' => env('WHATSAPP_PHONE_ID'),'dry_run' => env('WHATSAPP_DRY_RUN', true),
        'template' => env('WHATSAPP_TEMPLATE', 'student_absence_alert'),
        'leave_template' => env('WHATSAPP_LEAVE_TEMPLATE', 'student_leave_request'),
        'hm_phone' => env('WHATSAPP_HM_PHONE'),   // Head Master's number; falls back to the school phone
        'template_lang' => env('WHATSAPP_TEMPLATE_LANG', 'en_US'),
        'auto_absence' => env('WHATSAPP_AUTO_ABSENCE', true),
    ],
];
