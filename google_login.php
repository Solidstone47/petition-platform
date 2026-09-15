<?php

require_once "includes/functions.php";
require_once "includes/auth.php";

/*
|--------------------------------------------------------------------------
| GOOGLE OAUTH CONFIGURATION
|--------------------------------------------------------------------------
*/

$google_client_id =
    "62180751187-i9737ts81pqdnftnj1lf82bm2vs6prmm.apps.googleusercontent.com";

$google_redirect_uri =
    "http://localhost/petition_platform/google_callback.php";


/*
|--------------------------------------------------------------------------
| GOOGLE AUTHORIZATION URL
|--------------------------------------------------------------------------
*/

$params = [

    "client_id" =>
        $google_client_id,

    "redirect_uri" =>
        $google_redirect_uri,

    "response_type" =>
        "code",

    "scope" =>
        "openid email profile",

    "access_type" =>
        "online",

    "prompt" =>
        "select_account"

];


$google_url =
    "https://accounts.google.com/o/oauth2/v2/auth?"
    . http_build_query($params);


/*
|--------------------------------------------------------------------------
| SEND USER TO GOOGLE
|--------------------------------------------------------------------------
*/

header(
    "Location: " . $google_url
);

exit;