<?php

require_once "includes/db.php";
require_once "includes/functions.php";
require_once "includes/auth.php";

require_user();


// ==================================================
// GET LOGGED-IN USER
// ==================================================

$user_id = (int) $_SESSION['user_id'];

$stmt = $conn->prepare(
    "SELECT
        id,
        fullname,
        email,
        phone,
        created_at
     FROM users
     WHERE id = ?
     LIMIT 1"
);

if (!$stmt) {
    http_response_code(500);
    exit("Unable to load your account.");
}

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

$user = $result->fetch_assoc();

$stmt->close();


if (!$user) {

    logout_user();

    set_flash(
        "error",
        "Your account could not be found."
    );

    redirect("login.php");
}


// ==================================================
// COUNT SIGNED PETITIONS
// ==================================================

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM signatures
     WHERE user_id = ?"
);

if (!$stmt) {
    http_response_code(500);
    exit("Unable to load dashboard statistics.");
}

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

$signed_count = (int) $result->fetch_assoc()['total'];

$stmt->close();


// ==================================================
// COUNT ACTIVE SIGNED PETITIONS
// ==================================================

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM signatures s
     INNER JOIN petitions p
        ON p.id = s.petition_id
     WHERE s.user_id = ?
       AND p.status = 'active'"
);

if (!$stmt) {
    http_response_code(500);
    exit("Unable to load dashboard statistics.");
}

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

$active_supported_count =
    (int) $result->fetch_assoc()['total'];

$stmt->close();


// ==================================================
// GET SIGNED PETITIONS
// ==================================================

$stmt = $conn->prepare(
    "SELECT
        p.id,
        p.title,
        p.goal,
        p.status,
        p.created_at,

        s.created_at AS signed_at,

        (
            SELECT COUNT(*)
            FROM signatures s2
            WHERE s2.petition_id = p.id
        ) AS signature_count

     FROM signatures s

     INNER JOIN petitions p
        ON p.id = s.petition_id

     WHERE s.user_id = ?

     ORDER BY s.created_at DESC"
);

if (!$stmt) {
    http_response_code(500);
    exit("Unable to load your petitions.");
}

$stmt->bind_param("i", $user_id);
$stmt->execute();

$signed_petitions = $stmt->get_result();


