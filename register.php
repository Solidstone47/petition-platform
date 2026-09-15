<?php

require_once "includes/db.php";
require_once "includes/functions.php";
require_once "includes/auth.php";


/*
|--------------------------------------------------------------------------
| AUTHENTICATION CHECK
|--------------------------------------------------------------------------
*/

if (is_user_logged_in()) {
    redirect("index.php");
}

$errors = [];


/*
|--------------------------------------------------------------------------
| GOOGLE OAUTH
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
|
| The button must first send the user to Google's authorization endpoint.
| It must NOT point directly to google_callback.php.
|
*/

$google_auth_url =
    "https://accounts.google.com/o/oauth2/v2/auth?" .
    http_build_query([
        "client_id" => $google_client_id,
        "redirect_uri" => $google_redirect_uri,
        "response_type" => "code",
        "scope" => "openid email profile",
        "access_type" => "online",
        "prompt" => "select_account"
    ]);


/*
|--------------------------------------------------------------------------
| COUNTRIES
|--------------------------------------------------------------------------
*/

$countries = [
    "Afghanistan",
    "Albania",
    "Algeria",
    "Andorra",
    "Angola",
    "Antigua and Barbuda",
    "Argentina",
    "Armenia",
    "Australia",
    "Austria",
    "Azerbaijan",
    "Bahamas",
    "Bahrain",
    "Bangladesh",
    "Barbados",
    "Belarus",
    "Belgium",
    "Belize",
    "Benin",
    "Bhutan",
    "Bolivia",
    "Bosnia and Herzegovina",
    "Botswana",
    "Brazil",
    "Brunei",
    "Bulgaria",
    "Burkina Faso",
    "Burundi",
    "Cabo Verde",
    "Cambodia",
    "Cameroon",
    "Canada",
    "Central African Republic",
    "Chad",
    "Chile",
    "China",
    "Colombia",
    "Comoros",
    "Congo",
    "Costa Rica",
    "Croatia",
    "Cuba",
    "Cyprus",
    "Czechia",
    "Democratic Republic of the Congo",
    "Denmark",
    "Djibouti",
    "Dominica",
    "Dominican Republic",
    "Ecuador",
    "Egypt",
    "El Salvador",
    "Equatorial Guinea",
    "Eritrea",
    "Estonia",
    "Eswatini",
    "Ethiopia",
    "Fiji",
    "Finland",
    "France",
    "Gabon",
    "Gambia",
    "Georgia",
    "Germany",
    "Ghana",
    "Greece",
    "Grenada",
    "Guatemala",
    "Guinea",
    "Guinea-Bissau",
    "Guyana",
    "Haiti",
    "Honduras",
    "Hungary",
    "Iceland",
    "India",
    "Indonesia",
    "Iran",
    "Iraq",
    "Ireland",
    "Israel",
    "Italy",
    "Ivory Coast",
    "Jamaica",
    "Japan",
    "Jordan",
    "Kazakhstan",
    "Kenya",
    "Kiribati",
    "Kuwait",
    "Laos",
    "Latvia",
    "Lebanon",
    "Lesotho",
    "Liberia",
    "Libya",
    "Liechtenstein",
    "Lithuania",
    "Luxembourg",
    "Madagascar",
    "Malawi",
    "Malaysia",
    "Maldives",
    "Mali",
    "Malta",
    "Marshall Islands",
    "Mauritania",
    "Mauritius",
    "Mexico",
    "Micronesia",
    "Moldova",
    "Monaco",
    "Mongolia",
    "Montenegro",
    "Morocco",
    "Mozambique",
    "Myanmar",
    "Namibia",
    "Nauru",
    "Nepal",
    "Netherlands",
    "New Zealand",
    "Nicaragua",
    "Niger",
    "Nigeria",
    "North Korea",
    "North Macedonia",
    "Norway",
    "Oman",
    "Pakistan",
    "Palau",
    "Palestine",
    "Panama",
    "Papua New Guinea",
    "Paraguay",
    "Peru",
    "Philippines",
    "Poland",
    "Portugal",
    "Qatar",
    "Romania",
    "Russia",
    "Rwanda",
    "Saint Kitts and Nevis",
    "Saint Lucia",
    "Saint Vincent and the Grenadines",
    "Samoa",
    "San Marino",
    "Sao Tome and Principe",
    "Saudi Arabia",
    "Senegal",
    "Serbia",
    "Seychelles",
    "Sierra Leone",
    "Singapore",
    "Slovakia",
    "Slovenia",
    "Solomon Islands",
    "Somalia",
    "South Africa",
    "South Korea",
    "South Sudan",
    "Spain",
    "Sri Lanka",
    "Sudan",
    "Suriname",
    "Sweden",
    "Switzerland",
    "Syria",
    "Tajikistan",
    "Tanzania",
    "Thailand",
    "Timor-Leste",
    "Togo",
    "Tonga",
    "Trinidad and Tobago",
    "Tunisia",
    "Türkiye",
    "Turkmenistan",
    "Tuvalu",
    "Uganda",
    "Ukraine",
    "United Arab Emirates",
    "United Kingdom",
    "United States",
    "Uruguay",
    "Uzbekistan",
    "Vanuatu",
    "Vatican City",
    "Venezuela",
    "Vietnam",
    "Yemen",
    "Zambia",
    "Zimbabwe"
];


