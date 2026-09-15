<?php

require_once "includes/db.php";
require_once "includes/functions.php";
require_once "includes/auth.php";

// ======================================================
// SESSION / USER
// ======================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_logged_in = is_user_logged_in();

// ======================================================
// FORM VALUES
// ======================================================

$fullname = "";
$email = "";
$subject = "";
$message = "";

// ======================================================
// PRE-FILL LOGGED-IN USER
// ======================================================

if ($user_logged_in) {

    $user_id = (int) ($_SESSION["user_id"] ?? 0);

    if ($user_id > 0) {

        $stmt = $conn->prepare(
            "SELECT fullname, email
             FROM users
             WHERE id = ?
             LIMIT 1"
        );

        if ($stmt) {

            $stmt->bind_param("i", $user_id);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {

                $user = $result->fetch_assoc();

                $fullname = $user["fullname"] ?? "";
                $email = $user["email"] ?? "";
            }

            $stmt->close();
        }
    }
}

// ======================================================
// HANDLE FORM SUBMISSION
// ======================================================

if (is_post()) {

    verify_csrf();

    $fullname = trim($_POST["fullname"] ?? "");
    $email    = trim($_POST["email"] ?? "");
    $subject  = trim($_POST["subject"] ?? "");
    $message  = trim($_POST["message"] ?? "");

    // ==================================================
    // VALIDATION
    // ==================================================

    if ($fullname === "") {

        set_flash(
            "error",
            "Please enter your full name."
        );

        redirect("contact.php");
    }

    if ($email === "") {

        set_flash(
            "error",
            "Please enter your email address."
        );

        redirect("contact.php");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        set_flash(
            "error",
            "Please enter a valid email address."
        );

        redirect("contact.php");
    }

    if ($subject === "") {

        set_flash(
            "error",
            "Please enter a subject."
        );

        redirect("contact.php");
    }

    if ($message === "") {

        set_flash(
            "error",
            "Please enter your message."
        );

        redirect("contact.php");
    }

    if (strlen($fullname) > 150) {

        set_flash(
            "error",
            "Your name is too long."
        );

        redirect("contact.php");
    }

    if (strlen($email) > 190) {

        set_flash(
            "error",
            "Your email address is too long."
        );

        redirect("contact.php");
    }

    if (strlen($subject) > 255) {

        set_flash(
            "error",
            "Your subject is too long."
        );

        redirect("contact.php");
    }

    if (strlen($message) > 10000) {

        set_flash(
            "error",
            "Your message is too long. Please keep it under 10,000 characters."
        );

        redirect("contact.php");
    }

    // ==================================================
    // INSERT MESSAGE
    // ==================================================

    $status = "unread";

    $stmt = $conn->prepare(
        "INSERT INTO messages
        (
            fullname,
            email,
            subject,
            message,
            status,
            created_at
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?,
            NOW()
        )"
    );

    if (!$stmt) {

        set_flash(
            "error",
            "Unable to prepare your message. Please try again."
        );

        redirect("contact.php");
    }

    $stmt->bind_param(
        "sssss",
        $fullname,
        $email,
        $subject,
        $message,
        $status
    );

    if ($stmt->execute()) {

        $stmt->close();

        set_flash(
            "success",
            "Your message has been sent successfully. Thank you for contacting us."
        );

        redirect("contact.php");
    }

    $stmt->close();

    set_flash(
        "error",
        "Unable to send your message. Please try again later."
    );

    redirect("contact.php");
}

// ======================================================
// FLASH MESSAGE
// ======================================================

