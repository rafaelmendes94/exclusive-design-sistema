<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'xbz' => [
        'cnpj' => env('XBZ_CNPJ'),
        'token' => env('XBZ_TOKEN'),
        'url' => env('XBZ_API_URL'),
    ],

    'asia' => [
        'key' => env('ASIA_API_KEY'),
        'secret' => env('ASIA_API_SECRET'),
        'url' => env('ASIA_API_URL'),
    ],

    'stricker' => [
        'client_id' => env('STRICKER_CLIENT_ID'),
        'access_key' => env('STRICKER_ACCESS_KEY'),
        'url' => env('STRICKER_API_URL'),
    ],

];