/*
|--------------------------------------------------------------------------
| COUNTRY FROM URL
|--------------------------------------------------------------------------
*/

$selected_country = $_GET['country'] ?? '';

if (!in_array($selected_country, $countries, true)) {
    $selected_country = '';
}


/*
|--------------------------------------------------------------------------
| REGISTRATION
|--------------------------------------------------------------------------
*/

if (is_post()) {

    verify_csrf();

    $fullname =
        trim($_POST['fullname'] ?? '');

    $email =
        trim($_POST['email'] ?? '');

    $phone =
        trim($_POST['phone'] ?? '');

    $country =
        trim($_POST['country'] ?? '');

    $password =
        $_POST['password'] ?? '';

    $confirm_password =
        $_POST['confirm_password'] ?? '';


    /*
    |--------------------------------------------------------------------------
    | FULL NAME
    |--------------------------------------------------------------------------
    */

    if ($fullname === '') {

        $errors[] =
            "Full name is required.";

    } elseif (strlen($fullname) < 2) {

        $errors[] =
            "Full name must contain at least 2 characters.";
    }


    /*
    |--------------------------------------------------------------------------
    | EMAIL
    |--------------------------------------------------------------------------
    */

    if ($email === '') {

        $errors[] =
            "Email address is required.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $errors[] =
            "Please enter a valid email address.";
    }


    /*
    |--------------------------------------------------------------------------
    | COUNTRY
    |--------------------------------------------------------------------------
    */

    if ($country === '') {

        $errors[] =
            "Please select your country.";

    } elseif (!in_array($country, $countries, true)) {

        $errors[] =
            "Please select a valid country.";
    }


    /*
    |--------------------------------------------------------------------------
    | TANZANIA PHONE
    |--------------------------------------------------------------------------
    */

    if ($country === 'Tanzania') {

        if ($phone === '') {

            $errors[] =
                "Tanzanian users must provide a phone number.";

        } else {

            $clean_phone =
                preg_replace(
                    '/[\s\-\(\)]/',
                    '',
                    $phone
                );

            /*
            |--------------------------------------------------------------------------
            | Accept:
            | 0712345678
            | 0612345678
            | 255712345678
            | +255712345678
            |--------------------------------------------------------------------------
            */

            if (
                !preg_match(
                    '/^(?:\+255|255|0)[67][0-9]{8}$/',
                    $clean_phone
                )
            ) {

                $errors[] =
                    "Please enter a valid Tanzanian phone number.";

            } else {

                /*
                |--------------------------------------------------------------------------
                | Normalize to +255XXXXXXXXX
                |--------------------------------------------------------------------------
                */

                if (
                    substr(
                        $clean_phone,
                        0,
                        1
                    ) === '0'
                ) {

                    $phone =
                        '+255' .
                        substr(
                            $clean_phone,
                            1
                        );

                } elseif (
                    substr(
                        $clean_phone,
                        0,
                        3
                    ) === '255'
                ) {

                    $phone =
                        '+' .
                        $clean_phone;

                } else {

                    $phone =
                        $clean_phone;
                }
            }
        }

    } else {

        /*
        |--------------------------------------------------------------------------
        | Non-Tanzanian users do not need a phone number.
        |--------------------------------------------------------------------------
        */

        $phone = '';
    }


    /*
    |--------------------------------------------------------------------------
    | PASSWORD
    |--------------------------------------------------------------------------
    */

    if ($password === '') {

        $errors[] =
            "Password is required.";

    } elseif (strlen($password) < 8) {

        $errors[] =
            "Password must contain at least 8 characters.";
    }


    /*
    |--------------------------------------------------------------------------
    | CONFIRM PASSWORD
    |--------------------------------------------------------------------------
    */

    if ($password !== $confirm_password) {

        $errors[] =
            "Passwords do not match.";
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK EMAIL
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        $stmt = $conn->prepare(
            "SELECT id
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        if (!$stmt) {

            $errors[] =
                "Unable to process your registration.";

        } else {

            $stmt->bind_param(
                "s",
                $email
            );

            if (!$stmt->execute()) {

                $errors[] =
                    "Unable to process your registration.";

            } else {

                $result =
                    $stmt->get_result();

                if ($result->num_rows > 0) {

                    $errors[] =
                        "An account with this email already exists.";
                }
            }

            $stmt->close();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE ACCOUNT
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        $hashed_password =
            password_hash(
                $password,
                PASSWORD_DEFAULT
            );

        if ($hashed_password === false) {

            $errors[] =
                "Unable to secure your password.";

        } else {

            $stmt = $conn->prepare(
                "INSERT INTO users
                (
                    fullname,
                    email,
                    phone,
                    country,
                    password
                )
                VALUES (?, ?, ?, ?, ?)"
            );

            if (!$stmt) {

                $errors[] =
                    "Unable to create your account. Please try again.";

            } else {

                $stmt->bind_param(
                    "sssss",
                    $fullname,
                    $email,
                    $phone,
                    $country,
                    $hashed_password
                );

                if ($stmt->execute()) {

                    $user_id =
                        $stmt->insert_id;

                    login_user(
                        $user_id
                    );

                    set_flash(
                        "success",
                        "Your account has been created successfully."
                    );

                    redirect(
                        "index.php"
                    );

                } else {

                    $errors[] =
                        "Unable to create your account. Please try again.";
                }

                $stmt->close();
            }
        }
    }

} else {

    $_POST['country'] =
        $selected_country;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Create Account - Tanzania Petition Platform
    </title>

    <style>

        /*
        |--------------------------------------------------------------------------
        | RESET
        |--------------------------------------------------------------------------
        */

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            min-height: 100vh;
            padding: 30px 18px;

            display: flex;
            align-items: center;
            justify-content: center;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            color: #172033;

            background:
                radial-gradient(
                    circle at 15% 15%,
                    rgba(255, 255, 255, 0.95),
                    transparent 30%
                ),
                radial-gradient(
                    circle at 85% 85%,
                    rgba(59, 130, 246, 0.16),
                    transparent 32%
                ),
                linear-gradient(
                    135deg,
                    #eef4f0 0%,
                    #eef3fb 50%,
                    #e8f0eb 100%
                );

            position: relative;
            overflow-x: hidden;
        }


        /*
        |--------------------------------------------------------------------------
        | BACKGROUND DECORATION
        |--------------------------------------------------------------------------
        */

        body::before,
        body::after {
            content: "";
            position: fixed;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            pointer-events: none;
            filter: blur(5px);
            z-index: 0;
        }

        body::before {
            top: -120px;
            left: -100px;
            background:
                rgba(16, 185, 129, 0.10);
        }

        body::after {
            right: -120px;
            bottom: -120px;
            background:
                rgba(59, 130, 246, 0.10);
        }


        /*
        |--------------------------------------------------------------------------
        | MAIN CONTAINER
        |--------------------------------------------------------------------------
        */

        .register-container {
            width: 100%;
            max-width: 510px;
            position: relative;
            z-index: 1;
        }


        /*
        |--------------------------------------------------------------------------
        | GLASS CARD
        |--------------------------------------------------------------------------
        */

        .register-card {
            width: 100%;

            padding: 36px;

            background:
                rgba(255, 255, 255, 0.76);

            border:
                1px solid
                rgba(255, 255, 255, 0.78);

            border-radius: 24px;

            box-shadow:
                0 25px 70px
                rgba(15, 23, 42, 0.10),
                inset 0 1px 0
                rgba(255, 255, 255, 0.85);

            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }


        /*
        |--------------------------------------------------------------------------
        | BRAND / ICON
        |--------------------------------------------------------------------------
        */

        .register-icon {
            width: 64px;
            height: 64px;

            display: flex;
            align-items: center;
            justify-content: center;

            margin-bottom: 20px;

            border-radius: 18px;

            background:
                linear-gradient(
                    135deg,
                    rgba(20, 83, 45, 0.12),
                    rgba(234, 179, 8, 0.13)
                );

            border:
                1px solid
                rgba(255, 255, 255, 0.80);

            box-shadow:
                0 10px 25px
                rgba(15, 23, 42, 0.06);

            font-size: 29px;
        }


        /*
        |--------------------------------------------------------------------------
        | HEADINGS
        |--------------------------------------------------------------------------
        */

        .register-title {
            margin: 0 0 9px;

            color: #111827;

            font-size: 29px;
            font-weight: 800;
            line-height: 1.2;

            letter-spacing: -0.6px;
        }

        .register-subtitle {
            margin: 0 0 25px;

            color: #667085;

            font-size: 13px;
            line-height: 1.65;
        }


        /*
        |--------------------------------------------------------------------------
        | SOCIAL LOGIN
        |--------------------------------------------------------------------------
        */

        .social-section {
            margin-bottom: 23px;
        }

        .social-button {
            width: 100%;
            min-height: 49px;

            display: flex;
            align-items: center;
            justify-content: center;

            gap: 12px;

            margin-bottom: 10px;
            padding: 0 16px;

            border:
                1px solid
                rgba(148, 163, 184, 0.42);

            border-radius: 12px;

            background:
                rgba(255, 255, 255, 0.70);

            color: #172033;

            font-family: inherit;
            font-size: 13px;
            font-weight: 700;

            text-decoration: none;

            cursor: pointer;

            box-shadow:
                0 5px 16px
                rgba(15, 23, 42, 0.035);

            transition:
                transform 0.2s ease,
                background 0.2s ease,
                border-color 0.2s ease,
                box-shadow 0.2s ease;
        }

        .social-button:hover {
            transform: translateY(-1px);

            background:
                rgba(255, 255, 255, 0.94);

            border-color:
                rgba(100, 116, 139, 0.45);

            box-shadow:
                0 8px 22px
                rgba(15, 23, 42, 0.07);
        }

        .social-button:active {
            transform: translateY(0);
        }


        /*
        |--------------------------------------------------------------------------
        | SOCIAL ICONS
        |--------------------------------------------------------------------------
        */

        .social-icon {
            width: 22px;
            height: 22px;

            display: flex;
            align-items: center;
            justify-content: center;

            flex-shrink: 0;
        }

        .google-icon svg {
            width: 20px;
            height: 20px;
            display: block;
        }

        .facebook-icon {
            width: 22px;
            height: 22px;

            border-radius: 50%;

            display: flex;
            align-items: center;
            justify-content: center;

            background: #1877f2;
            color: #ffffff;

            font-size: 15px;
            font-weight: 800;
            line-height: 1;

            font-family: Arial, sans-serif;
        }

        .x-icon {
            color: #111827;
            font-size: 18px;
            font-weight: 800;
            line-height: 1;
        }


        /*
        |--------------------------------------------------------------------------
        | DISABLED SOCIAL BUTTONS
        |--------------------------------------------------------------------------
        */

        .social-disabled {
            opacity: 0.55;
            cursor: not-allowed;
        }

        .social-disabled:hover {
            transform: none;

            background:
                rgba(255, 255, 255, 0.70);

            border-color:
                rgba(148, 163, 184, 0.42);

            box-shadow:
                0 5px 16px
                rgba(15, 23, 42, 0.035);
        }


        /*
        |--------------------------------------------------------------------------
        | DIVIDER
        |--------------------------------------------------------------------------
        */

        .divider {
            display: flex;
            align-items: center;

            gap: 12px;

            margin: 23px 0;

            color: #98a2b3;

            font-size: 10px;
            font-weight: 800;

            letter-spacing: 1px;
        }

        .divider::before,
        .divider::after {
            content: "";

            flex: 1;

            height: 1px;

            background:
                rgba(148, 163, 184, 0.30);
        }


        /*
        |--------------------------------------------------------------------------
        | ERROR BOX
        |--------------------------------------------------------------------------
        */

        .error-box {
            margin-bottom: 20px;
            padding: 13px 15px;

            border:
                1px solid
                rgba(239, 68, 68, 0.25);

            border-radius: 12px;

            background:
                rgba(254, 226, 226, 0.72);

            color: #b42318;

            font-size: 13px;
            line-height: 1.55;

            box-shadow:
                inset 0 1px 0
                rgba(255, 255, 255, 0.60);
        }

        .error-box p {
            margin: 3px 0;
        }


        /*
        |--------------------------------------------------------------------------
        | FORM
        |--------------------------------------------------------------------------
        */

        .form-group {
            margin-bottom: 17px;
        }

        label {
            display: block;

            margin-bottom: 7px;

            color: #344054;

            font-size: 12px;
            font-weight: 700;
        }

        .required {
            color: #dc2626;
        }


        /*
        |--------------------------------------------------------------------------
        | INPUTS
        |--------------------------------------------------------------------------
        */

        input,
        select {
            width: 100%;
            height: 48px;

            padding: 0 14px;

            border:
                1px solid
                rgba(148, 163, 184, 0.45);

            border-radius: 11px;

            outline: none;

            background:
                rgba(255, 255, 255, 0.68);

            color: #172033;

            font-family: inherit;
            font-size: 14px;

            box-shadow:
                inset 0 1px 2px
                rgba(15, 23, 42, 0.025);

            transition:
                border-color 0.2s ease,
                background 0.2s ease,
                box-shadow 0.2s ease;
        }

        input::placeholder {
            color: #98a2b3;
        }

        input:hover,
        select:hover {
            background:
                rgba(255, 255, 255, 0.84);
        }

        input:focus,
        select:focus {
            border-color: #4f46e5;

            background:
                rgba(255, 255, 255, 0.92);

            box-shadow:
                0 0 0 3px
                rgba(79, 70, 229, 0.10);
        }

        select {
            cursor: pointer;
        }


        /*
        |--------------------------------------------------------------------------
        | TANZANIA PHONE SECTION
        |--------------------------------------------------------------------------
        */

        .phone-section {
            display: none;

            margin-top: -2px;
            margin-bottom: 17px;

            padding: 16px;

            border:
                1px solid
                rgba(79, 70, 229, 0.18);

            border-radius: 13px;

            background:
                rgba(238, 242, 255, 0.64);

            box-shadow:
                inset 0 1px 0
                rgba(255, 255, 255, 0.65);
        }

        .phone-section.show {
            display: block;

            animation:
                phoneAppear 0.22s ease;
        }

        @keyframes phoneAppear {
            from {
                opacity: 0;
                transform: translateY(-5px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .phone-title {
            margin-bottom: 5px;

            color: #3730a3;

            font-size: 13px;
            font-weight: 800;
        }

        .phone-description {
            margin-bottom: 12px;

            color: #667085;

            font-size: 11px;
            line-height: 1.5;
        }

        .phone-prefix {
            display: flex;
            align-items: center;

            gap: 9px;
        }

        .tz-code {
            height: 48px;
            min-width: 68px;

            display: flex;
            align-items: center;
            justify-content: center;

            border:
                1px solid
                rgba(148, 163, 184, 0.42);

            border-radius: 11px;

            background:
                rgba(255, 255, 255, 0.72);

            color: #344054;

            font-size: 13px;
            font-weight: 800;
        }

        .phone-prefix input {
            flex: 1;
        }


        /*
        |--------------------------------------------------------------------------
        | PASSWORD
        |--------------------------------------------------------------------------
        */

        .password-hint {
            margin-top: 6px;

            color: #98a2b3;

            font-size: 11px;
            line-height: 1.4;
        }


        /*
        |--------------------------------------------------------------------------
        | REGISTER BUTTON
        |--------------------------------------------------------------------------
        */

        .register-button {
            width: 100%;
            height: 49px;

            margin-top: 4px;

            border: none;
            border-radius: 12px;

            background:
                linear-gradient(
                    135deg,
                    #172033 0%,
                    #263248 100%
                );

            color: #ffffff;

            font-family: inherit;
            font-size: 14px;
            font-weight: 800;

            cursor: pointer;

            box-shadow:
                0 10px 25px
                rgba(15, 23, 42, 0.15);

            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease,
                filter 0.2s ease;
        }

        .register-button:hover {
            transform: translateY(-1px);

            filter: brightness(1.06);

            box-shadow:
                0 13px 30px
                rgba(15, 23, 42, 0.18);
        }

        .register-button:active {
            transform: translateY(0);
        }


        /*
        |--------------------------------------------------------------------------
        | LOGIN AREA
        |--------------------------------------------------------------------------
        */

        .login-area {
            margin-top: 24px;
            padding-top: 21px;

            border-top:
                1px solid
                rgba(148, 163, 184, 0.20);

            text-align: center;

            color: #667085;

            font-size: 12px;
        }

        .login-area a {
            color: #4f46e5;

            font-weight: 800;

            text-decoration: none;
        }

        .login-area a:hover {
            text-decoration: underline;
        }


        /*
        |--------------------------------------------------------------------------
        | SECURITY NOTE
        |--------------------------------------------------------------------------
        */

        .security-note {
            margin-top: 17px;

            color: #98a2b3;

            font-size: 10px;
            line-height: 1.55;

            text-align: center;
        }


        /*
        |--------------------------------------------------------------------------
        | MOBILE
        |--------------------------------------------------------------------------
        */

        @media (max-width: 560px) {

            body {
                padding: 18px 12px;
                align-items: flex-start;
            }

            .register-container {
                margin: 12px 0;
            }

            .register-card {
                padding: 27px 20px;
                border-radius: 20px;
            }

            .register-icon {
                width: 58px;
                height: 58px;

                margin-bottom: 17px;

                border-radius: 16px;

                font-size: 26px;
            }

            .register-title {
                font-size: 24px;
                letter-spacing: -0.4px;
            }

            .register-subtitle {
                font-size: 12px;
                margin-bottom: 22px;
            }

            .social-button {
                min-height: 48px;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | VERY SMALL SCREENS
        |--------------------------------------------------------------------------
        */

        @media (max-width: 360px) {

            body {
                padding: 10px;
            }

            .register-card {
                padding: 23px 16px;
            }

            .register-title {
                font-size: 22px;
            }

            .phone-prefix {
                gap: 7px;
            }

            .tz-code {
                min-width: 62px;
            }
        }

    </style>

</head>


<body>

<div class="register-container">

    <div class="register-card">


        <!-- ==========================================================
             ACCOUNT ICON
        =========================================================== -->

        <div class="register-icon">
            👤
        </div>


        <!-- ==========================================================
             HEADING
        =========================================================== -->

        <h1 class="register-title">
            Create Your Account
        </h1>

        <p class="register-subtitle">
            Join the Tanzania Petition Platform and make your voice heard.
        </p>


        <!-- ==========================================================
             SOCIAL REGISTRATION
        =========================================================== -->

        <div class="social-section">


            <!-- GOOGLE -->

            <a
                href="<?= e($google_auth_url) ?>"
                class="social-button"
            >

                <span class="social-icon google-icon">

                    <svg
                        viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg"
                        aria-hidden="true"
                    >

                        <path
                            fill="#4285F4"
                            d="M21.35 12.23c0-.79-.07-1.55-.2-2.27H12v4.3h5.23a4.47 4.47 0 0 1-1.94 2.93v2.44h3.14c1.84-1.69 2.92-4.18 2.92-7.4z"
                        />

                        <path
                            fill="#34A853"
                            d="M12 21.75c2.63 0 4.84-.87 6.45-2.35l-3.14-2.44c-.87.58-1.98.92-3.31.92-2.54 0-4.7-1.72-5.47-4.03H3.29v2.52A9.75 9.75 0 0 0 12 21.75z"
                        />

                        <path
                            fill="#FBBC05"
                            d="M6.53 13.85A5.86 5.86 0 0 1 6.22 12c0-.64.11-1.26.31-1.85V7.63H3.29A9.75 9.75 0 0 0 2.25 12c0 1.57.38 3.05 1.04 4.37l3.24-2.52z"
                        />

                        <path
                            fill="#EA4335"
                            d="M12 6.12c1.43 0 2.72.49 3.74 1.46l2.8-2.8C16.84 3.23 14.63 2.25 12 2.25a9.75 9.75 0 0 0-8.71 5.38l3.24 2.52C7.3 7.84 9.46 6.12 12 6.12z"
                        />

                    </svg>

                </span>

                Continue with Google

            </a>


            <!-- FACEBOOK -->

            <a
                href="#"
                class="social-button social-disabled"
                onclick="return false;"
                title="Facebook sign-up will be configured later"
            >

                <span class="social-icon facebook-icon">
                    f
                </span>

                Continue with Facebook

            </a>


            <!-- X -->

            <a
                href="#"
                class="social-button social-disabled"
                onclick="return false;"
                title="X sign-up will be configured later"
            >

                <span class="social-icon x-icon">
                    X
                </span>

                Continue with X

            </a>

        </div>


        <!-- ==========================================================
             DIVIDER
        =========================================================== -->

        <div class="divider">
            OR
        </div>


        <!-- ==========================================================
             ERRORS
        =========================================================== -->

        <?php if (!empty($errors)): ?>

            <div class="error-box">

                <?php foreach ($errors as $error): ?>

                    <p>
                        <?= e($error) ?>
                    </p>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>


        <!-- ==========================================================
             REGISTRATION FORM
        =========================================================== -->

        <form
            method="POST"
            action=""
        >

            <?= csrf_field() ?>


            <!-- FULL NAME -->

            <div class="form-group">

                <label for="fullname">

                    Full Name

                    <span class="required">
                        *
                    </span>

                </label>

                <input
                    type="text"
                    id="fullname"
                    name="fullname"
                    value="<?= e($_POST['fullname'] ?? '') ?>"
                    placeholder="Enter your full name"
                    autocomplete="name"
                    required
                >

            </div>


            <!-- EMAIL -->

            <div class="form-group">

                <label for="email">

                    Email Address

                    <span class="required">
                        *
                    </span>

                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?= e($_POST['email'] ?? '') ?>"
                    placeholder="Enter your email address"
                    autocomplete="email"
                    required
                >

            </div>


            <!-- COUNTRY -->

            <div class="form-group">

                <label for="country">

                    Country

                    <span class="required">
                        *
                    </span>

                </label>

                <select
                    id="country"
                    name="country"
                    required
                >

                    <option value="">
                        -- Select your country --
                    </option>

                    <?php foreach ($countries as $country_option): ?>

                        <option
                            value="<?= e($country_option) ?>"
                            <?= (
                                ($_POST['country'] ?? '') === $country_option
                            ) ? 'selected' : '' ?>
                        >
                            <?= e($country_option) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <!-- TANZANIA PHONE -->

            <div
                id="phoneSection"
                class="phone-section"
            >

                <div class="phone-title">
                    🇹🇿 Tanzanian Phone Number
                </div>

                <div class="phone-description">
                    Since you selected Tanzania, please provide your Tanzanian mobile number.
                </div>

                <div class="phone-prefix">

                    <div class="tz-code">
                        +255
                    </div>

                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        value="<?= e($_POST['phone'] ?? '') ?>"
                        placeholder="712 345 678"
                        autocomplete="tel"
                    >

                </div>

            </div>


            <!-- PASSWORD -->

            <div class="form-group">

                <label for="password">

                    Password

                    <span class="required">
                        *
                    </span>

                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Create a password"
                    minlength="8"
                    autocomplete="new-password"
                    required
                >

                <div class="password-hint">
                    Password must contain at least 8 characters.
                </div>

            </div>


            <!-- CONFIRM PASSWORD -->

            <div class="form-group">

                <label for="confirm_password">

                    Confirm Password

                    <span class="required">
                        *
                    </span>

                </label>

                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    placeholder="Enter your password again"
                    minlength="8"
                    autocomplete="new-password"
                    required
                >

            </div>


            <!-- SUBMIT -->

            <button
                type="submit"
                class="register-button"
            >
                Create Account
            </button>

        </form>


        <!-- ==========================================================
             LOGIN
        =========================================================== -->

        <div class="login-area">

            Already have an account?

            <a href="login.php">
                Login
            </a>

        </div>


        <!-- ==========================================================
             SECURITY
        =========================================================== -->

        <div class="security-note">

            Your account information and password are securely protected.

        </div>

    </div>

</div>


<script>

    /*
    |--------------------------------------------------------------------------
    | TANZANIA PHONE FIELD
    |--------------------------------------------------------------------------
    */

    const countrySelect =
        document.getElementById('country');

    const phoneSection =
        document.getElementById('phoneSection');

    const phoneInput =
        document.getElementById('phone');


    function updatePhoneField() {

        if (
            countrySelect.value === 'Tanzania'
        ) {

            phoneSection.classList.add('show');

            phoneInput.required = true;

        } else {

            phoneSection.classList.remove('show');

            phoneInput.required = false;

            phoneInput.value = '';
        }
    }


    countrySelect.addEventListener(
        'change',
        updatePhoneField
    );


    /*
    |--------------------------------------------------------------------------
    | INITIAL STATE
    |--------------------------------------------------------------------------
    */

    updatePhoneField();

</script>

</body>

</html>