// ==================================================
// FLASH MESSAGE
// ==================================================

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
        content="Your Petition Platform dashboard."
    >

    <title>
        My Dashboard | Petition Platform
    </title>


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
           PAGE
        ================================================== */

        body {

            background:
                linear-gradient(
                    180deg,
                    #f4f8f6 0%,
                    #f8fafc 55%,
                    #ffffff 100%
                );

        }


        /* ==================================================
           NAVIGATION
        ================================================== */

        .dashboard-navigation {

            position:
                relative;

            width:
                100%;

            min-height:
                72px;

            background:
                rgba(
                    255,
                    255,
                    255,
                    0.94
                );

            backdrop-filter:
                blur(14px);

            -webkit-backdrop-filter:
                blur(14px);

            border-bottom:
                1px solid var(--border);

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

        .dashboard-brand {

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


        .dashboard-brand a {

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


        .dashboard-brand a:hover {

            color:
                var(--primary-dark);

        }


        /* ==================================================
           NAV TABS
        ================================================== */

        .dashboard-tabs {

            position:
                absolute;

            left:
                50%;

            top:
                50%;

            transform:
                translate(
                    -50%,
                    -50%
                );

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


        .dashboard-tabs a {

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
                all 0.2s ease;

        }


        .dashboard-tabs a:hover {

            color:
                var(--primary);

        }


        .dashboard-tabs a[aria-current="page"] {

            color:
                var(--primary);

            font-weight:
                800;

        }


        /* ==================================================
           LOGOUT
        ================================================== */

        .dashboard-logout {

            color:
                #b91c1c !important;

        }


        .dashboard-logout:hover {

            color:
                #991b1b !important;

        }


        /* ==================================================
           TANZANIA FLAG
        ================================================== */

        .dashboard-flag {

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


        .dashboard-flag svg {

            width:
                100%;

            height:
                100%;

            display:
                block;

        }


        /* ==================================================
           MAIN CONTAINER
        ================================================== */

        .dashboard-main {

            width:
                100%;

            max-width:
                1120px;

            margin:
                0 auto;

            padding:
                42px 25px 75px;

            box-sizing:
                border-box;

        }


        /* ==================================================
           FLASH
        ================================================== */

        .dashboard-flash {

            margin-bottom:
                25px;

        }


        /* ==================================================
           WELCOME HERO
        ================================================== */

        .dashboard-welcome {

            position:
                relative;

            overflow:
                hidden;

            background:
                linear-gradient(
                    135deg,
                    var(--primary-dark),
                    var(--primary)
                );

            color:
                #ffffff;

            border-radius:
                18px;

            padding:
                38px 40px;

            margin-bottom:
                25px;

            border-bottom:
                4px solid var(--gold);

            box-shadow:
                0 15px 40px
                rgba(
                    0,
                    107,
                    63,
                    0.16
                );

        }


        .dashboard-welcome::before {

            content:
                "";

            position:
                absolute;

            width:
                230px;

            height:
                230px;

            border-radius:
                50%;

            background:
                rgba(
                    255,
                    255,
                    255,
                    0.07
                );

            right:
                -75px;

            top:
                -100px;

        }


        .dashboard-welcome::after {

            content:
                "";

            position:
                absolute;

            width:
                160px;

            height:
                160px;

            border-radius:
                50%;

            background:
                rgba(
                    255,
                    215,
                    0,
                    0.08
                );

            right:
                100px;

            bottom:
                -100px;

        }


        .dashboard-welcome-content {

            position:
                relative;

            z-index:
                2;

        }


        .dashboard-welcome-badge {

            display:
                inline-flex;

            align-items:
                center;

            gap:
                7px;

            padding:
                7px 12px;

            background:
                rgba(
                    255,
                    255,
                    255,
                    0.12
                );

            border:
                1px solid
                rgba(
                    255,
                    255,
                    255,
                    0.18
                );

            border-radius:
                999px;

            font-size:
                12px;

            font-weight:
                800;

            margin-bottom:
                15px;

        }


        .dashboard-welcome h1 {

            color:
                #ffffff;

            font-size:
                clamp(
                    28px,
                    4vw,
                    38px
                );

            line-height:
                1.2;

            margin:
                0 0 10px;

        }


        .dashboard-welcome p {

            color:
                rgba(
                    255,
                    255,
                    255,
                    0.82
                );

            font-size:
                15px;

            line-height:
                1.6;

            margin:
                0;

            max-width:
                650px;

        }


        /* ==================================================
           STATISTICS
        ================================================== */

        .dashboard-stats {

            display:
                grid;

            grid-template-columns:
                repeat(
                    3,
                    minmax(
                        0,
                        1fr
                    )
                );

            gap:
                16px;

            margin-bottom:
                38px;

        }


        .dashboard-stat-card {

            position:
                relative;

            overflow:
                hidden;

            display:
                flex;

            align-items:
                center;

            gap:
                15px;

            background:
                rgba(
                    255,
                    255,
                    255,
                    0.78
                );

            backdrop-filter:
                blur(10px);

            -webkit-backdrop-filter:
                blur(10px);

            border:
                1px solid
                rgba(
                    226,
                    232,
                    240,
                    0.9
                );

            border-radius:
                14px;

            padding:
                20px;

            box-shadow:
                0 8px 25px
                rgba(
                    15,
                    23,
                    42,
                    0.05
                );

        }


        .dashboard-stat-icon {

            flex:
                0 0 46px;

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
                12px;

            background:
                var(--primary-light);

            color:
                var(--primary);

            font-size:
                19px;

        }


        .dashboard-stat-number {

            display:
                block;

            color:
                var(--dark);

            font-size:
                25px;

            line-height:
                1.1;

            font-weight:
                800;

        }


        .dashboard-stat-label {

            display:
                block;

            color:
                var(--text-muted);

            font-size:
                13px;

            margin-top:
                4px;

        }


        /* ==================================================
           SECTION
        ================================================== */

        .dashboard-section {

            margin-top:
                35px;

        }


        .dashboard-section-heading {

            display:
                flex;

            align-items:
                flex-end;

            justify-content:
                space-between;

            gap:
                20px;

            margin-bottom:
                18px;

        }


        .dashboard-section-heading h2 {

            margin:
                0 0 5px;

            font-size:
                23px;

        }


        .dashboard-section-heading p {

            margin:
                0;

            color:
                var(--text-muted);

            font-size:
                14px;

        }


        .dashboard-browse-link {

            display:
                inline-flex;

            align-items:
                center;

            gap:
                6px;

            color:
                var(--primary);

            text-decoration:
                none;

            font-size:
                14px;

            font-weight:
                700;

            white-space:
                nowrap;

        }


        .dashboard-browse-link:hover {

            color:
                var(--primary-dark);

        }


        /* ==================================================
           PETITION GRID
        ================================================== */

        .dashboard-petition-grid {

            display:
                grid;

            grid-template-columns:
                repeat(
                    2,
                    minmax(
                        0,
                        1fr
                    )
                );

            gap:
                18px;

        }


        /* ==================================================
           PETITION CARD
        ================================================== */

        .dashboard-petition-card {

            position:
                relative;

            background:
                rgba(
                    255,
                    255,
                    255,
                    0.86
                );

            backdrop-filter:
                blur(10px);

            -webkit-backdrop-filter:
                blur(10px);

            border:
                1px solid
                rgba(
                    226,
                    232,
                    240,
                    0.95
                );

            border-radius:
                15px;

            padding:
                23px;

            box-shadow:
                0 8px 28px
                rgba(
                    15,
                    23,
                    42,
                    0.05
                );

            display:
                flex;

            flex-direction:
                column;

            min-width:
                0;

            transition:
                transform 0.22s ease,
                box-shadow 0.22s ease,
                border-color 0.22s ease;

        }


        .dashboard-petition-card:hover {

            transform:
                translateY(-4px);

            box-shadow:
                0 14px 35px
                rgba(
                    15,
                    23,
                    42,
                    0.09
                );

            border-color:
                rgba(
                    0,
                    107,
                    63,
                    0.22
                );

        }


        /* ==================================================
           CARD TOP
        ================================================== */

        .dashboard-card-top {

            display:
                flex;

            align-items:
                center;

            justify-content:
                space-between;

            gap:
                10px;

            margin-bottom:
                13px;

        }


        .dashboard-status {

            display:
                inline-flex;

            align-items:
                center;

            gap:
                6px;

            border-radius:
                999px;

            padding:
                6px 10px;

            font-size:
                11px;

            font-weight:
                800;

        }


        .dashboard-status-active {

            background:
                #dcfce7;

            color:
                #166534;

        }


        .dashboard-status-dot {

            width:
                6px;

            height:
                6px;

            border-radius:
                50%;

            background:
                #16a34a;

        }


        .dashboard-status-other {

            background:
                #f1f5f9;

            color:
                #475569;

        }


        /* ==================================================
           TITLE
        ================================================== */

        .dashboard-petition-title {

            margin:
                0 0 18px;

            font-size:
                18px;

            line-height:
                1.45;

            min-height:
                52px;

        }


        .dashboard-petition-title a {

            color:
                var(--dark);

            text-decoration:
                none;

        }


        .dashboard-petition-title a:hover {

            color:
                var(--primary);

        }


        /* ==================================================
           SIGNATURE INFO
        ================================================== */

        .dashboard-signature-info {

            display:
                flex;

            align-items:
                baseline;

            justify-content:
                space-between;

            gap:
                10px;

            margin-bottom:
                10px;

        }


        .dashboard-signature-main {

            display:
                flex;

            align-items:
                baseline;

            gap:
                6px;

        }


        .dashboard-signature-main strong {

            color:
                var(--primary);

            font-size:
                22px;

            font-weight:
                800;

        }


        .dashboard-signature-main span {

            color:
                var(--text-muted);

            font-size:
                13px;

        }


        .dashboard-percentage {

            color:
                var(--primary);

            font-size:
                13px;

            font-weight:
                800;

        }


        /* ==================================================
           PROGRESS
        ================================================== */

        .dashboard-progress-track {

            width:
                100%;

            height:
                9px;

            background:
                #e8eef0;

            border-radius:
                999px;

            overflow:
                hidden;

        }


        .dashboard-progress-fill {

            height:
                100%;

            background:
                linear-gradient(
                    90deg,
                    var(--primary),
                    #18a765
                );

            border-radius:
                999px;

            transition:
                width 0.5s ease;

        }


        .dashboard-progress-info {

            display:
                flex;

            align-items:
                center;

            justify-content:
                space-between;

            gap:
                10px;

            margin-top:
                8px;

            color:
                var(--text-muted);

            font-size:
                12px;

        }


        .dashboard-goal-reached {

            color:
                var(--success);

            font-weight:
                800;

        }


        /* ==================================================
           CARD FOOTER
        ================================================== */

        .dashboard-card-footer {

            display:
                flex;

            align-items:
                center;

            justify-content:
                space-between;

            gap:
                10px;

            margin-top:
                20px;

            padding-top:
                15px;

            border-top:
                1px solid
                var(--border);

        }


        .dashboard-signed-date {

            color:
                var(--text-muted);

            font-size:
                12px;

        }


        .dashboard-view-link {

            display:
                inline-flex;

            align-items:
                center;

            gap:
                5px;

            color:
                var(--primary);

            text-decoration:
                none;

            font-size:
                13px;

            font-weight:
                800;

        }


        .dashboard-view-link:hover {

            color:
                var(--primary-dark);

        }


        /* ==================================================
           EMPTY STATE
        ================================================== */

        .dashboard-empty {

            background:
                rgba(
                    255,
                    255,
                    255,
                    0.86
                );

            backdrop-filter:
                blur(10px);

            -webkit-backdrop-filter:
                blur(10px);

            border:
                1px solid
                var(--border);

            border-radius:
                15px;

            padding:
                55px 25px;

            text-align:
                center;

            box-shadow:
                0 8px 28px
                rgba(
                    15,
                    23,
                    42,
                    0.05
                );

        }


        .dashboard-empty-icon {

            width:
                56px;

            height:
                56px;

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
                var(--primary-light);

            color:
                var(--primary);

            font-size:
                22px;

        }


        .dashboard-empty h3 {

            margin:
                0 0 7px;

            font-size:
                19px;

        }


        .dashboard-empty p {

            color:
                var(--text-muted);

            font-size:
                14px;

            margin:
                0 0 20px;

        }


        .dashboard-primary-button {

            display:
                inline-flex;

            align-items:
                center;

            justify-content:
                center;

            gap:
                8px;

            background:
                var(--primary);

            color:
                #ffffff;

            text-decoration:
                none;

            border-radius:
                8px;

            padding:
                11px 17px;

            font-size:
                14px;

            font-weight:
                800;

        }


        .dashboard-primary-button:hover {

            background:
                var(--primary-dark);

            color:
                #ffffff;

        }


        /* ==================================================
           ACCOUNT CARD
        ================================================== */

        .dashboard-account-card {

            display:
                grid;

            grid-template-columns:
                repeat(
                    2,
                    minmax(
                        0,
                        1fr
                    )
                );

            background:
                rgba(
                    255,
                    255,
                    255,
                    0.86
                );

            backdrop-filter:
                blur(10px);

            -webkit-backdrop-filter:
                blur(10px);

            border:
                1px solid
                var(--border);

            border-radius:
                15px;

            overflow:
                hidden;

            box-shadow:
                0 8px 28px
                rgba(
                    15,
                    23,
                    42,
                    0.05
                );

        }


        .dashboard-account-item {

            padding:
                20px;

            border-bottom:
                1px solid
                var(--border);

        }


        .dashboard-account-item:nth-child(
            odd
        ) {

            border-right:
                1px solid
                var(--border);

        }


        .dashboard-account-item:nth-last-child(-n + 2) {

            border-bottom:
                none;

        }


        .dashboard-account-label {

            display:
                block;

            color:
                var(--text-muted);

            font-size:
                12px;

            margin-bottom:
                6px;

        }


        .dashboard-account-value {

            display:
                block;

            color:
                var(--dark);

            font-size:
                14px;

            font-weight:
                700;

            word-break:
                break-word;

        }


        /* ==================================================
           FOOTER
        ================================================== */

        .dashboard-footer {

            background:
                #111827;

            color:
                #ffffff;

            border-top:
                4px solid
                var(--gold);

            padding:
                40px 25px;

            text-align:
                center;

        }


        .dashboard-footer h3 {

            color:
                #ffffff;

            margin:
                0 0 8px;

        }


        .dashboard-footer p {

            color:
                #cbd5e1;

            margin:
                0;

        }


        .dashboard-footer .copyright {

            color:
                #94a3b8;

            font-size:
                13px;

            margin-top:
                20px;

        }


        /* ==================================================
           RESPONSIVE
        ================================================== */

        @media (max-width: 900px) {

            .dashboard-tabs {

                gap:
                    15px;

            }


            .dashboard-stats {

                grid-template-columns:
                    repeat(
                        2,
                        minmax(
                            0,
                            1fr
                        )
                    );

            }


            .dashboard-stat-card:last-child {

                grid-column:
                    1 / -1;

            }

        }


        @media (max-width: 700px) {

            .dashboard-navigation {

                min-height:
                    125px;

                padding:
                    10px 15px;

                flex-direction:
                    column;

                justify-content:
                    center;

            }


            .dashboard-brand {

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


            .dashboard-tabs {

                position:
                    static;

                transform:
                    none;

                width:
                    100%;

                flex-wrap:
                    wrap;

                justify-content:
                    center;

                gap:
                    9px 15px;

                padding:
                    5px 45px 5px 0;

                box-sizing:
                    border-box;

            }


            .dashboard-tabs a {

                font-size:
                    14px;

            }


            .dashboard-flag {

                right:
                    8px;

                width:
                    48px;

                height:
                    48px;

            }


            .dashboard-main {

                padding:
                    28px 16px 55px;

            }


            .dashboard-welcome {

                padding:
                    30px 24px;

                border-radius:
                    15px;

            }


            .dashboard-welcome h1 {

                font-size:
                    28px;

            }


            .dashboard-stats {

                grid-template-columns:
                    1fr;

                gap:
                    12px;

                margin-bottom:
                    30px;

            }


            .dashboard-stat-card:last-child {

                grid-column:
                    auto;

            }


            .dashboard-petition-grid {

                grid-template-columns:
                    1fr;

            }


            .dashboard-section-heading {

                align-items:
                    flex-start;

                flex-direction:
                    column;

                gap:
                    9px;

            }


            .dashboard-account-card {

                grid-template-columns:
                    1fr;

            }


            .dashboard-account-item:nth-child(
                odd
            ) {

                border-right:
                    none;

            }


            .dashboard-account-item:nth-last-child(-n + 2) {

                border-bottom:
                    1px solid
                    var(--border);

            }


            .dashboard-account-item:last-child {

                border-bottom:
                    none;

            }

        }


        @media (max-width: 430px) {

            .dashboard-tabs {

                gap:
                    7px 11px;

            }


            .dashboard-tabs a {

                font-size:
                    13px;

            }


            .dashboard-welcome {

                padding:
                    26px 20px;

            }


            .dashboard-welcome h1 {

                font-size:
                    25px;

            }


            .dashboard-welcome p {

                font-size:
                    14px;

            }


            .dashboard-petition-card {

                padding:
                    19px;

            }


            .dashboard-petition-title {

                font-size:
                    17px;

            }

        }

    </style>

</head>


<body>


<!-- ======================================================
     NAVIGATION
====================================================== -->

<nav class="dashboard-navigation">


    <!-- BRAND -->

    <div class="dashboard-brand">

        <a href="index.php">

            Petition Platform

        </a>

    </div>


    <!-- TABS -->

    <div class="dashboard-tabs">

        <a href="index.php">

            Home

        </a>


        <a href="petitions.php">

            Petitions

        </a>


        <a
            href="dashboard.php"
            aria-current="page"
        >

            Dashboard

        </a>


        <a
            href="logout.php"
            class="dashboard-logout"
        >

            Logout

        </a>

    </div>


    <!-- TANZANIA FLAG -->

    <div
        id="tanzania-flag"
        class="dashboard-flag"
        aria-label="Tanzania Flag"
        title="Tanzania"
    ></div>


</nav>



<!-- ======================================================
     MAIN
====================================================== -->

<main class="dashboard-main">


    <!-- ==================================================
         FLASH MESSAGE
    ================================================== -->

    <?php if ($flash): ?>

        <div class="dashboard-flash flash">

            <p>

                <?= e($flash['message']) ?>

            </p>

        </div>

    <?php endif; ?>



    <!-- ==================================================
         WELCOME
    ================================================== -->

    <section class="dashboard-welcome">

        <div class="dashboard-welcome-content">

            <span class="dashboard-welcome-badge">

                <i class="fa-solid fa-user"></i>

                My Dashboard

            </span>


            <h1>

                Welcome,
                <?= e($user['fullname']) ?>

            </h1>


            <p>

                Your voice matters. Keep track of the
                petitions you support and the causes
                you're helping move forward.

            </p>

        </div>

    </section>



    <!-- ==================================================
         STATISTICS
    ================================================== -->

    <section class="dashboard-stats">


        <!-- SIGNED -->

        <div class="dashboard-stat-card">

            <div class="dashboard-stat-icon">

                <i class="fa-solid fa-signature"></i>

            </div>


            <div>

                <strong class="dashboard-stat-number">

                    <?= number_format($signed_count) ?>

                </strong>


                <span class="dashboard-stat-label">

                    Petitions Signed

                </span>

            </div>

        </div>



        <!-- ACTIVE -->

        <div class="dashboard-stat-card">

            <div class="dashboard-stat-icon">

                <i class="fa-solid fa-bullhorn"></i>

            </div>


            <div>

                <strong class="dashboard-stat-number">

                    <?= number_format($active_supported_count) ?>

                </strong>


                <span class="dashboard-stat-label">

                    Active Causes Supported

                </span>

            </div>

        </div>



        <!-- ACCOUNT -->

        <div class="dashboard-stat-card">

            <div class="dashboard-stat-icon">

                <i class="fa-solid fa-user-check"></i>

            </div>


            <div>

                <strong class="dashboard-stat-number">

                    <i class="fa-solid fa-circle-check"></i>

                </strong>


                <span class="dashboard-stat-label">

                    Account Active

                </span>

            </div>

        </div>


    </section>



    <!-- ==================================================
         YOUR PETITIONS
    ================================================== -->

    <section class="dashboard-section">


        <div class="dashboard-section-heading">


            <div>

                <h2>

                    Your Petitions

                </h2>


                <p>

                    Petitions you have supported.

                </p>

            </div>


            <a
                href="petitions.php"
                class="dashboard-browse-link"
            >

                Browse Petitions

                <i class="fa-solid fa-arrow-right"></i>

            </a>


        </div>



        <?php if ($signed_petitions->num_rows === 0): ?>


            <!-- ==================================================
                 EMPTY STATE
            ================================================== -->

            <div class="dashboard-empty">


                <div class="dashboard-empty-icon">

                    <i class="fa-solid fa-signature"></i>

                </div>


                <h3>

                    No petitions yet

                </h3>


                <p>

                    You haven't signed any petitions yet.
                    Find a cause you care about and make your
                    voice count.

                </p>


                <a
                    href="petitions.php"
                    class="dashboard-primary-button"
                >

                    <i class="fa-solid fa-magnifying-glass"></i>

                    Browse Petitions

                </a>


            </div>


        <?php else: ?>


            <!-- ==================================================
                 PETITION GRID
            ================================================== -->

            <div class="dashboard-petition-grid">


                <?php while (
                    $petition =
                    $signed_petitions->fetch_assoc()
                ): ?>


                    <?php

                    $signatures =
                        (int) $petition['signature_count'];

                    $goal =
                        (int) $petition['goal'];

                    $percentage =
                        0;

                    if ($goal > 0) {

                        $percentage =
                            min(
                                100,
                                ($signatures / $goal) * 100
                            );

                    }

                    ?>


                    <!-- ==================================================
                         PETITION CARD
                    ================================================== -->

                    <article
                        class="dashboard-petition-card"
                    >


                        <!-- CARD TOP -->

                        <div
                            class="dashboard-card-top"
                        >


                            <?php if (
                                strtolower(
                                    (string)
                                    $petition['status']
                                )
                                === 'active'
                            ): ?>


                                <span
                                    class="
                                        dashboard-status
                                        dashboard-status-active
                                    "
                                >

                                    <span
                                        class="dashboard-status-dot"
                                    ></span>

                                    Active

                                </span>


                            <?php else: ?>


                                <span
                                    class="
                                        dashboard-status
                                        dashboard-status-other
                                    "
                                >

                                    <?= e(
                                        ucfirst(
                                            (string)
                                            $petition['status']
                                        )
                                    ) ?>

                                </span>


                            <?php endif; ?>


                        </div>



                        <!-- TITLE -->

                        <h3
                            class="dashboard-petition-title"
                        >

                            <a
                                href="petition.php?id=<?= (int) $petition['id'] ?>"
                            >

                                <?= e($petition['title']) ?>

                            </a>

                        </h3>



                        <!-- SIGNATURES -->

                        <div
                            class="dashboard-signature-info"
                        >


                            <div
                                class="dashboard-signature-main"
                            >

                                <strong>

                                    <?= number_format(
                                        $signatures
                                    ) ?>

                                </strong>


                                <span>

                                    of
                                    <?= number_format(
                                        $goal
                                    ) ?>

                                </span>

                            </div>


                            <span
                                class="dashboard-percentage"
                            >

                                <?= number_format(
                                    $percentage,
                                    0
                                ) ?>%

                            </span>


                        </div>



                        <!-- PROGRESS -->

                        <div
                            class="dashboard-progress-track"

                            role="progressbar"

                            aria-valuenow="<?= number_format($percentage, 1, '.', '') ?>"

                            aria-valuemin="0"

                            aria-valuemax="100"
                            
                        >
                            <div
                                class="dashboard-progress-fill"
                                style="width: <?= number_format($percentage, 2, '.', '') ?>%;"
                            ></div>
                        </div>



                        <!-- PROGRESS INFO -->

                        <div
                            class="dashboard-progress-info"
                        >


                            <span>

                                <?= number_format(
                                    $percentage,
                                    1
                                ) ?>% reached

                            </span>


                            <?php if (
                                $goal > $signatures
                            ): ?>


                                <span>

                                    <?= number_format(
                                        $goal - $signatures
                                    ) ?>

                                    needed

                                </span>


                            <?php else: ?>


                                <span
                                    class="
                                        dashboard-goal-reached
                                    "
                                >

                                    Goal reached

                                </span>


                            <?php endif; ?>


                        </div>



                        <!-- FOOTER -->

                        <div
                            class="dashboard-card-footer"
                        >


                            <span
                                class="dashboard-signed-date"
                            >

                                <i
                                    class="fa-regular fa-calendar"
                                ></i>

                                Signed
                                <?= e(
                                    format_date(
                                        $petition['signed_at']
                                    )
                                ) ?>

                            </span>


                            <a
                                href="petition.php?id=<?= (int) $petition['id'] ?>"
                                class="dashboard-view-link"
                            >

                                View Petition

                                <i
                                    class="fa-solid fa-arrow-right"
                                ></i>

                            </a>


                        </div>


                    </article>


                <?php endwhile; ?>


            </div>


        <?php endif; ?>


    </section>



    <!-- ==================================================
         ACCOUNT INFORMATION
    ================================================== -->

    <section
        class="
            dashboard-section
            dashboard-account-section
        "
    >


        <div class="dashboard-section-heading">


            <div>

                <h2>

                    Account Information

                </h2>


                <p>

                    Your personal account details.

                </p>

            </div>


        </div>



        <div class="dashboard-account-card">


            <!-- NAME -->

            <div
                class="dashboard-account-item"
            >

                <span
                    class="dashboard-account-label"
                >

                    Full Name

                </span>


                <strong
                    class="dashboard-account-value"
                >

                    <?= e($user['fullname']) ?>

                </strong>

            </div>



            <!-- EMAIL -->

            <div
                class="dashboard-account-item"
            >

                <span
                    class="dashboard-account-label"
                >

                    Email Address

                </span>


                <strong
                    class="dashboard-account-value"
                >

                    <?= e($user['email']) ?>

                </strong>

            </div>



            <!-- PHONE -->

            <div
                class="dashboard-account-item"
            >

                <span
                    class="dashboard-account-label"
                >

                    Phone Number

                </span>


                <strong
                    class="dashboard-account-value"
                >

                    <?= e(
                        !empty($user['phone'])
                            ? $user['phone']
                            : 'Not provided'
                    ) ?>

                </strong>

            </div>



            <!-- MEMBER SINCE -->

            <div
                class="dashboard-account-item"
            >

                <span
                    class="dashboard-account-label"
                >

                    Member Since

                </span>


                <strong
                    class="dashboard-account-value"
                >

                    <?= e(
                        format_date(
                            $user['created_at']
                        )
                    ) ?>

                </strong>

            </div>


        </div>


    </section>


</main>



<!-- ======================================================
     FOOTER
====================================================== -->

<footer class="dashboard-footer">


    <h3>

        Petition Platform

    </h3>


    <p>

        Empowering Tanzanian communities
        to make their voices heard.

    </p>


    <p class="copyright">

        &copy;

        <?= date('Y') ?>

        Petition Platform.

        All rights reserved.

    </p>


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


<?php

$stmt->close();

?>