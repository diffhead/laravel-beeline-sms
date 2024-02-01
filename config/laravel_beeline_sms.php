<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel beeline sms sending
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for beeline sms sending
    |
    | For example: https://a2p-sms-https.beeline.ru/proto/http/
    |
    */
    'api_host' => env('LARAVEL_BEELINE_API_HOST'),

    'login' => env('LARAVEL_BEELINE_LOGIN'),

    'password' => env('LARAVEL_BEELINE_PASSWORD'),

    'sender' => env('LARAVEL_BEELINE_SENDER'),

    'gzip' => env('LARAVEL_BEELINE_GZIP', false),

    'comment' => env('LARAVEL_BEELINE_COMMENT'),

    'driver' => \SaintSample\LaravelBeelineSms\BeelineA2PSMS::class,

    'log_channel' => env('LARAVEL_BEELINE_LOG_CHANNEL', 'null'),

    'message_registry' => env('LARAVEL_BEELINE_MESSAGE_REGISTRY', false),

    'messages' => [
        'table' => 'laravel_beeline_sms',

        'model' => \SaintSample\LaravelBeelineSms\Models\SmsMessage::class,

        'auto_update_statuses' => env('LARAVEL_BEELINE_MESSAGE_AUTO_STATUS', false) && env('LARAVEL_BEELINE_MESSAGE_REGISTRY', false),
    ]
];