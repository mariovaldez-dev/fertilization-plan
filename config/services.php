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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'auth_api' => [
        'url' => env('AUTH_API_URL'),
    ],

    'api_fert' => [
        'url' => env('API_FERT_URL', 'http://localhost:8000/api'),
    ],

    'ultramsg' => [
        'instance_id' => env('ULTRAMSG_INSTANCE_ID', 'instance30943'),
        'token' => env('ULTRAMSG_TOKEN', 'jmcd6zz2c1jq1je0'),
        'url' => env('ULTRAMSG_URL', 'https://api.ultramsg.com/instance30943/messages/document'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

];
