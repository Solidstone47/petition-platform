<?php

require_once "includes/db.php";
require_once "includes/functions.php";
require_once "includes/auth.php";

/*
|--------------------------------------------------------------------------
| GOOGLE OAUTH CONFIGURATION
|--------------------------------------------------------------------------
*/

$google_client_id =
    "62180751187-i9737ts81pqdnftnj1lf82bm2vs6prmm.apps.googleusercontent.com";

/*
|--------------------------------------------------------------------------
| IMPORTANT
|--------------------------------------------------------------------------
| Put your NEW Google client secret here after regenerating it.
|--------------------------------------------------------------------------
*/

$google_client_secret =
    "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";

$google_redirect_uri =
    "http://localhost/petition_platform/google_callback.php";


/*
|--------------------------------------------------------------------------
| CHECK GOOGLE RESPONSE
|--------------------------------------------------------------------------
*/

if (
    !isset($_GET['code']) ||
    $_GET['code'] === ''
) {

    set_flash(
        "error",
        "Google sign-in was cancelled or failed."
    );

    redirect("login.php");
}


$code = $_GET['code'];


/*
|--------------------------------------------------------------------------
| EXCHANGE AUTHORIZATION CODE FOR ACCESS TOKEN
|--------------------------------------------------------------------------
*/

$token_data = [

    "code" =>
        $code,

    "client_id" =>
        $google_client_id,

    "client_secret" =>
        $google_client_secret,

    "redirect_uri" =>
        $google_redirect_uri,

    "grant_type" =>
        "authorization_code"

];


$token_ch = curl_init(
    "https://oauth2.googleapis.com/token"
);


curl_setopt(
    $token_ch,
    CURLOPT_POST,
    true
);


curl_setopt(
    $token_ch,
    CURLOPT_POSTFIELDS,
    http_build_query($token_data)
);


curl_setopt(
    $token_ch,
    CURLOPT_RETURNTRANSFER,
    true
);


curl_setopt(
    $token_ch,
    CURLOPT_HTTPHEADER,
    [
        "Content-Type: application/x-www-form-urlencoded"
    ]
);


$token_response =
    curl_exec($token_ch);


$token_http_code =
    curl_getinfo(
        $token_ch,
        CURLINFO_HTTP_CODE
    );


$token_error =
    curl_error($token_ch);


curl_close($token_ch);


/*
|--------------------------------------------------------------------------
| CHECK TOKEN REQUEST
|--------------------------------------------------------------------------
*/

if (
    $token_response === false ||
    $token_error !== ''
) {

    set_flash(
        "error",
        "Unable to connect to Google. Please try again."
    );

    redirect("login.php");
}


$token_json =
    json_decode(
        $token_response,
        true
    );


if (
    $token_http_code !== 200 ||
    empty($token_json['access_token'])
) {

    set_flash(
        "error",
        "Google authentication failed. Please try again."
    );

    redirect("login.php");
}


$access_token =
    $token_json['access_token'];


/*
|--------------------------------------------------------------------------
| GET GOOGLE USER INFORMATION
|--------------------------------------------------------------------------
*/

$user_ch = curl_init(
    "https://www.googleapis.com/oauth2/v3/userinfo"
);


curl_setopt(
    $user_ch,
    CURLOPT_RETURNTRANSFER,
    true
);


curl_setopt(
    $user_ch,
    CURLOPT_HTTPHEADER,
    [
        "Authorization: Bearer " . $access_token
    ]
);


$google_user_response =
    curl_exec($user_ch);


$user_http_code =
    curl_getinfo(
        $user_ch,
        CURLINFO_HTTP_CODE
    );


$user_error =
    curl_error($user_ch);


curl_close($user_ch);


/*
|--------------------------------------------------------------------------
| CHECK USER REQUEST
|--------------------------------------------------------------------------
*/

if (
    $google_user_response === false ||
    $user_error !== ''
) {

    set_flash(
        "error",
        "Unable to retrieve your Google account information."
    );

    redirect("login.php");
}


