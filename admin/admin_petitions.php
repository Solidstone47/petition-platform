<?php

require_once "../includes/db.php";
require_once "../includes/functions.php";
require_once "../includes/auth.php";

require_admin();

/*
|--------------------------------------------------------------------------
| HANDLE PETITION ACTIONS
|--------------------------------------------------------------------------
*/

if (is_post()) {

    verify_csrf();

    $action = $_POST['action'] ?? '';
    $petition_id = (int) ($_POST['petition_id'] ?? 0);

    if ($petition_id < 1) {

        set_flash(
            "error",
            "Invalid petition."
        );

        redirect("admin_petitions.php");
    }

    /*
    |--------------------------------------------------------------------------
    | PUBLISH PETITION
    |--------------------------------------------------------------------------
    */

    if ($action === "publish") {

        $stmt = $conn->prepare(
            "UPDATE petitions
             SET status = 'active'
             WHERE id = ?"
        );

        $stmt->bind_param(
            "i",
            $petition_id
        );

        if ($stmt->execute()) {

            set_flash(
                "success",
                "Petition published successfully."
            );

        } else {

            set_flash(
                "error",
                "Unable to publish petition."
            );
        }

        $stmt->close();

        redirect("admin_petitions.php");
    }

    /*
    |--------------------------------------------------------------------------
    | CLOSE PETITION
    |--------------------------------------------------------------------------
    */

    if ($action === "close") {

        $stmt = $conn->prepare(
            "UPDATE petitions
             SET status = 'closed'
             WHERE id = ?"
        );

        $stmt->bind_param(
            "i",
            $petition_id
        );

        if ($stmt->execute()) {

            set_flash(
                "success",
                "Petition closed successfully."
            );

        } else {

            set_flash(
                "error",
                "Unable to close petition."
            );
        }

        $stmt->close();

        redirect("admin_petitions.php");
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE PETITION
    |--------------------------------------------------------------------------
    */

    if ($action === "delete") {

        $stmt = $conn->prepare(
            "DELETE FROM petitions
             WHERE id = ?"
        );

        $stmt->bind_param(
            "i",
            $petition_id
        );

        if ($stmt->execute()) {

            set_flash(
                "success",
                "Petition deleted successfully."
            );

        } else {

            set_flash(
                "error",
                "Unable to delete petition."
            );
        }

        $stmt->close();

        redirect("admin_petitions.php");
    }
}


/*
|--------------------------------------------------------------------------
| GET PETITIONS
|--------------------------------------------------------------------------
*/

$result = $conn->query(
    "SELECT
        p.id,
        p.title,
        p.category,
        p.goal,
        p.status,
        p.created_at,
        a.fullname AS admin_name,

        (
            SELECT COUNT(*)
            FROM signatures s
            WHERE s.petition_id = p.id
        ) AS signature_count

     FROM petitions p

     LEFT JOIN admins a
        ON p.created_by = a.id

     ORDER BY p.created_at DESC"
);


if (!$result) {

    die(
        "Unable to load petitions: " .
        e($conn->error)
    );
}


/*
|--------------------------------------------------------------------------
| PETITION SUMMARY
|--------------------------------------------------------------------------
*/

$total_petitions = 0;
$active_petitions = 0;
$draft_petitions = 0;
$closed_petitions = 0;
$total_signatures = 0;

$petitions = [];

while ($petition = $result->fetch_assoc()) {

    $petitions[] = $petition;

    $total_petitions++;

    $signature_count = (int) $petition['signature_count'];

    $total_signatures += $signature_count;

    if ($petition['status'] === 'active') {
        $active_petitions++;
    }

    if ($petition['status'] === 'draft') {
        $draft_petitions++;
    }

    if ($petition['status'] === 'closed') {
        $closed_petitions++;
    }
}


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

    <title>Manage Petitions | Administration</title>

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
        | PETITIONS PAGE ENHANCEMENTS
        |--------------------------------------------------------------------------
        */

        .petition-summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 24px;
        }

        .petition-summary-card {
            position: relative;
            overflow: hidden;
            padding: 22px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.62);
            border: 1px solid rgba(255, 255, 255, 0.72);
            box-shadow: 0 10px 30px rgba(20, 45, 35, 0.07);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease;
        }

        .petition-summary-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 16px 36px rgba(20, 45, 35, 0.11);
        }

        .petition-summary-card::after {
            content: "";
            position: absolute;
            width: 90px;
            height: 90px;
            right: -35px;
            top: -35px;
            border-radius: 50%;
            background: rgba(0, 107, 63, 0.07);
        }

        .petition-summary-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }

        .petition-summary-icon {
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.72);
            border: 1px solid rgba(255, 255, 255, 0.85);
            box-shadow: 0 7px 18px rgba(20, 45, 35, 0.07);
            font-size: 18px;
        }

        .petition-summary-icon.total {
            color: #006b3f;
            background: rgba(0, 107, 63, 0.10);
        }

        .petition-summary-icon.active {
            color: #16834e;
            background: rgba(22, 131, 78, 0.10);
        }

        .petition-summary-icon.draft {
            color: #d99b16;
            background: rgba(217, 155, 22, 0.12);
        }

        .petition-summary-icon.signatures {
            color: #6d4aff;
            background: rgba(109, 74, 255, 0.10);
        }

        .petition-summary-label {
            color: #64748b;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.7px;
        }

        .petition-summary-value {
            color: #17211c;
            font-size: 28px;
            line-height: 1;
            font-weight: 800;
        }

        .petition-summary-note {
            margin-top: 8px;
            color: #7b8790;
            font-size: 12px;
        }

        .petition-panel-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 18px;
        }

        .petition-panel-heading h2 {
            margin: 0;
        }

        .petition-panel-heading p {
            margin: 5px 0 0;
            color: #718096;
            font-size: 13px;
        }

        .petition-count-chip {
            flex-shrink: 0;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(0, 107, 63, 0.08);
            border: 1px solid rgba(0, 107, 63, 0.12);
            color: #006b3f;
            font-size: 12px;
            font-weight: 800;
        }

        .petition-title-cell {
            min-width: 220px;
        }

        .petition-title-main {
            display: block;
            max-width: 310px;
            color: #1c2721;
            font-weight: 750;
            line-height: 1.35;
        }

        .petition-id {
            display: inline-block;
            margin-top: 5px;
            color: #94a0a8;
            font-size: 11px;
            font-weight: 700;
        }

        .signature-cell {
            min-width: 150px;
        }

        .signature-number {
            display: block;
            margin-bottom: 7px;
            color: #1e2923;
            font-weight: 800;
        }

        .signature-progress {
            width: 100%;
            max-width: 130px;
            height: 6px;
            overflow: hidden;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.08);
        }

        .signature-progress span {
            display: block;
            height: 100%;
            border-radius: inherit;
            background: #006b3f;
        }

        .signature-percent {
            display: block;
            margin-top: 5px;
            color: #84908a;
            font-size: 10px;
        }

        .petition-actions {
            min-width: 245px;
        }

        .petition-actions-inner {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
        }

        .petition-actions form {
            margin: 0;
        }

        .petition-actions .admin-btn {
            margin: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .petition-actions .admin-btn i {
            font-size: 12px;
        }

        .admin-table tbody tr {
            transition:
                background 0.18s ease,
                transform 0.18s ease;
        }

        .admin-table tbody tr:hover {
            background: rgba(0, 107, 63, 0.025);
        }

        .admin-table td {
            vertical-align: middle;
        }

        .status-dot {
            display: inline-block;
            width: 7px;
            height: 7px;
            margin-right: 6px;
            border-radius: 50%;
            vertical-align: middle;
        }

        .status-dot-active {
            background: #16834e;
            box-shadow: 0 0 0 4px rgba(22, 131, 78, 0.10);
        }

        .status-dot-draft {
            background: #d99b16;
            box-shadow: 0 0 0 4px rgba(217, 155, 22, 0.10);
        }

        .status-dot-closed {
            background: #c74a4a;
            box-shadow: 0 0 0 4px rgba(199, 74, 74, 0.10);
        }

        .table-icon {
            margin-right: 6px;
            font-size: 12px;
        }

        .table-icon.petition {
            color: #006b3f;
        }

        .table-icon.category {
            color: #8b5cf6;
        }

        .table-icon.goal {
            color: #d99b16;
        }

        .table-icon.signature {
            color: #2563eb;
        }

        .table-icon.status {
            color: #16834e;
        }

        .table-icon.creator {
            color: #0891b2;
        }

        .table-icon.date {
            color: #64748b;
        }

        .category-icon {
            margin-right: 5px;
        }

        .admin-alert {
            position: relative;
            border-radius: 14px;
            margin-bottom: 20px;
        }

        .empty-petition-icon {
            color: #006b3f;
            font-size: 34px;
            margin-bottom: 10px;
            opacity: .65;
        }

        .page-title-icon {
            color: #006b3f;
            margin-right: 8px;
        }

        .create-icon {
            margin-right: 6px;
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

        @media (max-width: 1100px) {

            .petition-summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

        }

        @media (max-width: 700px) {

            .petition-summary-grid {
                grid-template-columns: 1fr;
            }

            .petition-panel-heading {
                align-items: flex-start;
                flex-direction: column;
            }

            .petition-count-chip {
                align-self: flex-start;
            }

            .petition-actions {
                min-width: 180px;
            }

            .petition-actions-inner {
                flex-direction: column;
                align-items: stretch;
            }

            .petition-actions .admin-btn {
                width: 100%;
                text-align: center;
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


        <a
            href="admin_petitions.php"
            class="active"
        >
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
                    <i class="fa-solid fa-file-signature page-title-icon"></i>
                    Manage Petitions
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
                 PAGE HEADER
            =================================================== -->

            <div class="admin-page-header">

                <div>

                    <h1>
                        <i class="fa-solid fa-file-signature page-title-icon"></i>
                        Petitions
                    </h1>

                    <p>
                        Monitor, publish, manage and control petitions
                        across the Tanzania Petition Platform.
                    </p>

                </div>


                <a
                    href="create_petition.php"
                    class="admin-btn admin-btn-primary"
                >
                    <i class="fa-solid fa-plus create-icon"></i>
                    Create Petition
                </a>

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

            <div class="petition-summary-grid">


                <!-- TOTAL -->

                <div class="petition-summary-card">

                    <div class="petition-summary-top">

                        <div class="petition-summary-icon total">
                            <i class="fa-solid fa-layer-group"></i>
                        </div>

                        <span class="petition-summary-label">
                            Total Petitions
                        </span>

                    </div>

                    <div class="petition-summary-value">
                        <?= number_format($total_petitions) ?>
                    </div>

                    <div class="petition-summary-note">
                        All petitions currently in the system
                    </div>

                </div>


                <!-- ACTIVE -->

                <div class="petition-summary-card">

                    <div class="petition-summary-top">

                        <div class="petition-summary-icon active">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>

                        <span class="petition-summary-label">
                            Active
                        </span>

                    </div>

                    <div class="petition-summary-value">
                        <?= number_format($active_petitions) ?>
                    </div>

                    <div class="petition-summary-note">
                        Currently published petitions
                    </div>

                </div>


                <!-- DRAFTS -->

                <div class="petition-summary-card">

                    <div class="petition-summary-top">

                        <div class="petition-summary-icon draft">
                            <i class="fa-solid fa-file-pen"></i>
                        </div>

                        <span class="petition-summary-label">
                            Drafts
                        </span>

                    </div>

                    <div class="petition-summary-value">
                        <?= number_format($draft_petitions) ?>
                    </div>

                    <div class="petition-summary-note">
                        Awaiting publication
                    </div>

                </div>


                <!-- SIGNATURES -->

                <div class="petition-summary-card">

                    <div class="petition-summary-top">

                        <div class="petition-summary-icon signatures">
                            <i class="fa-solid fa-signature"></i>
                        </div>

                        <span class="petition-summary-label">
                            Signatures
                        </span>

                    </div>

                    <div class="petition-summary-value">
                        <?= number_format($total_signatures) ?>
                    </div>

                    <div class="petition-summary-note">
                        Total signatures collected
                    </div>

                </div>


            </div>


            <!-- ==================================================
                 PETITIONS PANEL
            =================================================== -->

            <div class="admin-panel">


                <div class="petition-panel-heading">

                    <div>

                        <h2>
                            <i class="fa-solid fa-list-check page-title-icon"></i>
                            All Petitions
                        </h2>

                        <p>
                            Review petition performance, status and
                            management actions.
                        </p>

                    </div>


                    <div class="petition-count-chip">

                        <i class="fa-solid fa-file-lines"></i>

                        <?= number_format($total_petitions) ?>

                        <?= $total_petitions === 1
                            ? 'Petition'
                            : 'Petitions'
                        ?>

                    </div>

                </div>


                <!-- ==================================================
                     TABLE
                =================================================== -->

                <div class="admin-table-wrapper">

                    <table class="admin-table">


                        <thead>

                            <tr>

                                <th>
                                    <i class="fa-solid fa-file-signature table-icon petition"></i>
                                    Petition
                                </th>

                                <th>
                                    <i class="fa-solid fa-tags table-icon category"></i>
                                    Category
                                </th>

                                <th>
                                    <i class="fa-solid fa-bullseye table-icon goal"></i>
                                    Goal
                                </th>

                                <th>
                                    <i class="fa-solid fa-signature table-icon signature"></i>
                                    Signatures
                                </th>

                                <th>
                                    <i class="fa-solid fa-circle-info table-icon status"></i>
                                    Status
                                </th>

                                <th>
                                    <i class="fa-solid fa-user-pen table-icon creator"></i>
                                    Created By
                                </th>

                                <th>
                                    <i class="fa-solid fa-calendar-days table-icon date"></i>
                                    Date
                                </th>

                                <th>
                                    <i class="fa-solid fa-sliders table-icon"></i>
                                    Actions
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php if (count($petitions) > 0): ?>


                            <?php foreach ($petitions as $petition): ?>


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


                                $progress = 0;

                                if ($goal > 0) {

                                    $progress = (
                                        $signature_count /
                                        $goal
                                    ) * 100;

                                    $progress = min(
                                        100,
                                        max(0, $progress)
                                    );
                                }

                                ?>


                                <tr>


                                    <!-- PETITION -->

                                    <td class="petition-title-cell">

                                        <span class="petition-title-main">

                                            <i class="fa-solid fa-file-signature table-icon petition"></i>

                                            <?= e(
                                                $petition['title']
                                            ) ?>

                                        </span>

                                        <span class="petition-id">

                                            <i class="fa-solid fa-hashtag"></i>

                                            Petition #<?= e(
                                                $petition['id']
                                            ) ?>

                                        </span>

                                    </td>


                                    <!-- CATEGORY -->

                                    <td>

                                        <span
                                            class="admin-badge admin-badge-info"
                                        >

                                            <i class="fa-solid fa-tag category-icon"></i>

                                            <?= e($category) ?>

                                        </span>

                                    </td>


                                    <!-- GOAL -->

                                    <td>

                                        <strong>

                                            <i class="fa-solid fa-bullseye table-icon goal"></i>

                                            <?= number_format($goal) ?>

                                        </strong>

                                    </td>


                                    <!-- SIGNATURES -->

                                    <td class="signature-cell">

                                        <span class="signature-number">

                                            <i class="fa-solid fa-signature table-icon signature"></i>

                                            <?= number_format(
                                                $signature_count
                                            ) ?>

                                            /

                                            <?= number_format(
                                                $goal
                                            ) ?>

                                        </span>


                                        <div class="signature-progress">

                                            <span
                                                style="width: <?= e(
                                                    (string) $progress
                                                ) ?>%;"
                                            ></span>

                                        </div>


                                        <span class="signature-percent">

                                            <i class="fa-solid fa-chart-simple"></i>

                                            <?= number_format(
                                                $progress,
                                                1
                                            ) ?>% of goal

                                        </span>

                                    </td>


                                    <!-- STATUS -->

                                    <td>


                                        <?php if (
                                            $petition['status'] === 'active'
                                        ): ?>

                                            <span
                                                class="admin-badge admin-badge-success"
                                            >

                                                <span
                                                    class="status-dot status-dot-active"
                                                ></span>

                                                <i class="fa-solid fa-circle-check"></i>

                                                Active

                                            </span>


                                        <?php elseif (
                                            $petition['status'] === 'draft'
                                        ): ?>

                                            <span
                                                class="admin-badge admin-badge-warning"
                                            >

                                                <span
                                                    class="status-dot status-dot-draft"
                                                ></span>

                                                <i class="fa-solid fa-file-pen"></i>

                                                Draft

                                            </span>


                                        <?php elseif (
                                            $petition['status'] === 'closed'
                                        ): ?>

                                            <span
                                                class="admin-badge admin-badge-danger"
                                            >

                                                <span
                                                    class="status-dot status-dot-closed"
                                                ></span>

                                                <i class="fa-solid fa-lock"></i>

                                                Closed

                                            </span>


                                        <?php else: ?>

                                            <span
                                                class="admin-badge admin-badge-info"
                                            >

                                                <i class="fa-solid fa-circle-question"></i>

                                                <?= e(
                                                    ucfirst(
                                                        $petition['status']
                                                    )
                                                ) ?>

                                            </span>

                                        <?php endif; ?>


                                    </td>


                                    <!-- CREATED BY -->

                                    <td>

                                        <i class="fa-solid fa-user-pen table-icon creator"></i>

                                        <?= e(
                                            $petition['admin_name']
                                            ?? 'Unknown'
                                        ) ?>

                                    </td>


                                    <!-- DATE -->

                                    <td>

                                        <i class="fa-solid fa-calendar-day table-icon date"></i>

                                        <?= e(
                                            format_date(
                                                $petition['created_at']
                                            )
                                        ) ?>

                                    </td>


                                    <!-- ACTIONS -->

                                    <td class="petition-actions">


                                        <div class="petition-actions-inner">


                                            <!-- EDIT -->

                                            <a
                                                href="edit_petition.php?id=<?= e(
                                                    $petition['id']
                                                ) ?>"
                                                class="admin-btn admin-btn-light"
                                            >
                                                <i class="fa-solid fa-pen-to-square"></i>
                                                Edit
                                            </a>


                                            <!-- PUBLISH -->

                                            <?php if (
                                                $petition['status'] === 'draft'
                                            ): ?>

                                                <form
                                                    method="POST"
                                                >

                                                    <?= csrf_field() ?>

                                                    <input
                                                        type="hidden"
                                                        name="petition_id"
                                                        value="<?= e(
                                                            $petition['id']
                                                        ) ?>"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="action"
                                                        value="publish"
                                                    >

                                                    <button
                                                        type="submit"
                                                        class="admin-btn admin-btn-success"
                                                    >
                                                        <i class="fa-solid fa-cloud-arrow-up"></i>
                                                        Publish
                                                    </button>

                                                </form>

                                            <?php endif; ?>


                                            <!-- CLOSE -->

                                            <?php if (
                                                $petition['status'] === 'active'
                                            ): ?>

                                                <form
                                                    method="POST"
                                                >

                                                    <?= csrf_field() ?>

                                                    <input
                                                        type="hidden"
                                                        name="petition_id"
                                                        value="<?= e(
                                                            $petition['id']
                                                        ) ?>"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="action"
                                                        value="close"
                                                    >

                                                    <button
                                                        type="submit"
                                                        class="admin-btn admin-btn-warning"
                                                    >
                                                        <i class="fa-solid fa-lock"></i>
                                                        Close
                                                    </button>

                                                </form>

                                            <?php endif; ?>


                                            <!-- DELETE -->

                                            <?php if (
                                                $petition['status'] !== 'active'
                                            ): ?>

                                                <form
                                                    method="POST"
                                                    onsubmit="return confirm('Are you sure you want to delete this petition?');"
                                                >

                                                    <?= csrf_field() ?>

                                                    <input
                                                        type="hidden"
                                                        name="petition_id"
                                                        value="<?= e(
                                                            $petition['id']
                                                        ) ?>"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="action"
                                                        value="delete"
                                                    >

                                                    <button
                                                        type="submit"
                                                        class="admin-btn admin-btn-danger"
                                                    >
                                                        <i class="fa-solid fa-trash-can"></i>
                                                        Delete
                                                    </button>

                                                </form>

                                            <?php endif; ?>


                                        </div>


                                    </td>


                                </tr>


                            <?php endforeach; ?>


                        <?php else: ?>


                            <tr>

                                <td
                                    colspan="8"
                                    class="admin-empty"
                                >

                                    <div style="
                                        padding: 30px 15px;
                                        text-align: center;
                                    ">

                                        <div class="empty-petition-icon">
                                            <i class="fa-solid fa-file-circle-plus"></i>
                                        </div>

                                        <strong>
                                            No petitions yet
                                        </strong>

                                        <div style="
                                            margin-top: 6px;
                                            color: #7b8790;
                                            font-size: 13px;
                                        ">
                                            Create the first petition to
                                            begin building the platform.
                                        </div>

                                    </div>

                                </td>

                            </tr>


                        <?php endif; ?>


                        </tbody>


                    </table>

                </div>


            </div>


        </div>


    </main>


</div>


</body>

</html>