$flash = get_flash();

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
        content="Contact the Tanzania Petition Platform administration."
    >

    <title>
        Contact Us | Petition Platform
    </title>

    <!-- ==================================================
         FONT AWESOME
    =================================================== -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    <!-- ==================================================
         MAIN CSS
    =================================================== -->

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

    <!-- ==================================================
         LOTTIE
    =================================================== -->

    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/bodymovin/5.12.2/lottie.min.js"
    ></script>


    <style>

        /* ==================================================
           PAGE
        ================================================== */

        body {

            background:
                linear-gradient(
                    180deg,
                    #f4f7fb 0%,
                    #eef3f8 45%,
                    #f8fafc 100%
                );

        }


        /* ==================================================
           EXACT PETITIONS-STYLE NAVIGATION
        ================================================== */

        .homepage-navigation {

            position: relative;

            width: 100%;

            min-height: 72px;

            background: #ffffff;

            border-bottom:
                1px solid var(--border);

            display: flex;

            align-items: center;

            padding:
                0 20px;

            box-sizing:
                border-box;

        }


        .homepage-brand {

            position: absolute;

            left: 25px;

            top: 50%;

            transform:
                translateY(-50%);

            z-index: 5;

        }


        .homepage-brand a {

            color:
                var(--primary);

            text-decoration:
                none;

            font-size:
                20px;

            font-weight:
                800;

            white-space:
                nowrap;

        }


        .homepage-brand a:hover {

            color:
                var(--primary-dark);

        }


        .homepage-tabs {

            position: absolute;

            left: 50%;

            top: 50%;

            transform:
                translate(-50%, -50%);

            display: flex;

            align-items: center;

            justify-content: center;

            gap: 26px;

            white-space: nowrap;

        }


        .homepage-tabs a {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            color:
                var(--text);

            text-decoration:
                none;

            font-size:
                15px;

            font-weight:
                600;

            padding:
                8px 2px;

            transition:
                color 0.2s ease;

        }


        .homepage-tabs a:hover {

            color:
                var(--primary);

        }


        .homepage-tabs a[aria-current="page"] {

            color:
                var(--primary);

            font-weight:
                700;

        }


        /* ==================================================
           CONTACT SUPPORT-STYLE TAB
        ================================================== */

        .homepage-tabs .homepage-contact {

            position:
                relative;

            display:
                inline-flex;

            align-items:
                center;

            gap:
                6px;

            padding:
                9px 13px;

            border-radius:
                999px;

            background:
                rgba(0,106,78,0.055);

            border:
                1px solid rgba(0,106,78,0.12);

            color:
                var(--primary);

            backdrop-filter:
                blur(8px);

            -webkit-backdrop-filter:
                blur(8px);

            box-shadow:
                0 4px 15px rgba(
                    15,
                    23,
                    42,
                    0.045
                );

            transition:
                all 0.22s ease;

        }


        .homepage-tabs .homepage-contact i {

            font-size:
                12px;

        }


        .homepage-tabs .homepage-contact:hover {

            background:
                rgba(0,106,78,0.10);

            color:
                var(--primary-dark);

            transform:
                translateY(-1px);

            box-shadow:
                0 7px 20px rgba(
                    15,
                    23,
                    42,
                    0.09
                );

        }


        /* ==================================================
           REGISTER
        ================================================== */

        .homepage-tabs .homepage-register {

            background:
                var(--gold);

            color:
                #111827;

            padding:
                9px 16px;

            border-radius:
                7px;

            font-weight:
                700;

        }


        .homepage-tabs .homepage-register:hover {

            color:
                #111827;

            opacity:
                0.9;

        }


        /* ==================================================
           TANZANIA FLAG
        ================================================== */

        .homepage-flag {

            position: absolute;

            right: 18px;

            top: 50%;

            transform:
                translateY(-50%);

            width: 62px;

            height: 62px;

            display: flex;

            align-items: center;

            justify-content: center;

            overflow: hidden;

            z-index: 10;

        }


        .homepage-flag svg {

            width:
                100%;

            height:
                100%;

            display:
                block;

        }


        /* ==================================================
           CONTACT PAGE
        ================================================== */

        .contact-page {

            min-height:
                calc(100vh - 72px);

            padding:
                58px 25px 75px;

        }


        .contact-container {

            width:
                100%;

            max-width:
                1140px;

            margin:
                0 auto;

        }


        /* ==================================================
           HERO
        ================================================== */

        .contact-hero {

            position:
                relative;

            overflow:
                hidden;

            max-width:
                850px;

            margin:
                0 auto 38px;

            text-align:
                center;

        }


        .contact-eyebrow {

            display:
                inline-flex;

            align-items:
                center;

            gap:
                8px;

            padding:
                7px 13px;

            margin-bottom:
                16px;

            border:
                1px solid rgba(
                    0,
                    106,
                    78,
                    0.12
                );

            background:
                rgba(
                    0,
                    106,
                    78,
                    0.055
                );

            color:
                var(--primary);

            border-radius:
                999px;

            font-size:
                11px;

            font-weight:
                800;

            letter-spacing:
                .7px;

            text-transform:
                uppercase;

            backdrop-filter:
                blur(8px);

            -webkit-backdrop-filter:
                blur(8px);

        }


        .contact-eyebrow i {

            font-size:
                11px;

        }


        .contact-hero h1 {

            margin:
                0 0 14px;

            color:
                var(--text);

            font-size:
                clamp(
                    38px,
                    5vw,
                    54px
                );

            line-height:
                1.08;

            letter-spacing:
                -1px;

            font-weight:
                800;

        }


        .contact-hero p {

            max-width:
                720px;

            margin:
                0 auto;

            color:
                var(--text-muted);

            font-size:
                16px;

            line-height:
                1.7;

        }


        /* ==================================================
           FLASH
        ================================================== */

        .contact-flash {

            display:
                flex;

            align-items:
                center;

            gap:
                12px;

            max-width:
                820px;

            margin:
                0 auto 25px;

            padding:
                14px 17px;

            border-radius:
                14px;

            font-size:
                13px;

            line-height:
                1.5;

            font-weight:
                700;

            backdrop-filter:
                blur(16px);

            -webkit-backdrop-filter:
                blur(16px);

        }


        .contact-flash-success {

            background:
                rgba(
                    236,
                    253,
                    245,
                    .9
                );

            border:
                1px solid rgba(
                    167,
                    243,
                    208,
                    .9
                );

            color:
                #047857;

        }


        .contact-flash-error {

            background:
                rgba(
                    254,
                    242,
                    242,
                    .9
                );

            border:
                1px solid rgba(
                    254,
                    202,
                    202,
                    .9
                );

            color:
                #b91c1c;

        }


        .contact-flash-icon {

            width:
                22px;

            height:
                22px;

            min-width:
                22px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

        }


        /* ==================================================
           CONTACT GRID
        ================================================== */

        .contact-grid {

            display:
                grid;

            grid-template-columns:
                minmax(300px, .78fr)
                minmax(0, 1.22fr);

            gap:
                22px;

            align-items:
                stretch;

        }


        /* ==================================================
           INFORMATION CARD
        ================================================== */

        .contact-info {

            position:
                relative;

            overflow:
                hidden;

            padding:
                30px;

            border-radius:
                20px;

            background:
                rgba(
                    255,
                    255,
                    255,
                    0.74
                );

            border:
                1px solid rgba(
                    255,
                    255,
                    255,
                    0.92
                );

            box-shadow:
                0 10px 30px rgba(
                    15,
                    23,
                    42,
                    0.06
                );

            backdrop-filter:
                blur(15px);

            -webkit-backdrop-filter:
                blur(15px);

        }


        .contact-info::before {

            content:
                "";

            position:
                absolute;

            width:
                180px;

            height:
                180px;

            top:
                -90px;

            right:
                -80px;

            border-radius:
                50%;

            background:
                rgba(
                    0,
                    106,
                    78,
                    .055
                );

            pointer-events:
                none;

        }


        .contact-info-icon {

            width:
                54px;

            height:
                54px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            margin-bottom:
                18px;

            border-radius:
                16px;

            background:
                var(--primary-light);

            border:
                1px solid rgba(
                    0,
                    106,
                    78,
                    .11
                );

            color:
                var(--primary);

            font-size:
                21px;

        }


        .contact-info h2 {

            margin:
                0 0 9px;

            color:
                var(--text);

            font-size:
                22px;

            letter-spacing:
                -.3px;

        }


        .contact-info > p {

            margin:
                0 0 25px;

            color:
                var(--text-muted);

            font-size:
                13px;

            line-height:
                1.7;

        }


        .contact-reasons {

            display:
                flex;

            flex-direction:
                column;

            gap:
                14px;

        }


        .contact-reason {

            display:
                flex;

            align-items:
                flex-start;

            gap:
                11px;

            color:
                var(--text-muted);

            font-size:
                12px;

            line-height:
                1.5;

        }


        .contact-reason i {

            margin-top:
                2px;

            color:
                var(--primary);

            font-size:
                11px;

        }


        /* ==================================================
           FORM CARD
        ================================================== */

        .contact-form-card {

            padding:
                30px;

            border-radius:
                20px;

            background:
                rgba(
                    255,
                    255,
                    255,
                    0.78
                );

            border:
                1px solid rgba(
                    255,
                    255,
                    255,
                    0.92
                );

            box-shadow:
                0 10px 30px rgba(
                    15,
                    23,
                    42,
                    0.06
                );

            backdrop-filter:
                blur(15px);

            -webkit-backdrop-filter:
                blur(15px);

        }


        .contact-form-header {

            margin-bottom:
                21px;

        }


        .contact-form-header h2 {

            margin:
                0;

            color:
                var(--text);

            font-size:
                21px;

            letter-spacing:
                -.3px;

        }


        .contact-form-header p {

            margin:
                6px 0 0;

            color:
                var(--text-muted);

            font-size:
                12px;

            line-height:
                1.5;

        }


        /* ==================================================
           FORM
        ================================================== */

        .contact-form {

            display:
                flex;

            flex-direction:
                column;

            gap:
                16px;

        }


        .contact-form-row {

            display:
                grid;

            grid-template-columns:
                1fr 1fr;

            gap:
                14px;

        }


        .contact-field {

            display:
                flex;

            flex-direction:
                column;

            gap:
                7px;

        }


        .contact-field label {

            color:
                #334155;

            font-size:
                11px;

            font-weight:
                800;

        }


        .contact-required {

            color:
                #dc2626;

        }


        .contact-input-wrap {

            position:
                relative;

        }


        .contact-input-icon {

            position:
                absolute;

            left:
                14px;

            top:
                15px;

            color:
                #94a3b8;

            font-size:
                13px;

            pointer-events:
                none;

        }


        .contact-field input,
        .contact-field textarea {

            width:
                100%;

            box-sizing:
                border-box;

            border:
                1px solid #dbe2e8;

            border-radius:
                11px;

            background:
                rgba(
                    255,
                    255,
                    255,
                    .84
                );

            color:
                #1e293b;

            font-family:
                inherit;

            font-size:
                13px;

            outline:
                none;

            transition:
                border-color .2s ease,
                box-shadow .2s ease,
                background .2s ease;

        }


        .contact-field input {

            height:
                46px;

            padding:
                0 13px 0 40px;

        }


        .contact-field textarea {

            min-height:
                150px;

            resize:
                vertical;

            padding:
                13px 13px 13px 40px;

            line-height:
                1.6;

        }


        .contact-field input::placeholder,
        .contact-field textarea::placeholder {

            color:
                #a1aab8;

        }


        .contact-field input:focus,
        .contact-field textarea:focus {

            background:
                #ffffff;

            border-color:
                var(--primary);

            box-shadow:
                0 0 0 3px rgba(
                    0,
                    106,
                    78,
                    .09
                );

        }


        .contact-textarea-icon {

            top:
                14px;

        }


        /* ==================================================
           MESSAGE META
        ================================================== */

        .contact-message-meta {

            display:
                flex;

            align-items:
                center;

            justify-content:
                space-between;

            margin-top:
                -1px;

            color:
                #94a3b8;

            font-size:
                10px;

        }


        /* ==================================================
           SUBMIT
        ================================================== */

        .contact-submit-area {

            display:
                flex;

            align-items:
                center;

            justify-content:
                space-between;

            gap:
                15px;

            margin-top:
                2px;

        }


        .contact-note {

            display:
                flex;

            align-items:
                flex-start;

            gap:
                7px;

            color:
                #94a3b8;

            font-size:
                10px;

            line-height:
                1.45;

        }


        .contact-note i {

            margin-top:
                2px;

            color:
                var(--primary);

        }


        .contact-submit {

            min-width:
                155px;

            height:
                46px;

            display:
                inline-flex;

            align-items:
                center;

            justify-content:
                center;

            gap:
                8px;

            padding:
                0 20px;

            border:
                1px solid var(--primary);

            border-radius:
                11px;

            background:
                var(--primary);

            color:
                #ffffff;

            font-family:
                inherit;

            font-size:
                12px;

            font-weight:
                800;

            cursor:
                pointer;

            box-shadow:
                0 8px 20px rgba(
                    0,
                    106,
                    78,
                    .17
                );

            transition:
                transform .18s ease,
                background .18s ease,
                box-shadow .18s ease;

        }


        .contact-submit:hover {

            background:
                var(--primary-dark);

            transform:
                translateY(-1px);

            box-shadow:
                0 12px 26px rgba(
                    0,
                    106,
                    78,
                    .22
                );

        }


        .contact-submit:active {

            transform:
                translateY(0);

        }


        /* ==================================================
           RESPONSIVE NAVIGATION
        ================================================== */

        @media (max-width: 900px) {

            .homepage-tabs {

                gap:
                    15px;

            }


            .contact-grid {

                grid-template-columns:
                    1fr;

            }


            .contact-info {

                order:
                    2;

            }


            .contact-form-card {

                order:
                    1;

            }

        }


        @media (max-width: 700px) {

            .homepage-navigation {

                min-height:
                    125px;

                padding:
                    10px 15px;

                flex-direction:
                    column;

                justify-content:
                    center;

            }


            .homepage-brand {

                position:
                    static;

                transform:
                    none;

                width:
                    100%;

                text-align:
                    center;

                padding-top:
                    5px;

            }


            .homepage-tabs {

                position:
                    static;

                transform:
                    none;

                width:
                    100%;

                flex-wrap:
                    wrap;

                gap:
                    10px;

                padding:
                    5px 45px 5px 0;

                box-sizing:
                    border-box;

            }


            .homepage-flag {

                right:
                    8px;

                width:
                    48px;

                height:
                    48px;

            }


            .contact-page {

                padding:
                    45px 16px 60px;

            }


            .contact-form-row {

                grid-template-columns:
                    1fr;

            }


            .contact-info,
            .contact-form-card {

                padding:
                    22px;

                border-radius:
                    19px;

            }


            .contact-submit-area {

                flex-direction:
                    column;

                align-items:
                    stretch;

            }


            .contact-submit {

                width:
                    100%;

            }

        }


        @media (max-width: 480px) {

            .contact-page {

                padding:
                    35px 12px 50px;

            }


            .contact-hero h1 {

                font-size:
                    36px;

            }


            .contact-hero p {

                font-size:
                    15px;

            }


            .contact-info,
            .contact-form-card {

                padding:
                    19px;

            }


            .homepage-tabs a {

                font-size:
                    14px;

            }

        }

    </style>

