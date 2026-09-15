<?php

require_once "../includes/db.php";
require_once "../includes/functions.php";
require_once "../includes/auth.php";

require_admin();

error_reporting(E_ALL);
ini_set("display_errors", "1");
ini_set("display_startup_errors", "1");

// ======================================================
// SETTINGS
// ======================================================

$per_page = 10;

$page = isset($_GET["page"])
    ? max(1, (int) $_GET["page"])
    : 1;

$search = trim($_GET["search"] ?? "");

$petition_filter = isset($_GET["petition"])
    ? (int) $_GET["petition"]
    : 0;

// ======================================================
// HANDLE ACTIONS
// ======================================================

if (is_post()) {

    verify_csrf();

    $action = $_POST["action"] ?? "";

    $signature_id = (int) ($_POST["signature_id"] ?? 0);

    if ($signature_id < 1) {

        set_flash(
            "error",
            "Invalid signature."
        );

        redirect("admin_signatures.php");
    }

    // ==================================================
    // DELETE SIGNATURE
    // ==================================================

    if ($action === "delete") {

        $stmt = $conn->prepare(
            "DELETE FROM signatures
             WHERE id = ?"
        );

        if (!$stmt) {

            set_flash(
                "error",
                "Unable to prepare delete request."
            );

            redirect("admin_signatures.php");
        }

        $stmt->bind_param(
            "i",
            $signature_id
        );

        if ($stmt->execute()) {

            if ($stmt->affected_rows > 0) {

                set_flash(
                    "success",
                    "Signature deleted successfully."
                );

            } else {

                set_flash(
                    "error",
                    "Signature not found."
                );
            }

        } else {

            set_flash(
                "error",
                "Unable to delete signature."
            );
        }

        $stmt->close();

        redirect("admin_signatures.php");
    }
}

// ======================================================
// TOTAL SIGNATURES
// ======================================================

$total_signatures = 0;

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM signatures"
);

if ($result) {

    $row = $result->fetch_assoc();

    $total_signatures = (int) $row["total"];
}

// ======================================================
// TODAY'S SIGNATURES
// ======================================================

$today_signatures = 0;

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM signatures
     WHERE DATE(created_at) = CURDATE()"
);

if ($result) {

    $row = $result->fetch_assoc();

    $today_signatures = (int) $row["total"];
}

// ======================================================
// PETITIONS WITH SIGNATURES
// ======================================================

$petition_count = 0;

$result = $conn->query(
    "SELECT COUNT(DISTINCT petition_id) AS total
     FROM signatures"
);

if ($result) {

    $row = $result->fetch_assoc();

    $petition_count = (int) $row["total"];
}

// ======================================================
// GET PETITIONS FOR FILTER
// ======================================================

$petitions = [];

$result = $conn->query(
    "SELECT
        id,
        title
     FROM petitions
     ORDER BY title ASC"
);

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $petitions[] = $row;
    }
}

// ======================================================
// COUNT FILTERED SIGNATURES
// ======================================================

$count_sql = "
    SELECT COUNT(*) AS total
    FROM signatures s
    INNER JOIN users u
        ON u.id = s.user_id
    INNER JOIN petitions p
        ON p.id = s.petition_id
    WHERE 1 = 1
";

$count_params = [];
$count_types = "";

// SEARCH

if ($search !== "") {

    $count_sql .= "
        AND (
            u.fullname LIKE ?
            OR u.email LIKE ?
            OR u.phone LIKE ?
            OR p.title LIKE ?
        )
    ";

    $search_value = "%" . $search . "%";

    $count_params[] = $search_value;
    $count_params[] = $search_value;
    $count_params[] = $search_value;
    $count_params[] = $search_value;

    $count_types .= "ssss";
}

// PETITION FILTER

if ($petition_filter > 0) {

    $count_sql .= "
        AND s.petition_id = ?
    ";

    $count_params[] = $petition_filter;

    $count_types .= "i";
}

$count_stmt = $conn->prepare($count_sql);

$total_filtered = 0;

