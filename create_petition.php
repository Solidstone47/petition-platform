<?php

require_once "includes/db.php";
require_once "includes/functions.php";
require_once "includes/auth.php";
require_user();

// ======================================================
// PAGE INFORMATION
// ======================================================

$page_title = "Create a Petition | Petition Platform";

// ======================================================
// GET LOGGED-IN USER ID
// ======================================================

$user_id = 0;

if (isset($_SESSION['user_id'])) {
    $user_id = (int) $_SESSION['user_id'];
}

if ($user_id < 1) {
    redirect("login.php");
}

// ======================================================
// FORM VARIABLES
// ======================================================

$title = "";
$description = "";
$goal = 1000;
$errors = [];

// ======================================================
// HANDLE FORM SUBMISSION
// ======================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    $goal = isset($_POST['goal'])
        ? (int) $_POST['goal']
        : 1000;

    // ==================================================
    // VALIDATE TITLE
    // ==================================================

    if ($title === '') {

        $errors[] =
            "Please enter a title for your petition.";

    } elseif (strlen($title) < 10) {

        $errors[] =
            "Your petition title should be at least 10 characters long.";

    } elseif (strlen($title) > 255) {

        $errors[] =
            "Your petition title cannot exceed 255 characters.";
    }

    // ==================================================
    // VALIDATE DESCRIPTION
    // ==================================================

    if ($description === '') {

        $errors[] =
            "Please explain what your petition is about.";

    } elseif (strlen($description) < 30) {

        $errors[] =
            "Please provide more information about your petition. The description should be at least 30 characters.";
    }

    // ==================================================
    // VALIDATE SIGNATURE GOAL
    // ==================================================

    if ($goal < 10) {

        $errors[] =
            "Your signature goal must be at least 10.";

    } elseif ($goal > 100000000) {

        $errors[] =
            "Your signature goal is too high.";
    }

    // ==================================================
    // CREATE PETITION
    // ==================================================

    if (empty($errors)) {

        $stmt = $conn->prepare(
            "INSERT INTO petitions
            (
                title,
                description,
                goal,
                status,
                created_by
            )
            VALUES
            (
                ?,
                ?,
                ?,
                'active',
                ?
            )"
        );

        if ($stmt) {

            $stmt->bind_param(
                "ssii",
                $title,
                $description,
                $goal,
                $user_id
            );

            if ($stmt->execute()) {

                $petition_id = $stmt->insert_id;

                $stmt->close();

                redirect(
                    "petition.php?id=" . (int) $petition_id
                );

            } else {

                $errors[] =
                    "We could not create your petition. Please try again.";

                $stmt->close();
            }

        } else {

            $errors[] =
                "A database error occurred while preparing your petition.";
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

    <meta
        name="description"
        content="Create and publish a petition on the Tanzania Petition Platform."
    >

    <title>
        <?= e($page_title) ?>
    </title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/bodymovin/5.12.2/lottie.min.js"
    ></script>

    <style>

        /* ==================================================
           NAVIGATION
        ================================================== */

        .homepage-navigation {
            position: relative;
            width: 100%;
            min-height: 72px;
            background: rgba(255, 255, 255, 0.82);
            border-bottom: 1px solid rgba(15, 23, 42, 0.08);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            display: flex;
            align-items: center;
            padding: 0 20px;
            box-sizing: border-box;
            z-index: 100;
        }

        .homepage-brand {
            position: absolute;
            left: 25px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 5;
        }

        .homepage-brand a {
            color: var(--primary);
            text-decoration: none;
            font-size: 20px;
            font-weight: 800;
            white-space: nowrap;
            letter-spacing: -0.3px;
        }

        .homepage-brand a:hover {
            color: var(--primary-dark);
        }

        /* ==================================================
           TABS
        ================================================== */

        .homepage-tabs {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
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
                transform 0.2s ease;
        }

        .homepage-tabs a:hover {
            color: var(--primary);
            transform: translateY(-1px);
        }

        .homepage-tabs a[aria-current="page"] {
            color: var(--primary);
            font-weight: 700;
        }

        /* ==================================================
           FLAG
        ================================================== */

        .homepage-flag {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
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
           MAIN
        ================================================== */

        .create-petition-main {
            width: 100%;
            max-width: 920px;
            margin: 55px auto 80px;
            padding: 0 25px;
            box-sizing: border-box;
        }

        /* ==================================================
           HEADER
        ================================================== */

        .create-petition-header {
            text-align: center;
            margin-bottom: 38px;
        }

        .create-petition-header .badge {
            display: inline-block;
            margin-bottom: 14px;
        }

        .create-petition-header h1 {
            font-size: clamp(30px, 5vw, 46px);
            line-height: 1.15;
            color: var(--dark);
            margin-bottom: 15px;
            letter-spacing: -0.8px;
        }

        .create-petition-header p {
            max-width: 720px;
            margin: 0 auto;
            color: var(--text-muted);
            font-size: 16px;
            line-height: 1.75;
        }

        /* ==================================================
           GLASS FORM CARD
        ================================================== */

        .create-petition-card {
            position: relative;
            background: rgba(255, 255, 255, 0.72);
            border: 1px solid rgba(255, 255, 255, 0.75);
            border-radius: 20px;
            padding: 38px;
            box-shadow:
                0 20px 55px rgba(15, 23, 42, 0.08),
                0 2px 10px rgba(15, 23, 42, 0.04);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            overflow: hidden;
        }

        .create-petition-card::before {
            content: "";
            position: absolute;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: rgba(0, 107, 63, 0.06);
            top: -90px;
            right: -70px;
            pointer-events: none;
        }

        /* ==================================================
           INTRODUCTION
        ================================================== */

        .form-introduction {
            position: relative;
            background: rgba(0, 107, 63, 0.055);
            border: 1px solid rgba(0, 107, 63, 0.12);
            border-radius: 14px;
            padding: 22px;
            margin-bottom: 30px;
        }

        .form-introduction h2 {
            color: var(--primary);
            font-size: 19px;
            margin-bottom: 8px;
        }

        .form-introduction p {
            color: var(--text);
            font-size: 14px;
            line-height: 1.7;
            margin: 0;
        }

        /* ==================================================
           ERROR BOX
        ================================================== */

        .petition-errors {
            background: rgba(254, 242, 242, 0.88);
            border: 1px solid #fecaca;
            border-radius: 12px;
            padding: 18px 20px;
            margin-bottom: 25px;
        }

        .petition-errors h3 {
            color: #991b1b;
            font-size: 17px;
            margin-bottom: 10px;
        }

        .petition-errors ul {
            margin: 0;
            padding-left: 20px;
        }

        .petition-errors li {
            color: #7f1d1d;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 5px;
        }

        .petition-errors li:last-child {
            margin-bottom: 0;
        }

        /* ==================================================
           FORM GROUP
        ================================================== */

        .petition-form-group {
            margin-bottom: 27px;
        }

        .petition-form-group label {
            display: block;
            font-size: 15px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 9px;
        }

        .petition-form-group label span {
            color: #dc2626;
        }

        .petition-form-help {
            display: block;
            color: var(--text-muted);
            font-size: 13px;
            line-height: 1.5;
            margin-top: 8px;
        }

        /* ==================================================
           INPUTS
        ================================================== */

        .petition-form-group input,
        .petition-form-group textarea {
            width: 100%;
            box-sizing: border-box;
            border: 1px solid rgba(15, 23, 42, 0.12);
            border-radius: 11px;
            background: rgba(255, 255, 255, 0.76);
            color: var(--text);
            font-family: inherit;
            font-size: 15px;
            padding: 14px 15px;
            outline: none;
            transition:
                border-color 0.2s ease,
                box-shadow 0.2s ease,
                background 0.2s ease,
                transform 0.2s ease;
        }

        .petition-form-group input {
            min-height: 50px;
        }

        .petition-form-group textarea {
            min-height: 200px;
            resize: vertical;
            line-height: 1.65;
        }

        .petition-form-group input:hover,
        .petition-form-group textarea:hover {
            background: rgba(255, 255, 255, 0.92);
            border-color: rgba(0, 107, 63, 0.20);
        }

        .petition-form-group input:focus,
        .petition-form-group textarea:focus {
            background: rgba(255, 255, 255, 0.96);
            border-color: var(--primary);
            box-shadow:
                0 0 0 4px rgba(0, 107, 63, 0.09),
                0 8px 25px rgba(15, 23, 42, 0.05);
        }

        /* ==================================================
           GOAL
        ================================================== */

        .goal-wrapper {
            position: relative;
        }

        .goal-wrapper input {
            padding-left: 62px;
        }

        .goal-prefix {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
            font-size: 13px;
            font-weight: 800;
            pointer-events: none;
            z-index: 2;
        }

        /* ==================================================
           TIPS
        ================================================== */

        .petition-tips {
            background: rgba(255, 255, 255, 0.55);
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 14px;
            padding: 23px;
            margin-top: 30px;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
        }

        .petition-tips h3 {
            color: var(--dark);
            font-size: 18px;
            margin-bottom: 14px;
        }

        .petition-tips ul {
            margin: 0;
            padding-left: 20px;
        }

        .petition-tips li {
            color: var(--text);
            font-size: 14px;
            line-height: 1.65;
            margin-bottom: 9px;
        }

        .petition-tips li:last-child {
            margin-bottom: 0;
        }

        /* ==================================================
           FORM ACTIONS
        ================================================== */

        .petition-form-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            margin-top: 32px;
            padding-top: 27px;
            border-top: 1px solid rgba(15, 23, 42, 0.08);
        }

        .petition-cancel-button,
        .petition-submit-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 48px;
            box-sizing: border-box;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            transition:
                background 0.2s ease,
                border-color 0.2s ease,
                transform 0.2s ease,
                box-shadow 0.2s ease;
        }

        .petition-cancel-button {
            padding: 13px 22px;
            border: 1px solid rgba(15, 23, 42, 0.12);
            background: rgba(255, 255, 255, 0.72);
            color: var(--text);
            text-decoration: none;
        }

        .petition-cancel-button:hover {
            background: #ffffff;
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-1px);
        }

        .petition-submit-button {
            padding: 14px 30px;
            border: 1px solid var(--primary);
            background: var(--primary);
            color: #ffffff;
            cursor: pointer;
            box-shadow:
                0 8px 20px rgba(0, 107, 63, 0.16);
        }

        .petition-submit-button:hover {
            background: var(--primary-dark);
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow:
                0 12px 25px rgba(0, 107, 63, 0.22);
        }

        .petition-submit-button:active {
            transform: translateY(0);
        }

        /* ==================================================
           FOOTER
        ================================================== */

        .dashboard-footer {
            background: var(--dark);
            color: #ffffff;
            margin-top: 50px;
            padding: 35px 25px;
            border-top: 4px solid var(--gold);
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
                padding: 10px 15px;
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
                padding: 5px 45px 5px 0;
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

            .create-petition-main {
                margin: 35px auto 50px;
                padding: 0 16px;
            }

            .create-petition-card {
                padding: 24px 18px;
                border-radius: 16px;
            }

            .create-petition-header h1 {
                font-size: 30px;
            }

            .petition-form-actions {
                flex-direction: column-reverse;
                align-items: stretch;
            }

            .petition-submit-button,
            .petition-cancel-button {
                width: 100%;
            }

        }

        @media (max-width: 480px) {

            .homepage-tabs {
                gap: 8px;
            }

            .homepage-tabs a {
                font-size: 14px;
            }

            .create-petition-main {
                margin-top: 25px;
            }

            .form-introduction,
            .petition-tips {
                padding: 18px;
            }

        }

    </style>

