<?php

return [

    'meta' => [
        'pixel_id' => env('META_PIXEL_ID'),
    ],
    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'sewolah' => [
        'whatsapp' => env('SEWOLAH_WHATSAPP_NUMBER', '601116946696'),
        'email' => env('SEWOLAH_ADMIN_EMAIL', 'sewolah.hq@gmail.com'),
    ],
];
