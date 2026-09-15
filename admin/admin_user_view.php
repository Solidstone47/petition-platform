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
        email,
        password_reset_required
    FROM users
    WHERE id = ?
    LIMIT 1
");


if (!$stmt) {

    exit("Unable to prepare user request.");

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


    $action = $_POST['action'] ?? '';


    // ==================================================
    // RESET PASSWORD
    // ==================================================

    if ($action === 'reset_password') {


        /*
        --------------------------------------------------
        REMOVE THE CURRENT PASSWORD.

        Because the password column allows NULL, the user's
        current password can safely be removed.

        password_reset_required = 1 tells the login system
        that this user must create a new password.
        --------------------------------------------------
        */

        $reset_stmt = $conn->prepare("
            UPDATE users
            SET
                password = NULL,
                password_reset_required = 1
            WHERE id = ?
            LIMIT 1
        ");


        if (!$reset_stmt) {

            set_flash(
                "error",
                "Unable to prepare password reset."
            );

            redirect(
                "admin_user_reset.php?id=" . $user_id
            );
        }


        $reset_stmt->bind_param(
            "i",
            $user_id
        );


        if ($reset_stmt->execute()) {

            $reset_stmt->close();


            set_flash(
                "success",
                "Password reset successfully. The user must create a new password when they next log in."
            );


            redirect(
                "admin_user_view.php?id=" . $user_id
            );


        } else {

            $reset_stmt->close();


            set_flash(
                "error",
                "Unable to reset the user's password."
            );


            redirect(
                "admin_user_reset.php?id=" . $user_id
            );

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
        Reset Password - Admin Panel
    </title>

    <link
        rel="stylesheet"
        href="css/admin.css"
    >


    <style>

/* ======================================================
   RESET PAGE
====================================================== */

.user-reset-page {

    max-width: 850px;

    margin: 0 auto;

}


/* ======================================================
   HEADER
====================================================== */

.user-reset-header {

    display: flex;

    align-items: center;

    gap: 15px;

    margin-bottom: 25px;

}


.user-reset-back {

    width: 42px;

    height: 42px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    border-radius: 10px;

    background: #ffffff;

    border: 1px solid #dfe3e8;

    color: #374151;

    text-decoration: none;

    font-size: 20px;

    transition: 0.2s ease;

}


.user-reset-back:hover {

    background: #f3f4f6;

    transform: translateX(-2px);

}


.user-reset-header h1 {

    margin: 0;

    color: #111827;

    font-size: 28px;

    font-weight: 800;

}


.user-reset-header p {

    margin: 5px 0 0;

    color: #6b7280;

    font-size: 13px;

}


/* ======================================================
   CARD
====================================================== */

.reset-card {

    background: #ffffff;

    border: 1px solid #e5e7eb;

    border-radius: 18px;

    box-shadow:
        0 5px 20px rgba(15, 23, 42, 0.04);

    overflow: hidden;

}


/* ======================================================
   USER INFORMATION
====================================================== */

.reset-user {

    display: flex;

    align-items: center;

    gap: 15px;

    padding: 24px 26px;

    background: #fafbff;

    border-bottom: 1px solid #eef0f3;

}


.reset-avatar {

    width: 52px;

    height: 52px;

    border-radius: 14px;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #eef2ff;

    color: #4338ca;

    font-size: 20px;

    font-weight: 800;

    flex-shrink: 0;

}


.reset-user-name {

    margin: 0 0 4px;

    color: #111827;

    font-size: 17px;

    font-weight: 800;

}


.reset-user-email {

    color: #6b7280;

    font-size: 12px;

}


/* ======================================================
   CONTENT
====================================================== */

.reset-content {

    padding: 28px 26px;

}


/* ======================================================
   INFORMATION BOX
====================================================== */

.reset-info {

    padding: 18px;

    border-radius: 12px;

    background: #eff6ff;

    border: 1px solid #dbeafe;

    color: #1e40af;

    margin-bottom: 20px;

}


.reset-info-title {

    font-size: 14px;

    font-weight: 800;

    margin-bottom: 7px;

}


.reset-info-text {

    font-size: 13px;

    line-height: 1.6;

    color: #374151;

}


/* ======================================================
   WARNING
====================================================== */

.reset-warning {

    padding: 16px 18px;

    border-radius: 12px;

    background: #fff7ed;

    border: 1px solid #fed7aa;

    color: #9a3412;

    font-size: 13px;

    line-height: 1.6;

    margin-bottom: 24px;

}


.reset-warning strong {

    display: block;

    margin-bottom: 5px;

}


/* ======================================================
   ACTIONS
====================================================== */

.reset-actions {

    display: flex;

    justify-content: flex-end;

    gap: 9px;

    padding-top: 5px;

}


.reset-button,
.reset-cancel {

    min-height: 43px;

    padding: 0 18px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    border-radius: 10px;

    font-family: inherit;

    font-size: 13px;

    font-weight: 700;

    text-decoration: none;

    box-sizing: border-box;

    cursor: pointer;

    transition: 0.2s ease;

}


.reset-button {

    border: none;

    background: #dc2626;

    color: #ffffff;

}


.reset-button:hover {

    background: #b91c1c;

    transform: translateY(-1px);

}


.reset-cancel {

    background: #ffffff;

    color: #374151;

    border: 1px solid #d1d5db;

}


.reset-cancel:hover {

    background: #f3f4f6;

}



/* ======================================================
   MOBILE
====================================================== */

@media (max-width: 600px) {

    .user-reset-header h1 {

        font-size: 23px;

    }


    .reset-user {

        padding: 20px;

    }


    .reset-content {

        padding: 22px 20px;

    }


    .reset-actions {

        flex-direction: column-reverse;

    }


    .reset-button,
    .reset-cancel {

        width: 100%;

    }

}

    </style>

</head>


<body>


<div class="admin-layout">


   <!-- ======================================================
         SIDEBAR
    ======================================================= -->

    <aside class="admin-sidebar">


        <div class="admin-brand">

            🇹🇿 Petition Platform

            <span>
                Administration Panel
            </span>

        </div>


        <div class="admin-menu-title">
            Main
        </div>


        <a href="admin_dashboard.php">

            <i class="fa-solid fa-gauge-high icon-dashboard"></i>

            <span>
                Dashboard
            </span>

        </a>


        <div class="admin-menu-title">
            Management
        </div>


        <a href="admin_petitions.php">

            <i class="fa-solid fa-file-signature icon-petitions"></i>

            <span>
                Petitions
            </span>

        </a>


        <a
            href="admin_users.php"
            class="active"
        >

            <i class="fa-solid fa-users icon-users"></i>

            <span>
                Users
            </span>

        </a>


        <a href="admin_messages.php">

            <i class="fa-solid fa-comments icon-messages"></i>

            <span>
                Messages
            </span>

        </a>


        <a href="admin_signatures.php">

            <i class="fa-solid fa-pen-nib icon-signatures"></i>

            <span>
                Signatures
            </span>

        </a>


        <div class="admin-menu-title">
            Analytics
        </div>


        <a href="admin_statistics.php">

            <i class="fa-solid fa-chart-line icon-statistics"></i>

            <span>
                Statistics
            </span>

        </a>


        <div class="admin-menu-title">
            System
        </div>


        <a href="admin_settings.php">

            <i class="fa-solid fa-gear icon-settings"></i>

            <span>
                Settings
            </span>

        </a>


        <a
            href="admin_logout.php"
            class="admin-logout"
        >

            <i class="fa-solid fa-right-from-bracket icon-logout"></i>

            <span>
                Logout
            </span>

        </a>


    </aside>


    <!-- ==================================================
         MAIN
    ================================================== -->

    <main class="admin-main">


        <header class="admin-topbar">

            <h2>
                User Management
            </h2>


            <div class="admin-user">
                Administrator
            </div>

        </header>


        <div class="admin-content">


            <div class="user-reset-page">


                <!-- ==================================================
                     PAGE HEADER
                ================================================== -->

                <div class="user-reset-header">


                    <a
                        href="admin_user_view.php?id=<?= (int) $user['id'] ?>"
                        class="user-reset-back"
                        title="Back to User"
                    >
                        ←
                    </a>


                    <div>

                        <h1>
                            Reset Password
                        </h1>


                        <p>
                            Force this user to create a new password.
                        </p>

                    </div>


                </div>


                <!-- ==================================================
                     RESET CARD
                ================================================== -->

                <div class="reset-card">


                    <!-- USER INFORMATION -->

                    <div class="reset-user">


                        <div class="reset-avatar">

                            <?= e(
                                strtoupper(
                                    substr(
                                        trim(
                                            $user['fullname'] ?? ''
                                        ) ?: 'U',
                                        0,
                                        1
                                    )
                                )
                            ) ?>

                        </div>


                        <div>

                            <h2 class="reset-user-name">

                                <?= e(
                                    trim(
                                        $user['fullname'] ?? ''
                                    ) ?: 'Unnamed User'
                                ) ?>

                            </h2>


                            <div class="reset-user-email">

                                <?= e(
                                    $user['email']
                                ) ?>

                            </div>

                        </div>


                    </div>


                    <!-- CONTENT -->

                    <div class="reset-content">


                        <!-- ==================================================
                             INFORMATION
                        ================================================== -->

                        <div class="reset-info">


                            <div class="reset-info-title">

                                🔐 How this works

                            </div>


                            <div class="reset-info-text">

                                The user's current password will be
                                permanently removed from the database.

                                The account will then require the user
                                to create a new password the next time
                                they attempt to log in.

                            </div>


                        </div>


                        <!-- ==================================================
                             WARNING
                        ================================================== -->

                        <div class="reset-warning">


                            <strong>
                                ⚠️ Important
                            </strong>


                            This action will immediately invalidate the
                            user's current password.


                            <br>


                            The user will not be able to log in using
                            their old password.


                            <br><br>


                            They will be required to create a new password
                            before they can access their account.

                        </div>


                        <!-- ==================================================
                             RESET FORM
                        ================================================== -->

                        <form
                            method="POST"
                            action="admin_user_reset.php?id=<?= (int) $user['id'] ?>"
                        >


                            <?= csrf_field() ?>


                            <input
                                type="hidden"
                                name="action"
                                value="reset_password"
                            >


                            <div class="reset-actions">


                                <a
                                    href="admin_user_view.php?id=<?= (int) $user['id'] ?>"
                                    class="reset-cancel"
                                >
                                    Cancel
                                </a>


                                <button
                                    type="submit"
                                    class="reset-button"
                                    onclick="return confirm('Are you sure you want to reset this user\'s password?');"
                                >
                                    Reset User Password
                                </button>


                            </div>


                        </form>


                    </div>


                </div>


            </div>


        </div>


    </main>


</div>


</body>

</html>