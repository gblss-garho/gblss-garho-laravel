<?php

// Festive home-page themes. Dates are inclusive (Y-m-d) and can be changed in .env.
// Preview any time with  /?festive=sindhi   and force it off with  /?festive=off
return [
    'sindhi' => [
        'from' => env('FESTIVE_SINDHI_FROM', '2026-11-30'),
        'to'   => env('FESTIVE_SINDHI_TO', '2026-12-06'),
    ],
];
