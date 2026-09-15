<?php

require_once "includes/db.php";
require_once "includes/functions.php";
require_once "includes/auth.php";


// ======================================================
// GET ACTIVE PETITIONS
// ======================================================

$petitions = [];

$result = $conn->query(
    "SELECT
        p.id,
        p.title,
        p.description,
        p.goal,
        p.category,
        p.created_at,

        (
            SELECT COUNT(*)
            FROM signatures s
            WHERE s.petition_id = p.id
        ) AS signature_count

     FROM petitions p

     WHERE p.status = 'active'

     ORDER BY p.created_at DESC

     LIMIT 6"
);

if ($result) {

    while ($petition = $result->fetch_assoc()) {

        $petitions[] = $petition;

    }

}


// ======================================================
// PLATFORM STATISTICS
// ======================================================

$total_petitions = 0;

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM petitions"
);

if ($result) {

    $row = $result->fetch_assoc();

    $total_petitions =
        (int) ($row['total'] ?? 0);

}


$active_petitions = 0;

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM petitions
     WHERE status = 'active'"
);

if ($result) {

    $row = $result->fetch_assoc();

    $active_petitions =
        (int) ($row['total'] ?? 0);

}


$total_signatures = 0;

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM signatures"
);

if ($result) {

    $row = $result->fetch_assoc();

    $total_signatures =
        (int) ($row['total'] ?? 0);

}


$total_users = 0;

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM users"
);

if ($result) {

    $row = $result->fetch_assoc();

    $total_users =
        (int) ($row['total'] ?? 0);

}


// ======================================================
// LOGIN STATE
// ======================================================

