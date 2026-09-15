<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

require_once "../includes/db.php";
require_once "../includes/functions.php";
require_once "../includes/auth.php";

require_admin();


// ======================================================
// GET USER ID
// ======================================================

$user_id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

if ($user_id < 1) {

    set_flash(
        "error",
        "Invalid user."
    );

    redirect("admin_users.php");
}


// ======================================================
// GET USER
// ======================================================

$stmt = $conn->prepare("
    SELECT
        id,
        fullname,
        email
    FROM users
    WHERE id = ?
    LIMIT 1
");

if (!$stmt) {

    set_flash(
        "error",
        "Unable to load user."
    );

    redirect("admin_users.php");
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

    set_flash(
        "error",
        "User not found."
    );

    redirect("admin_users.php");
}


// ======================================================
// HANDLE PASSWORD RESET
// ======================================================

if (is_post()) {

    verify_csrf();

    $update_stmt = $conn->prepare("
        UPDATE users
        SET
            password = NULL,
            password_reset_required = 1
        WHERE id = ?
        LIMIT 1
    ");

    if (!$update_stmt) {

        set_flash(
            "error",
            "Unable to prepare password reset."
        );

        redirect(
            "admin_user_reset.php?id=" . $user_id
        );
    }

    $update_stmt->bind_param(
        "i",
        $user_id
    );

    if ($update_stmt->execute()) {

        if ($update_stmt->affected_rows > 0) {

            set_flash(
                "success",
                "Password reset successfully. The user must create a new password before logging in."
            );

        } else {

            set_flash(
                "success",
                "Password reset requirement has been applied to this user."
            );
        }

    } else {

        set_flash(
            "error",
            "Unable to reset the user's password."
        );
    }

    $update_stmt->close();

    redirect(
        "admin_user_view.php?id=" . $user_id
    );
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
        Reset User Password - Admin Panel
    </title>

    <link
        rel="stylesheet"
        href="css/admin.css"
    >

    <style>

        /* ==================================================
           RESET PAGE
        ================================================== */

        .reset-page {

            max-width: 700px;

            margin: 0 auto;

        }


        .reset-card {

            position: relative;

            overflow: hidden;

            background:
                linear-gradient(
                    145deg,
                    rgba(255,255,255,.96),
                    rgba(248,250,252,.92)
                );

            border: 1px solid rgba(255,255,255,.85);

            border-radius: 24px;

            padding: 34px;

            box-shadow:
                0 20px 55px rgba(15,23,42,.08),
                0 4px 15px rgba(15,23,42,.04);

            backdrop-filter: blur(16px);

            -webkit-backdrop-filter: blur(16px);

        }


        .reset-card::before {

            content: "";

            position: absolute;

            top: 0;

            left: 0;

            right: 0;

            height: 4px;

            background:
                linear-gradient(
                    90deg,
                    #006b3f,
                    #fcd116,
                    #1eb4d4
                );

        }


        /* ==================================================
           ICON
        ================================================== */

        .reset-icon {

            width: 64px;

            height: 64px;

            display: flex;

            align-items: center;

            justify-content: center;

            margin-bottom: 20px;

            border-radius: 18px;

            background:
                linear-gradient(
                    145deg,
                    #fff1f2,
                    #fee2e2
                );

            border: 1px solid #fecaca;

            font-size: 28px;

            box-shadow:
                0 8px 20px rgba(220,38,38,.08);

        }


        /* ==================================================
           HEADING
        ================================================== */

        .reset-card h1 {

            margin: 0 0 9px;

            color: #111827;

            font-size: 28px;

            font-weight: 800;

            letter-spacing: -.4px;

        }


        .reset-description {

            margin: 0 0 26px;

            color: #6b7280;

            font-size: 14px;

            line-height: 1.7;

        }


        /* ==================================================
           USER INFORMATION
        ================================================== */

        .user-info {

            margin-bottom: 22px;

            padding: 6px 18px;

            background:
                rgba(248,250,252,.9);

            border: 1px solid #e5e7eb;

            border-radius: 16px;

        }


        .user-info-row {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;

            padding: 14px 0;

        }


        .user-info-row:not(:last-child) {

            border-bottom: 1px solid #e5e7eb;

        }


        .user-info-label {

            color: #6b7280;

            font-size: 12px;

            font-weight: 800;

            text-transform: uppercase;

            letter-spacing: .5px;

        }


        .user-info-value {

            color: #111827;

            font-size: 14px;

            font-weight: 700;

            text-align: right;

            word-break: break-word;

        }


        /* ==================================================
           WARNING
        ================================================== */

        .warning {

            display: flex;

            gap: 12px;

            margin-bottom: 25px;

            padding: 16px 17px;

            background:
                linear-gradient(
                    145deg,
                    #fffbeb,
                    #fef3c7
                );

            border: 1px solid #fde68a;

            border-radius: 14px;

            color: #92400e;

            font-size: 13px;

            line-height: 1.65;

        }


        .warning strong {

            color: #78350f;

        }


        /* ==================================================
           ACTIONS
        ================================================== */

        .actions {

            display: flex;

            gap: 10px;

        }


        .button {

            min-height: 46px;

            padding: 0 20px;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            border-radius: 11px;

            font-family: inherit;

            font-size: 13px;

            font-weight: 800;

            text-decoration: none;

            cursor: pointer;

            transition:
                transform .18s ease,
                box-shadow .18s ease,
                background .18s ease;

            box-sizing: border-box;

        }


        .button:hover {

            transform: translateY(-1px);

        }


        .button-reset {

            border: 1px solid #dc2626;

            background:
                linear-gradient(
                    135deg,
                    #dc2626,
                    #b91c1c
                );

            color: #ffffff;

            box-shadow:
                0 8px 18px rgba(220,38,38,.18);

        }


        .button-reset:hover {

            background:
                linear-gradient(
                    135deg,
                    #b91c1c,
                    #991b1b
                );

            box-shadow:
                0 10px 22px rgba(220,38,38,.24);

        }


        .button-cancel {

            border: 1px solid #d1d5db;

            background: rgba(255,255,255,.9);

            color: #374151;

        }


        .button-cancel:hover {

            background: #f3f4f6;

        }


        /* ==================================================
           RESPONSIVE
        ================================================== */

        @media (max-width: 700px) {

            .reset-page {

                max-width: 100%;

            }

            .reset-card {

                padding: 25px 20px;

                border-radius: 20px;

            }

            .reset-card h1 {

                font-size: 24px;

            }

        }


        @media (max-width: 520px) {

            .user-info-row {

                align-items: flex-start;

                flex-direction: column;

                gap: 5px;

            }

            .user-info-value {

                text-align: left;

            }

            .actions {

                flex-direction: column;

            }

            .button {

                width: 100%;

            }

        }

    </style>

</head>


<body>

<div class="admin-layout">


    <!-- ==================================================
         SIDEBAR
    ================================================== -->

    <aside class="admin-sidebar">

        <div class="admin-brand">

            Petition Platform

            <span>
                Administration Panel
            </span>

        </div>


        <div class="admin-menu-title">
            Main
        </div>


        <a href="admin_dashboard.php">
            Dashboard
        </a>


        <div class="admin-menu-title">
            Management
        </div>


        <a href="admin_petitions.php">
            Petitions
        </a>


        <a
            href="admin_users.php"
            class="active"
        >
            Users
        </a>


        <a href="admin_messages.php">
            Messages
        </a>


        <a href="admin_signatures.php">
            Signatures
        </a>


        <div class="admin-menu-title">
            Analytics
        </div>


        <a href="admin_statistics.php">
            Statistics
        </a>


        <div class="admin-menu-title">
            System
        </div>


        <a href="admin_settings.php">
            Settings
        </a>


        <a
            href="admin_logout.php"
            class="admin-logout"
        >
            Logout
        </a>

    </aside>


    <!-- ==================================================
         MAIN
    ================================================== -->

    <main class="admin-main">


        <!-- TOP BAR -->

        <header class="admin-topbar">

            <h2>
                User Management
            </h2>


            <div class="admin-user">
                Administrator
            </div>

        </header>


        <!-- CONTENT -->

        <div class="admin-content">


            <div class="reset-page">


                <div class="reset-card">


                    <!-- ICON -->

                    <div class="reset-icon">
                        🔐
                    </div>


                    <!-- TITLE -->

                    <h1>
                        Reset User Password
                    </h1>


                    <p class="reset-description">

                        Reset the selected user's password.
                        Their existing password will be removed,
                        and they will be required to create a new
                        password before they can log in again.

                    </p>


                    <!-- ==================================================
                         USER INFORMATION
                    ================================================== -->

                    <div class="user-info">


                        <div class="user-info-row">

                            <div class="user-info-label">
                                Full Name
                            </div>


                            <div class="user-info-value">

                                <?= e(
                                    $user['fullname']
                                    ?: 'Unnamed User'
                                ) ?>

                            </div>

                        </div>


                        <div class="user-info-row">

                            <div class="user-info-label">
                                Email
                            </div>


                            <div class="user-info-value">

                                <?= e(
                                    $user['email']
                                ) ?>

                            </div>

                        </div>


                        <div class="user-info-row">

                            <div class="user-info-label">
                                Account ID
                            </div>


                            <div class="user-info-value">

                                #<?= (int) $user['id'] ?>

                            </div>

                        </div>


                    </div>


                    <!-- ==================================================
                         WARNING
                    ================================================== -->

                    <div class="warning">

                        <div>
                            ⚠️
                        </div>

                        <div>

                            <strong>
                                Important:
                            </strong>

                            This action will remove the user's
                            current password. The old password
                            will no longer work.

                            The user will be required to create a
                            new password through the password
                            reset flow.

                        </div>

                    </div>


                   <!-- ==================================================
                                             ACTIONS
                        ================================================== -->

                        <form
                            method="POST"
                            action=""
                            onsubmit="return confirm('Are you sure you want to reset this user\'s password? The old password will no longer work.');"
                        >

                                <?= csrf_field() ?>

                                <div class="actions">

                                    <button
                                        type="submit"
                                        class="button button-reset"
                                    >
                                        Reset Password
                                    </button>

                                    <a
                                        href="admin_user_view.php?id=<?= (int) $user['id'] ?>"
                                        class="button button-cancel"
                                    >
                                        Cancel
                                    </a>

                                </div>

                            </form>

                        <div class="actions">


                            <button
                                type="submit"
                                class="button button-reset"
                            >
                                🔐 Reset Password
                            </button>


                            <a
                                href="admin_user_view.php?id=<?= (int) $user['id'] ?>"
                                class="button button-cancel"
                            >
                                Cancel
                            </a>


                        </div>


                    </form>


                </div>


            </div>


        </div>


    </main>


</div>

</body>

</html>