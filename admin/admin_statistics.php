<?php

require_once "../includes/db.php";
require_once "../includes/functions.php";
require_once "../includes/auth.php";

require_admin();


// ======================================================
// STATISTICS
// ======================================================

$total_users = 0;
$total_petitions = 0;
$total_active_petitions = 0;
$total_draft_petitions = 0;
$total_closed_petitions = 0;
$total_signatures = 0;

$total_messages = 0;
$total_unread_messages = 0;

$total_countries = 0;
$top_country = "None";
$top_country_users = 0;


// ======================================================
// TOTAL USERS
// ======================================================

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM users"
);

if ($result) {

    $row = $result->fetch_assoc();

    $total_users = (int) $row['total'];
}


// ======================================================
// TOTAL PETITIONS
// ======================================================

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM petitions"
);

if ($result) {

    $row = $result->fetch_assoc();

    $total_petitions = (int) $row['total'];
}


// ======================================================
// ACTIVE PETITIONS
// ======================================================

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM petitions
     WHERE status = 'active'"
);

if ($result) {

    $row = $result->fetch_assoc();

    $total_active_petitions = (int) $row['total'];
}


// ======================================================
// DRAFT PETITIONS
// ======================================================

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM petitions
     WHERE status = 'draft'"
);

if ($result) {

    $row = $result->fetch_assoc();

    $total_draft_petitions = (int) $row['total'];
}


// ======================================================
// CLOSED PETITIONS
// ======================================================

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM petitions
     WHERE status = 'closed'"
);

if ($result) {

    $row = $result->fetch_assoc();

    $total_closed_petitions = (int) $row['total'];
}


// ======================================================
// TOTAL SIGNATURES
// ======================================================

$result = $conn->query(
    "SHOW TABLES LIKE 'signatures'"
);

if ($result && $result->num_rows > 0) {

    $result = $conn->query(
        "SELECT COUNT(*) AS total
         FROM signatures"
    );

    if ($result) {

        $row = $result->fetch_assoc();

        $total_signatures = (int) $row['total'];
    }
}


// ======================================================
// TOTAL MESSAGES
// ======================================================

$result = $conn->query(
    "SHOW TABLES LIKE 'messages'"
);

if ($result && $result->num_rows > 0) {

    $result = $conn->query(
        "SELECT COUNT(*) AS total
         FROM messages"
    );

    if ($result) {

        $row = $result->fetch_assoc();

        $total_messages = (int) $row['total'];
    }


    // ==================================================
    // UNREAD MESSAGES
    // ==================================================

    $result = $conn->query(
        "SELECT COUNT(*) AS total
         FROM messages
         WHERE status = 'unread'"
    );

    if ($result) {

        $row = $result->fetch_assoc();

        $total_unread_messages = (int) $row['total'];
    }
}


// ======================================================
// USERS BY COUNTRY
// ======================================================

$country_labels = [];
$country_values = [];

$result = $conn->query(
    "SELECT
        country,
        COUNT(*) AS total
     FROM users
     WHERE country IS NOT NULL
       AND TRIM(country) != ''
     GROUP BY country
     ORDER BY total DESC, country ASC"
);

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $country_labels[] = $row['country'];

        $country_values[] = (int) $row['total'];
    }
}


// ======================================================
// COUNTRY SUMMARY
// ======================================================

$total_countries = count($country_labels);

if (!empty($country_labels)) {

    $top_country = $country_labels[0];

    $top_country_users = $country_values[0];
}


// ======================================================
// MAXIMUM CHART ITEMS
// ======================================================

// Keep the main chart readable when the platform
// contains many countries.

$chart_labels = $country_labels;
$chart_values = $country_values;

$remaining_users = 0;