$google_user =
    json_decode(
        $google_user_response,
        true
    );


/*
|--------------------------------------------------------------------------
| VERIFY GOOGLE USER DATA
|--------------------------------------------------------------------------
*/

if (
    $user_http_code !== 200 ||
    empty($google_user['sub']) ||
    empty($google_user['email'])
) {

    set_flash(
        "error",
        "Unable to verify your Google account."
    );

    redirect("login.php");
}


/*
|--------------------------------------------------------------------------
| GOOGLE USER DATA
|--------------------------------------------------------------------------
*/

$google_id =
    trim(
        $google_user['sub']
    );


$google_email =
    trim(
        $google_user['email']
    );


$google_name =
    trim(
        $google_user['name'] ?? 'Google User'
    );


/*
|--------------------------------------------------------------------------
| CHECK WHETHER GOOGLE ACCOUNT ALREADY EXISTS
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        id,
        fullname,
        email,
        google_id,
        country,
        password_reset_required
    FROM users
    WHERE google_id = ?
    LIMIT 1
");


if (!$stmt) {

    set_flash(
        "error",
        "Unable to process Google sign-in."
    );

    redirect("login.php");
}


$stmt->bind_param(
    "s",
    $google_id
);


$stmt->execute();


$result =
    $stmt->get_result();


$user =
    $result->fetch_assoc();


$stmt->close();


/*
|--------------------------------------------------------------------------
| EXISTING GOOGLE ACCOUNT
|--------------------------------------------------------------------------
|
| THIS IS THE IMPORTANT PART.
|
| If the Google account already exists, log the user in
| immediately. Do NOT send them to register.php.
|--------------------------------------------------------------------------
*/

if ($user) {

    /*
    --------------------------------------------------------------
    Check whether the administrator has forced a password reset.
    --------------------------------------------------------------
    */

    if (
        isset($user['password_reset_required']) &&
        (int) $user['password_reset_required'] === 1
    ) {

        /*
        ----------------------------------------------------------
        Google accounts normally do not use a local password.
        Therefore we simply allow the Google authentication
        to continue.
        ----------------------------------------------------------
        */

    }


    /*
    --------------------------------------------------------------
    Log the existing Google user in.
    --------------------------------------------------------------
    */

    login_user(
        (int) $user['id']
    );


    set_flash(
        "success",
        "Welcome back, " . $user['fullname'] . "!"
    );


    redirect("index.php");
}


/*
|--------------------------------------------------------------------------
| CHECK WHETHER EMAIL ALREADY EXISTS
|--------------------------------------------------------------------------
|
| The email may belong to a normal password account.
|
| We DO NOT automatically attach Google to it.
|
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        id,
        fullname,
        email,
        google_id
    FROM users
    WHERE email = ?
    LIMIT 1
");


if (!$stmt) {

    set_flash(
        "error",
        "Unable to process Google sign-in."
    );

    redirect("login.php");
}


$stmt->bind_param(
    "s",
    $google_email
);


$stmt->execute();


$result =
    $stmt->get_result();


$existing_user =
    $result->fetch_assoc();


$stmt->close();


/*
|--------------------------------------------------------------------------
| EMAIL ALREADY BELONGS TO NORMAL ACCOUNT
|--------------------------------------------------------------------------
*/

if ($existing_user) {

    set_flash(
        "error",
        "An account with this email already exists. Please log in using your existing password."
    );


    redirect("login.php");
}


/*
|--------------------------------------------------------------------------
| NEW GOOGLE USER
|--------------------------------------------------------------------------
|
| This is a completely new account.
|
| Store the Google information temporarily and send the user
| directly to the country/phone completion page.
|
|--------------------------------------------------------------------------
*/

$_SESSION['google_signup'] = [

    'google_id' =>
        $google_id,

    'email' =>
        $google_email,

    'fullname' =>
        $google_name

];


/*
|--------------------------------------------------------------------------
| SEND NEW GOOGLE USER TO COMPLETION
|--------------------------------------------------------------------------
*/

redirect(
    "google_complete_registration.php"
);