$user_logged_in =
    is_user_logged_in();

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
        content="A community petition platform where people can discover, support and create petitions."
    >

    <title>
        Petition Platform | Your Voice Matters
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
           HOMEPAGE NAVIGATION
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
           SUPPORT TAB
        ================================================== */

        .homepage-tabs .homepage-support {

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


        .homepage-tabs .homepage-support i {

            font-size:
                12px;

        }


        .homepage-tabs .homepage-support:hover {

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
           GENERAL HOMEPAGE BACKGROUND
        ================================================== */

        body {

            background:
                #f4f7f8;

        }


        /* ==================================================
           HERO
        ================================================== */

        .home-hero {

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

            padding:
                105px 25px 125px;

            border-bottom:
                4px solid var(--gold);

        }


        .home-hero::before {

            content:
                "";

            position:
                absolute;

            width:
                420px;

            height:
                420px;

            border-radius:
                50%;

            background:
                rgba(255,255,255,0.055);

            top:
                -190px;

            right:
                -100px;

        }


        .home-hero::after {

            content:
                "";

            position:
                absolute;

            width:
                280px;

            height:
                280px;

            border-radius:
                50%;

            background:
                rgba(255,255,255,0.04);

            bottom:
                -150px;

            left:
                -80px;

        }


        .hero-content {

            position:
                relative;

            z-index:
                2;

            max-width:
                1100px;

            margin:
                0 auto;

            padding:
                0 25px;

        }


        .hero-label {

            display:
                inline-flex;

            align-items:
                center;

            gap:
                8px;

            color:
                var(--gold);

            font-size:
                13px;

            font-weight:
                800;

            text-transform:
                uppercase;

            letter-spacing:
                1.5px;

            margin-bottom:
                17px;

        }


        .hero-label::before {

            content:
                "";

            width:
                8px;

            height:
                8px;

            border-radius:
                50%;

            background:
                var(--gold);

            box-shadow:
                0 0 0 5px rgba(255,255,255,0.08);

        }


        .home-hero h1 {

            max-width:
                780px;

            color:
                #ffffff;

            font-size:
                clamp(
                    42px,
                    6vw,
                    68px
                );

            line-height:
                1.06;

            letter-spacing:
                -1.5px;

            margin:
                0 0 23px;

        }


        .hero-text {

            max-width:
                690px;

            color:
                rgba(
                    255,
                    255,
                    255,
                    0.88
                );

            font-size:
                19px;

            line-height:
                1.7;

            margin:
                0 0 34px;

        }


        /* ==================================================
           HERO BUTTONS
        ================================================== */

        .hero-buttons {

            display:
                flex;

            align-items:
                center;

            gap:
                13px;

            flex-wrap:
                wrap;

        }


        .hero-buttons .btn {

            display:
                inline-flex;

            align-items:
                center;

            justify-content:
                center;

            gap:
                8px;

            padding:
                14px 23px;

            border-radius:
                9px;

            text-decoration:
                none;

            cursor:
                pointer;

            font-weight:
                750;

            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease,
                background 0.2s ease;

        }


        .hero-buttons .btn:hover {

            transform:
                translateY(-2px);

        }


        .btn-light {

            background:
                rgba(255,255,255,0.94);

            color:
                var(--primary);

            border:
                1px solid rgba(255,255,255,0.5);

            box-shadow:
                0 10px 25px rgba(0,0,0,0.12);

        }


        .btn-light:hover {

            background:
                #ffffff;

            color:
                var(--primary-dark);

            box-shadow:
                0 14px 30px rgba(0,0,0,0.16);

        }


        .hero-buttons .btn-gold {

            background:
                var(--gold);

            color:
                #111827;

            border:
                1px solid rgba(255,255,255,0.12);

            box-shadow:
                0 10px 25px rgba(0,0,0,0.12);

        }


        .hero-buttons .btn-gold:hover {

            background:
                var(--gold-dark);

            color:
                #111827;

        }


        /* ==================================================
           GLASS STATISTICS
        ================================================== */

        .stats-wrapper {

            max-width:
                1100px;

            margin:
                -55px auto 0;

            padding:
                0 25px;

            position:
                relative;

            z-index:
                10;

        }


        .stats-grid {

            display:
                grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap:
                15px;

        }


        .stat-card {

            position:
                relative;

            overflow:
                hidden;

            background:
                rgba(255,255,255,0.78);

            backdrop-filter:
                blur(14px);

            -webkit-backdrop-filter:
                blur(14px);

            padding:
                27px 20px;

            text-align:
                center;

            border-radius:
                15px;

            border:
                1px solid rgba(255,255,255,0.9);

            box-shadow:
                0 12px 35px rgba(
                    15,
                    23,
                    42,
                    0.10
                );

            transition:
                transform 0.22s ease,
                box-shadow 0.22s ease;

        }


        .stat-card::before {

            content:
                "";

            position:
                absolute;

            top:
                0;

            left:
                18%;

            width:
                64%;

            height:
                1px;

            background:
                rgba(255,255,255,0.95);

        }


        .stat-card:hover {

            transform:
                translateY(-5px);

            box-shadow:
                0 17px 40px rgba(
                    15,
                    23,
                    42,
                    0.14
                );

        }


        .stat-icon {

            width:
                38px;

            height:
                38px;

            margin:
                0 auto 10px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            border-radius:
                11px;

            background:
                rgba(
                    0,
                    106,
                    78,
                    0.09
                );

            color:
                var(--primary);

            font-size:
                15px;

        }


        .stat-card h2 {

            color:
                var(--primary);

            font-size:
                30px;

            line-height:
                1;

            margin:
                0 0 7px;

        }


        .stat-card p {

            margin:
                0;

            font-size:
                13px;

            color:
                var(--text-muted);

            font-weight:
                600;

        }


        .stat-card.gold .stat-icon {

            background:
                rgba(218,165,32,0.12);

            color:
                var(--gold-dark);

        }


        .stat-card.gold h2 {

            color:
                var(--gold-dark);

        }


        .stat-card.blue .stat-icon {

            background:
                rgba(37,99,235,0.09);

            color:
                var(--blue-dark);

        }


        .stat-card.blue h2 {

            color:
                var(--blue-dark);

        }


        /* ==================================================
           HOME SECTIONS
        ================================================== */

        .home-section {

            max-width:
                1100px;

            margin:
                82px auto;

            padding:
                0 25px;

        }


        .section-heading {

            display:
                flex;

            align-items:
                flex-end;

            justify-content:
                space-between;

            gap:
                20px;

            margin-bottom:
                28px;

        }


        .section-heading h2 {

            margin:
                0 0 6px;

            font-size:
                30px;

            letter-spacing:
                -0.4px;

        }


        .section-heading p {

            margin:
                0;

            color:
                var(--text-muted);

        }


        .section-kicker {

            display:
                block;

            color:
                var(--primary);

            font-size:
                12px;

            font-weight:
                800;

            text-transform:
                uppercase;

            letter-spacing:
                1.2px;

            margin-bottom:
                7px;

        }


        /* ==================================================
           SECTION BUTTON
        ================================================== */

        .section-heading .btn-secondary {

            display:
                inline-flex;

            align-items:
                center;

            gap:
                7px;

            text-decoration:
                none;

            border-radius:
                9px;

            padding:
                11px 16px;

            background:
                rgba(255,255,255,0.75);

            border:
                1px solid rgba(148,163,184,0.28);

            color:
                var(--primary);

            box-shadow:
                0 5px 18px rgba(15,23,42,0.06);

            transition:
                all 0.2s ease;

        }


        .section-heading .btn-secondary:hover {

            background:
                #ffffff;

            transform:
                translateY(-2px);

            box-shadow:
                0 9px 22px rgba(15,23,42,0.10);

        }


        /* ==================================================
           PETITION GRID
        ================================================== */

        .petition-grid {

            display:
                grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap:
                20px;

        }


        /* ==================================================
           GLASS PETITION CARDS
        ================================================== */

        .petition-card {

            position:
                relative;

            overflow:
                hidden;

            display:
                flex;

            flex-direction:
                column;

            height:
                100%;

            margin:
                0;

            padding:
                23px;

            background:
                rgba(255,255,255,0.72);

            backdrop-filter:
                blur(13px);

            -webkit-backdrop-filter:
                blur(13px);

            border:
                1px solid rgba(255,255,255,0.88);

            border-radius:
                16px;

            box-shadow:
                0 10px 30px rgba(
                    15,
                    23,
                    42,
                    0.075
                );

            transition:
                transform 0.22s ease,
                box-shadow 0.22s ease,
                border-color 0.22s ease;

        }


        .petition-card::before {

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
                2px;

            background:
                linear-gradient(
                    90deg,
                    transparent,
                    rgba(0,106,78,0.35),
                    transparent
                );

        }


        .petition-card:hover {

            transform:
                translateY(-5px);

            box-shadow:
                0 18px 38px rgba(
                    15,
                    23,
                    42,
                    0.12
                );

            border-color:
                rgba(255,255,255,1);

        }


        .petition-status {

            display:
                inline-flex;

            align-items:
                center;

            gap:
                6px;

            align-self:
                flex-start;

            padding:
                6px 10px;

            border-radius:
                999px;

            background:
                rgba(220,252,231,0.82);

            color:
                #166534;

            border:
                1px solid rgba(134,239,172,0.28);

            font-size:
                11px;

            font-weight:
                800;

            margin-bottom:
                16px;

        }


        .petition-status::before {

            content:
                "";

            width:
                6px;

            height:
                6px;

            border-radius:
                50%;

            background:
                #16a34a;

        }


        .petition-category {

            display:
                inline-flex;

            align-items:
                center;

            gap:
                7px;

            color:
                var(--primary);

            font-size:
                12px;

            font-weight:
                800;

            text-transform:
                uppercase;

            letter-spacing:
                0.6px;

            margin-bottom:
                10px;

        }


        .petition-category i {

            font-size:
                12px;

        }


        .petition-card h3 {

            margin:
                0 0 10px;

            font-size:
                20px;

            line-height:
                1.3;

        }


        .petition-card h3 a {

            color:
                var(--text);

            text-decoration:
                none;

        }


        .petition-card h3 a:hover {

            color:
                var(--primary);

        }


        .petition-description {

            color:
                var(--text-muted);

            font-size:
                14px;

            line-height:
                1.65;

            margin:
                0 0 21px;

        }


        /* ==================================================
           PROGRESS
        ================================================== */

        .petition-progress {

            margin-top:
                auto;

        }


        .petition-progress-track {

            width:
                100%;

            height:
                8px;

            background:
                rgba(226,232,240,0.8);

            border-radius:
                999px;

            overflow:
                hidden;

            margin-bottom:
                10px;

        }


        .petition-progress-fill {

            height:
                100%;

            background:
                linear-gradient(
                    90deg,
                    var(--primary),
                    #16a34a
                );

            border-radius:
                999px;

        }


        .petition-progress progress {

            display:
                none;

        }


        .petition-meta {

            display:
                flex;

            justify-content:
                space-between;

            gap:
                10px;

            font-size:
                13px;

            color:
                var(--text-muted);

        }


        .petition-meta strong {

            color:
                var(--text);

        }


        .petition-percentage {

            display:
                inline-block;

            color:
                var(--primary);

            font-weight:
                800;

            font-size:
                13px;

            margin:
                8px 0 0;

        }


        .petition-card .btn {

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            gap:
                7px;

            margin-top:
                18px;

            padding:
                11px 15px;

            text-align:
                center;

            text-decoration:
                none;

            border-radius:
                9px;

            background:
                rgba(
                    0,
                    106,
                    78,
                    0.075
                );

            border:
                1px solid rgba(
                    0,
                    106,
                    78,
                    0.12
                );

            color:
                var(--primary);

            font-size:
                13px;

            font-weight:
                800;

            transition:
                all 0.2s ease;

        }


        .petition-card .btn:hover {

            background:
                var(--primary);

            color:
                #ffffff;

            transform:
                translateY(-1px);

        }


        /* ==================================================
           PETITION CATEGORIES
           NEW HOMEPAGE SECTION
        ================================================== */

        .categories-section {

            position:
                relative;

            overflow:
                hidden;

            background:
                linear-gradient(
                    135deg,
                    #ffffff,
                    #f2f8f5
                );

            border-top:
                1px solid rgba(148,163,184,0.16);

            border-bottom:
                1px solid rgba(148,163,184,0.16);

            padding:
                80px 25px;

        }


        .categories-section::before {

            content:
                "";

            position:
                absolute;

            width:
                280px;

            height:
                280px;

            border-radius:
                50%;

            background:
                rgba(0,106,78,0.045);

            top:
                -140px;

            right:
                -70px;

            pointer-events:
                none;

        }


        .categories-section::after {

            content:
                "";

            position:
                absolute;

            width:
                220px;

            height:
                220px;

            border-radius:
                50%;

            background:
                rgba(218,165,32,0.045);

            bottom:
                -120px;

            left:
                -80px;

            pointer-events:
                none;

        }


        .categories-content {

            position:
                relative;

            z-index:
                2;

            max-width:
                1100px;

            margin:
                0 auto;

        }


        .categories-header {

            display:
                flex;

            align-items:
                flex-end;

            justify-content:
                space-between;

            gap:
                25px;

            margin-bottom:
                32px;

        }


        .categories-header-text {

            max-width:
                690px;

        }


        .categories-header h2 {

            margin:
                0 0 10px;

            font-size:
                32px;

            letter-spacing:
                -0.5px;

        }


        .categories-header p {

            margin:
                0;

            color:
                var(--text-muted);

            line-height:
                1.7;

        }


        .categories-view-all {

            display:
                inline-flex;

            align-items:
                center;

            gap:
                7px;

            flex-shrink:
                0;

            padding:
                11px 16px;

            border-radius:
                9px;

            background:
                rgba(255,255,255,0.75);

            border:
                1px solid rgba(148,163,184,0.25);

            color:
                var(--primary);

            text-decoration:
                none;

            font-size:
                13px;

            font-weight:
                800;

            box-shadow:
                0 6px 18px rgba(
                    15,
                    23,
                    42,
                    0.055
                );

            transition:
                all 0.2s ease;

        }


        .categories-view-all:hover {

            background:
                #ffffff;

            transform:
                translateY(-2px);

            box-shadow:
                0 10px 24px rgba(
                    15,
                    23,
                    42,
                    0.09
                );

        }


        .categories-grid {

            display:
                grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap:
                16px;

        }


        .category-card {

            position:
                relative;

            overflow:
                hidden;

            min-height:
                150px;

            display:
                flex;

            flex-direction:
                column;

            justify-content:
                space-between;

            padding:
                22px;

            border-radius:
                16px;

            background:
                rgba(255,255,255,0.70);

            backdrop-filter:
                blur(14px);

            -webkit-backdrop-filter:
                blur(14px);

            border:
                1px solid rgba(255,255,255,0.92);

            box-shadow:
                0 9px 27px rgba(
                    15,
                    23,
                    42,
                    0.065
                );

            text-decoration:
                none;

            color:
                var(--text);

            transition:
                transform 0.22s ease,
                box-shadow 0.22s ease,
                border-color 0.22s ease;

        }


        .category-card::after {

            content:
                "";

            position:
                absolute;

            width:
                100px;

            height:
                100px;

            border-radius:
                50%;

            background:
                rgba(0,106,78,0.045);

            right:
                -45px;

            bottom:
                -45px;

            pointer-events:
                none;

        }


        .category-card:hover {

            transform:
                translateY(-5px);

            border-color:
                rgba(255,255,255,1);

            box-shadow:
                0 16px 34px rgba(
                    15,
                    23,
                    42,
                    0.11
                );

        }


        .category-icon {

            width:
                47px;

            height:
                47px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            border-radius:
                13px;

            background:
                rgba(0,106,78,0.09);

            color:
                var(--primary);

            font-size:
                18px;

            transition:
                transform 0.2s ease;

        }


        .category-card:hover
        .category-icon {

            transform:
                scale(1.05);

        }


        .category-card:nth-child(2)
        .category-icon {

            background:
                rgba(218,165,32,0.13);

            color:
                var(--gold-dark);

        }


        .category-card:nth-child(3)
        .category-icon {

            background:
                rgba(37,99,235,0.09);

            color:
                var(--blue-dark);

        }


        .category-card:nth-child(4)
        .category-icon {

            background:
                rgba(220,38,38,0.08);

            color:
                #b91c1c;

        }


        .category-card:nth-child(5)
        .category-icon {

            background:
                rgba(124,58,237,0.09);

            color:
                #6d28d9;

        }


        .category-card:nth-child(6)
        .category-icon {

            background:
                rgba(5,150,105,0.09);

            color:
                #047857;

        }


        .category-card:nth-child(7)
        .category-icon {

            background:
                rgba(234,88,12,0.09);

            color:
                #c2410c;

        }


        .category-card:nth-child(8)
        .category-icon {

            background:
                rgba(8,145,178,0.09);

            color:
                #0e7490;

        }


        .category-info {

            position:
                relative;

            z-index:
                2;

            margin-top:
                20px;

        }


        .category-info h3 {

            margin:
                0 0 5px;

            font-size:
                16px;

            line-height:
                1.3;

        }


        .category-info span {

            display:
                inline-flex;

            align-items:
                center;

            gap:
                5px;

            color:
                var(--text-muted);

            font-size:
                12px;

            font-weight:
                600;

        }


        .category-arrow {

            position:
                absolute;

            right:
                17px;

            top:
                18px;

            width:
                28px;

            height:
                28px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            border-radius:
                50%;

            background:
                rgba(255,255,255,0.70);

            color:
                var(--primary);

            font-size:
                11px;

            opacity:
                0;

            transform:
                translateX(-4px);

            transition:
                all 0.2s ease;

        }


        .category-card:hover
        .category-arrow {

            opacity:
                1;

            transform:
                translateX(0);

        }


        /* ==================================================
           WHY SECTION
        ================================================== */

        .why-section {

            position:
                relative;

            overflow:
                hidden;

            background:
                linear-gradient(
                    135deg,
                    #eef6f3,
                    #f7faf9
                );

            border-top:
                1px solid rgba(148,163,184,0.18);

            border-bottom:
                1px solid rgba(148,163,184,0.18);

            padding:
                80px 25px;

        }


        .why-content {

            max-width:
                1100px;

            margin:
                0 auto;

        }


        .why-header {

            max-width:
                700px;

            margin:
                0 auto 40px;

            text-align:
                center;

        }


        .why-header h2 {

            margin:
                0 0 10px;

            font-size:
                32px;

        }


        .why-header p {

            margin:
                0;

            color:
                var(--text-muted);

            line-height:
                1.7;

        }


        .why-grid {

            display:
                grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap:
                18px;

        }


        .why-card {

            background:
                rgba(255,255,255,0.68);

            backdrop-filter:
                blur(12px);

            -webkit-backdrop-filter:
                blur(12px);

            border:
                1px solid rgba(255,255,255,0.9);

            border-radius:
                15px;

            padding:
                28px 24px;

            box-shadow:
                0 9px 25px rgba(
                    15,
                    23,
                    42,
                    0.055
                );

            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease;

        }


        .why-card:hover {

            transform:
                translateY(-4px);

            box-shadow:
                0 14px 30px rgba(
                    15,
                    23,
                    42,
                    0.09
                );

        }


        .why-icon {

            width:
                48px;

            height:
                48px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            border-radius:
                13px;

            background:
                rgba(
                    0,
                    106,
                    78,
                    0.09
                );

            color:
                var(--primary);

            margin-bottom:
                18px;

            font-size:
                18px;

        }


        .why-card:nth-child(2) .why-icon {

            background:
                rgba(218,165,32,0.12);

            color:
                var(--gold-dark);

        }


        .why-card:nth-child(3) .why-icon {

            background:
                rgba(37,99,235,0.09);

            color:
                var(--blue-dark);

        }


        .why-card h3 {

            margin:
                0 0 8px;

            font-size:
                18px;

        }


        .why-card p {

            color:
                var(--text-muted);

            font-size:
                14px;

            line-height:
                1.65;

            margin:
                0;

        }


        /* ==================================================
           IMPACT SECTION
        ================================================== */

        .impact-section {

            max-width:
                1100px;

            margin:
                80px auto;

            padding:
                0 25px;

        }


        .impact-box {

            display:
                grid;

            grid-template-columns:
                1.1fr 0.9fr;

            gap:
                30px;

            align-items:
                center;

            background:
                rgba(255,255,255,0.74);

            backdrop-filter:
                blur(15px);

            -webkit-backdrop-filter:
                blur(15px);

            border:
                1px solid rgba(255,255,255,0.9);

            border-radius:
                18px;

            padding:
                38px;

            box-shadow:
                0 12px 35px rgba(
                    15,
                    23,
                    42,
                    0.07
                );

        }


        .impact-content .section-kicker {

            margin-bottom:
                9px;

        }


        .impact-content h2 {

            margin:
                0 0 12px;

            font-size:
                30px;

        }


        .impact-content p {

            color:
                var(--text-muted);

            line-height:
                1.7;

            margin:
                0 0 20px;

        }


        .impact-points {

            display:
                grid;

            grid-template-columns:
                1fr 1fr;

            gap:
                11px;

        }


        .impact-point {

            display:
                flex;

            align-items:
                center;

            gap:
                9px;

            font-size:
                13px;

            font-weight:
                700;

            color:
                var(--text);

        }


        .impact-point i {

            color:
                var(--primary);

        }


        .impact-visual {

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            min-height:
                220px;

            border-radius:
                16px;

            background:
                linear-gradient(
                    145deg,
                    rgba(0,106,78,0.10),
                    rgba(218,165,32,0.09)
                );

            border:
                1px solid rgba(255,255,255,0.8);

        }


        .impact-circle {

            width:
                145px;

            height:
                145px;

            border-radius:
                50%;

            display:
                flex;

            flex-direction:
                column;

            align-items:
                center;

            justify-content:
                center;

            background:
                rgba(255,255,255,0.72);

            backdrop-filter:
                blur(10px);

            -webkit-backdrop-filter:
                blur(10px);

            border:
                1px solid rgba(255,255,255,0.95);

            box-shadow:
                0 12px 30px rgba(
                    15,
                    23,
                    42,
                    0.08
                );

        }


        .impact-circle i {

            color:
                var(--primary);

            font-size:
                25px;

            margin-bottom:
                8px;

        }


        .impact-circle strong {

            color:
                var(--primary);

            font-size:
                20px;

        }


        .impact-circle span {

            color:
                var(--text-muted);

            font-size:
                11px;

            margin-top:
                2px;

        }


        /* ==================================================
           HOW IT WORKS
        ================================================== */

        .how-section {

            background:
                #ffffff;

            border-top:
                1px solid var(--border);

            border-bottom:
                1px solid var(--border);

            padding:
                80px 25px;

        }


        .how-content {

            max-width:
                1100px;

            margin:
                0 auto;

        }


        .how-header {

            text-align:
                center;

            max-width:
                650px;

            margin:
                0 auto 42px;

        }


        .how-header h2 {

            margin:
                0 0 9px;

            font-size:
                32px;

        }


        .how-header p {

            margin:
                0;

            color:
                var(--text-muted);

        }


        .how-grid {

            display:
                grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap:
                20px;

        }


        .how-card {

            position:
                relative;

            text-align:
                center;

            padding:
                31px 25px;

            background:
                rgba(248,250,252,0.82);

            border:
                1px solid var(--border);

            border-radius:
                15px;

            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease;

        }


        .how-card:hover {

            transform:
                translateY(-4px);

            box-shadow:
                0 12px 28px rgba(
                    15,
                    23,
                    42,
                    0.07
                );

        }


        .how-number {

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            width:
                55px;

            height:
                55px;

            margin:
                0 auto 18px;

            border-radius:
                50%;

            background:
                var(--primary-light);

            color:
                var(--primary);

            font-size:
                17px;

            font-weight:
                800;

        }


        .how-card:nth-child(2)
        .how-number {

            background:
                var(--gold-light);

            color:
                var(--gold-dark);

        }


        .how-card:nth-child(3)
        .how-number {

            background:
                var(--blue-light);

            color:
                var(--blue-dark);

        }


        .how-card h3 {

            margin:
                0 0 8px;

        }


        .how-card p {

            color:
                var(--text-muted);

            line-height:
                1.6;

            margin:
                0;

        }


        /* ==================================================
           COMMUNITY QUOTE
        ================================================== */

        .voice-section {

            max-width:
                900px;

            margin:
                75px auto;

            padding:
                0 25px;

            text-align:
                center;

        }


        .voice-card {

            position:
                relative;

            background:
                linear-gradient(
                    135deg,
                    rgba(0,106,78,0.07),
                    rgba(218,165,32,0.07)
                );

            border:
                1px solid rgba(255,255,255,0.9);

            border-radius:
                18px;

            padding:
                45px 35px;

            box-shadow:
                0 10px 30px rgba(
                    15,
                    23,
                    42,
                    0.05
                );

        }


        .voice-icon {

            color:
                var(--gold-dark);

            font-size:
                25px;

            margin-bottom:
                16px;

        }


        .voice-card h2 {

            max-width:
                700px;

            margin:
                0 auto 12px;

            font-size:
                28px;

            line-height:
                1.3;

        }


        .voice-card p {

            max-width:
                620px;

            margin:
                0 auto;

            color:
                var(--text-muted);

            line-height:
                1.7;

        }


        /* ==================================================
           SUPPORT INFORMATION SECTION
        ================================================== */

        .support-section {

            position:
                relative;

            overflow:
                hidden;

            max-width:
                1100px;

            margin:
                85px auto;

            padding:
                0 25px;

        }


        .support-section::before {

            content:
                "";

            position:
                absolute;

            width:
                220px;

            height:
                220px;

            border-radius:
                50%;

            background:
                rgba(0,106,78,0.055);

            top:
                -90px;

            right:
                -50px;

            pointer-events:
                none;

        }


        .support-section::after {

            content:
                "";

            position:
                absolute;

            width:
                170px;

            height:
                170px;

            border-radius:
                50%;

            background:
                rgba(218,165,32,0.055);

            bottom:
                -80px;

            left:
                -50px;

            pointer-events:
                none;

        }


        .support-header {

            position:
                relative;

            z-index:
                2;

            text-align:
                center;

            max-width:
                720px;

            margin:
                0 auto 35px;

        }


        .support-header .section-kicker {

            margin-bottom:
                9px;

        }


        .support-header h2 {

            margin:
                0 0 11px;

            font-size:
                32px;

            letter-spacing:
                -0.5px;

        }


        .support-header p {

            margin:
                0;

            color:
                var(--text-muted);

            line-height:
                1.7;

        }


        .support-panel {

            position:
                relative;

            z-index:
                2;

            display:
                grid;

            grid-template-columns:
                1fr 1fr 1fr;

            gap:
                17px;

            padding:
                18px;

            border-radius:
                20px;

            background:
                rgba(255,255,255,0.56);

            backdrop-filter:
                blur(18px);

            -webkit-backdrop-filter:
                blur(18px);

            border:
                1px solid rgba(255,255,255,0.88);

            box-shadow:
                0 18px 45px rgba(
                    15,
                    23,
                    42,
                    0.075
                );

        }


        .support-card {

            position:
                relative;

            overflow:
                hidden;

            padding:
                28px 24px;

            border-radius:
                16px;

            background:
                rgba(255,255,255,0.72);

            border:
                1px solid rgba(255,255,255,0.9);

            box-shadow:
                0 8px 25px rgba(
                    15,
                    23,
                    42,
                    0.055
                );

            transition:
                transform 0.22s ease,
                box-shadow 0.22s ease;

        }


        .support-card:hover {

            transform:
                translateY(-5px);

            box-shadow:
                0 15px 32px rgba(
                    15,
                    23,
                    42,
                    0.10
                );

        }


        .support-card::after {

            content:
                "";

            position:
                absolute;

            width:
                100px;

            height:
                100px;

            border-radius:
                50%;

            background:
                rgba(0,106,78,0.045);

            right:
                -45px;

            top:
                -45px;

        }


        .support-icon {

            width:
                50px;

            height:
                50px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            border-radius:
                14px;

            background:
                rgba(0,106,78,0.09);

            color:
                var(--primary);

            font-size:
                19px;

            margin-bottom:
                18px;

        }


        .support-card:nth-child(2)
        .support-icon {

            background:
                rgba(218,165,32,0.13);

            color:
                var(--gold-dark);

        }


        .support-card:nth-child(3)
        .support-icon {

            background:
                rgba(37,99,235,0.09);

            color:
                var(--blue-dark);

        }


        .support-card h3 {

            margin:
                0 0 9px;

            font-size:
                18px;

        }


        .support-card p {

            margin:
                0;

            color:
                var(--text-muted);

            font-size:
                14px;

            line-height:
                1.7;

        }


        .support-list {

            list-style:
                none;

            padding:
                0;

            margin:
                14px 0 0;

        }


        .support-list li {

            display:
                flex;

            align-items:
                flex-start;

            gap:
                8px;

            margin-bottom:
                9px;

            color:
                var(--text);

            font-size:
                13px;

            line-height:
                1.5;

        }


        .support-list li i {

            color:
                var(--primary);

            margin-top:
                3px;

            font-size:
                11px;

        }


        .support-bottom {

            position:
                relative;

            z-index:
                2;

            margin-top:
                18px;

            padding:
                20px 23px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                space-between;

            gap:
                20px;

            border-radius:
                15px;

            background:
                linear-gradient(
                    135deg,
                    rgba(0,106,78,0.08),
                    rgba(218,165,32,0.07)
                );

            border:
                1px solid rgba(255,255,255,0.85);

        }


        .support-bottom-content {

            display:
                flex;

            align-items:
                center;

            gap:
                13px;

        }


        .support-bottom-icon {

            width:
                42px;

            height:
                42px;

            flex-shrink:
                0;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            border-radius:
                12px;

            background:
                rgba(255,255,255,0.72);

            color:
                var(--primary);

            box-shadow:
                0 5px 15px rgba(
                    15,
                    23,
                    42,
                    0.055
                );

        }


        .support-bottom strong {

            display:
                block;

            margin-bottom:
                3px;

            font-size:
                14px;

        }


        .support-bottom span {

            color:
                var(--text-muted);

            font-size:
                13px;

        }


        .support-bottom .support-action {

            display:
                inline-flex;

            align-items:
                center;

            gap:
                7px;

            flex-shrink:
                0;

            padding:
                10px 15px;

            border-radius:
                9px;

            background:
                var(--primary);

            color:
                #ffffff;

            text-decoration:
                none;

            font-size:
                13px;

            font-weight:
                800;

            box-shadow:
                0 7px 18px rgba(
                    0,
                    106,
                    78,
                    0.16
                );

            transition:
                all 0.2s ease;

        }


        .support-bottom .support-action:hover {

            background:
                var(--primary-dark);

            transform:
                translateY(-2px);

        }


        /* ==================================================
           EMPTY PETITIONS
        ================================================== */

        .empty-petitions {

            text-align:
                center;

            padding:
                60px 25px;

            background:
                rgba(255,255,255,0.76);

            backdrop-filter:
                blur(12px);

            -webkit-backdrop-filter:
                blur(12px);

            border:
                1px solid rgba(255,255,255,0.9);

            border-radius:
                16px;

            box-shadow:
                0 10px 28px rgba(
                    15,
                    23,
                    42,
                    0.06
                );

        }


        .empty-icon {

            width:
                55px;

            height:
                55px;

            margin:
                0 auto 15px;

            border-radius:
                15px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            background:
                rgba(
                    0,
                    106,
                    78,
                    0.08
                );

            color:
                var(--primary);

        }


        .empty-petitions h3 {

            margin:
                0 0 8px;

        }


        .empty-petitions p {

            max-width:
                550px;

            margin:
                0 auto 20px;

            color:
                var(--text-muted);

        }


        /* ==================================================
           CTA
        ================================================== */

        .home-cta {

            max-width:
                1100px;

            margin:
                75px auto;

            padding:
                0 25px;

        }


        .cta-box {

            position:
                relative;

            overflow:
                hidden;

            background:
                linear-gradient(
                    135deg,
                    var(--primary),
                    var(--primary-dark)
                );

            color:
                #ffffff;

            border-radius:
                18px;

            padding:
                62px 40px;

            text-align:
                center;

            border-bottom:
                4px solid var(--gold);

            box-shadow:
                0 16px 38px rgba(
                    15,
                    23,
                    42,
                    0.12
                );

        }


        .cta-box::before {

            content:
                "";

            position:
                absolute;

            width:
                260px;

            height:
                260px;

            border-radius:
                50%;

            background:
                rgba(255,255,255,0.05);

            top:
                -150px;

            right:
                -80px;

        }


        .cta-box::after {

            content:
                "";

            position:
                absolute;

            width:
                180px;

            height:
                180px;

            border-radius:
                50%;

            background:
                rgba(255,255,255,0.035);

            bottom:
                -100px;

            left:
                -50px;

        }


        .cta-box h2 {

            position:
                relative;

            z-index:
                2;

            color:
                #ffffff;

            margin:
                0 0 11px;

            font-size:
                31px;

        }


        .cta-box p {

            position:
                relative;

            z-index:
                2;

            color:
                rgba(
                    255,
                    255,
                    255,
                    0.86
                );

            max-width:
                650px;

            margin:
                0 auto 27px;

            line-height:
                1.7;

        }


        .cta-box .btn {

            position:
                relative;

            z-index:
                2;

            display:
                inline-flex;

            align-items:
                center;

            justify-content:
                center;

            gap:
                8px;

            padding:
                13px 22px;

            text-decoration:
                none;

            border-radius:
                9px;

            font-weight:
                800;

            transition:
                transform 0.2s ease;

        }


        .cta-box .btn:hover {

            transform:
                translateY(-2px);

        }


        .cta-box .btn-gold {

            background:
                var(--gold);

            color:
                #111827;

        }


        /* ==================================================
           FOOTER
        ================================================== */

        .homepage-footer {

            background:
                #111827;

            color:
                #ffffff;

            margin-top:
                30px;

            padding:
                45px 25px;

            border-top:
                4px solid var(--gold);

            text-align:
                center;

        }


        .homepage-footer h3 {

            color:
                #ffffff;

            margin:
                0 0 8px;

        }


        .homepage-footer p {

            color:
                #cbd5e1;

        }


        .homepage-footer .copyright {

            color:
                #94a3b8;

            font-size:
                14px;

            margin-top:
                20px;

        }


        /* ==================================================
           RESPONSIVE
        ================================================== */

        @media (max-width: 1050px) {

            .homepage-tabs {

                gap:
                    15px;

            }

        }


        @media (max-width: 900px) {

            .stats-grid {

                grid-template-columns:
                    repeat(2, 1fr);

            }


            .petition-grid {

                grid-template-columns:
                    repeat(2, 1fr);

            }


            .categories-grid {

                grid-template-columns:
                    repeat(2, 1fr);

            }


            .why-grid {

                grid-template-columns:
                    1fr;

            }


            .how-grid {

                grid-template-columns:
                    1fr;

            }


            .impact-box {

                grid-template-columns:
                    1fr;

            }


            .support-panel {

                grid-template-columns:
                    1fr;

            }


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


            .homepage-navigation {

                flex-direction:
                    column;

                justify-content:
                    center;

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


            .home-hero {

                padding:
                    75px 20px 105px;

            }


            .hero-content {

                padding:
                    0;

            }


            .home-hero h1 {

                font-size:
                    43px;

                letter-spacing:
                    -1px;

            }


            .hero-text {

                font-size:
                    17px;

            }


            .stats-wrapper {

                padding:
                    0 16px;

            }


            .stats-grid {

                grid-template-columns:
                    1fr 1fr;

            }


            .home-section {

                padding:
                    0 16px;

                margin:
                    60px auto;

            }


            .section-heading {

                align-items:
                    flex-start;

                flex-direction:
                    column;

            }


            .petition-grid {

                grid-template-columns:
                    1fr;

            }


            .categories-section {

                padding:
                    65px 16px;

            }


            .categories-header {

                align-items:
                    flex-start;

                flex-direction:
                    column;

            }


            .categories-view-all {

                width:
                    100%;

                justify-content:
                    center;

                box-sizing:
                    border-box;

            }


            .categories-grid {

                grid-template-columns:
                    1fr 1fr;

                gap:
                    12px;

            }


            .category-card {

                min-height:
                    140px;

                padding:
                    18px;

            }


            .category-info {

                margin-top:
                    16px;

            }


            .category-info h3 {

                font-size:
                    14px;

            }


            .category-info span {

                font-size:
                    11px;

            }


            .why-section {

                padding:
                    65px 16px;

            }


            .impact-section {

                padding:
                    0 16px;

                margin:
                    60px auto;

            }


            .impact-box {

                padding:
                    28px 22px;

            }


            .impact-points {

                grid-template-columns:
                    1fr;

            }


            .support-section {

                padding:
                    0 16px;

                margin:
                    60px auto;

            }


            .support-panel {

                padding:
                    12px;

                gap:
                    12px;

            }


            .support-card {

                padding:
                    24px 20px;

            }


            .support-bottom {

                align-items:
                    flex-start;

                flex-direction:
                    column;

                padding:
                    18px;

            }


            .support-bottom .support-action {

                width:
                    100%;

                justify-content:
                    center;

                box-sizing:
                    border-box;

            }


            .voice-section {

                padding:
                    0 16px;

            }


            .voice-card {

                padding:
                    35px 22px;

            }


            .home-cta {

                padding:
                    0 16px;

            }


            .cta-box {

                padding:
                    45px 22px;

            }

        }


        @media (max-width: 420px) {

            .stats-grid {

                grid-template-columns:
                    1fr;

            }


            .hero-buttons {

                flex-direction:
                    column;

                align-items:
                    stretch;

            }


            .hero-buttons .btn {

                width:
                    100%;

                box-sizing:
                    border-box;

                text-align:
                    center;

            }


            .homepage-tabs {

                gap:
                    7px;

            }


            .homepage-tabs a {

                font-size:
                    13px;

            }


            .homepage-tabs .homepage-support {

                padding:
                    8px 10px;

            }


            .home-hero h1 {

                font-size:
                    39px;

            }


            .petition-card {

                padding:
                    20px;

            }


            .categories-grid {

                grid-template-columns:
                    1fr;

            }


            .categories-header h2 {

                font-size:
                    28px;

            }


            .support-header h2 {

                font-size:
                    28px;

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


        <a
            href="index.php"
            aria-current="page"
        >
            Home
        </a>


        <a
            href="./petitions.php"
        >
            Petitions
        </a>


        <a
            href="#support"
            class="homepage-support"
        >

            <i class="fa-solid fa-circle-info"></i>

            Support

        </a>


        <a href="contact.php">Contact</a>

        
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


    <div
        id="tanzania-flag"
        class="homepage-flag"
        aria-label="Tanzania Flag"
        title="Tanzania"
    ></div>


</nav>


<!-- ======================================================
     HERO
====================================================== -->

<section class="home-hero">


    <div class="hero-content">


        <span class="hero-label">

            Your Voice. Your Community. Your Impact.

        </span>


        <h1>

            Make Your Voice Heard.

        </h1>


        <p class="hero-text">

            Discover important causes, support petitions
            that matter to you, and help turn community
            voices into meaningful action.

        </p>


        <div class="hero-buttons">


            <a
                href="./petitions.php"
                class="btn btn-light"
                role="button"
            >

                <i class="fa-solid fa-compass"></i>

                Browse Petitions

            </a>


            <?php if ($user_logged_in): ?>


                <a
                    href="start_petition.php"
                    class="btn btn-gold"
                    role="button"
                >

                    <i class="fa-solid fa-plus"></i>

                    Start a Petition

                </a>


            <?php else: ?>


                <a
                    href="register.php"
                    class="btn btn-gold"
                    role="button"
                >

                    <i class="fa-solid fa-plus"></i>

                    Start a Petition

                </a>


            <?php endif; ?>


        </div>


    </div>


</section>


<!-- ======================================================
     STATISTICS
====================================================== -->

<div class="stats-wrapper">


    <div class="stats-grid">


        <div class="stat-card">

            <div class="stat-icon">

                <i class="fa-solid fa-file-signature"></i>

            </div>

            <h2>
                <?= number_format($total_petitions) ?>
            </h2>

            <p>
                Total Petitions
            </p>

        </div>


        <div class="stat-card gold">

            <div class="stat-icon">

                <i class="fa-solid fa-bullhorn"></i>

            </div>

            <h2>
                <?= number_format($active_petitions) ?>
            </h2>

            <p>
                Active Petitions
            </p>

        </div>


        <div class="stat-card blue">

            <div class="stat-icon">

                <i class="fa-solid fa-users"></i>

            </div>

            <h2>
                <?= number_format($total_signatures) ?>
            </h2>

            <p>
                Community Signatures
            </p>

        </div>


        <div class="stat-card">

            <div class="stat-icon">

                <i class="fa-solid fa-user-group"></i>

            </div>

            <h2>
                <?= number_format($total_users) ?>
            </h2>

            <p>
                Community Members
            </p>

        </div>


    </div>


</div>


<!-- ======================================================
     ACTIVE PETITIONS
====================================================== -->

<section class="home-section">


    <div class="section-heading">


        <div>

            <span class="section-kicker">
                Discover & Support
            </span>

            <h2>
                Active Petitions
            </h2>

            <p>
                Support causes that matter to you.
            </p>

        </div>


        <a
            href="./petitions.php"
            class="btn btn-secondary"
            role="button"
        >

            View All

            <i class="fa-solid fa-arrow-right"></i>

        </a>


    </div>


    <?php if (empty($petitions)): ?>


        <div class="empty-petitions">


            <div class="empty-icon">

                <i class="fa-solid fa-file-circle-plus"></i>

            </div>


            <h3>
                No active petitions yet.
            </h3>


            <p>

                Be the first to create a petition
                and bring an important issue to the community.

            </p>


            <?php if ($user_logged_in): ?>


                <a
                    href="start_petition.php"
                    class="btn"
                    role="button"
                >
                    Start a Petition
                </a>


            <?php else: ?>


                <a
                    href="register.php"
                    class="btn"
                    role="button"
                >
                    Create an Account
                </a>


            <?php endif; ?>


        </div>


    <?php else: ?>


        <div class="petition-grid">


            <?php foreach ($petitions as $petition): ?>


                <?php

                $signatures =
                    (int) $petition['signature_count'];


                $goal =
                    (int) $petition['goal'];


                $percentage = 0;


                if ($goal > 0) {

                    $percentage =
                        min(
                            100,
                            (
                                $signatures /
                                $goal
                            ) * 100
                        );

                }


                $description =
                    trim(
                        $petition['description'] ?? ''
                    );


                if ($description === '') {

                    $description =
                        "Support this important petition and help make a difference.";

                }


                if (
                    strlen($description) > 140
                ) {

                    $description =
                        substr(
                            $description,
                            0,
                            140
                        ) . "...";

                }


                $category =
                    trim(
                        $petition['category'] ?? ''
                    );


                if ($category === '') {

                    $category =
                        'Community';

                }


                ?>


                <article
                    class="petition-card"
                >


                    <span class="petition-status">

                        Active

                    </span>


                    <div class="petition-category">

                        <i class="fa-solid fa-layer-group"></i>

                        <?= e($category) ?>

                    </div>


                    <h3>

                        <a
                            href="petition.php?id=<?= (int) $petition['id'] ?>"
                        >

                            <?= e($petition['title']) ?>

                        </a>

                    </h3>


                    <p
                        class="petition-description"
                    >

                        <?= e($description) ?>

                    </p>


                    <div
                        class="petition-progress"
                    >


                        <div
                            class="petition-progress-track"
                            aria-label="Petition progress"
                        >

                            <div
                                class="petition-progress-fill"
                                style="width: <?= number_format($percentage, 2, '.', '') ?>%;"
                            ></div>

                        </div>


                        <progress
                            value="<?= e($percentage) ?>"
                            max="100"
                        ></progress>


                        <div
                            class="petition-meta"
                        >


                            <span>

                                <strong>

                                    <?= number_format($signatures) ?>

                                </strong>

                                signatures

                            </span>


                            <span>

                                Goal:

                                <strong>

                                    <?= number_format($goal) ?>

                                </strong>

                            </span>


                        </div>


                        <p
                            class="petition-percentage"
                        >

                            <?= number_format(
                                $percentage,
                                1
                            ) ?>%

                            reached

                        </p>


                        <a
                            href="petition.php?id=<?= (int) $petition['id'] ?>"
                            class="btn"
                            role="button"
                        >

                            View Petition

                            <i class="fa-solid fa-arrow-right"></i>

                        </a>


                    </div>


                </article>


            <?php endforeach; ?>


        </div>


    <?php endif; ?>


</section>


<!-- ======================================================
     PETITION CATEGORIES
     NEW
====================================================== -->

<section class="categories-section">


    <div class="categories-content">


        <div class="categories-header">


            <div class="categories-header-text">

                <span class="section-kicker">

                    Explore By Topic

                </span>


                <h2>

                    Find a Cause That Matters to You

                </h2>


                <p>

                    Explore petitions by category and discover
                    issues that are important to you, your
                    community, and Tanzania.

                </p>

            </div>


            <a
                href="./petitions.php"
                class="categories-view-all"
            >

                Browse All Petitions

                <i class="fa-solid fa-arrow-right"></i>

            </a>


        </div>


        <div class="categories-grid">


            <!-- GOVERNMENT -->

            <a
                href="petitions.php?category=Government%20%26%20Accountability"
                class="category-card"
            >

                <span class="category-arrow">

                    <i class="fa-solid fa-arrow-right"></i>

                </span>


                <div class="category-icon">

                    <i class="fa-solid fa-landmark"></i>

                </div>


                <div class="category-info">

                    <h3>

                        Government & Accountability

                    </h3>


                    <span>

                        Public leadership & governance

                    </span>

                </div>

            </a>


            <!-- EDUCATION -->

            <a
                href="petitions.php?category=Education"
                class="category-card"
            >

                <span class="category-arrow">

                    <i class="fa-solid fa-arrow-right"></i>

                </span>


                <div class="category-icon">

                    <i class="fa-solid fa-graduation-cap"></i>

                </div>


                <div class="category-info">

                    <h3>

                        Education

                    </h3>


                    <span>

                        Schools & opportunities

                    </span>

                </div>

            </a>


            <!-- ENVIRONMENT -->

            <a
                href="petitions.php?category=Environment"
                class="category-card"
            >

                <span class="category-arrow">

                    <i class="fa-solid fa-leaf"></i>

                </span>


                <div class="category-icon">

                    <i class="fa-solid fa-seedling"></i>

                </div>


                <div class="category-info">

                    <h3>

                        Environment

                    </h3>


                    <span>

                        Nature & climate

                    </span>

                </div>

            </a>


            <!-- HEALTH -->

            <a
                href="petitions.php?category=Health"
                class="category-card"
            >

                <span class="category-arrow">

                    <i class="fa-solid fa-arrow-right"></i>

                </span>


                <div class="category-icon">

                    <i class="fa-solid fa-heart-pulse"></i>

                </div>


                <div class="category-info">

                    <h3>

                        Health

                    </h3>


                    <span>

                        Healthcare & wellbeing

                    </span>

                </div>

            </a>


            <!-- JUSTICE -->

            <a
                href="petitions.php?category=Justice%20%26%20Rights"
                class="category-card"
            >

                <span class="category-arrow">

                    <i class="fa-solid fa-arrow-right"></i>

                </span>


                <div class="category-icon">

                    <i class="fa-solid fa-scale-balanced"></i>

                </div>


                <div class="category-info">

                    <h3>

                        Justice & Rights

                    </h3>


                    <span>

                        Rights & fairness

                    </span>

                </div>

            </a>


            <!-- COMMUNITY -->

            <a
                href="petitions.php?category=Community"
                class="category-card"
            >

                <span class="category-arrow">

                    <i class="fa-solid fa-arrow-right"></i>

                </span>


                <div class="category-icon">

                    <i class="fa-solid fa-people-group"></i>

                </div>


                <div class="category-info">

                    <h3>

                        Community

                    </h3>


                    <span>

                        Local community issues

                    </span>

                </div>

            </a>


            <!-- EMPLOYMENT -->

            <a
                href="petitions.php?category=Employment"
                class="category-card"
            >

                <span class="category-arrow">

                    <i class="fa-solid fa-arrow-right"></i>

                </span>


                <div class="category-icon">

                    <i class="fa-solid fa-briefcase"></i>

                </div>


                <div class="category-info">

                    <h3>

                        Employment

                    </h3>


                    <span>

                        Jobs & workers

                    </span>

                </div>

            </a>


            <!-- INFRASTRUCTURE -->

            <a
                href="petitions.php?category=Infrastructure"
                class="category-card"
            >

                <span class="category-arrow">

                    <i class="fa-solid fa-arrow-right"></i>

                </span>


                <div class="category-icon">

                    <i class="fa-solid fa-road"></i>

                </div>


                <div class="category-info">

                    <h3>

                        Infrastructure

                    </h3>


                    <span>

                        Roads & public services

                    </span>

                </div>

            </a>


        </div>


    </div>


</section>


<!-- ======================================================
     WHY YOUR VOICE MATTERS
====================================================== -->

<section class="why-section">


    <div class="why-content">


        <div class="why-header">

            <span class="section-kicker">
                Built For Communities
            </span>

            <h2>
                One Voice Can Start Something Bigger
            </h2>

            <p>

                Petition Platform gives people a simple
                way to bring attention to issues, gather
                support, and create a stronger collective voice.

            </p>

        </div>


        <div class="why-grid">


            <article class="why-card">


                <div class="why-icon">

                    <i class="fa-solid fa-microphone"></i>

                </div>


                <h3>
                    Speak Up
                </h3>


                <p>

                    Put important issues in front of your
                    community and give people a place to
                    understand what needs to change.

                </p>


            </article>


            <article class="why-card">


                <div class="why-icon">

                    <i class="fa-solid fa-handshake"></i>

                </div>


                <h3>
                    Stand Together
                </h3>


                <p>

                    A single signature becomes more powerful
                    when people come together behind the same cause.

                </p>


            </article>


            <article class="why-card">


                <div class="why-icon">

                    <i class="fa-solid fa-chart-line"></i>

                </div>


                <h3>
                    Create Impact
                </h3>


                <p>

                    Turn public support into visibility and
                    help important community concerns gain momentum.

                </p>


            </article>


        </div>


    </div>


</section>


<!-- ======================================================
     COMMUNITY IMPACT
====================================================== -->

<section class="impact-section">


    <div class="impact-box">


        <div class="impact-content">


            <span class="section-kicker">
                Community Power
            </span>


            <h2>
                Change Starts With Participation
            </h2>


            <p>

                Whether you're supporting an existing cause
                or starting one of your own, every action adds
                to the conversation and helps communities
                make their priorities visible.

            </p>


            <div class="impact-points">


                <div class="impact-point">

                    <i class="fa-solid fa-check"></i>

                    Discover important causes

                </div>


                <div class="impact-point">

                    <i class="fa-solid fa-check"></i>

                    Add your signature

                </div>


                <div class="impact-point">

                    <i class="fa-solid fa-check"></i>

                    Build community support

                </div>


                <div class="impact-point">

                    <i class="fa-solid fa-check"></i>

                    Start your own petition

                </div>


            </div>


        </div>


        <div class="impact-visual">


            <div class="impact-circle">


                <i class="fa-solid fa-people-group"></i>


                <strong>

                    <?= number_format($total_users) ?>

                </strong>


                <span>
                    Community Members
                </span>


            </div>


        </div>


    </div>


</section>


<!-- ======================================================
     HOW IT WORKS
====================================================== -->

<section class="how-section">


    <div class="how-content">


        <div class="how-header">


            <span class="section-kicker">
                Simple & Powerful
            </span>


            <h2>
                How It Works
            </h2>


            <p>

                Turning individual voices into collective action
                is simple.

            </p>


        </div>


        <div class="how-grid">


            <article class="how-card">


                <div class="how-number">
                    01
                </div>


                <h3>
                    Discover
                </h3>


                <p>

                    Explore petitions and find causes
                    that matter to you and your community.

                </p>


            </article>


            <article class="how-card">


                <div class="how-number">
                    02
                </div>


                <h3>
                    Support
                </h3>


                <p>

                    Add your signature and stand together
                    with others behind an important cause.

                </p>


            </article>


            <article class="how-card">


                <div class="how-number">
                    03
                </div>


                <h3>
                    Create
                </h3>


                <p>

                    Start your own petition and bring
                    people together around an issue.

                </p>


            </article>


        </div>


    </div>


</section>


<!-- ======================================================
     SUPPORT & INFORMATION
====================================================== -->

<section
    class="support-section"
    id="support"
>


    <div class="support-header">


        <span class="section-kicker">

            Need a Little Help?

        </span>


        <h2>

            Support & Information

        </h2>


        <p>

            Everything you need to understand how
            Petition Platform works and how you can
            make your voice count.

        </p>


    </div>


    <div class="support-panel">


        <article class="support-card">


            <div class="support-icon">

                <i class="fa-solid fa-circle-question"></i>

            </div>


            <h3>

                How Can I Help?

            </h3>


            <p>

                You don't need to start a petition to
                make an impact. You can support causes
                already being raised by the community.

            </p>


            <ul class="support-list">


                <li>

                    <i class="fa-solid fa-check"></i>

                    Browse active petitions.

                </li>


                <li>

                    <i class="fa-solid fa-check"></i>

                    Read the issue carefully.

                </li>


                <li>

                    <i class="fa-solid fa-check"></i>

                    Add your signature.

                </li>


            </ul>


        </article>


        <article class="support-card">


            <div class="support-icon">

                <i class="fa-solid fa-shield-heart"></i>

            </div>


            <h3>

                Support Responsibly

            </h3>


            <p>

                Your signature represents your support
                for a cause. Take a moment to understand
                what you're supporting before signing.

            </p>


            <ul class="support-list">


                <li>

                    <i class="fa-solid fa-check"></i>

                    Check the petition details.

                </li>


                <li>

                    <i class="fa-solid fa-check"></i>

                    Keep your account information secure.

                </li>


                <li>

                    <i class="fa-solid fa-check"></i>

                    Respect other community members.

                </li>


            </ul>


        </article>


        <article class="support-card">


            <div class="support-icon">

                <i class="fa-solid fa-lightbulb"></i>

            </div>


            <h3>

                Want to Start a Cause?

            </h3>


            <p>

                If something important needs attention,
                you can create a petition and invite
                your community to stand with you.

            </p>


            <ul class="support-list">


                <li>

                    <i class="fa-solid fa-check"></i>

                    Explain the issue clearly.

                </li>


                <li>

                    <i class="fa-solid fa-check"></i>

                    Set a realistic signature goal.

                </li>


                <li>

                    <i class="fa-solid fa-check"></i>

                    Share your petition responsibly.

                </li>


            </ul>


        </article>


    </div>


    <div class="support-bottom">


        <div class="support-bottom-content">


            <div class="support-bottom-icon">

                <i class="fa-solid fa-users"></i>

            </div>


            <div>

                <strong>

                    Every signature adds to the conversation.

                </strong>


                <span>

                    Discover a cause, support your community,
                    and help important issues gain visibility.

                </span>

            </div>


        </div>


        <a
            href="./petitions.php"
            class="support-action"
        >

            Explore Petitions

            <i class="fa-solid fa-arrow-right"></i>

        </a>


    </div>


</section>


<!-- ======================================================
     VOICE MESSAGE
====================================================== -->

<section class="voice-section">


    <div class="voice-card">


        <div class="voice-icon">

            <i class="fa-solid fa-quote-left"></i>

        </div>


        <h2>

            Your voice doesn't have to be the loudest.
            It just needs to be heard.

        </h2>


        <p>

            Find your cause. Add your voice.
            Help your community move forward.

        </p>


    </div>


</section>


<!-- ======================================================
     CALL TO ACTION
====================================================== -->

<section class="home-cta">


    <div class="cta-box">


        <h2>

            Have Something Important to Say?

        </h2>


        <p>

            Your idea could be the beginning of meaningful
            change. Create a petition and give your community
            a chance to stand with you.

        </p>


        <?php if ($user_logged_in): ?>


            <a
                href="start_petition.php"
                class="btn btn-gold"
                role="button"
            >

                <i class="fa-solid fa-pen-to-square"></i>

                Start Your Petition

            </a>


        <?php else: ?>


            <a
                href="register.php"
                class="btn btn-gold"
                role="button"
            >

                <i class="fa-solid fa-user-plus"></i>

                Join the Community

            </a>


        <?php endif; ?>


    </div>


</section>


<!-- ======================================================
     FOOTER
====================================================== -->

<footer class="homepage-footer">


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