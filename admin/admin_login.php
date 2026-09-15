<?php

require_once "../includes/db.php";
require_once "../includes/functions.php";
require_once "../includes/auth.php";

if (is_admin_logged_in()) {
    redirect("admin_dashboard.php");
}

$errors = [];

if (is_post()) {

    verify_csrf();

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '') {

        $errors[] = "Email address is required.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $errors[] = "Please enter a valid email address.";
    }

    if ($password === '') {

        $errors[] = "Password is required.";
    }

    if (empty($errors)) {

        $stmt = $conn->prepare(
            "SELECT id, password FROM admins WHERE email = ? LIMIT 1"
        );

        if ($stmt) {

            $stmt->bind_param("s", $email);
            $stmt->execute();

            $result = $stmt->get_result();
            $admin = $result->fetch_assoc();

            $stmt->close();

            if (
                $admin &&
                password_verify(
                    $password,
                    $admin['password']
                )
            ) {

                login_admin($admin['id']);

                set_flash(
                    "success",
                    "Welcome to the admin panel."
                );

                redirect("admin_dashboard.php");

            } else {

                $errors[] =
                    "Invalid email or password.";
            }

        } else {

            $errors[] =
                "Unable to process login. Please try again.";
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
        Admin Login - Petition Platform
    </title>


    <style>

        /* ==================================================
           RESET
        ================================================== */

        * {
            box-sizing: border-box;
        }


        html,
        body {
            margin: 0;
            padding: 0;
            min-height: 100%;
        }


        body {

            font-family:
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                Roboto,
                Helvetica,
                Arial,
                sans-serif;

            background:
                linear-gradient(
                    135deg,
                    #f8fafc 0%,
                    #eef2f7 100%
                );

            color: #111827;

            min-height: 100vh;

        }


        /* ==================================================
           PAGE
        ================================================== */

        .login-page {

            min-height: 100vh;

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 30px 20px;

        }


        /* ==================================================
           LOGIN WRAPPER
        ================================================== */

        .login-wrapper {

            width: 100%;

            max-width: 440px;

        }


        /* ==================================================
           BRAND
        ================================================== */

        .login-brand {

            text-align: center;

            margin-bottom: 24px;

        }


        .brand-icon {

            width: 58px;

            height: 58px;

            margin: 0 auto 15px;

            border-radius: 17px;

            display: flex;

            align-items: center;

            justify-content: center;

            background: #111827;

            color: #ffffff;

            font-size: 25px;

            box-shadow:
                0 10px 25px
                rgba(15, 23, 42, 0.15);

        }


        .login-brand h1 {

            margin: 0;

            font-size: 24px;

            font-weight: 800;

            letter-spacing: -0.4px;

            color: #111827;

        }


        .login-brand p {

            margin: 7px 0 0;

            color: #6b7280;

            font-size: 13px;

        }


        /* ==================================================
           CARD
        ================================================== */

        .login-card {

            background: #ffffff;

            border: 1px solid #e5e7eb;

            border-radius: 20px;

            padding: 32px;

            box-shadow:
                0 20px 50px
                rgba(15, 23, 42, 0.08);

        }


        .login-card-header {

            margin-bottom: 25px;

        }


        .login-card-header h2 {

            margin: 0;

            font-size: 20px;

            font-weight: 800;

            color: #111827;

        }


        .login-card-header p {

            margin: 6px 0 0;

            color: #6b7280;

            font-size: 13px;

            line-height: 1.5;

        }


        /* ==================================================
           ERROR
        ================================================== */

        .login-error {

            margin-bottom: 20px;

            padding: 13px 15px;

            border-radius: 11px;

            background: #fef2f2;

            border: 1px solid #fecaca;

            color: #b91c1c;

            font-size: 13px;

            line-height: 1.5;

        }


        .login-error p {

            margin: 0;

        }


        .login-error p + p {

            margin-top: 4px;

        }


        /* ==================================================
           FORM
        ================================================== */

        .login-form {

            display: flex;

            flex-direction: column;

            gap: 20px;

        }


        .form-group {

            display: flex;

            flex-direction: column;

            gap: 8px;

        }


        .form-group label {

            font-size: 13px;

            font-weight: 700;

            color: #374151;

        }


        .input-wrapper {

            position: relative;

        }


        .input-icon {

            position: absolute;

            left: 14px;

            top: 50%;

            transform: translateY(-50%);

            font-size: 15px;

            pointer-events: none;

            opacity: 0.7;

        }


        .form-group input {

            width: 100%;

            height: 48px;

            padding: 0 14px 0 43px;

            border: 1px solid #d1d5db;

            border-radius: 11px;

            background: #ffffff;

            color: #111827;

            font-family: inherit;

            font-size: 14px;

            outline: none;

            transition:
                border-color 0.2s ease,
                box-shadow 0.2s ease;

        }


        .form-group input::placeholder {

            color: #9ca3af;

        }


        .form-group input:focus {

            border-color: #2563eb;

            box-shadow:
                0 0 0 3px
                rgba(37, 99, 235, 0.10);

        }


        /* ==================================================
           PASSWORD
        ================================================== */

        .password-wrapper input {

            padding-right: 48px;

        }


        .password-toggle {

            position: absolute;

            right: 12px;

            top: 50%;

            transform: translateY(-50%);

            width: 32px;

            height: 32px;

            border: none;

            background: transparent;

            color: #6b7280;

            cursor: pointer;

            border-radius: 7px;

            font-size: 14px;

        }


        .password-toggle:hover {

            background: #f3f4f6;

            color: #111827;

        }


        /* ==================================================
           LOGIN BUTTON
        ================================================== */

        .login-button {

            width: 100%;

            height: 48px;

            border: none;

            border-radius: 11px;

            background: #111827;

            color: #ffffff;

            font-family: inherit;

            font-size: 14px;

            font-weight: 700;

            cursor: pointer;

            transition:
                background 0.2s ease,
                transform 0.2s ease,
                box-shadow 0.2s ease;

        }


        .login-button:hover {

            background: #1f2937;

            transform: translateY(-1px);

            box-shadow:
                0 8px 20px
                rgba(15, 23, 42, 0.15);

        }


        .login-button:active {

            transform: translateY(0);

        }


        /* ==================================================
           FOOTER
        ================================================== */

        .login-footer {

            margin-top: 22px;

            text-align: center;

            color: #9ca3af;

            font-size: 11px;

            line-height: 1.5;

        }


        .login-footer strong {

            color: #6b7280;

        }


        /* ==================================================
           MOBILE
        ================================================== */

        @media (max-width: 500px) {

            .login-page {

                padding: 20px 14px;

            }


            .login-card {

                padding: 25px 20px;

                border-radius: 17px;

            }


            .login-brand h1 {

                font-size: 22px;

            }

        }

    </style>

</head>


<body>


<div class="login-page">


    <div class="login-wrapper">


        <!-- ==================================================
             BRAND
        ================================================== -->

        <div class="login-brand">

            <div class="brand-icon">
                🛡️
            </div>

            <h1>
                Petition Platform
            </h1>

            <p>
                Administration Panel
            </p>

        </div>


        <!-- ==================================================
             LOGIN CARD
        ================================================== -->

        <div class="login-card">


            <div class="login-card-header">

                <h2>
                    Admin Login
                </h2>

                <p>
                    Sign in to access the administration panel.
                </p>

            </div>


            <!-- ==================================================
                 ERRORS
            ================================================== -->

            <?php if (!empty($errors)): ?>

                <div class="login-error">

                    <?php foreach ($errors as $error): ?>

                        <p>
                            <?= e($error) ?>
                        </p>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>


            <!-- ==================================================
                 FORM
            ================================================== -->

            <form
                method="POST"
                class="login-form"
            >


                <?= csrf_field() ?>


                <!-- EMAIL -->

                <div class="form-group">

                    <label for="email">
                        Email Address
                    </label>

                    <div class="input-wrapper">

                        <span class="input-icon">
                            ✉️
                        </span>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="admin@example.com"
                            value="<?= e($email ?? '') ?>"
                            autocomplete="email"
                            required
                        >

                    </div>

                </div>


                <!-- PASSWORD -->

                <div class="form-group">

                    <label for="password">
                        Password
                    </label>

                    <div class="input-wrapper password-wrapper">

                        <span class="input-icon">
                            🔒
                        </span>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            required
                        >

                        <button
                            type="button"
                            class="password-toggle"
                            id="passwordToggle"
                            aria-label="Show password"
                        >
                            👁
                        </button>

                    </div>

                </div>


                <!-- LOGIN -->

                <button
                    type="submit"
                    class="login-button"
                >
                    Sign In to Admin Panel
                </button>


            </form>


        </div>


        <!-- ==================================================
             FOOTER
        ================================================== -->

        <div class="login-footer">

            <strong>
                Petition Platform
            </strong>

            &nbsp;•&nbsp;

            Administration Panel

        </div>


    </div>


</div>


<script>

const passwordInput =
    document.getElementById("password");

const passwordToggle =
    document.getElementById("passwordToggle");


passwordToggle.addEventListener(
    "click",
    function () {

        if (
            passwordInput.type === "password"
        ) {

            passwordInput.type = "text";

            passwordToggle.textContent = "🙈";

            passwordToggle.setAttribute(
                "aria-label",
                "Hide password"
            );

        } else {

            passwordInput.type = "password";

            passwordToggle.textContent = "👁";

            passwordToggle.setAttribute(
                "aria-label",
                "Show password"
            );
        }

    }
);

</script>


</body>

</html>