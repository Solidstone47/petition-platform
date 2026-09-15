<?php

require_once "includes/db.php";
require_once "includes/functions.php";
require_once "includes/auth.php";


// ======================================================
// REQUIRE LOGIN
// ======================================================

if (!is_user_logged_in()) {

    set_flash(
        "error",
        "Please log in to sign this petition."
    );

    redirect("login.php");
}


// ======================================================
// GET PETITION ID
// ======================================================

$petition_id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;


if ($petition_id < 1) {

    http_response_code(404);

    exit("Petition not found.");

}


// ======================================================
// GET ACTIVE PETITION
// ======================================================

$stmt = $conn->prepare(
    "SELECT
        id,
        title,
        description,
        goal
     FROM petitions
     WHERE id = ?
     AND status = 'active'
     LIMIT 1"
);

$stmt->bind_param("i", $petition_id);

$stmt->execute();

$result = $stmt->get_result();

$petition = $result->fetch_assoc();

$stmt->close();


if (!$petition) {

    http_response_code(404);

    exit("Petition not found.");

}


// ======================================================
// LOGGED-IN USER
// ======================================================

$user_id = (int) $_SESSION['user_id'];


// ======================================================
// CHECK EXISTING SIGNATURE
// ======================================================

$stmt = $conn->prepare(
    "SELECT id
     FROM signatures
     WHERE petition_id = ?
     AND user_id = ?
     LIMIT 1"
);

$stmt->bind_param(
    "ii",
    $petition_id,
    $user_id
);

$stmt->execute();

$result = $stmt->get_result();

$already_signed = $result->num_rows > 0;

$stmt->close();


// ======================================================
// PROCESS SIGNATURE
// ======================================================

if (is_post()) {

    verify_csrf();


    if ($already_signed) {

        set_flash(
            "error",
            "You have already signed this petition."
        );

        redirect(
            "petition.php?id=" . $petition_id
        );
    }


    $stmt = $conn->prepare(
        "INSERT INTO signatures
        (petition_id, user_id)
        VALUES (?, ?)"
    );

    $stmt->bind_param(
        "ii",
        $petition_id,
        $user_id
    );


    if ($stmt->execute()) {

        set_flash(
            "success",
            "Thank you for signing this petition."
        );

    } else {

        set_flash(
            "error",
            "Unable to record your signature."
        );
    }


    $stmt->close();


    redirect(
        "petition.php?id=" . $petition_id
    );
}


// ======================================================
// FLASH MESSAGE
// ======================================================

$flash = get_flash();


// ======================================================
// PETITION DATA
// ======================================================

$petition_title = e($petition['title']);
$petition_description = nl2br(
    e($petition['description'])
);

