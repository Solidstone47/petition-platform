<?php

require_once "../includes/db.php";
require_once "../includes/functions.php";
require_once "../includes/auth.php";

require_admin();

/*
|--------------------------------------------------------------------------
| DASHBOARD STATISTICS
|--------------------------------------------------------------------------
*/

$total_users = 0;
$total_petitions = 0;
$active_petitions = 0;
$draft_petitions = 0;
$closed_petitions = 0;
$total_signatures = 0;

/*
|--------------------------------------------------------------------------
| TOTAL USERS
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total FROM users"
);

if ($stmt && $stmt->execute()) {

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $total_users = (int) ($row['total'] ?? 0);
}

if ($stmt) {
    $stmt->close();
}

/*
|--------------------------------------------------------------------------
| PETITION COUNTS
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT
        COUNT(*) AS total,
        SUM(status = 'active') AS active,
        SUM(status = 'draft') AS draft,
        SUM(status = 'closed') AS closed
     FROM petitions"
);

if ($stmt && $stmt->execute()) {

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $total_petitions = (int) ($row['total'] ?? 0);
    $active_petitions = (int) ($row['active'] ?? 0);
    $draft_petitions = (int) ($row['draft'] ?? 0);
    $closed_petitions = (int) ($row['closed'] ?? 0);
}

if ($stmt) {
    $stmt->close();
}

/*
|--------------------------------------------------------------------------
| TOTAL SIGNATURES
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total FROM signatures"
);

if ($stmt && $stmt->execute()) {

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $total_signatures = (int) ($row['total'] ?? 0);
}

if ($stmt) {
    $stmt->close();
}

/*
|--------------------------------------------------------------------------
| RECENT PETITIONS
|--------------------------------------------------------------------------
*/

$recent_petitions = [];

$result = $conn->query(
    "SELECT
        p.id,
        p.title,
        p.category,
        p.goal,
        p.status,
        p.created_at,
        (
            SELECT COUNT(*)
            FROM signatures s
            WHERE s.petition_id = p.id
        ) AS signature_count
     FROM petitions p
     ORDER BY p.created_at DESC
     LIMIT 6"
);

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $recent_petitions[] = $row;
    }
}

/*
|--------------------------------------------------------------------------
| RECENT USERS
|--------------------------------------------------------------------------
*/

$recent_users = [];

$result = $conn->query(
    "SELECT
        id,
        fullname,
        email,
        created_at
     FROM users
     ORDER BY created_at DESC
     LIMIT 5"
);

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $recent_users[] = $row;
    }
}

/*
|--------------------------------------------------------------------------
| CATEGORY STATISTICS
|--------------------------------------------------------------------------
*/

$category_stats = [];

$result = $conn->query(
    "SELECT
        COALESCE(NULLIF(category, ''), 'Other') AS category,
        COUNT(*) AS total
     FROM petitions
     GROUP BY COALESCE(NULLIF(category, ''), 'Other')
     ORDER BY total DESC
     LIMIT 8"
);

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $category_stats[] = $row;
    }
}

/*
|--------------------------------------------------------------------------
| TOTAL ACTIVE PETITION GOAL
|--------------------------------------------------------------------------
*/

$total_goal = 0;

$result = $conn->query(
    "SELECT COALESCE(SUM(goal), 0) AS total_goal
     FROM petitions
     WHERE status = 'active'"
);

if ($result) {

    $row = $result->fetch_assoc();

    $total_goal = (int) ($row['total_goal'] ?? 0);
}

/*
|--------------------------------------------------------------------------
| SIGNATURE PERCENTAGE
|--------------------------------------------------------------------------
*/

$signature_percentage = 0;

if ($total_goal > 0) {

    $signature_percentage =
        ($total_signatures / $total_goal) * 100;

    if ($signature_percentage > 100) {

        $signature_percentage = 100;
    }
}