if ($count_stmt) {

    if (!empty($count_params)) {

        $count_stmt->bind_param(
            $count_types,
            ...$count_params
        );
    }

    $count_stmt->execute();

    $count_result = $count_stmt->get_result();

    if ($count_result) {

        $count_row = $count_result->fetch_assoc();

        $total_filtered = (int) $count_row["total"];
    }

    $count_stmt->close();
}

// ======================================================
// PAGINATION
// ======================================================

$total_pages = max(
    1,
    (int) ceil(
        $total_filtered / $per_page
    )
);

if ($page > $total_pages) {

    $page = $total_pages;
}

$offset = ($page - 1) * $per_page;

// ======================================================
// GET SIGNATURES
// ======================================================

$sql = "
    SELECT
        s.id,
        s.created_at,
        u.id AS user_id,
        u.fullname,
        u.email,
        u.phone,
        u.country,
        p.id AS petition_id,
        p.title AS petition_title
    FROM signatures s
    INNER JOIN users u
        ON u.id = s.user_id
    INNER JOIN petitions p
        ON p.id = s.petition_id
    WHERE 1 = 1
";

$params = [];
$types = "";

// SEARCH

if ($search !== "") {

    $sql .= "
        AND (
            u.fullname LIKE ?
            OR u.email LIKE ?
            OR u.phone LIKE ?
            OR p.title LIKE ?
        )
    ";

    $search_value = "%" . $search . "%";

    $params[] = $search_value;
    $params[] = $search_value;
    $params[] = $search_value;
    $params[] = $search_value;

    $types .= "ssss";
}

// PETITION FILTER

if ($petition_filter > 0) {

    $sql .= "
        AND s.petition_id = ?
    ";

    $params[] = $petition_filter;

    $types .= "i";
}

$sql .= "
    ORDER BY s.created_at DESC
    LIMIT ? OFFSET ?
";

$params[] = $per_page;
$params[] = $offset;

$types .= "ii";

$stmt = $conn->prepare($sql);

$signatures = [];

