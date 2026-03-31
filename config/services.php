<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location of this information, allowing packages to have a conventional
    | file to locate the various service credentials.
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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', '/auth/google/callback'),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URI', '/auth/facebook/callback'),
    ],

    'firefly' => [
        'url' => env('FIREFLY_URL'),
        'api_key' => env('FIREFLY_API_KEY'),
        'namespace' => env('FIREFLY_NAMESPACE'),
        'token_pool' => env('FIREFLY_TOKEN_POOL'),
        'token_name' => env('FIREFLY_TOKEN_NAME', 'KhaiTriCredit'),
        'platform_identity' => env('FIREFLY_PLATFORM_IDENTITY', 'platform'),
        'signer' => env('FIREFLY_SIGNER'),
        'audit_topic' => env('FIREFLY_AUDIT_TOPIC', 'audit'),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
        'assistant_model' => env('GEMINI_ASSISTANT_MODEL', 'gemini-2.5-flash-lite'),
        'assistant_context' => env('GEMINI_ASSISTANT_CONTEXT', ''),
    ],

    'vnpay' => [
        'url' => env('VNPAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),
        'tmn_code' => env('VNPAY_TMN_CODE'),
        'hash_secret' => env('VNPAY_HASH_SECRET'),
        'return_url' => env('VNPAY_RETURN_URL'),
        'ipn_url' => env('VNPAY_IPN_URL'),
        'expire_minutes' => env('VNPAY_EXPIRE_MINUTES', 15),
        'locale' => env('VNPAY_LOCALE', 'vn'),
        'bank_code' => env('VNPAY_BANK_CODE'),
    ],
];