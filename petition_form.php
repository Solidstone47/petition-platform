<?php

require_once "includes/db.php";
require_once "includes/functions.php";
require_once "includes/auth.php";

require_user();


// ==================================================
// PAGE INFORMATION
// ==================================================

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

    <title>
        <?= e($page_title) ?>
    </title>


    <!-- MAIN CSS -->

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >


    <!-- LOTTIE -->

    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/bodymovin/5.12.2/lottie.min.js"
    ></script>


    <style>

        /* ==================================================
           PAGE NAVIGATION
           SAME STYLE AS DASHBOARD
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

            padding: 0 20px;

            box-sizing: border-box;

        }


        /* ==================================================
           BRAND
        ================================================== */

        .homepage-brand {

            position: absolute;

            left: 25px;

            top: 50%;

            transform:
                translateY(-50%);

            z-index: 5;

        }


        .homepage-brand a {

            color: var(--primary);

            text-decoration: none;

            font-size: 20px;

            font-weight: 800;

            white-space: nowrap;

        }


        .homepage-brand a:hover {

            color: var(--primary-dark);

        }


        /* ==================================================
           CENTER TABS
        ================================================== */

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

            color: var(--text);

            text-decoration: none;

            font-size: 15px;

            font-weight: 600;

            padding: 8px 2px;

            transition:
                color 0.2s ease,
                background 0.2s ease;

        }


        .homepage-tabs a:hover {

            color: var(--primary);

        }


        /* ==================================================
           FLAG
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

            width: 100%;

            height: 100%;

            display: block;

        }


        /* ==================================================
           MAIN CONTENT
        ================================================== */

        .start-petition-main {

            width: 100%;

            max-width: 900px;

            margin: 45px auto 70px;

            padding: 0 25px;

            box-sizing: border-box;

        }


        /* ==================================================
           PAGE HEADER
        ================================================== */

        .start-petition-header {

            margin-bottom: 30px;

        }


        .start-petition-header .badge {

            display: inline-block;

            margin-bottom: 12px;

        }


        .start-petition-header h1 {

            font-size:
                clamp(30px, 5vw, 42px);

            line-height: 1.2;

            margin-bottom: 14px;

            color: var(--dark);

        }


        .start-petition-header p {

            font-size: 16px;

            line-height: 1.7;

            color: var(--text-muted);

            max-width: 760px;

            margin-bottom: 0;

        }


        /* ==================================================
           INFORMATION CARD
        ================================================== */

        .petition-guidance-card {

            background: var(--surface);

            border:
                1px solid var(--border);

            border-radius: 12px;

            padding: 32px;

            box-shadow: var(--shadow);

        }


        /* ==================================================
           SECTION
        ================================================== */

        .petition-guidance-section {

            margin-bottom: 32px;

        }


        .petition-guidance-section:last-child {

            margin-bottom: 0;

        }


        .petition-guidance-section h2 {

            font-size: 23px;

            margin-bottom: 18px;

            color: var(--dark);

        }


        /* ==================================================
           HOW IT WORKS LIST
        ================================================== */

        .petition-guidance-list {

            margin: 0;

            padding-left: 25px;

        }


        .petition-guidance-list li {

            margin-bottom: 14px;

            color: var(--text);

            font-size: 15px;

            line-height: 1.6;

        }


        .petition-guidance-list li:last-child {

            margin-bottom: 0;

        }


        /* ==================================================
           STANDARDS LINK
        ================================================== */

        .petition-guidance-link {

            color: var(--primary);

            font-weight: 600;

            text-decoration: none;

        }


        .petition-guidance-link:hover {

            color: var(--primary-dark);

            text-decoration: underline;

        }


        /* ==================================================
           SUPPORTER MESSAGE
        ================================================== */

        .petition-supporter-message {

            background:
                var(--primary-light);

            border:
                1px solid rgba(
                    0,
                    107,
                    63,
                    0.15
                );

            border-radius: 10px;

            padding: 20px;

            color: var(--text);

            font-size: 15px;

            line-height: 1.7;

        }


        .petition-supporter-message strong {

            color: var(--primary);

        }


        /* ==================================================
           START BUTTON AREA
        ================================================== */

        .start-petition-action {

            margin-top: 30px;

            padding-top: 25px;

            border-top:
                1px solid var(--border);

            text-align: center;

        }


        .start-petition-action p {

            color: var(--text-muted);

            font-size: 14px;

            margin-bottom: 16px;

        }


        .start-petition-button {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            min-width: 190px;

            padding:
                13px 24px;

            background:
                var(--primary);

            color: #ffffff;

            text-decoration: none;

            border-radius: 8px;

            font-size: 15px;

            font-weight: 700;

            border: none;

            cursor: pointer;

            transition:
                background 0.2s ease,
                transform 0.2s ease,
                box-shadow 0.2s ease;

            box-shadow:
                0 4px 10px
                rgba(0, 107, 63, 0.15);

        }


        .start-petition-button:hover {

            background:
                var(--primary-dark);

            color: #ffffff;

            transform:
                translateY(-2px);

            box-shadow:
                0 7px 15px
                rgba(0, 107, 63, 0.20);

        }


        /* ==================================================
           FOOTER
        ================================================== */

        .dashboard-footer {

            background: var(--dark);

            color: #ffffff;

            margin-top: 50px;

            padding: 35px 25px;

            border-top:
                4px solid var(--gold);

        }


        .dashboard-footer-inner {

            text-align: center;

        }


        .dashboard-footer h3 {

            color: #ffffff;

        }


        .dashboard-footer p {

            color: #cbd5e1;

        }


        .dashboard-footer .copyright {

            color: #94a3b8;

            font-size: 14px;

            margin-top: 20px;

        }


        /* ==================================================
           MOBILE
        ================================================== */

        @media (max-width: 900px) {

            .homepage-tabs {

                gap: 15px;

            }

        }


        @media (max-width: 700px) {

            .homepage-navigation {

                min-height: 125px;

                padding:
                    10px 15px;

            }


            .homepage-brand {

                position: static;

                transform: none;

                width: 100%;

                text-align: center;

                padding-top: 5px;

            }


            .homepage-tabs {

                position: static;

                transform: none;

                width: 100%;

                flex-wrap: wrap;

                gap: 12px;

                padding:
                    5px 45px 5px 0;

                box-sizing: border-box;

            }


            .homepage-navigation {

                flex-direction: column;

                justify-content: center;

            }


            .homepage-flag {

                right: 8px;

                top: 50%;

                width: 48px;

                height: 48px;

            }


            .start-petition-main {

                margin:
                    30px auto 50px;

                padding:
                    0 16px;

            }


            .petition-guidance-card {

                padding: 22px 18px;

            }


            .start-petition-header h1 {

                font-size: 30px;

            }


            .petition-guidance-section h2 {

                font-size: 21px;

            }


            .start-petition-button {

                width: 100%;

                box-sizing: border-box;

            }

        }


        @media (max-width: 480px) {

            .homepage-tabs {

                gap: 8px;

            }


            .homepage-tabs a {

                font-size: 14px;

            }


            .start-petition-main {

                margin-top: 25px;

            }

        }

    </style>