/*
|--------------------------------------------------------------------------
| FLASH MESSAGE
|--------------------------------------------------------------------------
*/

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

    <title>Dashboard | Administration</title>

    <link
        rel="stylesheet"
        href="css/admin.css"
    >

    <!-- FONT AWESOME -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    <style>

        /*
        |--------------------------------------------------------------------------
        | DASHBOARD PAGE
        |--------------------------------------------------------------------------
        */

        .dashboard-welcome {

            position: relative;

            overflow: hidden;

            padding: 26px 28px;

            margin-bottom: 24px;

            border-radius: 18px;

            background:
                linear-gradient(
                    135deg,
                    rgba(0, 107, 63, 0.95),
                    rgba(0, 107, 63, 0.82)
                );

            border: 1px solid rgba(255,255,255,0.18);

            box-shadow:
                0 12px 32px rgba(20,45,35,0.12);

            color: #ffffff;

        }

        .dashboard-welcome::after {

            content: "";

            position: absolute;

            width: 180px;

            height: 180px;

            right: -65px;

            top: -80px;

            border-radius: 50%;

            background: rgba(255,255,255,0.08);

        }

        .dashboard-welcome-content {

            position: relative;

            z-index: 2;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 24px;

        }

        .dashboard-welcome-text h1 {

            margin: 0;

            font-size: 25px;

            font-weight: 800;

            letter-spacing: -0.3px;

        }

        .dashboard-welcome-text h1 i {

            margin-right: 9px;

            color: #fcd116;

        }

        .dashboard-welcome-text p {

            margin: 7px 0 0;

            max-width: 720px;

            color: rgba(255,255,255,0.84);

            font-size: 13px;

            line-height: 1.6;

        }

        .dashboard-welcome-icon {

            width: 58px;

            height: 58px;

            flex-shrink: 0;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 17px;

            background: rgba(255,255,255,0.13);

            border: 1px solid rgba(255,255,255,0.18);

            box-shadow: 0 8px 22px rgba(0,0,0,0.10);

            font-size: 25px;

            color: #fcd116;

        }


        /*
        |--------------------------------------------------------------------------
        | SUMMARY CARDS
        |--------------------------------------------------------------------------
        */

        .dashboard-summary-grid {

            display: grid;

            grid-template-columns:
                repeat(6, minmax(0, 1fr));

            gap: 18px;

            margin-bottom: 24px;

        }

        .dashboard-summary-card {

            position: relative;

            overflow: hidden;

            padding: 20px;

            min-width: 0;

            border-radius: 18px;

            background: rgba(255,255,255,0.62);

            border: 1px solid rgba(255,255,255,0.72);

            box-shadow:
                0 10px 30px rgba(20,45,35,0.07);

            backdrop-filter: blur(16px);

            -webkit-backdrop-filter: blur(16px);

            transition:
                transform .2s ease,
                box-shadow .2s ease;

        }

        .dashboard-summary-card:hover {

            transform: translateY(-3px);

            box-shadow:
                0 16px 36px rgba(20,45,35,0.11);

        }

        .dashboard-summary-card::after {

            content: "";

            position: absolute;

            width: 85px;

            height: 85px;

            right: -32px;

            top: -32px;

            border-radius: 50%;

            background: rgba(0,107,63,0.07);

        }

        .dashboard-summary-top {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 10px;

            margin-bottom: 14px;

        }

        .dashboard-summary-icon {

            width: 44px;

            height: 44px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 14px;

            background: rgba(255,255,255,0.72);

            border: 1px solid rgba(255,255,255,0.85);

            box-shadow:
                0 7px 18px rgba(20,45,35,0.07);

            font-size: 18px;

        }

        .dashboard-summary-icon.users {

            color: #38bdf8;

            background: rgba(56,189,248,0.10);

        }

        .dashboard-summary-icon.total {

            color: #006b3f;

            background: rgba(0,107,63,0.10);

        }

        .dashboard-summary-icon.active {

            color: #16834e;

            background: rgba(22,131,78,0.10);

        }

        .dashboard-summary-icon.draft {

            color: #d99b16;

            background: rgba(217,155,22,0.12);

        }

        .dashboard-summary-icon.closed {

            color: #c74a4a;

            background: rgba(199,74,74,0.10);

        }

        .dashboard-summary-icon.signatures {

            color: #6d4aff;

            background: rgba(109,74,255,0.10);

        }

        .dashboard-summary-label {

            color: #64748b;

            font-size: 11px;

            font-weight: 700;

            text-transform: uppercase;

            letter-spacing: .6px;

        }

        .dashboard-summary-value {

            color: #17211c;

            font-size: 26px;

            line-height: 1;

            font-weight: 800;

        }

        .dashboard-summary-note {

            margin-top: 8px;

            color: #7b8790;

            font-size: 11px;

            line-height: 1.45;

        }


        /*
        |--------------------------------------------------------------------------
        | DASHBOARD GRID
        |--------------------------------------------------------------------------
        */

        .dashboard-grid {

            display: grid;

            grid-template-columns:
                minmax(0, 1.45fr)
                minmax(300px, .75fr);

            gap: 20px;

            margin-bottom: 20px;

        }

        .dashboard-grid-equal {

            display: grid;

            grid-template-columns:
                repeat(2, minmax(0, 1fr));

            gap: 20px;

            margin-bottom: 20px;

        }


        /*
        |--------------------------------------------------------------------------
        | PANEL
        |--------------------------------------------------------------------------
        */

        .dashboard-panel {

            position: relative;

            overflow: hidden;

            padding: 22px;

            border-radius: 18px;

            background: rgba(255,255,255,0.62);

            border: 1px solid rgba(255,255,255,0.72);

            box-shadow:
                0 10px 30px rgba(20,45,35,0.07);

            backdrop-filter: blur(16px);

            -webkit-backdrop-filter: blur(16px);

        }

        .dashboard-panel-heading {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;

            margin-bottom: 18px;

        }

        .dashboard-panel-heading h2 {

            margin: 0;

            color: #17211c;

            font-size: 17px;

            font-weight: 800;

        }

        .dashboard-panel-heading h2 i {

            color: #006b3f;

            margin-right: 7px;

        }

        .dashboard-panel-heading p {

            margin: 5px 0 0;

            color: #718096;

            font-size: 12px;

        }

        .dashboard-panel-chip {

            flex-shrink: 0;

            padding: 8px 12px;

            border-radius: 999px;

            background: rgba(0,107,63,0.08);

            border: 1px solid rgba(0,107,63,0.12);

            color: #006b3f;

            font-size: 11px;

            font-weight: 800;

        }


        /*
        |--------------------------------------------------------------------------
        | QUICK ACTIONS
        |--------------------------------------------------------------------------
        */

        .quick-actions {

            display: grid;

            grid-template-columns:
                repeat(2, minmax(0,1fr));

            gap: 12px;

        }

        .quick-action {

            display: flex;

            align-items: center;

            gap: 12px;

            padding: 14px;

            border-radius: 14px;

            text-decoration: none;

            background: rgba(255,255,255,0.58);

            border: 1px solid rgba(255,255,255,0.78);

            box-shadow:
                0 6px 18px rgba(20,45,35,0.05);

            transition:
                transform .2s ease,
                box-shadow .2s ease,
                background .2s ease;

        }

        .quick-action:hover {

            transform: translateY(-2px);

            background: rgba(255,255,255,0.82);

            box-shadow:
                0 10px 24px rgba(20,45,35,0.09);

        }

        .quick-action-icon {

            width: 40px;

            height: 40px;

            flex-shrink: 0;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 12px;

            font-size: 16px;

        }

        .quick-action-icon.create {

            color: #006b3f;

            background: rgba(0,107,63,0.10);

        }

        .quick-action-icon.petitions {

            color: #22c55e;

            background: rgba(34,197,94,0.10);

        }

        .quick-action-icon.users {

            color: #38bdf8;

            background: rgba(56,189,248,0.10);

        }

        .quick-action-icon.statistics {

            color: #06b6d4;

            background: rgba(6,182,212,0.10);

        }

        .quick-action-text strong {

            display: block;

            color: #1c2721;

            font-size: 12px;

            font-weight: 800;

        }

        .quick-action-text span {

            display: block;

            margin-top: 3px;

            color: #7b8790;

            font-size: 10px;

        }


        /*
        |--------------------------------------------------------------------------
        | RECENT PETITIONS
        |--------------------------------------------------------------------------
        */

        .dashboard-list {

            display: flex;

            flex-direction: column;

            gap: 10px;

        }

        .dashboard-list-item {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 14px;

            padding: 13px 14px;

            border-radius: 13px;

            background: rgba(255,255,255,0.48);

            border: 1px solid rgba(255,255,255,0.68);

            transition:
                background .18s ease,
                transform .18s ease;

        }

        .dashboard-list-item:hover {

            background: rgba(0,107,63,0.035);

            transform: translateX(2px);

        }

        .dashboard-list-main {

            min-width: 0;

        }

        .dashboard-list-title {

            display: block;

            overflow: hidden;

            color: #1c2721;

            font-size: 12px;

            font-weight: 750;

            line-height: 1.4;

            text-overflow: ellipsis;

            white-space: nowrap;

        }

        .dashboard-list-meta {

            display: flex;

            align-items: center;

            flex-wrap: wrap;

            gap: 9px;

            margin-top: 5px;

            color: #89949b;

            font-size: 10px;

        }

        .dashboard-list-meta i {

            margin-right: 3px;

        }

        .dashboard-list-meta .category {

            color: #8b5cf6;

        }

        .dashboard-list-meta .goal {

            color: #d99b16;

        }

        .dashboard-list-meta .signature {

            color: #2563eb;

        }


        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */

        .dashboard-status {

            flex-shrink: 0;

            display: inline-flex;

            align-items: center;

            gap: 5px;

            padding: 6px 9px;

            border-radius: 999px;

            font-size: 10px;

            font-weight: 800;

        }

        .dashboard-status.active {

            color: #16834e;

            background: rgba(22,131,78,0.10);

        }

        .dashboard-status.draft {

            color: #d99b16;

            background: rgba(217,155,22,0.12);

        }

        .dashboard-status.closed {

            color: #c74a4a;

            background: rgba(199,74,74,0.10);

        }


        /*
        |--------------------------------------------------------------------------
        | CATEGORY STATISTICS
        |--------------------------------------------------------------------------
        */

        .category-list {

            display: flex;

            flex-direction: column;

            gap: 13px;

        }

        .category-row {

            display: grid;

            grid-template-columns:
                34px
                minmax(0,1fr)
                auto;

            align-items: center;

            gap: 10px;

        }

        .category-icon {

            width: 34px;

            height: 34px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 10px;

            color: #8b5cf6;

            background: rgba(139,92,246,0.10);

            font-size: 13px;

        }

        .category-info {

            min-width: 0;

        }

        .category-name {

            display: block;

            overflow: hidden;

            color: #253129;

            font-size: 11px;

            font-weight: 750;

            text-overflow: ellipsis;

            white-space: nowrap;

        }

        .category-bar {

            height: 5px;

            margin-top: 6px;

            overflow: hidden;

            border-radius: 999px;

            background: rgba(15,23,42,0.07);

        }

        .category-bar span {

            display: block;

            height: 100%;

            border-radius: inherit;

            background: #8b5cf6;

        }

        .category-total {

            color: #64748b;

            font-size: 11px;

            font-weight: 800;

        }


        /*
        |--------------------------------------------------------------------------
        | SIGNATURE OVERVIEW
        |--------------------------------------------------------------------------
        */

        .signature-overview {

            display: flex;

            flex-direction: column;

            gap: 18px;

        }

        .signature-overview-main {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;

        }

        .signature-big-number {

            color: #17211c;

            font-size: 32px;

            line-height: 1;

            font-weight: 850;

        }

        .signature-big-label {

            margin-top: 7px;

            color: #7b8790;

            font-size: 11px;

        }

        .signature-target {

            text-align: right;

        }

        .signature-target-label {

            color: #94a0a8;

            font-size: 10px;

        }

        .signature-target-value {

            display: block;

            margin-top: 4px;

            color: #26332b;

            font-size: 14px;

            font-weight: 800;

        }

        .signature-progress-wrapper {

            width: 100%;

        }

        .signature-progress-header {

            display: flex;

            justify-content: space-between;

            gap: 10px;

            margin-bottom: 7px;

            color: #718096;

            font-size: 10px;

            font-weight: 700;

        }

        .signature-progress {

            width: 100%;

            height: 9px;

            overflow: hidden;

            border-radius: 999px;

            background: rgba(15,23,42,0.08);

        }

        .signature-progress span {

            display: block;

            height: 100%;

            border-radius: inherit;

            background: #6d4aff;

        }


        /*
        |--------------------------------------------------------------------------
        | RECENT USERS
        |--------------------------------------------------------------------------
        */

        .user-list {

            display: flex;

            flex-direction: column;

            gap: 10px;

        }

        .user-item {

            display: flex;

            align-items: center;

            gap: 12px;

            padding: 11px 12px;

            border-radius: 13px;

            background: rgba(255,255,255,0.48);

            border: 1px solid rgba(255,255,255,0.68);

            transition:
                background .18s ease,
                transform .18s ease;

        }

        .user-item:hover {

            background: rgba(255,255,255,0.78);

            transform: translateX(2px);

        }

        .user-avatar {

            width: 38px;

            height: 38px;

            flex-shrink: 0;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 12px;

            color: #0891b2;

            background: rgba(8,145,178,0.10);

            border: 1px solid rgba(8,145,178,0.12);

            font-size: 12px;

            font-weight: 800;

        }

        .user-info {

            min-width: 0;

        }

        .user-name {

            display: block;

            overflow: hidden;

            color: #1c2721;

            font-size: 11px;

            font-weight: 800;

            text-overflow: ellipsis;

            white-space: nowrap;

        }

        .user-email {

            display: block;

            overflow: hidden;

            margin-top: 3px;

            color: #89949b;

            font-size: 10px;

            text-overflow: ellipsis;

            white-space: nowrap;

        }

        .user-date {

            margin-left: auto;

            flex-shrink: 0;

            color: #94a0a8;

            font-size: 9px;

            text-align: right;

        }

        .user-date i {

            color: #64748b;

            margin-right: 3px;

        }


        /*
        |--------------------------------------------------------------------------
        | EMPTY STATES
        |--------------------------------------------------------------------------
        */

        .dashboard-empty {

            padding: 28px 15px;

            text-align: center;

        }

        .dashboard-empty-icon {

            margin-bottom: 9px;

            color: #006b3f;

            font-size: 30px;

            opacity: .60;

        }

        .dashboard-empty strong {

            color: #26332b;

            font-size: 12px;

        }

        .dashboard-empty p {

            margin: 6px 0 0;

            color: #7b8790;

            font-size: 11px;

        }


        /*
        |--------------------------------------------------------------------------
        | TABLE / GENERAL ICONS
        |--------------------------------------------------------------------------
        */

        .dashboard-table-icon {

            margin-right: 5px;

            font-size: 11px;

        }

        .dashboard-table-icon.petition {

            color: #006b3f;

        }

        .dashboard-table-icon.category {

            color: #8b5cf6;

        }

        .dashboard-table-icon.goal {

            color: #d99b16;

        }

        .dashboard-table-icon.signature {

            color: #2563eb;

        }

        .dashboard-table-icon.user {

            color: #0891b2;

        }

        .dashboard-table-icon.date {

            color: #64748b;

        }

        .dashboard-table-icon.chart {

            color: #06b6d4;

        }

        .page-title-icon {

            color: #006b3f;

            margin-right: 8px;

        }


        /*
        |--------------------------------------------------------------------------
        | SIDEBAR ICONS
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | RESPONSIVE
        |--------------------------------------------------------------------------
        */

        @media (max-width: 1250px) {

            .dashboard-summary-grid {

                grid-template-columns:
                    repeat(3, minmax(0,1fr));

            }

        }

        @media (max-width: 1050px) {

            .dashboard-grid {

                grid-template-columns: 1fr;

            }

            .dashboard-grid-equal {

                grid-template-columns: 1fr;

            }

        }

        @media (max-width: 700px) {

            .dashboard-summary-grid {

                grid-template-columns: 1fr;

            }

            .dashboard-welcome-content {

                align-items: flex-start;

            }

            .dashboard-welcome-icon {

                display: none;

            }

            .dashboard-panel {

                padding: 18px;

            }

            .dashboard-panel-heading {

                align-items: flex-start;

                flex-direction: column;

            }

            .dashboard-panel-chip {

                align-self: flex-start;

            }

            .quick-actions {

                grid-template-columns: 1fr;

            }

            .dashboard-list-item {

                align-items: flex-start;

                flex-direction: column;

            }

            .dashboard-status {

                align-self: flex-start;

            }

            .user-date {

                display: none;

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


        <a
            href="admin_dashboard.php"
            class="active"
        >

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


        <a href="admin_statistics.php">

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


    <!-- ======================================================
         MAIN
    ======================================================= -->

    <main class="admin-main">


        <!-- ==================================================
             TOP BAR
        =================================================== -->

        <header class="admin-topbar">

            <div>

                <h2>

                    <i class="fa-solid fa-gauge-high page-title-icon"></i>

                    Dashboard

                </h2>

            </div>


            <div class="admin-user">

                <i class="fa-solid fa-user-shield"></i>

                Administrator

            </div>

        </header>


        <!-- ==================================================
             CONTENT
        =================================================== -->

        <div class="admin-content">


            <!-- ==================================================
                 WELCOME
            =================================================== -->

            <div class="dashboard-welcome">

                <div class="dashboard-welcome-content">

                    <div class="dashboard-welcome-text">

                        <h1>

                            <i class="fa-solid fa-chart-line"></i>

                            Administration Dashboard

                        </h1>

                        <p>

                            Monitor petitions, users, signatures and

                            platform activity from one central

                            administration panel.

                        </p>

                    </div>


                    <div class="dashboard-welcome-icon">

                        <i class="fa-solid fa-gauge-high"></i>

                    </div>

                </div>

            </div>


            <!-- ==================================================
                 FLASH MESSAGE
            =================================================== -->

            <?php if ($flash): ?>

                <div
                    class="admin-alert
                    <?= $flash['type'] === 'success'
                        ? 'admin-alert-success'
                        : 'admin-alert-danger'
                    ?>"
                >

                    <?= e($flash['message']) ?>

                </div>

            <?php endif; ?>


            <!-- ==================================================
                 SUMMARY CARDS
            =================================================== -->

            <div class="dashboard-summary-grid">


                <!-- USERS -->

                <div class="dashboard-summary-card">

                    <div class="dashboard-summary-top">

                        <div class="dashboard-summary-icon users">

                            <i class="fa-solid fa-users"></i>

                        </div>

                        <span class="dashboard-summary-label">

                            Users

                        </span>

                    </div>

                    <div class="dashboard-summary-value">

                        <?= number_format($total_users) ?>

                    </div>

                    <div class="dashboard-summary-note">

                        Registered platform users

                    </div>

                </div>


                <!-- TOTAL PETITIONS -->

                <div class="dashboard-summary-card">

                    <div class="dashboard-summary-top">

                        <div class="dashboard-summary-icon total">

                            <i class="fa-solid fa-layer-group"></i>

                        </div>

                        <span class="dashboard-summary-label">

                            Petitions

                        </span>

                    </div>

                    <div class="dashboard-summary-value">

                        <?= number_format($total_petitions) ?>

                    </div>

                    <div class="dashboard-summary-note">

                        All petitions in the system

                    </div>

                </div>


                <!-- ACTIVE -->

                <div class="dashboard-summary-card">

                    <div class="dashboard-summary-top">

                        <div class="dashboard-summary-icon active">

                            <i class="fa-solid fa-circle-check"></i>

                        </div>

                        <span class="dashboard-summary-label">

                            Active

                        </span>

                    </div>

                    <div class="dashboard-summary-value">

                        <?= number_format($active_petitions) ?>

                    </div>

                    <div class="dashboard-summary-note">

                        Currently published

                    </div>

                </div>


                <!-- DRAFT -->

                <div class="dashboard-summary-card">

                    <div class="dashboard-summary-top">

                        <div class="dashboard-summary-icon draft">

                            <i class="fa-solid fa-file-pen"></i>

                        </div>

                        <span class="dashboard-summary-label">

                            Drafts

                        </span>

                    </div>

                    <div class="dashboard-summary-value">

                        <?= number_format($draft_petitions) ?>

                    </div>

                    <div class="dashboard-summary-note">

                        Awaiting publication

                    </div>

                </div>


                <!-- CLOSED -->

                <div class="dashboard-summary-card">

                    <div class="dashboard-summary-top">

                        <div class="dashboard-summary-icon closed">

                            <i class="fa-solid fa-lock"></i>

                        </div>

                        <span class="dashboard-summary-label">

                            Closed

                        </span>

                    </div>

                    <div class="dashboard-summary-value">

                        <?= number_format($closed_petitions) ?>

                    </div>

                    <div class="dashboard-summary-note">

                        Completed or closed

                    </div>

                </div>


                <!-- SIGNATURES -->

                <div class="dashboard-summary-card">

                    <div class="dashboard-summary-top">

                        <div class="dashboard-summary-icon signatures">

                            <i class="fa-solid fa-signature"></i>

                        </div>

                        <span class="dashboard-summary-label">

                            Signatures

                        </span>

                    </div>

                    <div class="dashboard-summary-value">

                        <?= number_format($total_signatures) ?>

                    </div>

                    <div class="dashboard-summary-note">

                        Total signatures collected

                    </div>

                </div>


            </div>


            <!-- ==================================================
                 FIRST ROW
            =================================================== -->

            <div class="dashboard-grid">


                <!-- QUICK ACTIONS -->

                <div class="dashboard-panel">

                    <div class="dashboard-panel-heading">

                        <div>

                            <h2>

                                <i class="fa-solid fa-bolt"></i>

                                Quick Actions

                            </h2>

                            <p>

                                Frequently used administration tools.

                            </p>

                        </div>

                    </div>


                    <div class="quick-actions">


                        <a
                            href="create_petition.php"
                            class="quick-action"
                        >

                            <div class="quick-action-icon create">

                                <i class="fa-solid fa-plus"></i>

                            </div>

                            <div class="quick-action-text">

                                <strong>

                                    Create Petition

                                </strong>

                                <span>

                                    Start a new petition

                                </span>

                            </div>

                        </a>


                        <a
                            href="admin_petitions.php"
                            class="quick-action"
                        >

                            <div class="quick-action-icon petitions">

                                <i class="fa-solid fa-file-signature"></i>

                            </div>

                            <div class="quick-action-text">

                                <strong>

                                    Manage Petitions

                                </strong>

                                <span>

                                    Review and control petitions

                                </span>

                            </div>

                        </a>


                        <a
                            href="admin_users.php"
                            class="quick-action"
                        >

                            <div class="quick-action-icon users">

                                <i class="fa-solid fa-users"></i>

                            </div>

                            <div class="quick-action-text">

                                <strong>

                                    Manage Users

                                </strong>

                                <span>

                                    View registered users

                                </span>

                            </div>

                        </a>


                        <a
                            href="admin_statistics.php"
                            class="quick-action"
                        >

                            <div class="quick-action-icon statistics">

                                <i class="fa-solid fa-chart-line"></i>

                            </div>

                            <div class="quick-action-text">

                                <strong>

                                    View Statistics

                                </strong>

                                <span>

                                    Analyze platform activity

                                </span>

                            </div>

                        </a>


                    </div>

                </div>


                <!-- SIGNATURE OVERVIEW -->

                <div class="dashboard-panel">

                    <div class="dashboard-panel-heading">

                        <div>

                            <h2>

                                <i class="fa-solid fa-pen-nib"></i>

                                Signature Overview

                            </h2>

                            <p>

                                Overall petition signature progress.

                            </p>

                        </div>

                    </div>


                    <div class="signature-overview">


                        <div class="signature-overview-main">


                            <div>

                                <div class="signature-big-number">

                                    <?= number_format(
                                        $total_signatures
                                    ) ?>

                                </div>

                                <div class="signature-big-label">

                                    Total signatures collected

                                </div>

                            </div>


                            <div class="signature-target">

                                <span class="signature-target-label">

                                    Active petition goal

                                </span>

                                <strong class="signature-target-value">

                                    <?= number_format(
                                        $total_goal
                                    ) ?>

                                </strong>

                            </div>


                        </div>


                        <div class="signature-progress-wrapper">

                            <div class="signature-progress-header">

                                <span>

                                    <i class="fa-solid fa-bullseye dashboard-table-icon goal"></i>

                                    Overall progress

                                </span>

                                <strong>

                                    <?= number_format(
                                        $signature_percentage,
                                        1
                                    ) ?>%

                                </strong>

                            </div>


                            <div class="signature-progress">

                                <span
                                    style="width: <?= e(
                                        (string) $signature_percentage
                                    ) ?>%;"
                                ></span>

                            </div>

                        </div>


                    </div>

                </div>


            </div>


            <!-- ==================================================
                 RECENT PETITIONS + CATEGORY
            =================================================== -->

            <div class="dashboard-grid">


                <!-- RECENT PETITIONS -->

                <div class="dashboard-panel">

                    <div class="dashboard-panel-heading">

                        <div>

                            <h2>

                                <i class="fa-solid fa-file-signature"></i>

                                Recent Petitions

                            </h2>

                            <p>

                                The latest petitions added to the platform.

                            </p>

                        </div>


                        <div class="dashboard-panel-chip">

                            <i class="fa-solid fa-list"></i>

                            <?= count($recent_petitions) ?>

                        </div>

                    </div>


                    <?php if (count($recent_petitions) > 0): ?>


                        <div class="dashboard-list">


                            <?php foreach (
                                $recent_petitions
                                as $petition
                            ): ?>


                                <?php

                                $category = trim(
                                    $petition['category'] ?? ''
                                );

                                if ($category === '') {

                                    $category = 'Other';
                                }

                                $goal = max(
                                    0,
                                    (int) $petition['goal']
                                );

                                $signature_count = max(
                                    0,
                                    (int) $petition['signature_count']
                                );

                                ?>


                                <div class="dashboard-list-item">


                                    <div class="dashboard-list-main">

                                        <span class="dashboard-list-title">

                                            <i class="fa-solid fa-file-signature dashboard-table-icon petition"></i>

                                            <?= e(
                                                $petition['title']
                                            ) ?>

                                        </span>


                                        <div class="dashboard-list-meta">

                                            <span class="category">

                                                <i class="fa-solid fa-tag"></i>

                                                <?= e($category) ?>

                                            </span>


                                            <span class="goal">

                                                <i class="fa-solid fa-bullseye"></i>

                                                <?= number_format(
                                                    $goal
                                                ) ?>

                                            </span>


                                            <span class="signature">

                                                <i class="fa-solid fa-signature"></i>

                                                <?= number_format(
                                                    $signature_count
                                                ) ?>

                                            </span>


                                            <span>

                                                <i class="fa-solid fa-calendar-day"></i>

                                                <?= e(
                                                    format_date(
                                                        $petition['created_at']
                                                    )
                                                ) ?>

                                            </span>

                                        </div>

                                    </div>


                                    <?php if (
                                        $petition['status'] === 'active'
                                    ): ?>

                                        <span class="dashboard-status active">

                                            <i class="fa-solid fa-circle-check"></i>

                                            Active

                                        </span>


                                    <?php elseif (
                                        $petition['status'] === 'draft'
                                    ): ?>

                                        <span class="dashboard-status draft">

                                            <i class="fa-solid fa-file-pen"></i>

                                            Draft

                                        </span>


                                    <?php elseif (
                                        $petition['status'] === 'closed'
                                    ): ?>

                                        <span class="dashboard-status closed">

                                            <i class="fa-solid fa-lock"></i>

                                            Closed

                                        </span>


                                    <?php else: ?>

                                        <span class="dashboard-status">

                                            <i class="fa-solid fa-circle-question"></i>

                                            <?= e(
                                                ucfirst(
                                                    $petition['status']
                                                )
                                            ) ?>

                                        </span>

                                    <?php endif; ?>


                                </div>


                            <?php endforeach; ?>


                        </div>


                    <?php else: ?>


                        <div class="dashboard-empty">

                            <div class="dashboard-empty-icon">

                                <i class="fa-solid fa-file-circle-plus"></i>

                            </div>

                            <strong>

                                No petitions yet

                            </strong>

                            <p>

                                Create the first petition to begin

                                building the platform.

                            </p>

                        </div>


                    <?php endif; ?>


                </div>


                <!-- PETITION AREAS -->

                <div class="dashboard-panel">

                    <div class="dashboard-panel-heading">

                        <div>

                            <h2>

                                <i class="fa-solid fa-layer-group"></i>

                                Petition Areas

                            </h2>

                            <p>

                                Most common petition categories.

                            </p>

                        </div>


                        <div class="dashboard-panel-chip">

                            <i class="fa-solid fa-chart-bar"></i>

                            <?= count($category_stats) ?>

                        </div>

                    </div>


                    <?php if (count($category_stats) > 0): ?>


                        <?php

                        $max_category_total = 1;

                        foreach (
                            $category_stats
                            as $category_stat
                        ) {

                            $category_total =
                                (int) $category_stat['total'];

                            if (
                                $category_total >
                                $max_category_total
                            ) {

                                $max_category_total =
                                    $category_total;
                            }
                        }

                        ?>


                        <div class="category-list">


                            <?php foreach (
                                $category_stats
                                as $category_stat
                            ): ?>


                                <?php

                                $category_total =
                                    (int) $category_stat['total'];

                                $category_percentage =
                                    (
                                        $category_total /
                                        $max_category_total
                                    ) * 100;

                                ?>


                                <div class="category-row">


                                    <div class="category-icon">

                                        <i class="fa-solid fa-folder"></i>

                                    </div>


                                    <div class="category-info">

                                        <span class="category-name">

                                            <?= e(
                                                $category_stat['category']
                                            ) ?>

                                        </span>


                                        <div class="category-bar">

                                            <span
                                                style="width: <?= e(
                                                    (string) $category_percentage
                                                ) ?>%;"
                                            ></span>

                                        </div>

                                    </div>


                                    <div class="category-total">

                                        <?= number_format(
                                            $category_total
                                        ) ?>

                                    </div>


                                </div>


                            <?php endforeach; ?>


                        </div>


                    <?php else: ?>


                        <div class="dashboard-empty">

                            <div class="dashboard-empty-icon">

                                <i class="fa-solid fa-folder-open"></i>

                            </div>

                            <strong>

                                No category data

                            </strong>

                            <p>

                                Petition categories will appear here.

                            </p>

                        </div>


                    <?php endif; ?>


                </div>


            </div>


            <!-- ==================================================
                 RECENT USERS
            =================================================== -->

            <div class="dashboard-panel">

                <div class="dashboard-panel-heading">

                    <div>

                        <h2>

                            <i class="fa-solid fa-users"></i>

                            New Users

                        </h2>

                        <p>

                            Recently registered users on the platform.

                        </p>

                    </div>


                    <div class="dashboard-panel-chip">

                        <i class="fa-solid fa-user-plus"></i>

                        <?= count($recent_users) ?>

                    </div>

                </div>


                <?php if (count($recent_users) > 0): ?>


                    <div class="user-list">


                        <?php foreach (
                            $recent_users
                            as $user
                        ): ?>


                            <?php

                            $fullname =
                                trim(
                                    $user['fullname'] ?? ''
                                );

                            $initials = '';

                            $name_parts =
                                preg_split(
                                    '/\s+/',
                                    $fullname
                                );

                            if (
                                !empty($name_parts[0])
                            ) {

                                $initials .=
                                    strtoupper(
                                        substr(
                                            $name_parts[0],
                                            0,
                                            1
                                        )
                                    );
                            }

                            if (
                                count($name_parts) > 1
                            ) {

                                $initials .=
                                    strtoupper(
                                        substr(
                                            $name_parts[
                                                count(
                                                    $name_parts
                                                ) - 1
                                            ],
                                            0,
                                            1
                                        )
                                    );
                            }

                            if ($initials === '') {

                                $initials = 'U';
                            }

                            ?>


                            <div class="user-item">


                                <div class="user-avatar">

                                    <?= e($initials) ?>

                                </div>


                                <div class="user-info">

                                    <span class="user-name">

                                        <i class="fa-solid fa-user dashboard-table-icon user"></i>

                                        <?= e(
                                            $fullname !== ''
                                                ? $fullname
                                                : 'Unknown User'
                                        ) ?>

                                    </span>


                                    <span class="user-email">

                                        <i class="fa-solid fa-envelope dashboard-table-icon user"></i>

                                        <?= e(
                                            $user['email']
                                        ) ?>

                                    </span>

                                </div>


                                <div class="user-date">

                                    <i class="fa-solid fa-calendar-day"></i>

                                    <?= e(
                                        format_date(
                                            $user['created_at']
                                        )
                                    ) ?>

                                </div>


                            </div>


                        <?php endforeach; ?>


                    </div>


                <?php else: ?>


                    <div class="dashboard-empty">

                        <div class="dashboard-empty-icon">

                            <i class="fa-solid fa-user-plus"></i>

                        </div>

                        <strong>

                            No users yet

                        </strong>

                        <p>

                            Newly registered users will appear here.

                        </p>

                    </div>


                <?php endif; ?>


            </div>


        </div>


    </main>


</div>


</body>

</html>