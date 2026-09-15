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

// ======================================================
// VARIABLES
// ======================================================

$errors = [];
$success = false;
$reset_link = '';

// ======================================================
// HANDLE FORM
// ======================================================

if (is_post()) {

    verify_csrf();

    $email = trim($_POST['email'] ?? '');

    // ==================================================
    // VALIDATE EMAIL
    // ==================================================

    if ($email === '') {

        $errors[] = "Email address is required.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $errors[] = "Please enter a valid email address.";
    }

    // ==================================================
    // FIND USER
    // ==================================================

    if (empty($errors)) {

        $stmt = $conn->prepare("
            SELECT
                id,
                email
            FROM users
            WHERE email = ?
            LIMIT 1
        ");

        if (!$stmt) {

            $errors[] = "Unable to process your request.";

        } else {

            $stmt->bind_param("s", $email);

            if (!$stmt->execute()) {

                $errors[] = "Unable to process your request.";

                $stmt->close();

            } else {

                $result = $stmt->get_result();
                $user = $result->fetch_assoc();

                $stmt->close();

                // ==================================================
                // USER FOUND
                // ==================================================

                if ($user) {

                    $user_id = (int) $user['id'];

                    // ==================================================
                    // GENERATE SECURE TOKEN
                    // ==================================================

                    $token = bin2hex(random_bytes(32));

                    // ==================================================
                    // TOKEN EXPIRATION
                    // 1 HOUR
                    // ==================================================

                    $expires_at = date(
                        'Y-m-d H:i:s',
                        time() + 3600
                    );

                    // ==================================================
                    // DELETE OLD UNUSED TOKENS
                    // ==================================================

                    $delete_stmt = $conn->prepare("
                        DELETE FROM password_resets
                        WHERE account_type = 'user'
                        AND account_id = ?
                        AND used = 0
                    ");

                    if ($delete_stmt) {

                        $delete_stmt->bind_param(
                            "i",
                            $user_id
                        );

                        $delete_stmt->execute();
                        $delete_stmt->close();
                    }

                    // ==================================================
                    // STORE RESET TOKEN
                    // ==================================================

                    $insert_stmt = $conn->prepare("
                        INSERT INTO password_resets
                        (
                            account_type,
                            account_id,
                            email,
                            token,
                            expires_at,
                            used
                        )
                        VALUES
                        (
                            'user',
                            ?,
                            ?,
                            ?,
                            ?,
                            0
                        )
                    ");

                    if (!$insert_stmt) {

                        $errors[] =
                            "Unable to create password reset request.";

                    } else {

                        $insert_stmt->bind_param(
                            "isss",
                            $user_id,
                            $email,
                            $token,
                            $expires_at
                        );

                        if ($insert_stmt->execute()) {

                            $success = true;

                            // ==================================================
                            // DEVELOPMENT RESET LINK
                            // ==================================================

                            $reset_link =
                                "http://localhost/petition_platform/reset_password.php?token="
                                . urlencode($token);

                        } else {

                            $errors[] =
                                "Unable to create password reset request.";
                        }

                        $insert_stmt->close();
                    }

                } else {

                    /*
                    --------------------------------------------------
                    SECURITY:

                    Do not reveal whether an email exists.
                    --------------------------------------------------
                    */

                    $success = true;
                }
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

    <title>
        Forgot Password - Petition Platform
    </title>

    <style>

        /* =========================================================
           GLOBAL
        ========================================================= */

        * {
            box-sizing: border-box;
        }

        :root {
            --primary: #4f46e5;
            --primary-dark: #3730a3;

            --text: #111827;
            --muted: #6b7280;
            --soft-muted: #9ca3af;

            --border: rgba(255, 255, 255, 0.65);

            --glass:
                rgba(255, 255, 255, 0.72);

            --glass-strong:
                rgba(255, 255, 255, 0.84);
        }

        html {
            min-height: 100%;
        }

        body {
            margin: 0;
            min-height: 100vh;

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 30px 18px;

            font-family:
                Inter,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                Arial,
                Helvetica,
                sans-serif;

            color: var(--text);

            background:
                radial-gradient(
                    circle at 15% 15%,
                    rgba(79, 70, 229, 0.16),
                    transparent 32%
                ),
                radial-gradient(
                    circle at 85% 80%,
                    rgba(14, 165, 233, 0.13),
                    transparent 30%
                ),
                linear-gradient(
                    135deg,
                    #eef2ff 0%,
                    #f8fafc 48%,
                    #eef6ff 100%
                );

            position: relative;
            overflow-x: hidden;
        }

        /* =========================================================
           BACKGROUND DECORATION
        ========================================================= */

        body::before,
        body::after {
            content: "";
            position: fixed;
            border-radius: 50%;
            pointer-events: none;
            filter: blur(2px);
            z-index: 0;
        }

        body::before {
            width: 280px;
            height: 280px;

            top: -100px;
            left: -90px;

            background:
                rgba(99, 102, 241, 0.12);
        }

        body::after {
            width: 330px;
            height: 330px;

            right: -120px;
            bottom: -130px;

            background:
                rgba(14, 165, 233, 0.10);
        }

        /* =========================================================
           CONTAINER
        ========================================================= */

        .forgot-container {
            width: 100%;
            max-width: 450px;

            position: relative;
            z-index: 1;
        }

        /* =========================================================
           GLASS CARD
        ========================================================= */

        .forgot-card {
            width: 100%;

            padding: 38px;

            background:
                linear-gradient(
                    145deg,
                    rgba(255, 255, 255, 0.84),
                    rgba(255, 255, 255, 0.62)
                );

            border:
                1px solid var(--border);

            border-radius: 26px;

            box-shadow:
                0 25px 70px
                rgba(15, 23, 42, 0.12),

                0 8px 24px
                rgba(15, 23, 42, 0.05),

                inset 0 1px 0
                rgba(255, 255, 255, 0.85);

            backdrop-filter: blur(22px);
            -webkit-backdrop-filter: blur(22px);

            position: relative;
            overflow: hidden;
        }

        .forgot-card::before {
            content: "";

            position: absolute;

            top: 0;
            left: 0;
            right: 0;

            height: 1px;

            background:
                linear-gradient(
                    90deg,
                    transparent,
                    rgba(255,255,255,0.95),
                    transparent
                );
        }

        /* =========================================================
           ICON
        ========================================================= */

        .forgot-icon {
            width: 68px;
            height: 68px;

            display: flex;
            align-items: center;
            justify-content: center;

            margin-bottom: 22px;

            border-radius: 20px;

            background:
                linear-gradient(
                    135deg,
                    rgba(99, 102, 241, 0.14),
                    rgba(129, 140, 248, 0.24)
                );

            border:
                1px solid
                rgba(99, 102, 241, 0.16);

            box-shadow:
                inset 0 1px 0
                rgba(255,255,255,0.75),

                0 8px 20px
                rgba(79, 70, 229, 0.08);

            font-size: 30px;

            position: relative;
        }

        .forgot-icon::after {
            content: "";

            position: absolute;

            width: 8px;
            height: 8px;

            top: 7px;
            right: 9px;

            border-radius: 50%;

            background:
                rgba(255,255,255,0.8);
        }

        /* =========================================================
           TITLE
        ========================================================= */

        .forgot-title {
            margin: 0 0 9px;

            color: var(--text);

            font-size: 29px;
            font-weight: 800;

            letter-spacing: -0.7px;

            line-height: 1.18;
        }

        .forgot-subtitle {
            margin: 0 0 28px;

            color: var(--muted);

            font-size: 13px;

            line-height: 1.65;
        }

        /* =========================================================
           ERROR
        ========================================================= */

        .error-box {
            margin-bottom: 21px;

            padding: 14px 16px;

            border:
                1px solid
                rgba(248, 113, 113, 0.35);

            border-radius: 13px;

            background:
                rgba(254, 242, 242, 0.78);

            color: #b91c1c;

            font-size: 13px;

            line-height: 1.55;

            box-shadow:
                inset 0 1px 0
                rgba(255,255,255,0.65);
        }

        .error-box p {
            margin: 3px 0;
        }

        /* =========================================================
           SUCCESS
        ========================================================= */

        .success-box {
            margin-bottom: 18px;

            padding: 15px 16px;

            border:
                1px solid
                rgba(74, 222, 128, 0.30);

            border-radius: 14px;

            background:
                rgba(240, 253, 244, 0.78);

            color: #166534;

            font-size: 13px;

            line-height: 1.65;

            box-shadow:
                inset 0 1px 0
                rgba(255,255,255,0.65);
        }

        /* =========================================================
           DEVELOPMENT RESET LINK
        ========================================================= */

        .development-box {
            margin-top: 16px;

            padding: 16px;

            border:
                1px solid
                rgba(245, 158, 11, 0.32);

            border-radius: 14px;

            background:
                rgba(255, 251, 235, 0.82);

            color: #92400e;

            font-size: 12px;

            line-height: 1.65;

            box-shadow:
                inset 0 1px 0
                rgba(255,255,255,0.65);
        }

        .development-box strong {
            display: flex;
            align-items: center;
            gap: 7px;

            margin-bottom: 8px;

            color: #78350f;

            font-size: 11px;

            letter-spacing: 0.6px;
            text-transform: uppercase;
        }

        .development-box strong::before {
            content: "⚠";

            font-size: 13px;
        }

        .development-box a {
            display: inline-block;

            margin-top: 4px;

            color: #92400e;

            font-weight: 700;

            word-break: break-all;

            text-decoration: none;
        }

        .development-box a:hover {
            text-decoration: underline;
        }

        /* =========================================================
           FORM
        ========================================================= */

        .form-group {
            margin-bottom: 19px;
        }

        label {
            display: block;

            margin-bottom: 8px;

            color: #374151;

            font-size: 12px;
            font-weight: 750;

            letter-spacing: 0.1px;
        }

        input {
            width: 100%;
            height: 50px;

            padding: 0 15px;

            border:
                1px solid
                rgba(209, 213, 219, 0.95);

            border-radius: 13px;

            outline: none;

            background:
                rgba(255, 255, 255, 0.68);

            color: var(--text);

            font-family: inherit;
            font-size: 14px;

            box-shadow:
                inset 0 1px 2px
                rgba(15, 23, 42, 0.025),

                0 1px 2px
                rgba(15, 23, 42, 0.02);

            transition:
                border-color 0.2s ease,
                box-shadow 0.2s ease,
                background 0.2s ease,
                transform 0.2s ease;
        }

        input::placeholder {
            color: #9ca3af;
        }

        input:hover {
            border-color:
                rgba(156, 163, 175, 0.95);

            background:
                rgba(255, 255, 255, 0.82);
        }

        input:focus {
            border-color: #6366f1;

            background:
                rgba(255, 255, 255, 0.92);

            box-shadow:
                0 0 0 4px
                rgba(99, 102, 241, 0.11),

                0 5px 15px
                rgba(79, 70, 229, 0.06);

            transform: translateY(-1px);
        }

        /* =========================================================
           SUBMIT BUTTON
        ========================================================= */

        .submit-button {
            width: 100%;
            height: 50px;

            margin-top: 4px;

            border: none;
            border-radius: 13px;

            background:
                linear-gradient(
                    135deg,
                    #111827 0%,
                    #1f2937 100%
                );

            color: #ffffff;

            font-family: inherit;

            font-size: 14px;
            font-weight: 750;

            letter-spacing: 0.1px;

            cursor: pointer;

            box-shadow:
                0 8px 18px
                rgba(17, 24, 39, 0.16),

                inset 0 1px 0
                rgba(255,255,255,0.10);

            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease,
                background 0.2s ease;
        }

        .submit-button:hover {
            background:
                linear-gradient(
                    135deg,
                    #1f2937 0%,
                    #111827 100%
                );

            transform: translateY(-2px);

            box-shadow:
                0 12px 24px
                rgba(17, 24, 39, 0.20),

                inset 0 1px 0
                rgba(255,255,255,0.10);
        }

        .submit-button:active {
            transform: translateY(0);

            box-shadow:
                0 5px 12px
                rgba(17, 24, 39, 0.14);
        }

        /* =========================================================
           BACK TO LOGIN
        ========================================================= */

        .back-login {
            margin-top: 25px;

            padding-top: 22px;

            border-top:
                1px solid
                rgba(229, 231, 235, 0.75);

            text-align: center;

            color: var(--muted);

            font-size: 12px;
        }

        .back-login a {
            color: var(--primary);

            font-weight: 750;

            text-decoration: none;

            transition:
                color 0.2s ease;
        }

        .back-login a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        /* =========================================================
           SECURITY NOTE
        ========================================================= */

        .security-note {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;

            margin-top: 17px;

            color: var(--soft-muted);

            font-size: 10px;

            line-height: 1.5;

            text-align: center;
        }

        .security-note::before {
            content: "🔒";

            font-size: 10px;
            opacity: 0.8;
        }

        /* =========================================================
           MOBILE
        ========================================================= */

        @media (max-width: 520px) {

            body {
                padding: 18px 13px;
            }

            .forgot-card {
                padding: 29px 22px;

                border-radius: 21px;
            }

            .forgot-icon {
                width: 60px;
                height: 60px;

                margin-bottom: 19px;

                border-radius: 18px;

                font-size: 27px;
            }

            .forgot-title {
                font-size: 24px;

                letter-spacing: -0.4px;
            }

            .forgot-subtitle {
                font-size: 12.5px;

                margin-bottom: 24px;
            }

            input,
            .submit-button {
                height: 48px;
            }
        }

        @media (max-width: 360px) {

            .forgot-card {
                padding: 25px 18px;
            }

            .forgot-title {
                font-size: 22px;
            }

            .forgot-icon {
                width: 56px;
                height: 56px;

                font-size: 25px;
            }
        }

    </style>

</head>

<body>

    <div class="forgot-container">

        <div class="forgot-card">

            <!-- ==================================================
                 ICON
            =================================================== -->

            <div class="forgot-icon">
                🔑
            </div>

            <!-- ==================================================
                 HEADER
            =================================================== -->

            <h1 class="forgot-title">
                Forgot Password?
            </h1>

            <p class="forgot-subtitle">
                Enter the email address associated with your
                account and we'll help you securely reset your
                password.
            </p>

            <!-- ==================================================
                 ERRORS
            =================================================== -->

            <?php if (!empty($errors)): ?>

                <div class="error-box">

                    <?php foreach ($errors as $error): ?>

                        <p>
                            <?= e($error) ?>
                        </p>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

            <!-- ==================================================
                 SUCCESS
            =================================================== -->

            <?php if ($success): ?>

                <div class="success-box">

                    If an account with that email exists,
                    a password reset link has been generated.

                    The link is valid for 1 hour.

                </div>

                <?php if ($reset_link !== ''): ?>

                    <div class="development-box">

                        <strong>
                            Development Mode
                        </strong>

                        Email delivery is not connected yet.

                        Use the reset link below to test the
                        password reset process:

                        <br><br>

                        <a
                            href="<?= e($reset_link) ?>"
                        >
                            <?= e($reset_link) ?>
                        </a>

                    </div>

                <?php endif; ?>

            <?php else: ?>

                <!-- ==================================================
                     RESET FORM
                =================================================== -->

                <form
                    method="POST"
                    action=""
                >

                    <?= csrf_field() ?>

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

                    <button
                        type="submit"
                        class="submit-button"
                    >
                        Send Password Reset Link
                    </button>

                </form>

            <?php endif; ?>

            <!-- ==================================================
                 LOGIN
            =================================================== -->

            <div class="back-login">

                Remember your password?

                <a href="login.php">
                    Back to Login
                </a>

            </div>

            <!-- ==================================================
                 SECURITY
            =================================================== -->

            <div class="security-note">

                Password reset links expire after one hour.

            </div>

        </div>

    </div>

</body>

</html>