<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY', 'AIzaSyACpbVqBRE4WNqNbsyqqsRQDMmrHqlNd7Q'),
        'api_key_2' => env('GEMINI_API_KEY_2', 'AIzaSyAG12llaY-UyA7DBesa6xolsTt0UeMjz_w'),
        'api_key_3' => env('GEMINI_API_KEY_3', 'AIzaSyDFU4U_pB9txpbJh2HOT3GZVRzwus9u2Ng'),
        'api_key_4' => env('GEMINI_API_KEY_4', 'AIzaSyD0APmICsRA4-4LIXxPapEpexvU6PNka50'),
        'api_key_5' => env('GEMINI_API_KEY_5', 'AIzaSyDdY1v1dWguWLtXYWvEfuTaFJxCk7mCmmw'),
    ],

];
