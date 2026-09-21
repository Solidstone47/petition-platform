<?php

// ======================================================
// GOOGLE OAUTH CONFIGURATION
// ======================================================
//
// All Google OAuth credentials are read from environment
// variables so they are never hardcoded in source files.
//
// Required environment variables:
//
//   GOOGLE_CLIENT_ID
//   GOOGLE_CLIENT_SECRET
//   GOOGLE_REDIRECT_URI
//
// ======================================================

define(
    'GOOGLE_CLIENT_ID',
    getenv('GOOGLE_CLIENT_ID') ?: ''
);

define(
    'GOOGLE_CLIENT_SECRET',
    getenv('GOOGLE_CLIENT_SECRET') ?: ''
);

define(
    'GOOGLE_REDIRECT_URI',
    getenv('GOOGLE_REDIRECT_URI') ?: 'http://localhost/petition_platform/google_callback.php'
);

// ======================================================
// GOOGLE AUTHORIZATION URL BUILDER
// ======================================================

function google_auth_url()
{
    $params = [
        'client_id'     => GOOGLE_CLIENT_ID,
        'redirect_uri'  => GOOGLE_REDIRECT_URI,
        'response_type' => 'code',
        'scope'         => 'openid email profile',
        'access_type'   => 'online',
        'prompt'        => 'select_account'
    ];

    return 'https://accounts.google.com/o/oauth2/v2/auth?'
        . http_build_query($params);
}