$petition_goal = (int) $petition['goal'];

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
        content="Sign this petition and add your voice to the cause."
    >

    <title>
        Sign Petition | Petition Platform
    </title>


    <!-- ==================================================
         MAIN CSS
    ================================================== -->

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >


    <!-- ==================================================
         LOTTIE
    ================================================== -->

    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/bodymovin/5.12.2/lottie.min.js"
    ></script>


    <!-- ==================================================
         SIGN PETITION PAGE STYLES
    ================================================== -->

    <style>

        /* ==================================================
           PAGE
        ================================================== */

        body {
            background:
                linear-gradient(
                    135deg,
                    #f8fafc 0%,
                    #eef8f3 50%,
                    #f8fafc 100%
                );

            min-height:
                100vh;
        }


        /* ==================================================
           NAVIGATION
        ================================================== */

        .sign-navigation {

            position:
                relative;

            width:
                100%;

            min-height:
                72px;

            background:
                rgba(255, 255, 255, 0.78);

            backdrop-filter:
                blur(18px);

            -webkit-backdrop-filter:
                blur(18px);

            border-bottom:
                1px solid rgba(255, 255, 255, 0.65);

            box-shadow:
                0 8px 30px rgba(15, 23, 42, 0.06);

            display:
                flex;

            align-items:
                center;

            padding:
                0 25px;

            box-sizing:
                border-box;

            z-index:
                100;
        }


        /* ==================================================
           BRAND
        ================================================== */

        .sign-brand {

            position:
                absolute;

            left:
                25px;

            top:
                50%;

            transform:
                translateY(-50%);

            z-index:
                5;
        }


        .sign-brand a {

            color:
                var(--primary);

            text-decoration:
                none;

            font-size:
                20px;

            font-weight:
                800;

            letter-spacing:
                -0.3px;

            white-space:
                nowrap;
        }


        .sign-brand a:hover {
            color:
                var(--primary-dark);
        }


        /* ==================================================
           NAVIGATION TABS
        ================================================== */

        .sign-tabs {

            position:
                absolute;

            left:
                50%;

            top:
                50%;

            transform:
                translate(-50%, -50%);

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            gap:
                28px;

            white-space:
                nowrap;
        }


        .sign-tabs a {

            position:
                relative;

            display:
                inline-flex;

            align-items:
                center;

            justify-content:
                center;

            color:
                var(--text);

            text-decoration:
                none;

            font-size:
                15px;

            font-weight:
                600;

            padding:
                9px 3px;

            transition:
                color 0.2s ease;
        }


        .sign-tabs a::after {

            content:
                "";

            position:
                absolute;

            left:
                50%;

            bottom:
                1px;

            width:
                0;

            height:
                2px;

            background:
                var(--primary);

            transform:
                translateX(-50%);

            transition:
                width 0.2s ease;
        }


        .sign-tabs a:hover {
            color:
                var(--primary);
        }


        .sign-tabs a:hover::after {
            width:
                70%;
        }


        .sign-tabs a.active {
            color:
                var(--primary);
        }


        .sign-tabs a.active::after {
            width:
                70%;
        }


        /* ==================================================
           TANZANIA FLAG
        ================================================== */

        .sign-flag {

            position:
                absolute;

            right:
                18px;

            top:
                50%;

            transform:
                translateY(-50%);

            width:
                58px;

            height:
                58px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            overflow:
                hidden;

            z-index:
                10;
        }


        .sign-flag svg {

            width:
                100%;

            height:
                100%;

            display:
                block;
        }


        /* ==================================================
           MAIN
        ================================================== */

        .sign-page-main {

            width:
                100%;

            max-width:
                980px;

            margin:
                45px auto 75px;

            padding:
                0 25px;

            box-sizing:
                border-box;
        }


        /* ==================================================
           BACK LINK
        ================================================== */

        .sign-back {

            display:
                inline-flex;

            align-items:
                center;

            gap:
                7px;

            color:
                var(--text-muted);

            text-decoration:
                none;

            font-size:
                14px;

            font-weight:
                600;

            margin-bottom:
                25px;

            transition:
                color 0.2s ease,
                transform 0.2s ease;
        }


        .sign-back:hover {

            color:
                var(--primary);

            transform:
                translateX(-3px);
        }


        /* ==================================================
           FLASH MESSAGE
        ================================================== */

        .sign-flash {

            display:
                flex;

            align-items:
                center;

            gap:
                12px;

            padding:
                15px 18px;

            margin-bottom:
                22px;

            border-radius:
                14px;

            background:
                rgba(255, 255, 255, 0.72);

            backdrop-filter:
                blur(14px);

            -webkit-backdrop-filter:
                blur(14px);

            border:
                1px solid rgba(255, 255, 255, 0.75);

            box-shadow:
                0 8px 25px rgba(15, 23, 42, 0.06);

            font-size:
                14px;
        }


        .sign-flash p {
            margin:
                0;
        }


        /* ==================================================
           HERO
        ================================================== */

        .sign-hero {

            text-align:
                center;

            margin-bottom:
                32px;
        }


        .sign-hero-icon {

            width:
                68px;

            height:
                68px;

            margin:
                0 auto 18px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            border-radius:
                22px;

            background:
                linear-gradient(
                    135deg,
                    rgba(0, 107, 63, 0.14),
                    rgba(255, 193, 7, 0.18)
                );

            border:
                1px solid rgba(0, 107, 63, 0.12);

            color:
                var(--primary);

            font-size:
                30px;

            font-weight:
                800;

            box-shadow:
                0 12px 30px rgba(0, 107, 63, 0.10);
        }


        .sign-hero h1 {

            margin:
                0 0 12px;

            color:
                var(--dark);

            font-size:
                clamp(32px, 5vw, 46px);

            line-height:
                1.15;

            letter-spacing:
                -1px;
        }


        .sign-hero p {

            max-width:
                650px;

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
           MAIN GLASS CARD
        ================================================== */

        .sign-card {

            position:
                relative;

            overflow:
                hidden;

            background:
                rgba(255, 255, 255, 0.70);

            backdrop-filter:
                blur(22px);

            -webkit-backdrop-filter:
                blur(22px);

            border:
                1px solid rgba(255, 255, 255, 0.82);

            border-radius:
                24px;

            padding:
                34px;

            box-shadow:
                0 24px 70px rgba(15, 23, 42, 0.10);
        }


        .sign-card::before {

            content:
                "";

            position:
                absolute;

            top:
                -100px;

            right:
                -100px;

            width:
                250px;

            height:
                250px;

            background:
                rgba(0, 107, 63, 0.07);

            border-radius:
                50%;

            pointer-events:
                none;
        }


        .sign-card::after {

            content:
                "";

            position:
                absolute;

            bottom:
                -130px;

            left:
                -100px;

            width:
                280px;

            height:
                280px;

            background:
                rgba(255, 193, 7, 0.07);

            border-radius:
                50%;

            pointer-events:
                none;
        }


        /* ==================================================
           PETITION HEADER
        ================================================== */

        .sign-petition-header {

            position:
                relative;

            z-index:
                2;

            display:
                flex;

            align-items:
                flex-start;

            justify-content:
                space-between;

            gap:
                25px;

            margin-bottom:
                28px;
        }


        .sign-status {

            display:
                inline-flex;

            align-items:
                center;

            gap:
                7px;

            flex-shrink:
                0;

            padding:
                7px 12px;

            border-radius:
                999px;

            background:
                rgba(0, 107, 63, 0.10);

            color:
                var(--primary);

            border:
                1px solid rgba(0, 107, 63, 0.12);

            font-size:
                12px;

            font-weight:
                800;

            text-transform:
                uppercase;

            letter-spacing:
                0.5px;
        }


        .sign-status-dot {

            width:
                7px;

            height:
                7px;

            border-radius:
                50%;

            background:
                var(--primary);

            box-shadow:
                0 0 0 4px rgba(0, 107, 63, 0.10);
        }


        .sign-petition-header h2 {

            margin:
                0;

            color:
                var(--dark);

            font-size:
                clamp(24px, 4vw, 32px);

            line-height:
                1.3;

            letter-spacing:
                -0.4px;
        }


        /* ==================================================
           PETITION DESCRIPTION
        ================================================== */

        .sign-description {

            position:
                relative;

            z-index:
                2;

            background:
                rgba(248, 250, 252, 0.68);

            border:
                1px solid rgba(226, 232, 240, 0.80);

            border-radius:
                18px;

            padding:
                24px;

            margin-bottom:
                28px;
        }


        .sign-description p {

            margin:
                0;

            color:
                var(--text);

            font-size:
                15px;

            line-height:
                1.85;
        }


        /* ==================================================
           GOAL PANEL
        ================================================== */

        .sign-goal-panel {

            position:
                relative;

            z-index:
                2;

            display:
                flex;

            align-items:
                center;

            justify-content:
                space-between;

            gap:
                20px;

            padding:
                22px 24px;

            border-radius:
                18px;

            background:
                linear-gradient(
                    135deg,
                    rgba(0, 107, 63, 0.08),
                    rgba(255, 255, 255, 0.65)
                );

            border:
                1px solid rgba(0, 107, 63, 0.10);

            margin-bottom:
                28px;
        }


        .sign-goal-label {

            color:
                var(--text-muted);

            font-size:
                13px;

            font-weight:
                700;

            text-transform:
                uppercase;

            letter-spacing:
                0.6px;

            margin-bottom:
                5px;
        }


        .sign-goal-number {

            color:
                var(--dark);

            font-size:
                24px;

            font-weight:
                800;
        }


        .sign-goal-number span {

            font-size:
                13px;

            font-weight:
                600;

            color:
                var(--text-muted);
        }


        .sign-goal-icon {

            width:
                46px;

            height:
                46px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            border-radius:
                14px;

            background:
                rgba(0, 107, 63, 0.10);

            color:
                var(--primary);

            font-size:
                21px;

            font-weight:
                800;
        }


        /* ==================================================
           ALREADY SIGNED
        ================================================== */

        .sign-already {

            position:
                relative;

            z-index:
                2;

            text-align:
                center;

            padding:
                30px 25px;

            border-radius:
                18px;

            background:
                rgba(0, 107, 63, 0.07);

            border:
                1px solid rgba(0, 107, 63, 0.14);

            margin-bottom:
                22px;
        }


        .sign-already-icon {

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

            margin:
                0 auto 15px;

            border-radius:
                50%;

            background:
                var(--primary);

            color:
                #ffffff;

            font-size:
                24px;

            font-weight:
                800;

            box-shadow:
                0 8px 22px rgba(0, 107, 63, 0.20);
        }


        .sign-already h3 {

            margin:
                0 0 7px;

            color:
                var(--dark);

            font-size:
                19px;
        }


        .sign-already p {

            margin:
                0;

            color:
                var(--text-muted);

            font-size:
                14px;

            line-height:
                1.6;
        }


        /* ==================================================
           CONFIRMATION NOTICE
        ================================================== */

        .sign-confirmation {

            position:
                relative;

            z-index:
                2;

            display:
                flex;

            align-items:
                flex-start;

            gap:
                13px;

            padding:
                20px;

            border-radius:
                16px;

            background:
                rgba(255, 193, 7, 0.10);

            border:
                1px solid rgba(245, 158, 11, 0.20);

            margin-bottom:
                22px;
        }


        .sign-confirmation-icon {

            flex-shrink:
                0;

            width:
                34px;

            height:
                34px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            border-radius:
                10px;

            background:
                rgba(245, 158, 11, 0.14);

            color:
                #92400e;

            font-size:
                16px;

            font-weight:
                800;
        }


        .sign-confirmation p {

            margin:
                0;

            color:
                #78350f;

            font-size:
                14px;

            line-height:
                1.7;
        }


        /* ==================================================
           ACTION AREA
        ================================================== */

        .sign-actions {

            position:
                relative;

            z-index:
                2;

            display:
                flex;

            align-items:
                center;

            justify-content:
                space-between;

            gap:
                15px;

            padding-top:
                24px;

            border-top:
                1px solid rgba(226, 232, 240, 0.85);
        }


        .sign-cancel {

            display:
                inline-flex;

            align-items:
                center;

            justify-content:
                center;

            min-height:
                50px;

            padding:
                0 22px;

            border-radius:
                12px;

            background:
                rgba(255, 255, 255, 0.70);

            border:
                1px solid var(--border);

            color:
                var(--text);

            text-decoration:
                none;

            font-size:
                14px;

            font-weight:
                700;

            transition:
                all 0.2s ease;
        }


        .sign-cancel:hover {

            border-color:
                var(--primary);

            color:
                var(--primary);

            background:
                #ffffff;

            transform:
                translateY(-1px);
        }


        .sign-submit {

            position:
                relative;

            display:
                inline-flex;

            align-items:
                center;

            justify-content:
                center;

            min-height:
                52px;

            min-width:
                230px;

            padding:
                0 26px;

            border:
                none;

            border-radius:
                13px;

            background:
                linear-gradient(
                    135deg,
                    var(--primary),
                    var(--primary-dark)
                );

            color:
                #ffffff;

            font-size:
                15px;

            font-weight:
                800;

            cursor:
                pointer;

            box-shadow:
                0 10px 25px rgba(0, 107, 63, 0.20);

            transition:
                all 0.2s ease;
        }


        .sign-submit:hover {

            transform:
                translateY(-2px);

            box-shadow:
                0 14px 30px rgba(0, 107, 63, 0.28);
        }


        .sign-submit:active {

            transform:
                translateY(0);
        }


        /* ==================================================
           FOOTER
        ================================================== */

        .sign-footer {

            background:
                rgba(15, 23, 42, 0.96);

            color:
                #ffffff;

            margin-top:
                40px;

            padding:
                40px 25px;

            border-top:
                4px solid var(--gold);
        }


        .sign-footer-inner {

            max-width:
                1100px;

            margin:
                0 auto;

            text-align:
                center;
        }


        .sign-footer h3 {

            color:
                #ffffff;

            margin-bottom:
                8px;
        }


        .sign-footer p {

            color:
                #cbd5e1;

            margin:
                0;
        }


        .sign-footer .copyright {

            color:
                #94a3b8;

            font-size:
                13px;

            margin-top:
                20px;
        }


        /* ==================================================
           MOBILE
        ================================================== */

        @media (max-width: 800px) {

            .sign-tabs {
                gap:
                    15px;
            }

            .sign-petition-header {
                flex-direction:
                    column;
            }

            .sign-status {
                align-self:
                    flex-start;
            }
        }


        @media (max-width: 700px) {

            .sign-navigation {

                min-height:
                    125px;

                padding:
                    10px 15px;

                flex-direction:
                    column;

                justify-content:
                    center;
            }


            .sign-brand {

                position:
                    static;

                transform:
                    none;

                width:
                    100%;

                text-align:
                    center;

                padding-top:
                    4px;
            }


            .sign-tabs {

                position:
                    static;

                transform:
                    none;

                width:
                    100%;

                flex-wrap:
                    wrap;

                gap:
                    9px;

                padding:
                    6px 45px 4px 0;

                box-sizing:
                    border-box;
            }


            .sign-flag {

                right:
                    8px;

                width:
                    48px;

                height:
                    48px;
            }


            .sign-page-main {

                margin:
                    30px auto 55px;

                padding:
                    0 16px;
            }


            .sign-card {

                padding:
                    24px 18px;

                border-radius:
                    20px;
            }


            .sign-hero h1 {
                font-size:
                    31px;
            }


            .sign-hero p {
                font-size:
                    15px;
            }


            .sign-description {
                padding:
                    19px;
            }


            .sign-goal-panel {
                padding:
                    18px;
            }


            .sign-actions {

                flex-direction:
                    column-reverse;

                align-items:
                    stretch;
            }


            .sign-submit,
            .sign-cancel {

                width:
                    100%;

                box-sizing:
                    border-box;
            }

        }


        @media (max-width: 480px) {

            .sign-tabs {
                gap:
                    7px;
            }


            .sign-tabs a {
                font-size:
                    13px;
            }


            .sign-back {
                margin-bottom:
                    20px;
            }


            .sign-hero-icon {
                width:
                    60px;

                height:
                    60px;

                border-radius:
                    18px;
            }


            .sign-petition-header h2 {
                font-size:
                    23px;
            }


            .sign-goal-number {
                font-size:
                    21px;
            }

        }

    </style>

</head>


<body>


<!-- ======================================================
     NAVIGATION
====================================================== -->

<nav class="sign-navigation">


    <!-- BRAND -->

    <div class="sign-brand">

        <a href="index.php">
            Petition Platform
        </a>

    </div>


    <!-- TABS -->

    <div class="sign-tabs">

        <a href="index.php">
            Home
        </a>


        <a
            href="petitions.php"
            class="active"
            aria-current="page"
        >
            Petitions
        </a>


        <a href="dashboard.php">
            Dashboard
        </a>


        <a href="logout.php">
            Logout
        </a>

    </div>


    <!-- TANZANIA FLAG -->

    <div
        id="tanzania-flag"
        class="sign-flag"
        aria-label="Tanzania Flag"
        title="Tanzania"
    ></div>


</nav>


<!-- ======================================================
     MAIN
====================================================== -->

<main class="sign-page-main">


    <!-- ==================================================
         FLASH MESSAGE
    ================================================== -->

    <?php if ($flash): ?>

        <div
            class="sign-flash"
            role="alert"
        >

            <span>
                ✓
            </span>

            <p>
                <?= e($flash['message']) ?>
            </p>

        </div>

    <?php endif; ?>


    <!-- ==================================================
         BACK
    ================================================== -->

    <a
        href="petition.php?id=<?= e($petition_id) ?>"
        class="sign-back"
    >
        ← Back to Petition
    </a>


    <!-- ==================================================
         HERO
    ================================================== -->

    <section class="sign-hero">

        <div class="sign-hero-icon">
            ✓
        </div>


        <h1>
            Sign This Petition
        </h1>


        <p>
            Your signature can help bring attention to this
            important cause and show decision-makers that
            citizens care about this issue.
        </p>

    </section>


    <!-- ==================================================
         GLASS CARD
    ================================================== -->

    <article class="sign-card">


        <!-- ==================================================
             PETITION HEADER
        ================================================== -->

        <div class="sign-petition-header">

            <div>

                <span class="sign-status">

                    <span class="sign-status-dot"></span>

                    Active Petition

                </span>


                <h2 style="margin-top: 14px;">

                    <?= $petition_title ?>

                </h2>

            </div>

        </div>


        <!-- ==================================================
             DESCRIPTION
        ================================================== -->

        <div class="sign-description">

            <p>
                <?= $petition_description ?>
            </p>

        </div>


        <!-- ==================================================
             GOAL
        ================================================== -->

        <div class="sign-goal-panel">

            <div>

                <div class="sign-goal-label">
                    Signature Goal
                </div>

                <div class="sign-goal-number">

                    <?= number_format($petition_goal) ?>

                    <span>
                        signatures
                    </span>

                </div>

            </div>


            <div class="sign-goal-icon">
                ✍
            </div>

        </div>


        <?php if ($already_signed): ?>


            <!-- ==================================================
                 ALREADY SIGNED
            ================================================== -->

            <div class="sign-already">

                <div class="sign-already-icon">
                    ✓
                </div>


                <h3>
                    You've already signed this petition.
                </h3>


                <p>
                    Thank you for adding your voice to this cause.
                    Your support has been recorded.
                </p>

            </div>


            <div class="sign-actions">

                <span></span>

                <a
                    href="petition.php?id=<?= e($petition_id) ?>"
                    class="sign-submit"
                >
                    Back to Petition
                </a>

            </div>


        <?php else: ?>


            <!-- ==================================================
                 CONFIRMATION
            ================================================== -->

            <div class="sign-confirmation">

                <div class="sign-confirmation-icon">
                    !
                </div>


                <p>

                    <strong>
                        Before you continue:
                    </strong>

                    By confirming below, you are adding your
                    signature and support to this petition.
                    Please make sure you understand what you
                    are supporting before submitting.

                </p>

            </div>


            <!-- ==================================================
                 ACTIONS
            ================================================== -->

            <div class="sign-actions">


                <a
                    href="petition.php?id=<?= e($petition_id) ?>"
                    class="sign-cancel"
                >
                    Cancel
                </a>


                <form
                    method="POST"
                    style="margin: 0;"
                >

                    <?= csrf_field() ?>


                    <button
                        type="submit"
                        class="sign-submit"
                    >
                        Confirm My Signature
                    </button>

                </form>

            </div>


        <?php endif; ?>


    </article>


</main>


<!-- ======================================================
     FOOTER
====================================================== -->

<footer class="sign-footer">

    <div class="sign-footer-inner">

        <h3>
            Petition Platform
        </h3>


        <p>
            Empowering communities to make their voices heard.
        </p>


        <p class="copyright">

            &copy;

            <?= date('Y') ?>

            Petition Platform.

            All rights reserved.

        </p>

    </div>

</footer>


<!-- ======================================================
     TANZANIA FLAG LOTTIE
====================================================== -->

<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {

        const flag =
            document.getElementById(
                "tanzania-flag"
            );


        if (
            !flag ||
            typeof lottie === "undefined"
        ) {
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