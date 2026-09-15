<?php

require_once "includes/db.php";
require_once "includes/functions.php";
require_once "includes/auth.php";
require_user();


// ======================================================
// PAGE INFORMATION
// ======================================================

$page_title = "Start a Petition | Petition Platform";

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
        content="Learn how to start a petition on the Tanzania Petition Platform."
    >

    <title><?= e($page_title) ?></title>


    <!-- ==================================================
         MAIN CSS
    ================================================== -->

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >


    <!-- ==================================================
         FONT AWESOME
    ================================================== -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >


    <!-- ==================================================
         LOTTIE
    ================================================== -->

    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/bodymovin/5.12.2/lottie.min.js">
    </script>


    <style>

        /* ==================================================
           PAGE BACKGROUND
        ================================================== */

        body {

            background:
                radial-gradient(
                    circle at 15% 10%,
                    rgba(0, 107, 63, 0.08),
                    transparent 32%
                ),
                radial-gradient(
                    circle at 85% 20%,
                    rgba(252, 209, 22, 0.10),
                    transparent 28%
                ),
                #f4f7f6;

            min-height:
                100vh;

        }


        /* ==================================================
           NAVIGATION
        ================================================== */

        .homepage-navigation {

            position:
                relative;

            width:
                100%;

            min-height:
                72px;

            background:
                rgba(255, 255, 255, 0.82);

            backdrop-filter:
                blur(16px);

            -webkit-backdrop-filter:
                blur(16px);

            border-bottom:
                1px solid rgba(226, 232, 240, 0.85);

            box-shadow:
                0 4px 20px rgba(15, 23, 42, 0.04);

            display:
                flex;

            align-items:
                center;

            padding:
                0 20px;

            box-sizing:
                border-box;

            z-index:
                100;

        }


        /* ==================================================
           BRAND
        ================================================== */

        .homepage-brand {

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

            letter-spacing:
                -0.3px;

        }


        .homepage-brand a:hover {

            color:
                var(--primary-dark);

        }


        /* ==================================================
           CENTER TABS
        ================================================== */

        .homepage-tabs {

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
                24px;

            white-space:
                nowrap;

        }


        .homepage-tabs a {

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
                9px 12px;

            border-radius:
                8px;

            transition:
                all 0.2s ease;

        }


        .homepage-tabs a:hover {

            color:
                var(--primary);

            background:
                rgba(0, 107, 63, 0.06);

        }


        .homepage-tabs a[aria-current="page"] {

            color:
                var(--primary);

            font-weight:
                700;

            background:
                rgba(0, 107, 63, 0.07);

        }


        /* ==================================================
           TANZANIA FLAG
        ================================================== */

        .homepage-flag {

            position:
                absolute;

            right:
                18px;

            top:
                50%;

            transform:
                translateY(-50%);

            width:
                62px;

            height:
                62px;

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


        .homepage-flag svg {

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

        .start-petition-main {

            width:
                100%;

            max-width:
                1050px;

            margin:
                0 auto;

            padding:
                65px 25px 80px;

            box-sizing:
                border-box;

        }


        /* ==================================================
           PAGE HEADER
        ================================================== */

        .start-petition-header {

            text-align:
                center;

            max-width:
                780px;

            margin:
                0 auto 38px;

        }


        .start-petition-header .badge {

            display:
                inline-flex;

            align-items:
                center;

            gap:
                7px;

            margin-bottom:
                16px;

            padding:
                7px 13px;

            border-radius:
                999px;

            background:
                rgba(0, 107, 63, 0.08);

            border:
                1px solid rgba(0, 107, 63, 0.12);

            color:
                var(--primary);

        }


        .start-petition-header h1 {

            font-size:
                clamp(32px, 5vw, 46px);

            line-height:
                1.15;

            margin:
                0 0 15px;

            color:
                var(--dark);

            letter-spacing:
                -0.8px;

        }


        .start-petition-header p {

            font-size:
                16px;

            line-height:
                1.75;

            color:
                var(--text-muted);

            max-width:
                720px;

            margin:
                0 auto;

        }


        .start-petition-header strong {

            color:
                var(--text);

        }


        /* ==================================================
           MAIN GLASS CARD
        ================================================== */

        .petition-guidance-card {

            position:
                relative;

            background:
                rgba(255, 255, 255, 0.72);

            backdrop-filter:
                blur(18px);

            -webkit-backdrop-filter:
                blur(18px);

            border:
                1px solid rgba(255, 255, 255, 0.8);

            border-radius:
                20px;

            padding:
                38px;

            box-shadow:
                0 20px 55px rgba(15, 23, 42, 0.08);

            overflow:
                hidden;

        }


        .petition-guidance-card::before {

            content:
                "";

            position:
                absolute;

            top:
                0;

            left:
                0;

            width:
                100%;

            height:
                4px;

            background:
                linear-gradient(
                    90deg,
                    var(--primary),
                    var(--gold),
                    var(--primary)
                );

        }


        /* ==================================================
           GUIDANCE SECTIONS
        ================================================== */

        .petition-guidance-section {

            margin-bottom:
                32px;

            padding-bottom:
                32px;

            border-bottom:
                1px solid rgba(226, 232, 240, 0.75);

        }


        .petition-guidance-section:last-of-type {

            margin-bottom:
                0;

            padding-bottom:
                0;

            border-bottom:
                none;

        }


        .petition-guidance-section h2 {

            display:
                flex;

            align-items:
                center;

            gap:
                10px;

            font-size:
                22px;

            margin:
                0 0 13px;

            color:
                var(--dark);

        }


        .petition-guidance-section h2::before {

            content:
                "";

            width:
                5px;

            height:
                22px;

            border-radius:
                999px;

            background:
                var(--primary);

        }


        .petition-guidance-section p {

            color:
                var(--text);

            line-height:
                1.75;

            font-size:
                15px;

            margin:
                0;

        }


        /* ==================================================
           HOW IT WORKS LIST
        ================================================== */

        .petition-guidance-list {

            list-style:
                none;

            margin:
                0;

            padding:
                0;

            display:
                grid;

            gap:
                12px;

        }


        .petition-guidance-list li {

            position:
                relative;

            padding:
                14px 16px 14px 45px;

            background:
                rgba(248, 250, 252, 0.75);

            border:
                1px solid rgba(226, 232, 240, 0.75);

            border-radius:
                10px;

            color:
                var(--text);

            font-size:
                15px;

            line-height:
                1.6;

        }


        .petition-guidance-list li::before {

            content:
                "\f00c";

            font-family:
                "Font Awesome 6 Free";

            font-weight:
                900;

            position:
                absolute;

            left:
                15px;

            top:
                50%;

            transform:
                translateY(-50%);

            width:
                23px;

            height:
                23px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            border-radius:
                50%;

            background:
                rgba(0, 107, 63, 0.10);

            color:
                var(--primary);

            font-size:
                11px;

        }


        .petition-guidance-list li:last-child {

            margin-bottom:
                0;

        }


        /* ==================================================
           LINKS
        ================================================== */

        .petition-guidance-link {

            color:
                var(--primary);

            font-weight:
                700;

            text-decoration:
                none;

        }


        .petition-guidance-link:hover {

            color:
                var(--primary-dark);

            text-decoration:
                underline;

        }


        /* ==================================================
           SUPPORTER MESSAGE
        ================================================== */

        .petition-supporter-message {

            position:
                relative;

            display:
                flex;

            align-items:
                center;

            gap:
                14px;

            background:
                linear-gradient(
                    135deg,
                    rgba(0, 107, 63, 0.08),
                    rgba(252, 209, 22, 0.08)
                );

            border:
                1px solid rgba(0, 107, 63, 0.12);

            border-radius:
                13px;

            padding:
                20px 22px;

            color:
                var(--text);

            font-size:
                15px;

            line-height:
                1.65;

        }


        .petition-supporter-message::before {

            content:
                "\f0c0";

            font-family:
                "Font Awesome 6 Free";

            font-weight:
                900;

            flex:
                0 0 auto;

            width:
                42px;

            height:
                42px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            border-radius:
                12px;

            background:
                var(--primary);

            color:
                #ffffff;

            font-size:
                16px;

        }


        .petition-supporter-message strong {

            color:
                var(--primary);

        }


        /* ==================================================
           START PETITION ACTION
        ================================================== */

        .start-petition-action {

            margin-top:
                34px;

            padding-top:
                32px;

            border-top:
                1px solid rgba(226, 232, 240, 0.8);

            text-align:
                center;

            display:
                flex;

            flex-direction:
                column;

            align-items:
                center;

        }


        .start-petition-action p {

            color:
                var(--text-muted);

            font-size:
                15px;

            margin:
                0 0 16px;

        }


        /* ==================================================
           START BUTTON
        ================================================== */

        .start-petition-button {

            display:
                inline-flex;

            align-items:
                center;

            justify-content:
                center;

            gap:
                9px;

            min-width:
                210px;

            padding:
                14px 25px;

            background:
                linear-gradient(
                    135deg,
                    var(--primary),
                    var(--primary-dark)
                );

            color:
                #ffffff;

            text-decoration:
                none;

            border:
                1px solid rgba(255, 255, 255, 0.15);

            border-radius:
                9px;

            font-size:
                15px;

            font-weight:
                800;

            cursor:
                pointer;

            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease,
                background 0.2s ease;

            box-shadow:
                0 8px 22px rgba(0, 107, 63, 0.20);

        }


        .start-petition-button::after {

            content:
                "\f061";

            font-family:
                "Font Awesome 6 Free";

            font-weight:
                900;

            font-size:
                13px;

            transition:
                transform 0.2s ease;

        }


        .start-petition-button:hover {

            background:
                linear-gradient(
                    135deg,
                    var(--primary-dark),
                    var(--primary)
                );

            color:
                #ffffff;

            transform:
                translateY(-2px);

            box-shadow:
                0 12px 28px rgba(0, 107, 63, 0.27);

        }


        .start-petition-button:hover::after {

            transform:
                translateX(4px);

        }


        /* ==================================================
           CITIZENSHIP OVERLAY
        ================================================== */

        .citizenship-overlay {

            position:
                fixed;

            inset:
                0;

            background:
                rgba(15, 23, 42, 0.58);

            backdrop-filter:
                blur(6px);

            -webkit-backdrop-filter:
                blur(6px);

            display:
                none;

            align-items:
                center;

            justify-content:
                center;

            padding:
                20px;

            box-sizing:
                border-box;

            z-index:
                9999;

        }


        .citizenship-overlay.active {

            display:
                flex;

        }


        /* ==================================================
           CITIZENSHIP DIALOG
        ================================================== */

        .citizenship-dialog {

            width:
                100%;

            max-width:
                530px;

            background:
                rgba(255, 255, 255, 0.94);

            backdrop-filter:
                blur(20px);

            -webkit-backdrop-filter:
                blur(20px);

            border:
                1px solid rgba(255, 255, 255, 0.9);

            border-radius:
                20px;

            padding:
                38px;

            box-sizing:
                border-box;

            text-align:
                center;

            box-shadow:
                0 25px 80px rgba(0, 0, 0, 0.25);

            animation:
                citizenshipDialogIn
                0.22s ease;

        }


        @keyframes citizenshipDialogIn {

            from {

                opacity:
                    0;

                transform:
                    translateY(18px)
                    scale(0.97);

            }

            to {

                opacity:
                    1;

                transform:
                    translateY(0)
                    scale(1);

            }

        }


        .citizenship-dialog::before {

            content:
                "\f024";

            font-family:
                "Font Awesome 6 Free";

            font-weight:
                900;

            width:
                58px;

            height:
                58px;

            margin:
                0 auto 17px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            border-radius:
                50%;

            background:
                rgba(0, 107, 63, 0.09);

            color:
                var(--primary);

            font-size:
                23px;

        }


        .citizenship-dialog h2 {

            margin:
                0 0 10px;

            color:
                var(--dark);

            font-size:
                26px;

        }


        .citizenship-dialog > p {

            margin:
                0 auto 27px;

            max-width:
                430px;

            color:
                var(--text-muted);

            font-size:
                15px;

            line-height:
                1.65;

        }


        /* ==================================================
           CITIZENSHIP BUTTONS
        ================================================== */

        .citizenship-buttons {

            display:
                grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap:
                13px;

            width:
                100%;

        }


        .citizenship-button {

            min-height:
                54px;

            border-radius:
                9px;

            padding:
                10px 14px;

            font-size:
                14px;

            font-weight:
                750;

            cursor:
                pointer;

            transition:
                all 0.2s ease;

        }


        .citizenship-yes {

            background:
                var(--primary);

            color:
                #ffffff;

            border:
                1px solid var(--primary);

        }


        .citizenship-yes:hover {

            background:
                var(--primary-dark);

            border-color:
                var(--primary-dark);

            transform:
                translateY(-1px);

            box-shadow:
                0 7px 18px rgba(0, 107, 63, 0.18);

        }


        .citizenship-no {

            background:
                #ffffff;

            color:
                var(--dark);

            border:
                1px solid var(--border);

        }


        .citizenship-no:hover {

            border-color:
                #dc2626;

            color:
                #dc2626;

            background:
                #fffafa;

        }


        /* ==================================================
           NOT ELIGIBLE MESSAGE
        ================================================== */

        .citizenship-not-eligible {

            display:
                none;

            margin-top:
                22px;

            padding:
                18px;

            background:
                #fef2f2;

            border:
                1px solid #fecaca;

            border-radius:
                11px;

            text-align:
                center;

        }


        .citizenship-not-eligible.active {

            display:
                block;

            animation:
                citizenshipMessageIn
                0.2s ease;

        }


        @keyframes citizenshipMessageIn {

            from {

                opacity:
                    0;

                transform:
                    translateY(6px);

            }

            to {

                opacity:
                    1;

                transform:
                    translateY(0);

            }

        }


        .citizenship-not-eligible h3 {

            color:
                #991b1b;

            font-size:
                18px;

            margin:
                0 0 8px;

        }


        .citizenship-not-eligible p {

            color:
                #7f1d1d;

            font-size:
                14px;

            line-height:
                1.6;

            margin:
                0;

        }


        /* ==================================================
           FOOTER
        ================================================== */

        .dashboard-footer {

            background:
                var(--dark);

            color:
                #ffffff;

            margin-top:
                0;

            padding:
                40px 25px;

            border-top:
                4px solid var(--gold);

        }


        .dashboard-footer-inner {

            text-align:
                center;

        }


        .dashboard-footer h3 {

            color:
                #ffffff;

            margin-bottom:
                8px;

        }


        .dashboard-footer p {

            color:
                #cbd5e1;

        }


        .dashboard-footer .copyright {

            color:
                #94a3b8;

            font-size:
                14px;

            margin-top:
                20px;

        }


        /* ==================================================
           MOBILE
        ================================================== */

        @media (max-width: 900px) {

            .homepage-tabs {

                gap:
                    12px;

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
                    7px;

                padding:
                    5px 45px 5px 0;

                box-sizing:
                    border-box;

            }


            .homepage-tabs a {

                font-size:
                    14px;

                padding:
                    7px 9px;

            }


            .homepage-flag {

                right:
                    8px;

                top:
                    50%;

                width:
                    48px;

                height:
                    48px;

            }


            .start-petition-main {

                padding:
                    40px 16px 55px;

            }


            .start-petition-header {

                margin-bottom:
                    28px;

            }


            .start-petition-header h1 {

                font-size:
                    31px;

            }


            .start-petition-header p {

                font-size:
                    15px;

            }


            .petition-guidance-card {

                padding:
                    28px 19px;

                border-radius:
                    16px;

            }


            .petition-guidance-section {

                margin-bottom:
                    27px;

                padding-bottom:
                    27px;

            }


            .petition-guidance-section h2 {

                font-size:
                    20px;

            }


            .petition-supporter-message {

                align-items:
                    flex-start;

                padding:
                    17px;

            }


            .petition-supporter-message::before {

                width:
                    38px;

                height:
                    38px;

            }


            .citizenship-dialog {

                padding:
                    30px 20px;

                border-radius:
                    17px;

            }


            .citizenship-buttons {

                grid-template-columns:
                    1fr;

            }

        }


        @media (max-width: 480px) {

            .homepage-tabs {

                gap:
                    5px;

            }


            .homepage-tabs a {

                font-size:
                    13px;

                padding:
                    6px 7px;

            }


            .start-petition-main {

                padding-top:
                    30px;

            }


            .petition-guidance-card {

                padding:
                    25px 16px;

            }


            .petition-guidance-list li {

                padding:
                    13px 13px 13px 43px;

            }


            .start-petition-button {

                width:
                    100%;

                max-width:
                    300px;

            }

        }

    </style>

</head>


<body>


<!-- ======================================================
     NAVIGATION
====================================================== -->

<nav class="homepage-navigation">


    <!-- BRAND -->

    <div class="homepage-brand">

        <a href="index.php">

            Petition Platform

        </a>

    </div>


    <!-- TABS -->

    <div class="homepage-tabs">

        <a href="index.php">

            Home

        </a>


        <a href="petitions.php">

            Petitions

        </a>


        <a href="dashboard.php">

            Dashboard

        </a>


        <a
            href="start_petition.php"
            aria-current="page"
        >

            Start Petition

        </a>


        <a href="logout.php">

            Logout

        </a>

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
     MAIN
====================================================== -->

<main class="start-petition-main">


    <!-- ==================================================
         HEADER
    ================================================== -->

    <section class="start-petition-header">

        <span class="badge">

            <i class="fa-solid fa-bullhorn"></i>

            Start a Petition

        </span>


        <h1>

            Make your voice heard.

        </h1>


        <p>

            Petitions give citizens a way to ask the
            <strong>United Republic of Tanzania</strong>
            or <strong>Parliament</strong> to take action
            on issues that matter to them.

        </p>

    </section>


    <!-- ==================================================
         GUIDANCE CARD
    ================================================== -->

    <section class="petition-guidance-card">


        <!-- BEFORE YOU START -->

        <div class="petition-guidance-section">

            <h2>

                Before you start

            </h2>


            <p>

                Make sure you understand what is required
                before submitting your petition. Your petition
                should clearly explain the action you want the
                Government of Tanzania or Parliament to take.

            </p>

        </div>


        <!-- HOW IT WORKS -->

        <div class="petition-guidance-section">

            <h2>

                How it works

            </h2>


            <ul class="petition-guidance-list">

                <li>

                    Write a clear and specific
                    <strong>petition action</strong>.

                </li>


                <li>

                    Provide details about
                    <strong>what you want to happen</strong>
                    and why.

                </li>


                <li>

                    Check that your petition meets the

                    <a
                        href="#petition-standards"
                        class="petition-guidance-link"
                    >

                        petition standards

                    </a>.

                </li>

            </ul>

        </div>


        <!-- PETITION STANDARDS -->

        <div
            class="petition-guidance-section"
            id="petition-standards"
        >

            <h2>

                Petition standards

            </h2>


            <p>

                Your petition should be clear, specific,
                respectful, and focused on an action that
                the United Republic of Tanzania or Parliament
                can consider.

            </p>

        </div>


        <!-- SUPPORTERS -->

        <div class="petition-guidance-section">

            <div class="petition-supporter-message">

                <span>

                    After you've submitted your petition,
                    you'll need to share it with at least
                    <strong>five supporters</strong>.

                </span>

            </div>

        </div>


        <!-- ==================================================
             START PETITION
        ================================================== -->

        <div class="start-petition-action">

            <p>

                Ready to create your petition?

            </p>


            <button
                type="button"
                class="start-petition-button"
                id="open-citizenship"
            >

                Start Petition

            </button>

        </div>


    </section>

</main>


<!-- ======================================================
     CITIZENSHIP DIALOG
====================================================== -->

<div
    class="citizenship-overlay"
    id="citizenship-overlay"
    role="dialog"
    aria-modal="true"
    aria-labelledby="citizenship-title"
>


    <div class="citizenship-dialog">


        <h2 id="citizenship-title">

            Citizenship Confirmation

        </h2>


        <p>

            Before creating a petition, please confirm
            that you are a Tanzanian citizen.

        </p>


        <div class="citizenship-buttons">


            <!-- YES -->

            <button
                type="button"
                class="citizenship-button citizenship-yes"
                id="citizenship-yes"
            >

                <i class="fa-solid fa-check"></i>

                Yes, I am a Tanzanian citizen

            </button>


            <!-- NO -->

            <button
                type="button"
                class="citizenship-button citizenship-no"
                id="citizenship-no"
            >

                <i class="fa-solid fa-xmark"></i>

                No, I am not a Tanzanian citizen

            </button>


        </div>


        <!-- NOT ELIGIBLE MESSAGE -->

        <div
            class="citizenship-not-eligible"
            id="citizenship-not-eligible"
        >

            <h3>

                You cannot create a petition

            </h3>


            <p>

                This petition platform is currently
                available for Tanzanian citizens only.
                You must be a Tanzanian citizen to
                create and submit a petition.

            </p>

        </div>


    </div>

</div>


<!-- ======================================================
     FOOTER
====================================================== -->

<footer class="dashboard-footer">


    <div class="container dashboard-footer-inner">


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


        /* ==================================================
           FLAG
        ================================================== */

        const flag =
            document.getElementById(
                "tanzania-flag"
            );


        if (
            flag &&
            typeof lottie !== "undefined"
        ) {

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


        /* ==================================================
           CITIZENSHIP ELEMENTS
        ================================================== */

        const openButton =
            document.getElementById(
                "open-citizenship"
            );


        const overlay =
            document.getElementById(
                "citizenship-overlay"
            );


        const yesButton =
            document.getElementById(
                "citizenship-yes"
            );


        const noButton =
            document.getElementById(
                "citizenship-no"
            );


        const notEligible =
            document.getElementById(
                "citizenship-not-eligible"
            );


        /* ==================================================
           OPEN DIALOG
        ================================================== */

        if (openButton) {

            openButton.addEventListener(
                "click",
                function () {

                    overlay.classList.add(
                        "active"
                    );

                    notEligible.classList.remove(
                        "active"
                    );

                }
            );

        }


        /* ==================================================
           YES
           GO TO CREATE PETITION
        ================================================== */

        if (yesButton) {

            yesButton.addEventListener(
                "click",
                function () {

                    window.location.href =
                        "create_petition.php";

                }
            );

        }


        /* ==================================================
           NO
           SHOW MESSAGE
        ================================================== */

        if (noButton) {

            noButton.addEventListener(
                "click",
                function () {

                    notEligible.classList.add(
                        "active"
                    );

                }
            );

        }


        /* ==================================================
           CLICK OUTSIDE DIALOG
        ================================================== */

        if (overlay) {

            overlay.addEventListener(
                "click",
                function (event) {

                    if (
                        event.target === overlay
                    ) {

                        overlay.classList.remove(
                            "active"
                        );

                    }

                }
            );

        }


        /* ==================================================
           ESCAPE KEY
        ================================================== */

        document.addEventListener(
            "keydown",
            function (event) {

                if (
                    event.key === "Escape" &&
                    overlay &&
                    overlay.classList.contains("active")
                ) {

                    overlay.classList.remove(
                        "active"
                    );

                }

            }
        );

    }

);

</script>


</body>

</html>