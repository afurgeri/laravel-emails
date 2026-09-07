<?php

return [
    'mailer' => env('EMAILS_MAILER'),

    'queue' => [
        'connection' => env('EMAILS_QUEUE_CONNECTION'),
        'queue' => env('EMAILS_QUEUE'),
        'tries' => (int) env('EMAILS_QUEUE_TRIES', 3),
        'backoff' => [60, 300, 900],
    ],
];
