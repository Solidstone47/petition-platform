<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

require_once "includes/db.php";
require_once "includes/functions.php";
require_once "includes/auth.php";


// ======================================================
// VARIABLES
// ======================================================

$errors = [];

$token = isset($_GET['token'])
    ? trim($_GET['token'])
    : '';

$reset = null;

$valid_token = false;


// ======================================================
// CHECK TOKEN
// ======================================================

if ($token === '') {

    $errors[] =
        "Invalid or missing password reset link.";

} else {

    // ==================================================
    // FIND VALID RESET TOKEN
    // ==================================================

    $stmt = $conn->prepare("
        SELECT
            id,
            account_type,
            account_id,
            email,
            expires_at,
            used
        FROM password_resets
        WHERE token = ?
        LIMIT 1
    ");


    if (!$stmt) {

        $errors[] =
            "Unable to process the password reset.";

    } else {

        $stmt->bind_param(
            "s",
            $token
        );

        $stmt->execute();

        $result =
            $stmt->get_result();

        $reset =
            $result->fetch_assoc();

        $stmt->close();


        // ==================================================
        // TOKEN NOT FOUND
        // ==================================================

        if (!$reset) {

            $errors[] =
                "This password reset link is invalid.";

        }


        // ==================================================
        // TOKEN ALREADY USED
        // ==================================================

        elseif ((int) $reset['used'] === 1) {

            $errors[] =
                "This password reset link has already been used.";

        }


        // ==================================================
        // TOKEN EXPIRED
        // ==================================================

        elseif (
            strtotime($reset['expires_at']) < time()
        ) {

            $errors[] =
                "This password reset link has expired.";

        }


        // ==================================================
        // USER ACCOUNT ONLY
        // ==================================================

        elseif ($reset['account_type'] !== 'user') {

            $errors[] =
                "Invalid password reset request.";

        }


        // ==================================================
        // TOKEN IS VALID
        // ==================================================

        else {

            $valid_token = true;

        }

    }

}


// ======================================================
// HANDLE NEW PASSWORD
// ======================================================

if (
    is_post() &&
    $valid_token
) {

    verify_csrf();


    // ==================================================
    // GET PASSWORDS
    // ==================================================

    $password = isset($_POST['password'])
        ? $_POST['password']
        : '';

    $confirm_password = isset($_POST['confirm_password'])
        ? $_POST['confirm_password']
        : '';


    // ==================================================
    // VALIDATE PASSWORD
    // ==================================================

    if ($password === '') {

        $errors[] =
            "New password is required.";

    } elseif (strlen($password) < 8) {

        $errors[] =
            "Password must be at least 8 characters.";

    }


    // ==================================================
    // CONFIRM PASSWORD
    // ==================================================

    if ($confirm_password === '') {

        $errors[] =
            "Please confirm your new password.";

    } elseif ($password !== $confirm_password) {

        $errors[] =
            "Passwords do not match.";

    }


    // ==================================================
    // UPDATE PASSWORD
    // ==================================================

    if (empty($errors)) {


        // ==================================================
        // HASH PASSWORD
        // ==================================================

        $hashed_password =
            password_hash(
                $password,
                PASSWORD_DEFAULT
            );


        if ($hashed_password === false) {

            $errors[] =
                "Unable to secure the new password.";

        }

    }


    // ==================================================
    // SAVE NEW PASSWORD
    // ==================================================

    if (empty($errors)) {

        $update_stmt = $conn->prepare("
            UPDATE users
            SET
                password = ?,
                password_reset_required = 0
            WHERE id = ?
            LIMIT 1
        ");


        if (!$update_stmt) {

            $errors[] =
                "Unable to update your password.";

        } else {

            $update_stmt->bind_param(
                "si",
                $hashed_password,
                $reset['account_id']
            );


            if (!$update_stmt->execute()) {

                $errors[] =
                    "Unable to update your password.";

            }


            $update_stmt->close();

        }

    }


    // ==================================================
    // MARK TOKEN AS USED
    // ==================================================

    if (empty($errors)) {

        $used_stmt = $conn->prepare("
            UPDATE password_resets
            SET used = 1
            WHERE id = ?
            LIMIT 1
        ");


        if (!$used_stmt) {

            $errors[] =
                "Password was changed, but the reset request could not be completed.";

        } else {

            $used_stmt->bind_param(
                "i",
                $reset['id']
            );


            if (!$used_stmt->execute()) {

                $errors[] =
                    "Password was changed, but the reset request could not be completed.";

            }


            $used_stmt->close();

        }

    }


    // ==================================================
    // SUCCESS
    // ==================================================

    if (empty($errors)) {

        set_flash(
            "success",
            "Your password has been changed successfully. You can now log in."
        );


        redirect("login.php");

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
        Reset Password
    </title>


    <style>

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            min-height: 100vh;

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 20px;

            background: #f5f7fb;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            color: #111827;

        }


        .reset-container {

            width: 100%;

            max-width: 460px;

        }


        .reset-card {

            background: #ffffff;

            border: 1px solid #e5e7eb;

            border-radius: 18px;

            padding: 30px;

            box-shadow:
                0 10px 30px
                rgba(15, 23, 42, 0.06);

        }


        .reset-icon {

            width: 56px;

            height: 56px;

            display: flex;

            align-items: center;

            justify-content: center;

            margin-bottom: 18px;

            border-radius: 15px;

            background: #eef2ff;

            color: #4338ca;

            font-size: 25px;

        }


        h1 {

            margin: 0 0 8px;

            font-size: 25px;

        }


        .description {

            margin: 0 0 24px;

            color: #6b7280;

            font-size: 13px;

            line-height: 1.6;

        }


        .error-box {

            margin-bottom: 18px;

            padding: 13px 15px;

            border-radius: 10px;

            background: #fef2f2;

            border: 1px solid #fecaca;

            color: #b91c1c;

            font-size: 13px;

        }


        .error-box p {

            margin: 4px 0;

        }


        .password-group {

            margin-bottom: 18px;

        }


        label {

            display: block;

            margin-bottom: 7px;

            font-size: 12px;

            font-weight: 700;

        }


        input {

            width: 100%;

            height: 46px;

            padding: 0 13px;

            border: 1px solid #d1d5db;

            border-radius: 9px;

            outline: none;

            font-family: inherit;

            font-size: 14px;

        }


        input:focus {

            border-color: #6366f1;

        }


        .password-help {

            margin-top: 6px;

            color: #9ca3af;

            font-size: 11px;

        }


        .button {

            width: 100%;

            height: 46px;

            margin-top: 4px;

            border: none;

            border-radius: 9px;

            background: #111827;

            color: #ffffff;

            font-family: inherit;

            font-size: 13px;

            font-weight: 700;

            cursor: pointer;

        }


        .button:hover {

            background: #1f2937;

        }


        .back {

            display: block;

            margin-top: 20px;

            text-align: center;

            color: #4f46e5;

            text-decoration: none;

            font-size: 13px;

            font-weight: 700;

        }


        .back:hover {

            text-decoration: underline;

        }


        .invalid-message {

            padding: 16px;

            margin-bottom: 18px;

            border-radius: 10px;

            background: #fff7ed;

            border: 1px solid #fed7aa;

            color: #c2410c;

            font-size: 13px;

            line-height: 1.6;

        }

    </style>

</head>


<body>


<div class="reset-container">


    <div class="reset-card">


        <div class="reset-icon">
            🔐
        </div>


        <h1>
            Create New Password
        </h1>


        <?php if (!empty($errors)): ?>

            <div class="error-box">

                <?php foreach ($errors as $error): ?>

                    <p>
                        <?= e($error) ?>
                    </p>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>


        <?php if ($valid_token): ?>

            <p class="description">

                Enter a new password for your account.
                Your old password will no longer work.

            </p>


            <form
                method="POST"
                action=""
            >

                <?= csrf_field() ?>


                <div class="password-group">

                    <label for="password">
                        New Password
                    </label>


                    <input
                        type="password"
                        id="password"
                        name="password"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >


                    <div class="password-help">

                        Password must contain at least 8 characters.

                    </div>

                </div>


                <div class="password-group">

                    <label for="confirm_password">
                        Confirm New Password
                    </label>


                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >

                </div>


                <button
                    type="submit"
                    class="button"
                >
                    Change Password
                </button>


            </form>


        <?php else: ?>


            <div class="invalid-message">

                This password reset link cannot be used.
                It may be invalid, expired, or already used.

            </div>


            <a
                href="forgot_password.php"
                class="back"
            >
                Request a New Reset Link
            </a>


        <?php endif; ?>


        <?php if ($valid_token): ?>

            <a
                href="login.php"
                class="back"
            >
                ← Back to Login
            </a>

        <?php endif; ?>


    </div>


</div>


</body>

</html>