</head>

<body>

<!-- ======================================================
     NAVIGATION
====================================================== -->

<nav class="homepage-navigation">

    <div class="homepage-brand">

        <a href="index.php">
            Petition Platform
        </a>

    </div>

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
            href="create_petition.php"
            aria-current="page"
        >
            Start Petition
        </a>

        <a href="logout.php">
            Logout
        </a>

    </div>

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

<main class="create-petition-main">

    <section class="create-petition-header">

        <span class="badge">
            Create a Petition
        </span>

        <h1>
            Tell Us What Needs to Change
        </h1>

        <p>
            Use this form to create your petition.
            Be clear about the issue, explain why it matters,
            and tell the Government of Tanzania, Parliament,
            or the wider community what action you want to see.
        </p>

    </section>

    <!-- ==================================================
         FORM CARD
    ================================================== -->

    <section class="create-petition-card">

        <div class="form-introduction">

            <h2>
                Make your petition clear and specific
            </h2>

            <p>
                A strong petition clearly explains the problem,
                identifies the action you want taken, and gives
                people enough information to understand why they
                should support it.
            </p>

        </div>

        <!-- ==================================================
             ERRORS
        ================================================== -->

        <?php if (!empty($errors)): ?>

            <div
                class="petition-errors"
                role="alert"
            >

                <h3>
                    Please correct the following:
                </h3>

                <ul>

                    <?php foreach ($errors as $error): ?>

                        <li>
                            <?= e($error) ?>
                        </li>

                    <?php endforeach; ?>

                </ul>

            </div>

        <?php endif; ?>

        <!-- ==================================================
             FORM
        ================================================== -->

        <form
            method="POST"
            action=""
            novalidate
        >

            <!-- ==================================================
                 TITLE
            ================================================== -->

            <div class="petition-form-group">

                <label for="title">

                    Petition title

                    <span>*</span>

                </label>

                <input
                    type="text"
                    id="title"
                    name="title"
                    value="<?= e($title) ?>"
                    maxlength="255"
                    required
                    placeholder="Example: Improve access to clean drinking water in rural communities"
                >

                <span class="petition-form-help">
                    Write a short, specific title that tells people
                    exactly what your petition is about.
                </span>

            </div>

            <!-- ==================================================
                 DESCRIPTION
            ================================================== -->

            <div class="petition-form-group">

                <label for="description">

                    Petition details

                    <span>*</span>

                </label>

                <textarea
                    id="description"
                    name="description"
                    required
                    placeholder="Explain the issue in detail. What is happening? Who is affected? Why is this important? What action do you want taken?"
                ><?= e($description) ?></textarea>

                <span class="petition-form-help">
                    Explain the situation clearly. Include relevant
                    background information, who is affected, why
                    the issue matters, and the change you want to see.
                </span>

            </div>

            <!-- ==================================================
                 SIGNATURE GOAL
            ================================================== -->

            <div class="petition-form-group">

                <label for="goal">

                    Signature goal

                    <span>*</span>

                </label>

                <div class="goal-wrapper">

                    <span class="goal-prefix">
                        Goal
                    </span>

                    <input
                        type="number"
                        id="goal"
                        name="goal"
                        value="<?= e($goal) ?>"
                        min="10"
                        max="100000000"
                        required
                    >

                </div>

                <span class="petition-form-help">
                    Choose the number of signatures you want
                    your petition to reach.
                </span>

            </div>

            <!-- ==================================================
                 TIPS
            ================================================== -->

            <div class="petition-tips">

                <h3>
                    Tips for a strong petition
                </h3>

                <ul>

                    <li>
                        Make your title clear and easy to understand.
                    </li>

                    <li>
                        Explain the problem rather than simply
                        making a general complaint.
                    </li>

                    <li>
                        Clearly state the action you want taken.
                    </li>

                    <li>
                        Keep your petition respectful and factual.
                    </li>

                    <li>
                        Avoid false information, threats,
                        personal attacks, or discriminatory language.
                    </li>

                    <li>
                        Give supporters enough information to
                        understand what they are supporting.
                    </li>

                </ul>

            </div>

            <!-- ==================================================
                 ACTIONS
            ================================================== -->

            <div class="petition-form-actions">

                <a
                    href="start_petition.php"
                    class="petition-cancel-button"
                >
                    ← Back
                </a>

                <button
                    type="submit"
                    class="petition-submit-button"
                >
                    Create Petition
                </button>

            </div>

        </form>

    </section>

</main>

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