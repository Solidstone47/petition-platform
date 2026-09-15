<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

require_once "includes/db.php";
require_once "includes/functions.php";
require_once "includes/auth.php";


// ======================================================
// CHECK RESET SESSION
// ======================================================

if (
    !isset($_SESSION['password_reset_user_id']) ||
    (int) $_SESSION['password_reset_user_id'] < 1
) {

    set_flash(
        "error",
        "Password reset session is invalid or has expired."
    );

    redirect("login.php");
}


$user_id = (int) $_SESSION['password_reset_user_id'];


// ======================================================
// GET USER
// ======================================================

$stmt = $conn->prepare("
    SELECT
        id,
        fullname,
        email,
        password_reset_required
    FROM users
    WHERE id = ?
    LIMIT 1
");


if (!$stmt) {

    exit("Unable to prepare password reset request.");

}


$stmt->bind_param(
    "i",
    $user_id
);


$stmt->execute();


$result = $stmt->get_result();

$user = $result->fetch_assoc();


$stmt->close();


// ======================================================
// USER NOT FOUND
// ======================================================

if (!$user) {

    unset($_SESSION['password_reset_user_id']);

    set_flash(
        "error",
        "User account could not be found."
    );

    redirect("login.php");
}


// ======================================================
// RESET NO LONGER REQUIRED
// ======================================================

if ((int) $user['password_reset_required'] !== 1) {

    unset($_SESSION['password_reset_user_id']);

    redirect("login.php");
}


// ======================================================
// VARIABLES
// ======================================================

$errors = [];


// ======================================================
// HANDLE NEW PASSWORD
// ======================================================

if (is_post()) {

    verify_csrf();


    $new_password = $_POST['new_password'] ?? '';

    $confirm_password = $_POST['confirm_password'] ?? '';


    // ==================================================
    // VALIDATE NEW PASSWORD
    // ==================================================

    if ($new_password === '') {

        $errors[] =
            "New password is required.";

    } elseif (strlen($new_password) < 8) {

        $errors[] =
            "Password must be at least 8 characters long.";

    }


    // ==================================================
    // CONFIRM PASSWORD
    // ==================================================

    if ($confirm_password === '') {

        $errors[] =
            "Please confirm your new password.";

    } elseif ($new_password !== $confirm_password) {

        $errors[] =
            "Passwords do not match.";

    }


    // ==================================================
    // UPDATE PASSWORD
    // ==================================================

    if (empty($errors)) {

        $hashed_password = password_hash(
            $new_password,
            PASSWORD_DEFAULT
        );


        if ($hashed_password === false) {

            $errors[] =
                "Unable to secure your new password.";

        } else {

            $update_stmt = $conn->prepare("
                UPDATE users
                SET
                    password = ?,
                    password_reset_required = 0
                WHERE id = ?
                  AND password_reset_required = 1
            ");


            if (!$update_stmt) {

                $errors[] =
                    "Unable to update your password.";

            } else {

                $update_stmt->bind_param(
                    "si",
                    $hashed_password,
                    $user_id
                );


                if ($update_stmt->execute()) {

                    if ($update_stmt->affected_rows === 1) {

                        /*
                        --------------------------------------------------
                        Password successfully changed.

                        Remove the temporary reset session.
                        --------------------------------------------------
                        */

                        unset(
                            $_SESSION['password_reset_user_id']
                        );


                        /*
                        --------------------------------------------------
                        Regenerate session ID for security.
                        --------------------------------------------------
                        */

                        session_regenerate_id(true);


                        set_flash(
                            "success",
                            "Your password has been changed successfully. You can now log in."
                        );


                        redirect(
                            "login.php"
                        );


                    } else {

                        $errors[] =
                            "Password reset could not be completed. Please try again.";

                    }

                } else {

                    $errors[] =
                        "Unable to update your password.";

                }


                $update_stmt->close();

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
        Create New Password
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


        .password-reset-container {

            width: 100%;

            max-width: 460px;

        }


        .password-reset-card {

            background: #ffffff;

            border: 1px solid #e5e7eb;

            border-radius: 18px;

            padding: 32px;

            box-shadow:
                0 10px 30px
                rgba(15, 23, 42, 0.06);

        }


        .password-reset-icon {

            width: 58px;

            height: 58px;

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

            font-weight: 800;

        }


        .subtitle {

            margin: 0 0 25px;

            color: #6b7280;

            font-size: 14px;

            line-height: 1.6;

        }


        .user-info {

            padding: 13px 15px;

            margin-bottom: 22px;

            border-radius: 10px;

            background: #f9fafb;

            border: 1px solid #e5e7eb;

            font-size: 13px;

        }


        .user-info strong {

            display: block;

            margin-bottom: 3px;

            color: #111827;

        }


        .user-info span {

            color: #6b7280;

        }


        .error-box {

            margin-bottom: 20px;

            padding: 13px 15px;

            border-radius: 10px;

            background: #fef2f2;

            border: 1px solid #fecaca;

            color: #b91c1c;

            font-size: 13px;

            line-height: 1.5;

        }


        .error-box p {

            margin: 3px 0;

        }


        .form-group {

            margin-bottom: 18px;

        }


        label {

            display: block;

            margin-bottom: 7px;

            font-size: 13px;

            font-weight: 700;

            color: #374151;

        }


        input {

            width: 100%;

            height: 46px;

            padding: 0 13px;

            border: 1px solid #d1d5db;

            border-radius: 9px;

            outline: none;

            font-size: 14px;

            color: #111827;

            background: #ffffff;

        }


        input:focus {

            border-color: #6366f1;

            box-shadow:
                0 0 0 3px
                rgba(99, 102, 241, 0.10);

        }


        .password-hint {

            margin-top: 7px;

            color: #6b7280;

            font-size: 11px;

        }


        .submit-button {

            width: 100%;

            height: 46px;

            border: none;

            border-radius: 9px;

            background: #111827;

            color: #ffffff;

            font-size: 14px;

            font-weight: 700;

            cursor: pointer;

            transition: 0.2s ease;

        }


        .submit-button:hover {

            background: #1f2937;

            transform: translateY(-1px);

        }


        .cancel-link {

            display: block;

            margin-top: 16px;

            text-align: center;

            color: #6b7280;

            font-size: 12px;

            text-decoration: none;

        }


        .cancel-link:hover {

            color: #111827;

        }


        .security-note {

            margin-top: 22px;

            padding-top: 18px;

            border-top: 1px solid #eef0f3;

            color: #6b7280;

            font-size: 11px;

            line-height: 1.6;

            text-align: center;

        }


        @media (max-width: 500px) {

            .password-reset-card {

                padding: 25px 20px;

            }


            h1 {

                font-size: 22px;

            }

        }

    </style>

</head>


<body>


<div class="password-reset-container">


    <div class="password-reset-card">


        <div class="password-reset-icon">
            🔐
        </div>


        <h1>
            Create New Password
        </h1>


        <p class="subtitle">

            Your administrator has reset your password.
            Please create a new password to continue.

        </p>


        <div class="user-info">

            <strong>
                <?= e($user['fullname']) ?>
            </strong>

            <span>
                <?= e($user['email']) ?>
            </span>

        </div>


        <?php if (!empty($errors)): ?>

            <div class="error-box">

                <?php foreach ($errors as $error): ?>

                    <p>
                        <?= e($error) ?>
                    </p>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>


        <form
            method="POST"
            action=""
        >

            <?= csrf_field() ?>


            <!-- ==================================================
                 NEW PASSWORD
            ================================================== -->

            <div class="form-group">

                <label for="new_password">
                    New Password
                </label>


                <input
                    type="password"
                    id="new_password"
                    name="new_password"
                    minlength="8"
                    autocomplete="new-password"
                    required
                >


                <div class="password-hint">

                    Minimum 8 characters.

                </div>

            </div>


            <!-- ==================================================
                 CONFIRM PASSWORD
            ================================================== -->

            <div class="form-group">

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
                class="submit-button"
            >
                Set New Password
            </button>


        </form>


        <a
            href="login.php"
            class="cancel-link"
        >
            Cancel and return to login
        </a>


        <div class="security-note">

            Your new password is securely encrypted before
            it is stored in the database.

        </div>


    </div>


</div>


</body>

</html>