if ($stmt) {

    $stmt->bind_param(
        $types,
        ...$params
    );

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result) {

        while ($row = $result->fetch_assoc()) {

            $signatures[] = $row;
        }
    }

    $stmt->close();
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

    <title>Signatures - Admin Panel</title>

    <link
        rel="stylesheet"
        href="css/admin.css"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    <style>

        /* ==================================================
           SIGNATURES PAGE
        ================================================== */

        .signatures-page {
            width: 100%;
        }

        /* ==================================================
           ICON COLORS
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

        .page-title-icon {
            color: #006b3f;
            margin-right: 8px;
        }

        .admin-user i {
            color: #006b3f;
            margin-right: 7px;
        }

        /* ==================================================
           PAGE INTRO
        ================================================== */

        .signature-page-intro {
            margin-bottom: 26px;
        }

        .signature-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1.5px;
            color: #006b3f;
        }

        .signature-eyebrow i {
            color: #fcd116;
        }

        .signature-eyebrow::before {
            content: "";
            width: 24px;
            height: 2px;
            border-radius: 5px;
            background: #fcd116;
        }

        .signature-page-intro h1 {
            margin: 0;
            font-size: 30px;
            line-height: 1.2;
            font-weight: 800;
            color: #111827;
        }

        .signature-page-intro p {
            margin: 8px 0 0;
            max-width: 700px;
            color: #6b7280;
            font-size: 14px;
            line-height: 1.6;
        }

        /* ==================================================
           FLASH
        ================================================== */

        .signature-flash {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 14px 17px;
            margin-bottom: 22px;
            border-radius: 14px;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            font-size: 13px;
            font-weight: 700;
            box-shadow: 0 8px 25px rgba(15, 23, 42, .05);
        }

        .flash-success {
            color: #047857;
            background: rgba(236, 253, 245, .92);
            border: 1px solid rgba(167, 243, 208, .9);
        }

        .flash-error {
            color: #b91c1c;
            background: rgba(254, 242, 242, .92);
            border: 1px solid rgba(254, 202, 202, .9);
        }

        .signature-flash-icon {
            width: 28px;
            height: 28px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 9px;
            background: rgba(255,255,255,.7);
            font-weight: 900;
        }

        .flash-success .signature-flash-icon {
            color: #059669;
        }

        .flash-error .signature-flash-icon {
            color: #dc2626;
        }

        /* ==================================================
           STAT CARDS
        ================================================== */

        .signature-cards {
            margin-bottom: 26px;
        }

        .signature-stat-card {
            position: relative;
            overflow: hidden;
            text-decoration: none;
            border: 1px solid rgba(255,255,255,.72);
            background:
                linear-gradient(
                    145deg,
                    rgba(255,255,255,.94),
                    rgba(255,255,255,.70)
                );
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            box-shadow:
                0 12px 35px rgba(15,23,42,.07),
                inset 0 1px 0 rgba(255,255,255,.9);
            transition:
                transform .22s ease,
                box-shadow .22s ease,
                border-color .22s ease;
        }

        .signature-stat-card::after {
            content: "";
            position: absolute;
            width: 100px;
            height: 100px;
            right: -45px;
            top: -45px;
            border-radius: 50%;
            background: rgba(0,107,63,.06);
            pointer-events: none;
        }

        .signature-stat-card:hover {
            transform: translateY(-4px);
            border-color: rgba(0,107,63,.18);
            box-shadow:
                0 18px 42px rgba(15,23,42,.11),
                inset 0 1px 0 rgba(255,255,255,.95);
        }

        .signature-stat-card .admin-stat-icon {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .signature-total-icon {
            color: #a78bfa !important;
            background: rgba(167,139,250,.12) !important;
        }

        .signature-today-icon {
            color: #f59e0b !important;
            background: rgba(245,158,11,.12) !important;
        }

        .signature-petitions-icon {
            color: #22c55e !important;
            background: rgba(34,197,94,.12) !important;
        }

        /* ==================================================
           MANAGEMENT PANEL
        ================================================== */

        .signatures-management {
            position: relative;
            overflow: hidden;
            margin-top: 4px;
            padding: 26px;
            border: 1px solid rgba(255,255,255,.78);
            border-radius: 22px;
            background:
                linear-gradient(
                    145deg,
                    rgba(255,255,255,.91),
                    rgba(248,250,252,.78)
                );
            backdrop-filter: blur(22px);
            -webkit-backdrop-filter: blur(22px);
            box-shadow:
                0 18px 55px rgba(15,23,42,.08),
                inset 0 1px 0 rgba(255,255,255,.95);
        }

        .signatures-management::before {
            content: "";
            position: absolute;
            width: 240px;
            height: 240px;
            top: -150px;
            right: -80px;
            border-radius: 50%;
            background: rgba(0,107,63,.045);
            pointer-events: none;
        }

        .signatures-header {
            position: relative;
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 24px;
        }

        .section-label {
            margin-bottom: 7px;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 1.5px;
            color: #006b3f;
        }

        .signatures-header h2 {
            margin: 0;
            font-size: 23px;
            line-height: 1.2;
            font-weight: 800;
            color: #111827;
        }

        .signatures-header p {
            margin: 7px 0 0;
            color: #6b7280;
            font-size: 13px;
            line-height: 1.5;
        }

        /* ==================================================
           FILTERS
        ================================================== */

        .signature-filters {
            position: relative;
            display: grid;
            grid-template-columns:
                minmax(250px, 1fr)
                280px
                auto;
            gap: 14px;
            align-items: end;
            margin-bottom: 20px;
            padding: 18px;
            border: 1px solid rgba(226,232,240,.95);
            border-radius: 16px;
            background: rgba(248,250,252,.72);
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .filter-group label {
            font-size: 11px;
            font-weight: 800;
            color: #374151;
        }

        .signature-search {
            position: relative;
        }

        .signature-search-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            font-size: 14px;
            color: #006b3f;
        }

        .signature-search input,
        .signature-filters select {
            width: 100%;
            height: 46px;
            box-sizing: border-box;
            border: 1px solid #d7dde5;
            border-radius: 11px;
            background: rgba(255,255,255,.94);
            color: #374151;
            font-family: inherit;
            font-size: 13px;
            outline: none;
            transition:
                border-color .18s ease,
                box-shadow .18s ease,
                background .18s ease;
        }

        .signature-search input {
            padding: 0 14px 0 40px;
        }

        .signature-filters select {
            padding: 0 12px;
        }

        .signature-search input:focus,
        .signature-filters select:focus {
            border-color: #006b3f;
            background: #ffffff;
            box-shadow:
                0 0 0 3px rgba(0,107,63,.09);
        }

        .signature-filter-actions {
            display: flex;
            gap: 8px;
        }

        .signature-btn-search,
        .signature-btn-clear {
            height: 46px;
            padding: 0 18px;
            border-radius: 11px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
            font-family: inherit;
            font-size: 12px;
            font-weight: 800;
            text-decoration: none;
            cursor: pointer;
            transition: all .18s ease;
        }

        .signature-btn-search {
            border: 1px solid #006b3f;
            background: linear-gradient(
                135deg,
                #006b3f,
                #005b36
            );
            color: #ffffff;
            box-shadow:
                0 7px 18px rgba(0,107,63,.18);
        }

        .signature-btn-search:hover {
            transform: translateY(-1px);
            box-shadow:
                0 10px 23px rgba(0,107,63,.24);
        }

        .signature-btn-clear {
            border: 1px solid #d6dbe2;
            background: rgba(255,255,255,.9);
            color: #374151;
        }

        .signature-btn-clear:hover {
            background: #ffffff;
            border-color: #cbd5e1;
        }

        /* ==================================================
           RESULTS BAR
        ================================================== */

        .signature-results-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 13px;
        }

        .signature-results-count {
            color: #6b7280;
            font-size: 12px;
        }

        .signature-results-count strong {
            color: #111827;
            font-weight: 800;
        }

        /* ==================================================
           TABLE WRAPPER
        ================================================== */

        .signatures-table-wrapper {
            width: 100%;
            overflow-x: auto;
            border: 1px solid rgba(226,232,240,.95);
            border-radius: 16px;
            background: rgba(255,255,255,.76);
        }

        .signatures-table {
            width: 100%;
            min-width: 1020px;
            border-collapse: separate;
            border-spacing: 0;
            background: transparent;
        }

        .signatures-table thead {
            background: rgba(248,250,252,.88);
        }

        .signatures-table th {
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .75px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .signatures-table td {
            padding: 15px 16px;
            vertical-align: middle;
            border-bottom: 1px solid rgba(226,232,240,.72);
        }

        .signatures-table tbody tr {
            transition: background .16s ease;
        }

        .signatures-table tbody tr:hover {
            background: rgba(248,250,252,.72);
        }

        .signatures-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* ==================================================
           SIGNER
        ================================================== */

        .signer-info {
            display: flex;
            align-items: center;
            gap: 11px;
        }

        .signer-avatar {
            width: 40px;
            height: 40px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 13px;
            background:
                linear-gradient(
                    145deg,
                    rgba(0,107,63,.12),
                    rgba(30,180,212,.10)
                );
            border: 1px solid rgba(0,107,63,.10);
            color: #006b3f;
            font-size: 14px;
            font-weight: 900;
        }

        .signer-name {
            margin-bottom: 3px;
            color: #111827;
            font-size: 13px;
            font-weight: 800;
        }

        .signer-id {
            color: #9ca3af;
            font-size: 10px;
        }

        /* ==================================================
           CONTACT
        ================================================== */

        .signature-contact {
            color: #374151;
            font-size: 12px;
            line-height: 1.6;
        }

        .signature-contact span {
            display: block;
            margin-top: 1px;
            color: #9ca3af;
            font-size: 10px;
        }

        .signature-contact .contact-icon-email {
            color: #38bdf8;
            margin-right: 5px;
        }

        .signature-contact .contact-icon-phone {
            color: #22c55e;
            margin-right: 5px;
        }

        /* ==================================================
           PETITION
        ================================================== */

        .petition-info {
            max-width: 310px;
        }

        .petition-info a {
            display: inline-block;
            color: #006b3f;
            text-decoration: none;
            font-size: 12px;
            font-weight: 800;
            line-height: 1.4;
        }

        .petition-info a:hover {
            text-decoration: underline;
        }

        .petition-id {
            display: block;
            margin-top: 3px;
            color: #9ca3af;
            font-size: 10px;
        }

        .petition-id i {
            color: #22c55e;
            margin-right: 4px;
        }

        /* ==================================================
           COUNTRY
        ================================================== */

        .signature-country {
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            background: rgba(248,250,252,.9);
            color: #374151;
            font-size: 10px;
            font-weight: 800;
            white-space: nowrap;
        }

        .signature-country i {
            color: #06b6d4;
            margin-right: 5px;
        }

        .signature-country-empty {
            color: #9ca3af;
            font-size: 10px;
        }

        .signature-country-empty i {
            margin-right: 5px;
            color: #94a3b8;
        }

        /* ==================================================
           DATE
        ================================================== */

        .signature-date {
            color: #4b5563;
            font-size: 11px;
            white-space: nowrap;
        }

        .signature-date i {
            color: #f59e0b;
            margin-right: 5px;
        }

        /* ==================================================
           ACTIONS
        ================================================== */

        .signature-actions {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .signature-actions a,
        .signature-actions button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 7px 10px;
            border-radius: 8px;
            font-family: inherit;
            font-size: 10px;
            font-weight: 800;
            text-decoration: none;
            cursor: pointer;
            transition: all .16s ease;
        }

        .signature-view {
            border: 1px solid #d1fae5;
            background: #ecfdf5;
            color: #047857;
        }

        .signature-view:hover {
            background: #d1fae5;
        }

        .signature-view i {
            color: #059669;
            margin-right: 5px;
        }

        .signature-delete {
            border: 1px solid #fee2e2;
            background: #fef2f2;
            color: #dc2626;
        }

        .signature-delete:hover {
            background: #fee2e2;
        }

        .signature-delete i {
            color: #dc2626;
            margin-right: 5px;
        }

        .delete-signature-form {
            margin: 0;
        }

        /* ==================================================
           EMPTY STATE
        ================================================== */

        .signatures-empty {
            padding: 65px 20px !important;
            text-align: center;
        }

        .signature-empty-icon {
            width: 58px;
            height: 58px;
            margin: 0 auto 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 17px;
            background: rgba(0,107,63,.07);
            border: 1px solid rgba(0,107,63,.08);
            font-size: 25px;
            color: #a78bfa;
        }

        .signatures-empty strong {
            display: block;
            margin-bottom: 5px;
            color: #374151;
            font-size: 15px;
            font-weight: 800;
        }

        .signatures-empty p {
            margin: 0;
            color: #9ca3af;
            font-size: 12px;
        }

        /* ==================================================
           PAGINATION
        ================================================== */

        .signature-pagination {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-top: 22px;
            flex-wrap: wrap;
        }

        .signature-pagination a,
        .signature-pagination span {
            min-width: 36px;
            height: 36px;
            padding: 0 9px;
            box-sizing: border-box;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            text-decoration: none;
            font-size: 11px;
            font-weight: 800;
        }

        .signature-pagination a {
            border: 1px solid #e2e8f0;
            background: rgba(255,255,255,.86);
            color: #374151;
        }

        .signature-pagination a:hover {
            background: #ffffff;
            border-color: #cbd5e1;
        }

        .signature-pagination .current {
            border: 1px solid #006b3f;
            background: #006b3f;
            color: #ffffff;
            box-shadow: 0 6px 15px rgba(0,107,63,.18);
        }

        .signature-pagination .disabled {
            border: 1px solid #edf0f3;
            background: #f8fafc;
            color: #cbd5e1;
        }

        .signature-pagination .pagination-prev {
            color: #006b3f;
        }

        .signature-pagination .pagination-next {
            color: #006b3f;
        }

        /* ==================================================
           RESPONSIVE
        ================================================== */

        @media (max-width: 1050px) {

            .signature-filters {
                grid-template-columns: 1fr 1fr;
            }

            .signature-filter-actions {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 850px) {

            .signatures-management {
                padding: 20px;
                border-radius: 18px;
            }

            .signature-filters {
                grid-template-columns: 1fr;
            }

            .signature-filter-actions {
                grid-column: auto;
                width: 100%;
            }

            .signature-btn-search,
            .signature-btn-clear {
                flex: 1;
            }
        }

        @media (max-width: 650px) {

            .signature-page-intro h1 {
                font-size: 25px;
            }

            .signature-page-intro p {
                font-size: 13px;
            }

            .signatures-header h2 {
                font-size: 20px;
            }

            .signature-cards {
                gap: 12px;
            }

            .signatures-management {
                padding: 15px;
            }

            .signature-filters {
                padding: 14px;
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

        <a
            href="admin_signatures.php"
            class="active"
        >

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

    <!-- ==================================================
         MAIN
    =================================================== -->

    <main class="admin-main">

        <!-- TOP BAR -->

        <header class="admin-topbar">

            <h2>

                <i class="fa-solid fa-pen-nib page-title-icon"></i>

                Signature Management

            </h2>

            <div class="admin-user">

                <i class="fa-solid fa-user-shield"></i>

                Administrator

            </div>

        </header>

        <!-- CONTENT -->

        <div class="admin-content signatures-page">

            <!-- ==================================================
                 PAGE INTRO
            ================================================== -->

            <div class="signature-page-intro">

                <div class="signature-eyebrow">

                    <i class="fa-solid fa-file-signature"></i>

                    PETITION ACTIVITY

                </div>

                <h1>
                    Signatures
                </h1>

                <p>
                    Monitor and manage signatures submitted
                    across the Tanzania Petition Platform.
                </p>

            </div>

            <!-- ==================================================
                 FLASH MESSAGE
            ================================================== -->

            <?php if (!empty($flash)): ?>

                <div
                    class="signature-flash
                    <?= ($flash["type"] ?? "") === "success"
                        ? "flash-success"
                        : "flash-error" ?>"
                >

                    <div class="signature-flash-icon">

                        <?php if (($flash["type"] ?? "") === "success"): ?>

                            <i class="fa-solid fa-circle-check"></i>

                        <?php else: ?>

                            <i class="fa-solid fa-triangle-exclamation"></i>

                        <?php endif; ?>

                    </div>

                    <div>

                        <?= e($flash["message"] ?? "") ?>

                    </div>

                </div>

            <?php endif; ?>

            <!-- ==================================================
                 STATISTICS
            ================================================== -->

            <div class="admin-cards signature-cards">

                <!-- TOTAL -->

                <a
                    href="admin_signatures.php"
                    class="admin-stat-card signature-stat-card"
                >

                    <div class="admin-stat-icon signature-total-icon">

                        <i class="fa-solid fa-pen-nib"></i>

                    </div>

                    <div class="admin-stat-title">
                        Total Signatures
                    </div>

                    <div class="admin-stat-number">

                        <?= number_format($total_signatures) ?>

                    </div>

                    <div class="admin-stat-link">
                        View all →
                    </div>

                </a>

                <!-- TODAY -->

                <a
                    href="admin_signatures.php"
                    class="admin-stat-card signature-stat-card"
                >

                    <div class="admin-stat-icon signature-today-icon">

                        <i class="fa-solid fa-calendar-day"></i>

                    </div>

                    <div class="admin-stat-title">
                        Today's Signatures
                    </div>

                    <div class="admin-stat-number">

                        <?= number_format($today_signatures) ?>

                    </div>

                    <div class="admin-stat-link">
                        Submitted today
                    </div>

                </a>

                <!-- PETITIONS -->

                <a
                    href="admin_petitions.php"
                    class="admin-stat-card signature-stat-card"
                >

                    <div class="admin-stat-icon signature-petitions-icon">

                        <i class="fa-solid fa-file-signature"></i>

                    </div>

                    <div class="admin-stat-title">
                        Petitions Signed
                    </div>

                    <div class="admin-stat-number">

                        <?= number_format($petition_count) ?>

                    </div>

                    <div class="admin-stat-link">
                        View petitions →
                    </div>

                </a>

            </div>

            <!-- ==================================================
                 MANAGEMENT PANEL
            ================================================== -->

            <section class="signatures-management">

                <div class="signatures-header">

                    <div>

                        <div class="section-label">
                            SIGNATURE MANAGEMENT
                        </div>

                        <h2>
                            All Signatures
                        </h2>

                        <p>
                            Search, filter and manage petition
                            signatures.
                        </p>

                    </div>

                </div>

                <!-- ==================================================
                     FILTERS
                ================================================== -->

                <form
                    method="GET"
                    action="admin_signatures.php"
                    class="signature-filters"
                >

                    <!-- SEARCH -->

                    <div class="filter-group">

                        <label for="signature-search">
                            Search signatures
                        </label>

                        <div class="signature-search">

                            <span class="signature-search-icon">

                                <i class="fa-solid fa-magnifying-glass"></i>

                            </span>

                            <input
                                type="text"
                                id="signature-search"
                                name="search"
                                placeholder="Signer, email, phone or petition..."
                                value="<?= e($search) ?>"
                            >

                        </div>

                    </div>

                    <!-- PETITION -->

                    <div class="filter-group">

                        <label for="petition">
                            Petition
                        </label>

                        <select
                            id="petition"
                            name="petition"
                        >

                            <option value="">
                                All Petitions
                            </option>

                            <?php foreach ($petitions as $petition): ?>

                                <option
                                    value="<?= (int) $petition["id"] ?>"
                                    <?= $petition_filter === (int) $petition["id"]
                                        ? "selected"
                                        : "" ?>
                                >

                                    <?= e($petition["title"]) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <!-- BUTTONS -->

                    <div class="signature-filter-actions">

                        <button
                            type="submit"
                            class="signature-btn-search"
                        >

                            Search

                        </button>

                        <a
                            href="admin_signatures.php"
                            class="signature-btn-clear"
                        >

                            Clear

                        </a>

                    </div>

                </form>

                <!-- ==================================================
                     RESULTS
                ================================================== -->

                <div class="signature-results-bar">

                    <div class="signature-results-count">

                        Showing

                        <strong>

                            <?= number_format($total_filtered) ?>

                        </strong>

                        signature<?= $total_filtered === 1 ? "" : "s" ?>

                    </div>

                </div>

                <!-- ==================================================
                     TABLE
                ================================================== -->

                <div class="signatures-table-wrapper">

                    <table class="signatures-table">

                        <thead>

                            <tr>

                                <th>
                                    Signer
                                </th>

                                <th>
                                    Contact
                                </th>

                                <th>
                                    Petition
                                </th>

                                <th>
                                    Country
                                </th>

                                <th>
                                    Signed
                                </th>

                                <th>
                                    Actions
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php if (!empty($signatures)): ?>

                                <?php foreach ($signatures as $signature): ?>

                                    <?php

                                    $fullname = trim(
                                        $signature["fullname"] ?? ""
                                    );

                                    $initial = strtoupper(
                                        substr(
                                            $fullname ?: "U",
                                            0,
                                            1
                                        )
                                    );

                                    ?>

                                    <tr>

                                        <!-- SIGNER -->

                                        <td>

                                            <div class="signer-info">

                                                <div class="signer-avatar">

                                                    <?= e($initial) ?>

                                                </div>

                                                <div>

                                                    <div class="signer-name">

                                                        <?= e($fullname) ?>

                                                    </div>

                                                    <div class="signer-id">

                                                        User #<?= (int) $signature["user_id"] ?>

                                                    </div>

                                                </div>

                                            </div>

                                        </td>

                                        <!-- CONTACT -->

                                        <td>

                                            <div class="signature-contact">

                                                <i class="fa-solid fa-envelope contact-icon-email"></i>

                                                <?= e(
                                                    $signature["email"] ?? ""
                                                ) ?>

                                                <?php if (!empty($signature["phone"])): ?>

                                                    <span>

                                                        <i class="fa-solid fa-phone contact-icon-phone"></i>

                                                        <?= e(
                                                            $signature["phone"]
                                                        ) ?>

                                                    </span>

                                                <?php else: ?>

                                                    <span>

                                                        <i class="fa-solid fa-phone-slash"></i>

                                                        No phone number

                                                    </span>

                                                <?php endif; ?>

                                            </div>

                                        </td>

                                        <!-- PETITION -->

                                        <td>

                                            <div class="petition-info">

                                                <a
                                                    href="edit_petition.php?id=<?= (int) $signature["petition_id"] ?>"
                                                >

                                                    <?= e(
                                                        $signature["petition_title"]
                                                    ) ?>

                                                </a>

                                                <span class="petition-id">

                                                    <i class="fa-solid fa-file-signature"></i>

                                                    Petition #<?= (int) $signature["petition_id"] ?>

                                                </span>

                                            </div>

                                        </td>

                                        <!-- COUNTRY -->

                                        <td>

                                            <?php if (!empty($signature["country"])): ?>

                                                <span class="signature-country">

                                                    <i class="fa-solid fa-earth-africa"></i>

                                                    <?= e(
                                                        $signature["country"]
                                                    ) ?>

                                                </span>

                                            <?php else: ?>

                                                <span class="signature-country-empty">

                                                    <i class="fa-solid fa-circle-minus"></i>

                                                    Not specified

                                                </span>

                                            <?php endif; ?>

                                        </td>

                                        <!-- DATE -->

                                        <td>

                                            <div class="signature-date">

                                                <i class="fa-solid fa-calendar-days"></i>

                                                <?= e(
                                                    format_date(
                                                        $signature["created_at"]
                                                    )
                                                ) ?>

                                            </div>

                                        </td>

                                        <!-- ACTIONS -->

                                        <td>

                                            <div class="signature-actions">

                                                <a
                                                    href="admin_user_view.php?id=<?= (int) $signature["user_id"] ?>"
                                                    class="signature-view"
                                                >

                                                    <i class="fa-solid fa-user"></i>

                                                    View User

                                                </a>

                                                <form
                                                    method="POST"
                                                    action="admin_signatures.php"
                                                    class="delete-signature-form"
                                                    onsubmit="return confirm('Are you sure you want to delete this signature? This action cannot be undone.');"
                                                >

                                                    <?= csrf_field() ?>

                                                    <input
                                                        type="hidden"
                                                        name="action"
                                                        value="delete"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="signature_id"
                                                        value="<?= (int) $signature["id"] ?>"
                                                    >

                                                    <button
                                                        type="submit"
                                                        class="signature-delete"
                                                    >

                                                        <i class="fa-solid fa-trash-can"></i>

                                                        Delete

                                                    </button>

                                                </form>

                                            </div>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>

                                    <td
                                        colspan="6"
                                        class="signatures-empty"
                                    >

                                        <div class="signature-empty-icon">

                                            <i class="fa-solid fa-pen-nib"></i>

                                        </div>

                                        <strong>
                                            No signatures found
                                        </strong>

                                        <p>
                                            Try changing your search
                                            or petition filter.
                                        </p>

                                    </td>

                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

                <!-- ==================================================
                     PAGINATION
                ================================================== -->

                <?php if ($total_pages > 1): ?>

                    <div class="signature-pagination">

                        <?php if ($page > 1): ?>

                            <a
                                href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&petition=<?= $petition_filter ?>"
                                aria-label="Previous page"
                                class="pagination-prev"
                            >

                                <i class="fa-solid fa-chevron-left"></i>

                            </a>

                        <?php else: ?>

                            <span class="disabled">

                                <i class="fa-solid fa-chevron-left"></i>

                            </span>

                        <?php endif; ?>

                        <?php

                        $start_page = max(
                            1,
                            $page - 2
                        );

                        $end_page = min(
                            $total_pages,
                            $page + 2
                        );

                        ?>

                        <?php for (
                            $i = $start_page;
                            $i <= $end_page;
                            $i++
                        ): ?>

                            <?php if ($i === $page): ?>

                                <span class="current">

                                    <?= $i ?>

                                </span>

                            <?php else: ?>

                                <a
                                    href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&petition=<?= $petition_filter ?>"
                                >

                                    <?= $i ?>

                                </a>

                            <?php endif; ?>

                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>

                            <a
                                href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&petition=<?= $petition_filter ?>"
                                aria-label="Next page"
                                class="pagination-next"
                            >

                                <i class="fa-solid fa-chevron-right"></i>

                            </a>

                        <?php else: ?>

                            <span class="disabled">

                                <i class="fa-solid fa-chevron-right"></i>

                            </span>

                        <?php endif; ?>

                    </div>

                <?php endif; ?>

            </section>

        </div>

    </main>

</div>

</body>

</html>