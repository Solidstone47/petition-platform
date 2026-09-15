<?php

require_once "includes/db.php";
require_once "includes/functions.php";
require_once "includes/auth.php";


// ======================================================
// ALREADY LOGGED IN
// ======================================================

if (is_user_logged_in()) {

    redirect("index.php");

}

$errors = [];


// ======================================================
// GOOGLE OAUTH
// ======================================================

$google_client_id =
    "62180751187-i9737ts81pqdnftnj1lf82bm2vs6prmm.apps.googleusercontent.com";

$google_redirect_uri =
    "http://localhost/petition_platform/google_callback.php";


// ======================================================
// HANDLE LOGIN
// ======================================================

if (is_post()) {

    verify_csrf();


    // ==================================================
    // GET FORM DATA
    // ==================================================

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';


    // ==================================================
    // VALIDATION
    // ==================================================

    if ($email === '') {

        $errors[] =
            "Email address is required.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $errors[] =
            "Please enter a valid email address.";

    }


    if ($password === '') {

        $errors[] =
            "Password is required.";

    }


    // ==================================================
    // CHECK USER
    // ==================================================

    if (empty($errors)) {

        $stmt = $conn->prepare("
            SELECT
                id,
                password,
                password_reset_required
            FROM users
            WHERE email = ?
            LIMIT 1
        ");


        if (!$stmt) {

            $errors[] =
                "Unable to process login.";

        } else {

            $stmt->bind_param(
                "s",
                $email
            );

            $stmt->execute();

            $result = $stmt->get_result();

            $user = $result->fetch_assoc();

            $stmt->close();


            // ==================================================
            // USER EXISTS
            // ==================================================

            if ($user) {


                // ==================================================
                // ADMIN HAS FORCED PASSWORD RESET
                // ==================================================

                if (
                    (int) $user['password_reset_required'] === 1
                ) {

                    $_SESSION['password_reset_user_id'] =
                        (int) $user['id'];

                    redirect(
                        "create_new_password.php"
                    );

                }


                // ==================================================
                // NORMAL PASSWORD LOGIN
                // ==================================================

                if (
                    !empty($user['password']) &&
                    password_verify(
                        $password,
                        $user['password']
                    )
                ) {

                    login_user(
                        $user['id']
                    );

                    set_flash(
                        "success",
                        "Welcome back!"
                    );

                    redirect(
                        "index.php"
                    );

                } else {

                    $errors[] =
                        "Invalid email or password.";

                }


            } else {

                $errors[] =
                    "Invalid email or password.";

            }

        }

    }

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

    <meta
        name="description"
        content="Sign in to your Tanzania Petition Platform account."
    >

    <meta
        name="theme-color"
        content="#006b3f"
    >

    <title>
        Sign In | Petition Platform
    </title>


    <style>

        /* ======================================================
           RESET
        ====================================================== */

        * {
            box-sizing: border-box;
        }


        /* ======================================================
           ROOT
        ====================================================== */

        :root {

            --primary: #006b3f;
            --primary-dark: #004d2d;
            --primary-light: #e8f5ee;

            --gold: #f4c430;
            --gold-light: #fff8dc;

            --dark: #0f172a;
            --text: #334155;
            --muted: #64748b;

            --border: rgba(255,255,255,0.45);

        }


        /* ======================================================
           BODY
        ====================================================== */

        body {

            margin: 0;

            min-height: 100vh;

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 30px 20px;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            color: var(--text);

            background:

                radial-gradient(
                    circle at 10% 10%,
                    rgba(0,107,63,0.16),
                    transparent 30%
                ),

                radial-gradient(
                    circle at 90% 90%,
                    rgba(244,196,48,0.16),
                    transparent 30%
                ),

                linear-gradient(
                    135deg,
                    #f4f8f6 0%,
                    #eef4f1 45%,
                    #f8f7f0 100%
                );

            position: relative;

            overflow-x: hidden;

        }


        /* ======================================================
           BACKGROUND DECORATION
        ====================================================== */

        body::before {

            content: "";

            position: fixed;

            width: 280px;

            height: 280px;

            top: -100px;

            right: -80px;

            border-radius: 50%;

            background:
                rgba(0,107,63,0.08);

            filter: blur(2px);

            pointer-events: none;

        }


        body::after {

            content: "";

            position: fixed;

            width: 220px;

            height: 220px;

            bottom: -90px;

            left: -70px;

            border-radius: 50%;

            background:
                rgba(244,196,48,0.10);

            pointer-events: none;

        }


        /* ======================================================
           PAGE CONTAINER
        ====================================================== */

        .login-container {

            width: 100%;

            max-width: 470px;

            position: relative;

            z-index: 2;

        }


        /* ======================================================
           BRAND
        ====================================================== */

        .brand {

            text-align: center;

            margin-bottom: 18px;

        }


        .brand a {

            color: var(--primary);

            text-decoration: none;

            font-size: 21px;

            font-weight: 900;

            letter-spacing: -0.4px;

        }


        .brand-mark {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            width: 34px;

            height: 34px;

            margin-right: 7px;

            vertical-align: middle;

            border-radius: 10px;

            background:
                linear-gradient(
                    135deg,
                    var(--primary),
                    #008a52
                );

            color: #ffffff;

            font-size: 17px;

            box-shadow:
                0 6px 16px
                rgba(0,107,63,0.20);

        }


        /* ======================================================
           LOGIN CARD
        ====================================================== */

        .login-card {

            position: relative;

            padding: 38px;

            border-radius: 26px;

            border:
                1px solid var(--border);

            background:
                rgba(255,255,255,0.72);

            backdrop-filter:
                blur(22px);

            -webkit-backdrop-filter:
                blur(22px);

            box-shadow:

                0 25px 70px
                rgba(15,23,42,0.12),

                0 2px 8px
                rgba(15,23,42,0.04);

            overflow: hidden;

        }


        /* ======================================================
           CARD TOP ACCENT
        ====================================================== */

        .login-card::before {

            content: "";

            position: absolute;

            top: 0;

            left: 0;

            right: 0;

            height: 4px;

            background:
                linear-gradient(
                    90deg,
                    var(--primary) 0%,
                    var(--primary) 65%,
                    var(--gold) 65%,
                    var(--gold) 100%
                );

        }


        /* ======================================================
           HEADER ICON
        ====================================================== */

        .login-icon {

            width: 68px;

            height: 68px;

            display: flex;

            align-items: center;

            justify-content: center;

            margin-bottom: 20px;

            border-radius: 20px;

            background:
                linear-gradient(
                    135deg,
                    rgba(0,107,63,0.13),
                    rgba(0,107,63,0.05)
                );

            border:
                1px solid
                rgba(0,107,63,0.10);

            color: var(--primary);

            font-size: 28px;

            box-shadow:
                inset 0 1px 0
                rgba(255,255,255,0.8);

        }


        /* ======================================================
           HEADING
        ====================================================== */

        .login-title {

            margin: 0 0 8px;

            color: var(--dark);

            font-size:
                clamp(28px, 6vw, 36px);

            font-weight: 900;

            line-height: 1.15;

            letter-spacing: -0.8px;

        }


        .login-subtitle {

            margin: 0 0 28px;

            color: var(--muted);

            font-size: 14px;

            line-height: 1.7;

        }


        /* ======================================================
           SOCIAL LOGIN
        ====================================================== */

        .social-section {

            display: grid;

            gap: 10px;

            margin-bottom: 24px;

        }


        .social-button {

            width: 100%;

            min-height: 50px;

            display: flex;

            align-items: center;

            justify-content: center;

            gap: 11px;

            padding: 0 16px;

            border:
                1px solid
                rgba(148,163,184,0.40);

            border-radius: 13px;

            background:
                rgba(255,255,255,0.72);

            color: var(--dark);

            font-family: inherit;

            font-size: 13px;

            font-weight: 750;

            text-decoration: none;

            cursor: pointer;

            box-shadow:
                0 3px 12px
                rgba(15,23,42,0.035);

            transition:
                transform 0.2s ease,
                background 0.2s ease,
                border-color 0.2s ease,
                box-shadow 0.2s ease;

        }


        .social-button:hover {

            background:
                rgba(255,255,255,0.95);

            border-color:
                rgba(0,107,63,0.30);

            transform:
                translateY(-2px);

            box-shadow:
                0 8px 22px
                rgba(15,23,42,0.08);

        }


        .social-button:active {

            transform:
                translateY(0);

        }


        /* ======================================================
           SOCIAL ICON
        ====================================================== */

        .social-icon {

            width: 21px;

            height: 21px;

            display: flex;

            align-items: center;

            justify-content: center;

            flex-shrink: 0;

        }


        .social-icon svg {

            width: 20px;

            height: 20px;

            display: block;

        }


        .google-svg {

            width: 20px;

            height: 20px;

        }


        .facebook-svg {

            width: 20px;

            height: 20px;

        }


        .x-svg {

            width: 19px;

            height: 19px;

        }


        /* ======================================================
           DISABLED SOCIAL BUTTONS
        ====================================================== */

        .social-disabled {

            opacity: 0.52;

            cursor: not-allowed;

        }


        .social-disabled:hover {

            background:
                rgba(255,255,255,0.72);

            border-color:
                rgba(148,163,184,0.40);

            transform: none;

            box-shadow:
                0 3px 12px
                rgba(15,23,42,0.035);

        }


        /* ======================================================
           DIVIDER
        ====================================================== */

        .divider {

            display: flex;

            align-items: center;

            gap: 14px;

            margin: 25px 0;

            color: #94a3b8;

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
                linear-gradient(
                    90deg,
                    transparent,
                    rgba(148,163,184,0.45)
                );

        }


        .divider::after {

            background:
                linear-gradient(
                    90deg,
                    rgba(148,163,184,0.45),
                    transparent
                );

        }


        /* ======================================================
           ERROR BOX
        ====================================================== */

        .error-box {

            margin-bottom: 21px;

            padding: 14px 16px;

            border:
                1px solid
                rgba(220,38,38,0.20);

            border-radius: 13px;

            background:
                rgba(254,242,242,0.82);

            color: #991b1b;

            font-size: 13px;

            line-height: 1.55;

            box-shadow:
                0 5px 15px
                rgba(127,29,29,0.04);

        }


        .error-box p {

            margin: 3px 0;

        }


        /* ======================================================
           FORM GROUP
        ====================================================== */

        .form-group {

            margin-bottom: 19px;

        }


        /* ======================================================
           LABEL
        ====================================================== */

        label {

            display: block;

            margin-bottom: 8px;

            color: #334155;

            font-size: 12px;

            font-weight: 800;

        }


        /* ======================================================
           INPUT
        ====================================================== */

        input {

            width: 100%;

            height: 51px;

            padding:
                0 15px;

            border:
                1px solid
                rgba(148,163,184,0.45);

            border-radius: 13px;

            outline: none;

            background:
                rgba(255,255,255,0.75);

            color: var(--dark);

            font-family: inherit;

            font-size: 14px;

            box-shadow:
                inset 0 1px 2px
                rgba(15,23,42,0.02);

            transition:
                border-color 0.2s ease,
                background 0.2s ease,
                box-shadow 0.2s ease;

        }


        input::placeholder {

            color: #94a3b8;

        }


        input:hover {

            background:
                rgba(255,255,255,0.90);

        }


        input:focus {

            background:
                rgba(255,255,255,0.98);

            border-color:
                var(--primary);

            box-shadow:

                0 0 0 4px
                rgba(0,107,63,0.09),

                0 5px 15px
                rgba(15,23,42,0.05);

        }


        /* ======================================================
           PASSWORD HEADER
        ====================================================== */

        .password-header {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 10px;

            margin-bottom: 8px;

        }


        .password-header label {

            margin: 0;

        }


        .forgot-link {

            color: var(--primary);

            font-size: 11px;

            font-weight: 800;

            text-decoration: none;

        }


        .forgot-link:hover {

            color:
                var(--primary-dark);

            text-decoration: underline;

        }


        /* ======================================================
           LOGIN BUTTON
        ====================================================== */

        .login-button {

            width: 100%;

            min-height: 52px;

            margin-top: 5px;

            border: none;

            border-radius: 13px;

            background:
                linear-gradient(
                    135deg,
                    var(--primary),
                    #008a52
                );

            color: #ffffff;

            font-family: inherit;

            font-size: 14px;

            font-weight: 850;

            letter-spacing: 0.1px;

            cursor: pointer;

            box-shadow:

                0 8px 20px
                rgba(0,107,63,0.20),

                inset 0 1px 0
                rgba(255,255,255,0.20);

            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease,
                filter 0.2s ease;

        }


        .login-button:hover {

            transform:
                translateY(-2px);

            filter:
                brightness(1.04);

            box-shadow:

                0 12px 28px
                rgba(0,107,63,0.25);

        }


        .login-button:active {

            transform:
                translateY(0);

        }


        /* ======================================================
           REGISTER AREA
        ====================================================== */

        .register-area {

            margin-top: 25px;

            padding-top: 22px;

            border-top:
                1px solid
                rgba(148,163,184,0.25);

            text-align: center;

            color: var(--muted);

            font-size: 12px;

        }


        .register-area a {

            color: var(--primary);

            font-weight: 850;

            text-decoration: none;

        }


        .register-area a:hover {

            color:
                var(--primary-dark);

            text-decoration: underline;

        }


        /* ======================================================
           SECURITY NOTE
        ====================================================== */

        .security-note {

            display: flex;

            align-items: center;

            justify-content: center;

            gap: 6px;

            margin-top: 17px;

            color: #94a3b8;

            font-size: 10px;

            line-height: 1.5;

            text-align: center;

        }


        .security-dot {

            width: 6px;

            height: 6px;

            border-radius: 50%;

            background: var(--primary);

            opacity: 0.65;

        }


        /* ======================================================
           MOBILE
        ====================================================== */

        @media (max-width: 520px) {

            body {

                padding:
                    20px 14px;

            }


            .login-container {

                max-width: 100%;

            }


            .login-card {

                padding:
                    30px 21px;

                border-radius: 22px;

            }


            .brand {

                margin-bottom: 14px;

            }


            .brand a {

                font-size: 19px;

            }


            .login-icon {

                width: 60px;

                height: 60px;

                border-radius: 18px;

                font-size: 25px;

            }


            .login-title {

                font-size: 28px;

            }


            .login-subtitle {

                font-size: 13px;

                margin-bottom: 23px;

            }


            .social-button {

                min-height: 48px;

            }

        }


        /* ======================================================
           SMALL MOBILE
        ====================================================== */

        @media (max-width: 360px) {

            .login-card {

                padding:
                    27px 17px;

            }


            .login-title {

                font-size: 25px;

            }


            .social-button {

                font-size: 12px;

            }

        }

    </style>

</head>


<body>


<div class="login-container">


    <!-- ==================================================
         BRAND
    ================================================== -->

    <div class="brand">

        <a href="index.php">

            <span class="brand-mark">
                ✓
            </span>

            Petition Platform

        </a>

    </div>


    <!-- ==================================================
         LOGIN CARD
    ================================================== -->

    <div class="login-card">


        <!-- ==================================================
             LOGIN ICON
        ================================================== -->

        <div class="login-icon">

            🔐

        </div>


        <!-- ==================================================
             TITLE
        ================================================== -->

        <h1 class="login-title">

            Welcome Back

        </h1>


        <p class="login-subtitle">

            Sign in to your account and continue making
            your voice heard.

        </p>


        <!-- ==================================================
             SOCIAL LOGIN
        ================================================== -->

        <div class="social-section">


            <!-- ==================================================
                 GOOGLE
            ================================================== -->

            <a
                href="google_login.php"
                class="social-button"
            >

                <span class="social-icon">

                    <svg
                        class="google-svg"
                        viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg"
                        aria-hidden="true"
                    >

                        <path
                            fill="#4285F4"
                            d="M21.35 12.27c0-.78-.07-1.54-.22-2.27H12v4.3h5.23a4.47 4.47 0 0 1-1.94 2.94v2.45h3.14c1.84-1.69 2.92-4.18 2.92-7.42z"
                        />

                        <path
                            fill="#34A853"
                            d="M12 21.75c2.63 0 4.84-.87 6.45-2.36l-3.14-2.45c-.87.58-1.98.93-3.31.93-2.54 0-4.69-1.72-5.46-4.03H3.3v2.52A9.74 9.74 0 0 0 12 21.75z"
                        />

                        <path
                            fill="#FBBC05"
                            d="M6.54 13.84A5.85 5.85 0 0 1 6.24 12c0-.64.11-1.26.3-1.84V7.64H3.3A9.74 9.74 0 0 0 2.25 12c0 1.57.38 3.05 1.05 4.36l3.24-2.52z"
                        />

                        <path
                            fill="#EA4335"
                            d="M12 6.13c1.43 0 2.71.49 3.72 1.46l2.79-2.79C16.84 3.25 14.63 2.25 12 2.25a9.74 9.74 0 0 0-8.7 5.39l3.24 2.52C7.31 7.85 9.46 6.13 12 6.13z"
                        />

                    </svg>

                </span>

                Continue with Google

            </a>


            <!-- ==================================================
                 FACEBOOK
            ================================================== -->

            <a
                href="#"
                class="social-button social-disabled"
                onclick="return false;"
                title="Facebook sign-up will be configured next"
                aria-disabled="true"
            >

                <span class="social-icon">

                    <svg
                        class="facebook-svg"
                        viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg"
                        aria-hidden="true"
                    >

                        <path
                            fill="#1877F2"
                            d="M24 12a12 12 0 1 0-13.88 11.85v-8.39H7.08V12h3.04V9.41c0-3 1.79-4.66 4.53-4.66 1.31 0 2.68.23 2.68.23v2.95h-1.51c-1.49 0-1.95.93-1.95 1.87V12h3.32l-.53 3.46h-2.79v8.39A12 12 0 0 0 24 12z"
                        />

                        <path
                            fill="#FFFFFF"
                            d="M16.65 15.46L17.18 12h-3.32V9.8c0-.94.46-1.87 1.95-1.87h1.51V4.98s-1.37-.23-2.68-.23c-2.74 0-4.53 1.66-4.53 4.66V12H7.08v3.46h3.04v8.39a12.1 12.1 0 0 0 3.74 0v-8.39h2.79z"
                        />

                    </svg>

                </span>

                Continue with Facebook

            </a>


            <!-- ==================================================
                 X
            ================================================== -->

            <a
                href="#"
                class="social-button social-disabled"
                onclick="return false;"
                title="X sign-up will be configured next"
                aria-disabled="true"
            >

                <span class="social-icon">

                    <svg
                        class="x-svg"
                        viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg"
                        aria-hidden="true"
                    >

                        <path
                            fill="#000000"
                            d="M18.244 2.25h3.308l-7.227 8.26L22.827 21.75h-6.603l-5.17-6.756-5.916 6.756H1.83l7.73-8.835L1.173 2.25H7.94l4.673 6.18 5.63-6.18zm-1.161 17.52h1.833L7.084 4.126H5.117L17.083 19.77z"
                        />

                    </svg>

                </span>

                Continue with X

            </a>


        </div>


        <!-- ==================================================
             DIVIDER
        ================================================== -->

        <div class="divider">

            OR

        </div>


        <!-- ==================================================
             ERRORS
        ================================================== -->

        <?php if (!empty($errors)): ?>

            <div
                class="error-box"
                role="alert"
            >

                <?php foreach ($errors as $error): ?>

                    <p>
                        <?= e($error) ?>
                    </p>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>


        <!-- ==================================================
             LOGIN FORM
        ================================================== -->

        <form
            method="POST"
            action=""
        >

            <?= csrf_field() ?>


            <!-- ==================================================
                 EMAIL
            ================================================== -->

            <div class="form-group">

                <label for="email">

                    Email Address

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


            <!-- ==================================================
                 PASSWORD
            ================================================== -->

            <div class="form-group">

                <div class="password-header">

                    <label for="password">

                        Password

                    </label>


                    <a
                        href="forgot_password.php"
                        class="forgot-link"
                    >

                        Forgot Password?

                    </a>

                </div>


                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter your password"
                    autocomplete="current-password"
                    required
                >

            </div>


            <!-- ==================================================
                 LOGIN BUTTON
            ================================================== -->

            <button
                type="submit"
                class="login-button"
            >

                Sign In to Your Account

            </button>


        </form>


        <!-- ==================================================
             REGISTER
        ================================================== -->

        <div class="register-area">

            Don't have an account?

            <a href="register.php">

                Create one

            </a>

        </div>


        <!-- ==================================================
             SECURITY NOTE
        ================================================== -->

        <div class="security-note">

            <span class="security-dot"></span>

            Your account credentials are securely protected.

        </div>


    </div>


</div>


</body>

</html>