</head>


<body>


<!-- ======================================================
     EXACT PETITIONS NAVIGATION
====================================================== -->

<nav class="homepage-navigation">

    <!-- BRAND -->

    <div class="homepage-brand">

        <a href="index.php">
            Petition Platform
        </a>

    </div>


    <!-- CENTER TABS -->

    <div class="homepage-tabs">

        <a href="index.php">
            Home
        </a>


        <a href="petitions.php">
            Petitions
        </a>


        <a
            href="contact.php"
            class="homepage-contact"
            aria-current="page"
        >

            <i class="fa-solid fa-circle-info"></i>

            Contact

        </a>


        <?php if ($user_logged_in): ?>

            <a href="dashboard.php">
                Dashboard
            </a>


            <a href="logout.php">
                Logout
            </a>

        <?php else: ?>

            <a href="login.php">
                Login
            </a>


            <a
                href="register.php"
                class="homepage-register"
            >
                Register
            </a>

        <?php endif; ?>

    </div>


    <!-- TANZANIA FLAG -->

    <div
        id="tanzania-flag"
        class="homepage-flag"
        aria-label="Tanzania Flag"
        title="Tanzania"
    ></div>

</nav>


<!-- ======================================================
     CONTACT PAGE
====================================================== -->

<main class="contact-page">

    <div class="contact-container">


        <!-- ==================================================
             HERO
        ================================================== -->

        <section class="contact-hero">

            <div class="contact-eyebrow">

                <i class="fa-solid fa-paper-plane"></i>

                Get in touch

            </div>


            <h1>
                Send Us a Message
            </h1>


            <p>
                Have a concern, suggestion, question, report,
                or something you believe we should know about?
                Send us a message and our administration team
                will review it.
            </p>

        </section>


        <!-- ==================================================
             FLASH
        ================================================== -->

        <?php if (!empty($flash)): ?>

            <div
                class="contact-flash
                <?= (
                    ($flash["type"] ?? "") === "success"
                )
                    ? "contact-flash-success"
                    : "contact-flash-error"
                ?>"
            >

                <div class="contact-flash-icon">

                    <?php if (
                        ($flash["type"] ?? "") === "success"
                    ): ?>

                        <i class="fa-solid fa-circle-check"></i>

                    <?php else: ?>

                        <i class="fa-solid fa-circle-exclamation"></i>

                    <?php endif; ?>

                </div>


                <div>

                    <?= e($flash["message"] ?? "") ?>

                </div>

            </div>

        <?php endif; ?>


        <!-- ==================================================
             CONTACT GRID
        ================================================== -->

        <div class="contact-grid">


            <!-- ==================================================
                 INFORMATION
            ================================================== -->

            <section class="contact-info">

                <div class="contact-info-icon">

                    <i class="fa-solid fa-comments"></i>

                </div>


                <h2>
                    We're Listening
                </h2>


                <p>
                    The Tanzania Petition Platform is built
                    around public participation. If there is
                    something concerning you, we want to hear
                    from you.
                </p>


                <div class="contact-reasons">


                    <div class="contact-reason">

                        <i class="fa-solid fa-check"></i>

                        <span>
                            Report a problem with the platform.
                        </span>

                    </div>


                    <div class="contact-reason">

                        <i class="fa-solid fa-check"></i>

                        <span>
                            Send feedback or suggestions.
                        </span>

                    </div>


                    <div class="contact-reason">

                        <i class="fa-solid fa-check"></i>

                        <span>
                            Ask a question about petitions.
                        </span>

                    </div>


                    <div class="contact-reason">

                        <i class="fa-solid fa-check"></i>

                        <span>
                            Raise a concern you believe
                            the administration should see.
                        </span>

                    </div>


                    <div class="contact-reason">

                        <i class="fa-solid fa-check"></i>

                        <span>
                            Share an idea for improving
                            the platform.
                        </span>

                    </div>

                </div>

            </section>


            <!-- ==================================================
                 FORM
            ================================================== -->

            <section class="contact-form-card">

                <div class="contact-form-header">

                    <h2>
                        Contact the Administration
                    </h2>


                    <p>
                        Complete the form below and your message
                        will be delivered to the administration panel.
                    </p>

                </div>


                <form
                    method="POST"
                    action="contact.php"
                    class="contact-form"
                >

                    <?= csrf_field() ?>


                    <!-- NAME + EMAIL -->

                    <div class="contact-form-row">


                        <div class="contact-field">

                            <label for="fullname">

                                Full Name

                                <span class="contact-required">
                                    *
                                </span>

                            </label>


                            <div class="contact-input-wrap">

                                <i
                                    class="fa-solid fa-user contact-input-icon"
                                ></i>


                                <input
                                    type="text"
                                    id="fullname"
                                    name="fullname"
                                    placeholder="Enter your full name"
                                    value="<?= e($fullname) ?>"
                                    maxlength="150"
                                    required
                                >

                            </div>

                        </div>


                        <div class="contact-field">

                            <label for="email">

                                Email Address

                                <span class="contact-required">
                                    *
                                </span>

                            </label>


                            <div class="contact-input-wrap">

                                <i
                                    class="fa-solid fa-envelope contact-input-icon"
                                ></i>


                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    placeholder="you@example.com"
                                    value="<?= e($email) ?>"
                                    maxlength="190"
                                    required
                                >

                            </div>

                        </div>

                    </div>


                    <!-- SUBJECT -->

                    <div class="contact-field">

                        <label for="subject">

                            Subject

                            <span class="contact-required">
                                *
                            </span>

                        </label>


                        <div class="contact-input-wrap">

                            <i
                                class="fa-solid fa-heading contact-input-icon"
                            ></i>


                            <input
                                type="text"
                                id="subject"
                                name="subject"
                                placeholder="What would you like to tell us about?"
                                value="<?= e($subject) ?>"
                                maxlength="255"
                                required
                            >

                        </div>

                    </div>


                    <!-- MESSAGE -->

                    <div class="contact-field">

                        <label for="message">

                            Your Message

                            <span class="contact-required">
                                *
                            </span>

                        </label>


                        <div class="contact-input-wrap">

                            <i
                                class="fa-solid fa-message contact-input-icon contact-textarea-icon"
                            ></i>


                            <textarea
                                id="message"
                                name="message"
                                placeholder="Write your message here..."
                                maxlength="10000"
                                required
                            ><?= e($message) ?></textarea>

                        </div>


                        <div class="contact-message-meta">

                            <span>
                                Please provide as much detail as possible.
                            </span>


                            <span>

                                <strong id="message-count">
                                    0
                                </strong>

                                / 10000

                            </span>

                        </div>

                    </div>


                    <!-- SUBMIT -->

                    <div class="contact-submit-area">


                        <div class="contact-note">

                            <i class="fa-solid fa-lock"></i>

                            <span>
                                Your message will be securely
                                delivered to the administration.
                            </span>

                        </div>


                        <button
                            type="submit"
                            class="contact-submit"
                        >

                            <i class="fa-solid fa-paper-plane"></i>

                            Send Message

                        </button>

                    </div>

                </form>

            </section>

        </div>

    </div>

</main>


<!-- ======================================================
     TANZANIA FLAG LOTTIE
====================================================== -->

<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {

        /* ================================================
           MESSAGE CHARACTER COUNT
        ================================================= */

        const messageInput =
            document.getElementById("message");

        const messageCount =
            document.getElementById("message-count");


        if (
            messageInput &&
            messageCount
        ) {

            function updateMessageCount() {

                messageCount.textContent =
                    messageInput.value.length;

            }


            updateMessageCount();


            messageInput.addEventListener(
                "input",
                updateMessageCount
            );

        }


        /* ================================================
           TANZANIA FLAG
        ================================================= */

        const flag =
            document.getElementById(
                "tanzania-flag"
            );


        if (!flag) {

            console.error(
                "Tanzania flag container not found."
            );

            return;

        }


        if (
            typeof lottie === "undefined"
        ) {

            console.error(
                "Lottie library failed to load."
            );

            return;

        }


        lottie.loadAnimation({

            container:
                flag,

            renderer:
                "svg",

            loop:
                true,

            autoplay:
                true,

            path:
                "assets/animations/Tanzania%20flag%20Lottie%20JSON%20animation.json"

        });

    }

);

</script>


</body>

</html>