</head>


<body>


<!-- ==================================================
     NAVIGATION
================================================== -->

<nav class="homepage-navigation">


    <!-- ==================================================
         BRAND
    ================================================== -->

    <div class="homepage-brand">

        <a href="index.php">

            Petition Platform

        </a>

    </div>


    <!-- ==================================================
         TABS
         START PETITION IS INTENTIONALLY NOT HERE
    ================================================== -->

    <div class="homepage-tabs">


        <a href="index.php">

            Home

        </a>


        <a href="petitions.php">

            Petitions

        </a>


        <a
            href="dashboard.php"
        >

            Dashboard

        </a>


        <a href="logout.php">

            Logout

        </a>


    </div>


    <!-- ==================================================
         TANZANIA FLAG
    ================================================== -->

    <div
        id="tanzania-flag"
        class="homepage-flag"
        aria-label="Tanzania Flag"
        title="Tanzania"
    ></div>


</nav>


<!-- ==================================================
     MAIN
================================================== -->

<main class="start-petition-main">


    <!-- ==================================================
         HEADER
    ================================================== -->

    <section class="start-petition-header">


        <span class="badge">

            Start a Petition

        </span>


        <h1>

            Start a petition

        </h1>


        <p>

            Petitions are a way to ask the
            <strong>
                United Republic of Tanzania
            </strong>
            or
            <strong>
                Parliament
            </strong>
            to take action on an issue that’s important
            to you.

        </p>


    </section>


    <!-- ==================================================
         GUIDANCE CARD
    ================================================== -->

    <section class="petition-guidance-card">


        <!-- ==================================================
             GUIDANCE
        ================================================== -->

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


        <!-- ==================================================
             HOW IT WORKS
        ================================================== -->

        <div class="petition-guidance-section">


            <h2>

                How it works

            </h2>


            <ul class="petition-guidance-list">


                <li>

                    Write a clear and specific
                    <strong>
                        petition action
                    </strong>.

                </li>


                <li>

                    Provide details about
                    <strong>
                        what you want to happen
                    </strong>
                    and why.

                </li>


                <li>

                    Check that your petition
                    meets the
                    <a
                        href="#petition-standards"
                        class="petition-guidance-link"
                    >
                        petition standards
                    </a>.

                </li>


            </ul>


        </div>


        <!-- ==================================================
             STANDARDS
        ================================================== -->

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


        <!-- ==================================================
             SUPPORTERS
        ================================================== -->

        <div class="petition-guidance-section">


            <div class="petition-supporter-message">


                After you've submitted your petition,
                you'll need to share it with at least
                <strong>
                    five supporters
                </strong>.


            </div>


        </div>


        <!-- ==================================================
             START PETITION BUTTON
        ================================================== -->

        <div class="start-petition-action">


            <p>

                Ready to create your petition?

            </p>


            <a
                href="petition_citizenship.php"
                class="start-petition-button"
            >

                Start Petition

            </a>


        </div>


    </section>


</main>


<!-- ==================================================
     FOOTER
================================================== -->

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


<!-- ==================================================
     TANZANIA FLAG LOTTIE
================================================== -->

<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {


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

            container: flag,

            renderer: "svg",

            loop: true,

            autoplay: true,

            path:
                "assets/animations/Tanzania%20flag%20Lottie%20JSON%20animation.json"

        });


    }
);

</script>


</body>

</html>