if (count($country_labels) > 12) {

    $chart_labels = array_slice(
        $country_labels,
        0,
        11
    );

    $chart_values = array_slice(
        $country_values,
        0,
        11
    );

    $remaining_users = array_sum(
        array_slice(
            $country_values,
            11
        )
    );

    if ($remaining_users > 0) {

        $chart_labels[] = "Other Countries";

        $chart_values[] = $remaining_users;
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
        Statistics - Admin Panel
    </title>

    <link
        rel="stylesheet"
        href="css/admin.css"
    >

    <!-- FONT AWESOME -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


    <style>

        /* ==================================================
           STATISTICS PAGE
        ================================================== */

        .statistics-page {
            width: 100%;
        }


        /* ==================================================
           SIDEBAR ICONS
        ================================================== */

        .admin-sidebar a i {
            width: 22px;
            min-width: 22px;
            text-align: center;
            margin-right: 8px;
            transition: transform .2s ease;
        }

        .admin-sidebar a:hover i {
            transform: translateX(2px);
        }

        .admin-sidebar a .icon-dashboard {
            color: #3b82f6;
        }

        .admin-sidebar a .icon-petitions {
            color: #22c55e;
        }

        .admin-sidebar a .icon-users {
            color: #38bdf8;
        }

        .admin-sidebar a .icon-messages {
            color: #f59e0b;
        }

        .admin-sidebar a .icon-signatures {
            color: #a78bfa;
        }

        .admin-sidebar a .icon-statistics {
            color: #06b6d4;
        }

        .admin-sidebar a .icon-settings {
            color: #94a3b8;
        }

        .admin-sidebar a .icon-logout {
            color: #ef4444;
        }


        /* ==================================================
           PAGE TITLE ICON
        ================================================== */

        .page-title-icon {
            color: #006b3f;
            margin-right: 8px;
        }

        .admin-user i {
            color: #006b3f;
            margin-right: 7px;
        }


        /* ==================================================
           PAGE HEADER
        ================================================== */

        .statistics-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 28px;
        }

        .statistics-eyebrow {
            margin-bottom: 7px;
            color: #6b7280;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1.4px;
        }

        .statistics-eyebrow i {
            color: #006b3f;
            margin-right: 6px;
        }

        .statistics-header h1 {
            margin: 0;
            color: #111827;
            font-size: 30px;
            font-weight: 800;
        }

        .statistics-header p {
            margin: 8px 0 0;
            color: #6b7280;
            font-size: 14px;
            line-height: 1.6;
        }


        /* ==================================================
           OVERVIEW GRID
        ================================================== */

        .statistics-overview {
            display: grid;
            grid-template-columns:
                repeat(4, minmax(0, 1fr));
            gap: 17px;
            margin-bottom: 24px;
        }

        .statistics-card {
            position: relative;
            overflow: hidden;
            min-height: 150px;
            padding: 21px;
            border: 1px solid rgba(255,255,255,.7);
            border-radius: 20px;
            background:
                linear-gradient(
                    145deg,
                    rgba(255,255,255,.92),
                    rgba(248,250,252,.82)
                );
            box-shadow:
                0 10px 35px rgba(15,23,42,.06),
                inset 0 1px 0 rgba(255,255,255,.9);
            text-decoration: none;
            transition:
                transform .2s ease,
                box-shadow .2s ease,
                border-color .2s ease;
        }

        .statistics-card::after {
            content: "";
            position: absolute;
            width: 100px;
            height: 100px;
            right: -35px;
            bottom: -45px;
            border-radius: 50%;
            background: rgba(0,107,63,.055);
            pointer-events: none;
        }

        .statistics-card:hover {
            transform: translateY(-4px);
            border-color: rgba(0,107,63,.16);
            box-shadow:
                0 18px 42px rgba(15,23,42,.10),
                inset 0 1px 0 rgba(255,255,255,.95);
        }

        .statistics-card-icon {
            width: 43px;
            height: 43px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            border-radius: 13px;
            font-size: 19px;
        }

        .statistics-card-icon.users {
            color: #38bdf8;
            background: rgba(56,189,248,.10);
        }

        .statistics-card-icon.petitions {
            color: #22c55e;
            background: rgba(34,197,94,.10);
        }

        .statistics-card-icon.signatures {
            color: #a78bfa;
            background: rgba(167,139,250,.12);
        }

        .statistics-card-icon.messages {
            color: #f59e0b;
            background: rgba(245,158,11,.12);
        }

        .statistics-card-title {
            color: #6b7280;
            font-size: 12px;
            font-weight: 700;
        }

        .statistics-card-number {
            margin-top: 6px;
            color: #111827;
            font-size: 28px;
            line-height: 1;
            font-weight: 850;
        }

        .statistics-card-link {
            margin-top: 12px;
            color: #006b3f;
            font-size: 11px;
            font-weight: 800;
        }


        /* ==================================================
           MAIN GRID
        ================================================== */

        .statistics-main-grid {
            display: grid;
            grid-template-columns:
                minmax(0, 1.65fr)
                minmax(280px, .85fr);
            gap: 22px;
            margin-bottom: 22px;
        }


        /* ==================================================
           GLASS PANEL
        ================================================== */

        .statistics-panel {
            overflow: hidden;
            border: 1px solid rgba(255,255,255,.78);
            border-radius: 22px;
            background:
                linear-gradient(
                    145deg,
                    rgba(255,255,255,.94),
                    rgba(248,250,252,.84)
                );
            box-shadow:
                0 12px 40px rgba(15,23,42,.065),
                inset 0 1px 0 rgba(255,255,255,.95);
            backdrop-filter: blur(12px);
        }

        .statistics-panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            padding: 22px 23px 18px;
            border-bottom: 1px solid rgba(229,231,235,.75);
        }

        .statistics-panel-label {
            margin-bottom: 5px;
            color: #006b3f;
            font-size: 10px;
            font-weight: 850;
            letter-spacing: 1.1px;
        }

        .statistics-panel-label i {
            margin-right: 5px;
        }

        .statistics-panel-header h2 {
            margin: 0;
            color: #111827;
            font-size: 18px;
            font-weight: 800;
        }

        .statistics-panel-header p {
            margin: 6px 0 0;
            color: #6b7280;
            font-size: 12px;
        }


        /* ==================================================
           PETITION STATUS
        ================================================== */

        .petition-status-list {
            padding: 20px 23px 23px;
        }

        .petition-status-item {
            margin-bottom: 20px;
        }

        .petition-status-item:last-child {
            margin-bottom: 0;
        }

        .petition-status-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 8px;
        }

        .petition-status-name {
            color: #374151;
            font-size: 13px;
            font-weight: 700;
        }

        .petition-status-name i {
            width: 18px;
            margin-right: 5px;
        }

        .petition-status-name .active-icon {
            color: #22c55e;
        }

        .petition-status-name .draft-icon {
            color: #f59e0b;
        }

        .petition-status-name .closed-icon {
            color: #64748b;
        }

        .petition-status-number {
            color: #111827;
            font-size: 13px;
            font-weight: 800;
        }

        .petition-status-bar {
            width: 100%;
            height: 9px;
            overflow: hidden;
            border-radius: 20px;
            background: #eef1f4;
        }

        .petition-status-fill {
            height: 100%;
            min-width: 3px;
            border-radius: inherit;
            background: #006b3f;
        }

        .petition-status-fill.draft {
            background: #f59e0b;
        }

        .petition-status-fill.closed {
            background: #64748b;
        }

        .petition-status-total {
            margin-top: 21px;
            padding-top: 17px;
            border-top: 1px solid #eef0f2;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .petition-status-total span {
            color: #6b7280;
            font-size: 12px;
            font-weight: 600;
        }

        .petition-status-total strong {
            color: #111827;
            font-size: 17px;
            font-weight: 850;
        }


        /* ==================================================
           COUNTRY PANEL
        ================================================== */

        .country-panel {
            display: block;
            color: inherit;
            text-decoration: none;
        }

        .country-summary {
            display: grid;
            grid-template-columns:
                repeat(3, 1fr);
            border-bottom: 1px solid #eef0f2;
        }

        .country-summary-item {
            padding: 18px 17px;
            border-right: 1px solid #eef0f2;
        }

        .country-summary-item:last-child {
            border-right: none;
        }

        .country-summary-item span {
            display: block;
            margin-bottom: 5px;
            color: #9ca3af;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .country-summary-item span i {
            margin-right: 4px;
            color: #006b3f;
        }

        .country-summary-item strong {
            display: block;
            color: #111827;
            font-size: 17px;
            font-weight: 850;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .country-chart-wrap {
            position: relative;
            height: 330px;
            padding: 20px 23px;
        }

        .country-chart-wrap canvas {
            width: 100% !important;
            height: 100% !important;
        }

        .country-empty {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
            font-size: 13px;
        }

        .country-empty i {
            margin-right: 7px;
            color: #06b6d4;
        }

        .country-footer {
            padding: 15px 23px;
            border-top: 1px solid #eef0f2;
            color: #006b3f;
            font-size: 12px;
            font-weight: 800;
        }

        .country-footer i {
            margin-left: 5px;
        }

        .country-panel:hover .country-footer {
            background: rgba(0,107,63,.035);
        }


        /* ==================================================
           ACTIVITY PANEL
        ================================================== */

        .activity-panel {
            margin-bottom: 22px;
        }

        .activity-grid {
            display: grid;
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 21px 23px;
            border-right: 1px solid #eef0f2;
        }

        .activity-item:nth-child(2n) {
            border-right: none;
        }

        .activity-item:nth-child(n + 3) {
            border-top: 1px solid #eef0f2;
        }

        .activity-icon {
            width: 43px;
            height: 43px;
            min-width: 43px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 13px;
            background: #f3f6f4;
            font-size: 18px;
        }

        .activity-icon.users {
            color: #38bdf8;
            background: rgba(56,189,248,.10);
        }

        .activity-icon.petitions {
            color: #22c55e;
            background: rgba(34,197,94,.10);
        }

        .activity-icon.active {
            color: #16a34a;
            background: rgba(34,197,94,.10);
        }

        .activity-icon.signatures {
            color: #a78bfa;
            background: rgba(167,139,250,.12);
        }

        .activity-icon.messages {
            color: #f59e0b;
            background: rgba(245,158,11,.12);
        }

        .activity-icon.countries {
            color: #06b6d4;
            background: rgba(6,182,212,.10);
        }

        .activity-label {
            color: #6b7280;
            font-size: 11px;
            font-weight: 700;
        }

        .activity-number {
            margin-top: 3px;
            color: #111827;
            font-size: 21px;
            font-weight: 850;
        }

        .activity-link {
            margin-left: auto;
            color: #006b3f;
            font-size: 11px;
            font-weight: 800;
            text-decoration: none;
        }

        .activity-link i {
            margin-left: 4px;
        }


        /* ==================================================
           RESPONSIVE
        ================================================== */

        @media (max-width: 1100px) {

            .statistics-overview {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }

            .statistics-main-grid {
                grid-template-columns: 1fr;
            }

        }


        @media (max-width: 650px) {

            .statistics-header {
                align-items: flex-start;
            }

            .statistics-header h1 {
                font-size: 25px;
            }

            .statistics-overview {
                grid-template-columns: 1fr;
            }

            .statistics-panel-header {
                padding: 19px;
            }

            .petition-status-list {
                padding: 18px 19px 20px;
            }

            .country-summary {
                grid-template-columns: 1fr;
            }

            .country-summary-item {
                border-right: none;
                border-bottom: 1px solid #eef0f2;
            }

            .country-summary-item:last-child {
                border-bottom: none;
            }

            .country-chart-wrap {
                height: 300px;
                padding: 17px;
            }

            .activity-grid {
                grid-template-columns: 1fr;
            }

            .activity-item,
            .activity-item:nth-child(2n) {
                border-right: none;
            }

            .activity-item:nth-child(n + 2) {
                border-top: 1px solid #eef0f2;
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
            <span>Dashboard</span>
        </a>


        <div class="admin-menu-title">
            Management
        </div>


        <a href="admin_petitions.php">
            <i class="fa-solid fa-file-signature icon-petitions"></i>
            <span>Petitions</span>
        </a>


        <a href="admin_users.php">
            <i class="fa-solid fa-users icon-users"></i>
            <span>Users</span>
        </a>


        <a href="admin_messages.php">
            <i class="fa-solid fa-comments icon-messages"></i>
            <span>Messages</span>
        </a>


        <a href="admin_signatures.php">
            <i class="fa-solid fa-pen-nib icon-signatures"></i>
            <span>Signatures</span>
        </a>


        <div class="admin-menu-title">
            Analytics
        </div>


        <a
            href="admin_statistics.php"
            class="active"
        >
            <i class="fa-solid fa-chart-line icon-statistics"></i>
            <span>Statistics</span>
        </a>


        <div class="admin-menu-title">
            System
        </div>


        <a href="admin_settings.php">
            <i class="fa-solid fa-gear icon-settings"></i>
            <span>Settings</span>
        </a>


        <a
            href="admin_logout.php"
            class="admin-logout"
        >
            <i class="fa-solid fa-right-from-bracket icon-logout"></i>
            <span>Logout</span>
        </a>


    </aside>


    <!-- ==================================================
         MAIN
    ================================================== -->

    <main class="admin-main">


        <!-- TOPBAR -->

        <header class="admin-topbar">

            <div>

                <h2>
                    <i class="fa-solid fa-chart-line page-title-icon"></i>
                    Statistics
                </h2>

            </div>


            <div class="admin-user">

                <i class="fa-solid fa-user-shield"></i>
                Administrator

            </div>

        </header>


        <!-- CONTENT -->

        <div class="admin-content statistics-page">


            <!-- ==================================================
                 HEADER
            ================================================== -->

            <div class="statistics-header">

                <div>

                    <div class="statistics-eyebrow">
                        <i class="fa-solid fa-chart-line"></i>
                        ANALYTICS & INSIGHTS
                    </div>

                    <h1>
                        Platform Statistics
                    </h1>

                    <p>
                        Monitor users, petitions, signatures,
                        messages and platform activity.
                    </p>

                </div>

            </div>


            <!-- ==================================================
                 OVERVIEW
            ================================================== -->

            <div class="statistics-overview">


                <!-- USERS -->

                <a
                    href="admin_users.php"
                    class="statistics-card"
                >

                    <div class="statistics-card-icon users">
                        <i class="fa-solid fa-users"></i>
                    </div>

                    <div class="statistics-card-title">
                        Registered Users
                    </div>

                    <div class="statistics-card-number">
                        <?= number_format($total_users) ?>
                    </div>

                    <div class="statistics-card-link">
                        Manage Users
                        <i class="fa-solid fa-arrow-right"></i>
                    </div>

                </a>


                <!-- PETITIONS -->

                <a
                    href="admin_petitions.php"
                    class="statistics-card"
                >

                    <div class="statistics-card-icon petitions">
                        <i class="fa-solid fa-file-signature"></i>
                    </div>

                    <div class="statistics-card-title">
                        Total Petitions
                    </div>

                    <div class="statistics-card-number">
                        <?= number_format($total_petitions) ?>
                    </div>

                    <div class="statistics-card-link">
                        Manage Petitions
                        <i class="fa-solid fa-arrow-right"></i>
                    </div>

                </a>


                <!-- SIGNATURES -->

                <a
                    href="admin_signatures.php"
                    class="statistics-card"
                >

                    <div class="statistics-card-icon signatures">
                        <i class="fa-solid fa-pen-nib"></i>
                    </div>

                    <div class="statistics-card-title">
                        Total Signatures
                    </div>

                    <div class="statistics-card-number">
                        <?= number_format($total_signatures) ?>
                    </div>

                    <div class="statistics-card-link">
                        View Signatures
                        <i class="fa-solid fa-arrow-right"></i>
                    </div>

                </a>


                <!-- MESSAGES -->

                <a
                    href="admin_messages.php"
                    class="statistics-card"
                >

                    <div class="statistics-card-icon messages">
                        <i class="fa-solid fa-comments"></i>
                    </div>

                    <div class="statistics-card-title">
                        Unread Messages
                    </div>

                    <div class="statistics-card-number">
                        <?= number_format($total_unread_messages) ?>
                    </div>

                    <div class="statistics-card-link">
                        <?= number_format($total_messages) ?>
                        total messages
                        <i class="fa-solid fa-arrow-right"></i>
                    </div>

                </a>


            </div>


            <!-- ==================================================
                 MAIN STATISTICS
            ================================================== -->

            <div class="statistics-main-grid">


                <!-- ==================================================
                     PETITION STATUS
                ================================================== -->

                <section class="statistics-panel">


                    <div class="statistics-panel-header">

                        <div>

                            <div class="statistics-panel-label">
                                <i class="fa-solid fa-file-signature"></i>
                                PETITIONS
                            </div>

                            <h2>
                                Petition Status
                            </h2>

                            <p>
                                Current distribution of petition statuses.
                            </p>

                        </div>

                    </div>


                    <?php

                    $petition_total_for_chart =
                        $total_active_petitions
                        + $total_draft_petitions
                        + $total_closed_petitions;

                    ?>


                    <div class="petition-status-list">


                        <!-- ACTIVE -->

                        <div class="petition-status-item">

                            <div class="petition-status-top">

                                <span class="petition-status-name">

                                    <i class="fa-solid fa-circle-check active-icon"></i>

                                    Active Petitions

                                </span>

                                <span class="petition-status-number">
                                    <?= number_format(
                                        $total_active_petitions
                                    ) ?>
                                </span>

                            </div>

                            <div class="petition-status-bar">

                                <?php

                                $active_percent =
                                    $petition_total_for_chart > 0
                                        ? (
                                            $total_active_petitions
                                            / $petition_total_for_chart
                                        ) * 100
                                        : 0;

                                ?>

                                <div
                                    class="petition-status-fill"
                                    style="width: <?= $active_percent ?>%;"
                                ></div>

                            </div>

                        </div>


                        <!-- DRAFT -->

                        <div class="petition-status-item">

                            <div class="petition-status-top">

                                <span class="petition-status-name">

                                    <i class="fa-solid fa-file-circle-plus draft-icon"></i>

                                    Draft Petitions

                                </span>

                                <span class="petition-status-number">
                                    <?= number_format(
                                        $total_draft_petitions
                                    ) ?>
                                </span>

                            </div>

                            <div class="petition-status-bar">

                                <?php

                                $draft_percent =
                                    $petition_total_for_chart > 0
                                        ? (
                                            $total_draft_petitions
                                            / $petition_total_for_chart
                                        ) * 100
                                        : 0;

                                ?>

                                <div
                                    class="petition-status-fill draft"
                                    style="width: <?= $draft_percent ?>%;"
                                ></div>

                            </div>

                        </div>


                        <!-- CLOSED -->

                        <div class="petition-status-item">

                            <div class="petition-status-top">

                                <span class="petition-status-name">

                                    <i class="fa-solid fa-circle-xmark closed-icon"></i>

                                    Closed Petitions

                                </span>

                                <span class="petition-status-number">
                                    <?= number_format(
                                        $total_closed_petitions
                                    ) ?>
                                </span>

                            </div>

                            <div class="petition-status-bar">

                                <?php

                                $closed_percent =
                                    $petition_total_for_chart > 0
                                        ? (
                                            $total_closed_petitions
                                            / $petition_total_for_chart
                                        ) * 100
                                        : 0;

                                ?>

                                <div
                                    class="petition-status-fill closed"
                                    style="width: <?= $closed_percent ?>%;"
                                ></div>

                            </div>

                        </div>


                        <!-- TOTAL -->

                        <div class="petition-status-total">

                            <span>
                                Total petitions
                            </span>

                            <strong>
                                <?= number_format(
                                    $total_petitions
                                ) ?>
                            </strong>

                        </div>


                    </div>


                </section>


                <!-- ==================================================
                     COUNTRY
                ================================================== -->

                <a
                    href="admin_country_statistics.php"
                    class="statistics-panel country-panel"
                >


                    <div class="statistics-panel-header">

                        <div>

                            <div class="statistics-panel-label">
                                <i class="fa-solid fa-earth-africa"></i>
                                GLOBAL REACH
                            </div>

                            <h2>
                                Users by Country
                            </h2>

                            <p>
                                Registered users worldwide.
                            </p>

                        </div>

                    </div>


                    <div class="country-summary">


                        <div class="country-summary-item">

                            <span>
                                <i class="fa-solid fa-earth-americas"></i>
                                Countries
                            </span>

                            <strong>
                                <?= number_format(
                                    $total_countries
                                ) ?>
                            </strong>

                        </div>


                        <div class="country-summary-item">

                            <span>
                                <i class="fa-solid fa-trophy"></i>
                                Top Country
                            </span>

                            <strong
                                title="<?= e($top_country) ?>"
                            >
                                <?= e($top_country) ?>
                            </strong>

                        </div>


                        <div class="country-summary-item">

                            <span>
                                <i class="fa-solid fa-users"></i>
                                Users
                            </span>

                            <strong>
                                <?= number_format(
                                    $top_country_users
                                ) ?>
                            </strong>

                        </div>


                    </div>


                    <div class="country-chart-wrap">


                        <?php if (!empty($chart_labels)): ?>

                            <canvas id="countryChart"></canvas>

                        <?php else: ?>

                            <div class="country-empty">

                                <i class="fa-solid fa-chart-column"></i>

                                No country data available yet.

                            </div>

                        <?php endif; ?>


                    </div>


                    <div class="country-footer">

                        View all countries
                        <i class="fa-solid fa-arrow-right"></i>

                    </div>


                </a>


            </div>


            <!-- ==================================================
                 PLATFORM ACTIVITY
            ================================================== -->

            <section class="statistics-panel activity-panel">


                <div class="statistics-panel-header">

                    <div>

                        <div class="statistics-panel-label">
                            <i class="fa-solid fa-chart-simple"></i>
                            PLATFORM OVERVIEW
                        </div>

                        <h2>
                            Activity Summary
                        </h2>

                        <p>
                            Quick overview of the platform's
                            current activity.
                        </p>

                    </div>

                </div>


                <div class="activity-grid">


                    <!-- USERS -->

                    <div class="activity-item">

                        <div class="activity-icon users">
                            <i class="fa-solid fa-users"></i>
                        </div>

                        <div>

                            <div class="activity-label">
                                Registered Users
                            </div>

                            <div class="activity-number">
                                <?= number_format(
                                    $total_users
                                ) ?>
                            </div>

                        </div>

                        <a
                            href="admin_users.php"
                            class="activity-link"
                        >
                            View
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>

                    </div>


                    <!-- PETITIONS -->

                    <div class="activity-item">

                        <div class="activity-icon petitions">
                            <i class="fa-solid fa-file-signature"></i>
                        </div>

                        <div>

                            <div class="activity-label">
                                Total Petitions
                            </div>

                            <div class="activity-number">
                                <?= number_format(
                                    $total_petitions
                                ) ?>
                            </div>

                        </div>

                        <a
                            href="admin_petitions.php"
                            class="activity-link"
                        >
                            View
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>

                    </div>


                    <!-- ACTIVE -->

                    <div class="activity-item">

                        <div class="activity-icon active">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>

                        <div>

                            <div class="activity-label">
                                Active Petitions
                            </div>

                            <div class="activity-number">
                                <?= number_format(
                                    $total_active_petitions
                                ) ?>
                            </div>

                        </div>

                        <a
                            href="admin_petitions.php"
                            class="activity-link"
                        >
                            View
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>

                    </div>


                    <!-- SIGNATURES -->

                    <div class="activity-item">

                        <div class="activity-icon signatures">
                            <i class="fa-solid fa-pen-nib"></i>
                        </div>

                        <div>

                            <div class="activity-label">
                                Total Signatures
                            </div>

                            <div class="activity-number">
                                <?= number_format(
                                    $total_signatures
                                ) ?>
                            </div>

                        </div>

                        <a
                            href="admin_signatures.php"
                            class="activity-link"
                        >
                            View
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>

                    </div>


                    <!-- MESSAGES -->

                    <div class="activity-item">

                        <div class="activity-icon messages">
                            <i class="fa-solid fa-comments"></i>
                        </div>

                        <div>

                            <div class="activity-label">
                                Total Messages
                            </div>

                            <div class="activity-number">
                                <?= number_format(
                                    $total_messages
                                ) ?>
                            </div>

                        </div>

                        <a
                            href="admin_messages.php"
                            class="activity-link"
                        >
                            View
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>

                    </div>


                    <!-- COUNTRIES -->

                    <div class="activity-item">

                        <div class="activity-icon countries">
                            <i class="fa-solid fa-earth-africa"></i>
                        </div>

                        <div>

                            <div class="activity-label">
                                Countries Represented
                            </div>

                            <div class="activity-number">
                                <?= number_format(
                                    $total_countries
                                ) ?>
                            </div>

                        </div>

                        <a
                            href="admin_country_statistics.php"
                            class="activity-link"
                        >
                            View
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>

                    </div>


                </div>


            </section>


        </div>


    </main>


</div>


<?php if (!empty($chart_labels)): ?>

<script>

const countryLabels =
    <?= json_encode(
        $chart_labels,
        JSON_UNESCAPED_UNICODE
    ) ?>;

const countryValues =
    <?= json_encode(
        $chart_values
    ) ?>;


const countryCanvas =
    document.getElementById(
        'countryChart'
    );


if (countryCanvas) {

    const countryContext =
        countryCanvas.getContext('2d');


    new Chart(
        countryContext,
        {

            type: 'bar',

            data: {

                labels: countryLabels,

                datasets: [

                    {

                        label: 'Registered Users',

                        data: countryValues,

                        borderWidth: 1,

                        borderRadius: 6,

                        barThickness: 18

                    }

                ]

            },

            options: {

                indexAxis: 'y',

                responsive: true,

                maintainAspectRatio: false,

                animation: {

                    duration: 700

                },

                plugins: {

                    legend: {

                        display: false

                    },

                    tooltip: {

                        callbacks: {

                            label: function(context) {

                                return (
                                    ' ' +
                                    Number(
                                        context.raw
                                    ).toLocaleString() +
                                    ' users'
                                );

                            }

                        }

                    }

                },

                scales: {

                    x: {

                        beginAtZero: true,

                        ticks: {

                            precision: 0

                        },

                        grid: {

                            color: 'rgba(148,163,184,.12)'

                        }

                    },

                    y: {

                        ticks: {

                            autoSkip: false,

                            font: {

                                size: 11

                            }

                        },

                        grid: {

                            display: false

                        }

                    }

                }

            }

        }
    );

}

</script>

<?php endif; ?>


</body>

</html>