<?php

return [
    'default' => env('OTP_DRIVER', 'email'),

    'code_length' => env('OTP_CODE_LENGTH', 6),
    'ttl' => env('OTP_TTL', 300), // seconds
    'throttle_seconds' => env('OTP_THROTTLE', 30), // seconds between resends

    'drivers' => [
        'email' => [
            'from' => env('MAIL_FROM_ADDRESS'),
            'name' => env('MAIL_FROM_NAME', config('app.name')),
            'subject' => env('OTP_EMAIL_SUBJECT', 'Your Login Code'),
        ],
        // 'sms' => [
        //     'provider' => 'twilio',
        //     'from' => env('TWILIO_FROM'),
        // ],
    ],
];
