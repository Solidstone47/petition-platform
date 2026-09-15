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
$status_filter = trim($_GET["status"] ?? "");


// ======================================================
// HANDLE ACTIONS
// ======================================================

if (is_post()) {

    verify_csrf();

    $action = $_POST["action"] ?? "";
    $message_id = (int) ($_POST["message_id"] ?? 0);

    if ($message_id < 1) {

        set_flash(
            "error",
            "Invalid message."
        );

        redirect("admin_messages.php");
    }


    // ==================================================
    // MARK AS READ
    // ==================================================

    if ($action === "mark_read") {

        $stmt = $conn->prepare(
            "UPDATE messages
             SET status = 'read'
             WHERE id = ?"
        );

        if ($stmt) {

            $stmt->bind_param(
                "i",
                $message_id
            );

            if ($stmt->execute()) {

                set_flash(
                    "success",
                    "Message marked as read."
                );

            } else {

                set_flash(
                    "error",
                    "Unable to update message."
                );
            }

            $stmt->close();

        } else {

            set_flash(
                "error",
                "Unable to prepare request."
            );
        }

        redirect("admin_messages.php");
    }


    // ==================================================
    // MARK AS UNREAD
    // ==================================================

    if ($action === "mark_unread") {

        $stmt = $conn->prepare(
            "UPDATE messages
             SET status = 'unread'
             WHERE id = ?"
        );

        if ($stmt) {

            $stmt->bind_param(
                "i",
                $message_id
            );

            if ($stmt->execute()) {

                set_flash(
                    "success",
                    "Message marked as unread."
                );

            } else {

                set_flash(
                    "error",
                    "Unable to update message."
                );
            }

            $stmt->close();

        } else {

            set_flash(
                "error",
                "Unable to prepare request."
            );
        }

        redirect("admin_messages.php");
    }


    // ==================================================
    // DELETE MESSAGE
    // ==================================================

    if ($action === "delete") {

        $stmt = $conn->prepare(
            "DELETE FROM messages
             WHERE id = ?"
        );

        if (!$stmt) {

            set_flash(
                "error",
                "Unable to prepare delete request."
            );

            redirect("admin_messages.php");
        }

        $stmt->bind_param(
            "i",
            $message_id
        );

        if ($stmt->execute()) {

            if ($stmt->affected_rows > 0) {

                set_flash(
                    "success",
                    "Message deleted successfully."
                );

            } else {

                set_flash(
                    "error",
                    "Message not found."
                );
            }

        } else {

            set_flash(
                "error",
                "Unable to delete message."
            );
        }

        $stmt->close();

        redirect("admin_messages.php");
    }
}


// ======================================================
// MESSAGE STATISTICS
// ======================================================

$total_messages = 0;
$total_unread = 0;
$total_read = 0;
$total_today = 0;


// TOTAL

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM messages"
);

if ($result) {

    $row = $result->fetch_assoc();

    $total_messages = (int) (
        $row["total"] ?? 0
    );
}


// UNREAD

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM messages
     WHERE status = 'unread'"
);

if ($result) {

    $row = $result->fetch_assoc();

    $total_unread = (int) (
        $row["total"] ?? 0
    );
}


// READ

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM messages
     WHERE status = 'read'"
);

if ($result) {

    $row = $result->fetch_assoc();

    $total_read = (int) (
        $row["total"] ?? 0
    );
}


// TODAY

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM messages
     WHERE DATE(created_at) = CURDATE()"
);

if ($result) {

    $row = $result->fetch_assoc();

    $total_today = (int) (
        $row["total"] ?? 0
    );
}


// ======================================================
// FILTERED MESSAGE COUNT
// ======================================================

$count_sql = "
    SELECT COUNT(*) AS total
    FROM messages
    WHERE 1 = 1
";

$count_params = [];
$count_types = "";

if ($search !== "") {

    $count_sql .= "
        AND (
            fullname LIKE ?
            OR email LIKE ?
            OR subject LIKE ?
            OR message LIKE ?
        )
    ";

    $search_value = "%" . $search . "%";

    $count_params[] = $search_value;
    $count_params[] = $search_value;
    $count_params[] = $search_value;
    $count_params[] = $search_value;

    $count_types .= "ssss";
}


if (
    $status_filter === "read" ||
    $status_filter === "unread"
) {

    $count_sql .= "
        AND status = ?
    ";

    $count_params[] = $status_filter;

    $count_types .= "s";
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

        $count_row =
            $count_result->fetch_assoc();

        $total_filtered = (int) (
            $count_row["total"] ?? 0
        );
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
// GET MESSAGES
// ======================================================

$sql = "
    SELECT
        id,
        fullname,
        email,
        subject,
        message,
        status,
        created_at
    FROM messages
    WHERE 1 = 1
";

$params = [];
$types = "";


if ($search !== "") {

    $sql .= "
        AND (
            fullname LIKE ?
            OR email LIKE ?
            OR subject LIKE ?
            OR message LIKE ?
        )
    ";

    $search_value = "%" . $search . "%";

    $params[] = $search_value;
    $params[] = $search_value;
    $params[] = $search_value;
    $params[] = $search_value;

    $types .= "ssss";
}


if (
    $status_filter === "read" ||
    $status_filter === "unread"
) {

    $sql .= "
        AND status = ?
    ";

    $params[] = $status_filter;

    $types .= "s";
}


$sql .= "
    ORDER BY
        CASE
            WHEN status = 'unread' THEN 0
            ELSE 1
        END,
        created_at DESC
    LIMIT ? OFFSET ?
";

$params[] = $per_page;
$params[] = $offset;

$types .= "ii";


$stmt = $conn->prepare($sql);

$messages = [];

if ($stmt) {

    $stmt->bind_param(
        $types,
        ...$params
    );

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result) {

        while ($row = $result->fetch_assoc()) {

            $messages[] = $row;
        }
    }

    $stmt->close();
}


// ======================================================
// FLASH
// ======================================================

$flash = get_flash();


// ======================================================
// PAGINATION URL
// ======================================================

function messages_page_url($page_number)
{
    global $search, $status_filter;

    $query = [
        "page" => $page_number
    ];

    if ($search !== "") {
        $query["search"] = $search;
    }

    if ($status_filter !== "") {
        $query["status"] = $status_filter;
    }

    return "admin_messages.php?" .
        http_build_query($query);
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
        Manage Messages | Petition Platform
    </title>

    <link
        rel="stylesheet"
        href="css/admin.css"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    <style>

        /* ==========================================================
           MESSAGE PAGE
        ========================================================== */

        .messages-page {
            max-width: 1500px;
            margin: 0 auto;
        }


        /* ==========================================================
           ICON COLORS
        ========================================================== */

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


        /* ==========================================================
           FLASH
        ========================================================== */

        .message-flash {
            display: flex;
            align-items: center;
            gap: 10px;

            padding: 14px 18px;
            margin-bottom: 22px;

            border-radius: 14px;

            font-size: 13px;
            font-weight: 700;

            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }

        .message-flash-success {
            color: #047857;
            background: rgba(236, 253, 245, .82);
            border: 1px solid rgba(167, 243, 208, .85);
        }

        .message-flash-error {
            color: #b91c1c;
            background: rgba(254, 242, 242, .82);
            border: 1px solid rgba(254, 202, 202, .85);
        }

        .message-flash i {
            font-size: 15px;
        }


        /* ==========================================================
           SUMMARY CARDS
        ========================================================== */

        .petition-summary-grid {
            display: grid;

            grid-template-columns:
                repeat(4, minmax(0, 1fr));

            gap: 18px;

            margin-bottom: 24px;
        }

        .petition-summary-card {
            position: relative;
            overflow: hidden;

            display: block;

            padding: 22px;

            border-radius: 18px;

            text-decoration: none;

            background:
                rgba(255, 255, 255, .62);

            border:
                1px solid rgba(255, 255, 255, .72);

            box-shadow:
                0 10px 30px rgba(20, 45, 35, .07);

            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);

            transition:
                transform .2s ease,
                box-shadow .2s ease;
        }

        .petition-summary-card:hover {
            transform: translateY(-3px);

            box-shadow:
                0 16px 36px rgba(20, 45, 35, .11);
        }

        .petition-summary-card::after {
            content: "";

            position: absolute;

            width: 110px;
            height: 110px;

            right: -45px;
            bottom: -55px;

            border-radius: 50%;

            background:
                rgba(0, 107, 63, .04);

            pointer-events: none;
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

            background:
                rgba(255, 255, 255, .72);

            border:
                1px solid rgba(255, 255, 255, .85);

            box-shadow:
                0 7px 18px rgba(20, 45, 35, .07);

            font-size: 18px;
        }

        .petition-summary-icon.message-total {
            color: #2563eb;
            background: rgba(37, 99, 235, .10);
        }

        .petition-summary-icon.message-unread {
            color: #d99b16;
            background: rgba(217, 155, 22, .12);
        }

        .petition-summary-icon.message-read {
            color: #16834e;
            background: rgba(22, 131, 78, .10);
        }

        .petition-summary-icon.message-today {
            color: #6d4aff;
            background: rgba(109, 74, 255, .10);
        }

        .petition-summary-label {
            color: #64748b;

            font-size: 12px;
            font-weight: 700;

            text-transform: uppercase;
            letter-spacing: .7px;
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


        /* ==========================================================
           MESSAGE PANEL
        ========================================================== */

        .admin-panel.messages-panel {
            overflow: hidden;

            border-radius: 18px;

            background:
                rgba(255, 255, 255, .62);

            border:
                1px solid rgba(255, 255, 255, .72);

            box-shadow:
                0 10px 30px rgba(20, 45, 35, .07);

            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }

        .messages-panel-header {
            display: flex;

            align-items: center;
            justify-content: space-between;

            gap: 18px;

            padding: 22px 24px;

            border-bottom:
                1px solid rgba(226, 232, 240, .75);
        }

        .messages-panel-title h2 {
            margin: 0;

            color: #17211c;

            font-size: 20px;
            line-height: 1.2;
        }

        .messages-panel-title h2 i {
            color: #006b3f;
            margin-right: 8px;
        }

        .messages-panel-title p {
            margin: 6px 0 0;

            color: #64748b;

            font-size: 12px;
        }

        .messages-total-badge {
            display: inline-flex;

            align-items: center;
            gap: 6px;

            padding: 7px 12px;

            border-radius: 999px;

            color: #475569;

            background:
                rgba(241, 245, 249, .82);

            border:
                1px solid rgba(226, 232, 240, .9);

            font-size: 11px;
            font-weight: 800;

            white-space: nowrap;
        }

        .messages-total-badge i {
            color: #006b3f;
        }


        /* ==========================================================
           FILTERS
        ========================================================== */

        .messages-filter-panel {
            padding: 20px 24px;

            background:
                rgba(248, 250, 252, .55);

            border-bottom:
                1px solid rgba(226, 232, 240, .72);
        }

        .messages-filters {
            display: grid;

            grid-template-columns:
                minmax(250px, 1fr)
                210px
                auto;

            gap: 12px;

            align-items: end;
        }

        .message-filter-group {
            display: flex;

            flex-direction: column;

            gap: 7px;
        }

        .message-filter-group label {
            display: flex;
            align-items: center;
            gap: 6px;

            color: #475569;

            font-size: 11px;
            font-weight: 800;
        }

        .message-filter-group label i {
            color: #006b3f;
        }

        .message-search-box {
            position: relative;
        }

        .message-search-icon {
            position: absolute;

            top: 50%;
            left: 14px;

            transform: translateY(-50%);

            color: #94a3b8;

            pointer-events: none;
        }

        .message-search-box input,
        .message-filter-group select {
            width: 100%;
            height: 44px;

            box-sizing: border-box;

            border:
                1px solid rgba(203, 213, 225, .95);

            border-radius: 11px;

            background:
                rgba(255, 255, 255, .82);

            color: #1e293b;

            font-family: inherit;
            font-size: 13px;

            outline: none;

            transition:
                border-color .2s ease,
                box-shadow .2s ease,
                background .2s ease;
        }

        .message-search-box input {
            padding: 0 13px 0 40px;
        }

        .message-filter-group select {
            padding: 0 12px;
        }

        .message-search-box input:focus,
        .message-filter-group select:focus {
            background: #fff;

            border-color: #006b3f;

            box-shadow:
                0 0 0 3px rgba(0, 107, 63, .10);
        }

        .message-filter-actions {
            display: flex;

            gap: 8px;
        }

        .message-filter-btn,
        .message-clear-btn {
            height: 44px;

            padding: 0 18px;

            display: inline-flex;

            align-items: center;
            justify-content: center;

            gap: 7px;

            border-radius: 11px;

            font-family: inherit;

            font-size: 12px;
            font-weight: 800;

            text-decoration: none;

            cursor: pointer;

            transition:
                transform .15s ease,
                box-shadow .15s ease,
                background .15s ease;
        }

        .message-filter-btn {
            border:
                1px solid #006b3f;

            background:
                #006b3f;

            color: #fff;
        }

        .message-filter-btn:hover {
            background: #005a35;

            transform: translateY(-1px);

            box-shadow:
                0 7px 16px rgba(0, 107, 63, .15);
        }

        .message-clear-btn {
            border:
                1px solid rgba(203, 213, 225, .95);

            background:
                rgba(255, 255, 255, .76);

            color: #475569;
        }

        .message-clear-btn:hover {
            background: #fff;

            transform: translateY(-1px);
        }


        /* ==========================================================
           RESULTS BAR
        ========================================================== */

        .messages-results-bar {
            display: flex;

            justify-content: space-between;
            align-items: center;

            padding: 15px 24px;

            border-bottom:
                1px solid rgba(226, 232, 240, .72);
        }

        .messages-results-count {
            display: flex;
            align-items: center;
            gap: 7px;

            color: #64748b;

            font-size: 12px;
        }

        .messages-results-count i {
            color: #006b3f;
        }

        .messages-results-count strong {
            color: #17211c;
        }


        /* ==========================================================
           MESSAGE LIST
        ========================================================== */

        .messages-list {
            display: flex;

            flex-direction: column;

            gap: 11px;

            padding: 18px 20px;
        }

        .message-row {
            display: flex;

            align-items: center;
            justify-content: space-between;

            gap: 18px;

            padding: 17px;

            border-radius: 15px;

            background:
                rgba(255, 255, 255, .72);

            border:
                1px solid rgba(226, 232, 240, .82);

            box-shadow:
                0 5px 18px rgba(15, 23, 42, .025);

            transition:
                transform .18s ease,
                border-color .18s ease,
                box-shadow .18s ease,
                background .18s ease;
        }

        .message-row:hover {
            transform: translateY(-2px);

            background:
                rgba(255, 255, 255, .92);

            border-color:
                rgba(203, 213, 225, .95);

            box-shadow:
                0 10px 25px rgba(15, 23, 42, .06);
        }

        .message-row.unread {
            border-left:
                4px solid #d99b16;

            background:
                linear-gradient(
                    135deg,
                    rgba(255, 251, 235, .82),
                    rgba(255, 255, 255, .76)
                );
        }

        .message-content-left {
            display: flex;

            align-items: flex-start;

            min-width: 0;

            flex: 1;

            gap: 13px;
        }


        /* ==========================================================
           AVATAR
        ========================================================== */

        .message-avatar {
            width: 44px;
            height: 44px;

            min-width: 44px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 13px;

            background:
                rgba(0, 107, 63, .10);

            border:
                1px solid rgba(0, 107, 63, .18);

            color: #006b3f;

            font-size: 15px;
            font-weight: 800;
        }


        /* ==========================================================
           MESSAGE INFORMATION
        ========================================================== */

        .message-main {
            min-width: 0;

            flex: 1;
        }

        .message-top {
            display: flex;

            align-items: center;

            flex-wrap: wrap;

            gap: 8px;

            margin-bottom: 6px;
        }

        .message-sender {
            display: inline-flex;

            align-items: center;

            gap: 6px;

            color: #17211c;

            font-size: 14px;
            font-weight: 800;
        }

        .message-sender i {
            color: #0891b2;

            font-size: 11px;
        }

        .message-email {
            display: inline-flex;

            align-items: center;

            gap: 5px;

            color: #94a3b8;

            font-size: 11px;
        }

        .message-email i {
            color: #64748b;
        }

        .message-subject {
            display: flex;

            align-items: center;

            gap: 7px;

            margin-bottom: 5px;

            overflow: hidden;

            text-overflow: ellipsis;

            white-space: nowrap;

            color: #334155;

            font-size: 13px;
            font-weight: 750;
        }

        .message-subject i {
            flex-shrink: 0;

            color: #8b5cf6;

            font-size: 12px;
        }

        .message-preview {
            max-width: 850px;

            overflow: hidden;

            text-overflow: ellipsis;

            white-space: nowrap;

            color: #64748b;

            font-size: 12px;

            line-height: 1.5;
        }

        .message-meta {
            display: flex;

            align-items: center;

            gap: 5px;

            margin-top: 7px;

            color: #94a3b8;

            font-size: 10px;
        }

        .message-meta i {
            color: #64748b;
        }


        /* ==========================================================
           STATUS
        ========================================================== */

        .message-status {
            display: inline-flex;

            align-items: center;

            gap: 5px;

            padding: 4px 8px;

            border-radius: 999px;

            font-size: 9px;

            font-weight: 900;

            text-transform: uppercase;

            letter-spacing: .5px;
        }

        .message-status-unread {
            color: #b77900;

            background:
                rgba(217, 155, 22, .10);

            border:
                1px solid rgba(217, 155, 22, .22);
        }

        .message-status-read {
            color: #16834e;

            background:
                rgba(22, 131, 78, .10);

            border:
                1px solid rgba(22, 131, 78, .18);
        }

        .message-status i {
            font-size: 8px;
        }


        /* ==========================================================
           ACTIONS
        ========================================================== */

        .message-actions {
            display: flex;

            align-items: center;

            justify-content: flex-end;

            gap: 6px;

            flex-wrap: wrap;

            flex-shrink: 0;
        }

        .message-action-form {
            margin: 0;
        }

        .message-actions a,
        .message-actions button {
            min-height: 32px;

            display: inline-flex;

            align-items: center;
            justify-content: center;

            gap: 6px;

            padding: 0 10px;

            border-radius: 8px;

            font-family: inherit;

            font-size: 10px;
            font-weight: 800;

            text-decoration: none;

            cursor: pointer;

            transition:
                background .15s ease,
                transform .15s ease,
                box-shadow .15s ease;
        }

        .message-actions a:hover,
        .message-actions button:hover {
            transform: translateY(-1px);
        }

        .message-action-view {
            color: #2563eb;

            background: #eff6ff;

            border:
                1px solid #dbeafe;
        }

        .message-action-view:hover {
            background: #dbeafe;

            box-shadow:
                0 5px 12px rgba(37, 99, 235, .10);
        }

        .message-action-status {
            color: #7c3aed;

            background: #f5f3ff;

            border:
                1px solid #ede9fe;
        }

        .message-action-status:hover {
            background: #ede9fe;
        }

        .message-action-delete {
            color: #dc2626;

            background: #fef2f2;

            border:
                1px solid #fee2e2;
        }

        .message-action-delete:hover {
            background: #fee2e2;
        }

        .message-action-view i {
            color: #2563eb;
        }

        .message-action-status i {
            color: #7c3aed;
        }

        .message-action-delete i {
            color: #dc2626;
        }


        /* ==========================================================
           EMPTY STATE
        ========================================================== */

        .messages-empty {
            margin: 18px 20px 20px;

            padding: 65px 20px;

            text-align: center;

            border-radius: 16px;

            background:
                rgba(248, 250, 252, .65);

            border:
                1px dashed rgba(203, 213, 225, .9);
        }

        .messages-empty-icon {
            width: 60px;
            height: 60px;

            margin: 0 auto 14px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 17px;

            background:
                rgba(0, 107, 63, .08);

            border:
                1px solid rgba(0, 107, 63, .14);

            color: #006b3f;

            font-size: 24px;
        }

        .messages-empty strong {
            display: block;

            margin-bottom: 6px;

            color: #334155;

            font-size: 16px;
        }

        .messages-empty p {
            margin: 0;

            color: #94a3b8;

            font-size: 12px;
        }


        /* ==========================================================
           PAGINATION
        ========================================================== */

        .messages-pagination {
            display: flex;

            align-items: center;
            justify-content: center;

            gap: 5px;

            padding: 20px 20px 24px;
        }

        .messages-pagination a,
        .messages-pagination span {
            min-width: 35px;
            height: 35px;

            display: inline-flex;

            align-items: center;
            justify-content: center;

            padding: 0 9px;

            border-radius: 9px;

            box-sizing: border-box;

            text-decoration: none;

            font-size: 11px;
            font-weight: 800;
        }

        .messages-pagination a {
            color: #475569;

            background:
                rgba(255, 255, 255, .8);

            border:
                1px solid #e2e8f0;
        }

        .messages-pagination a:hover {
            color: #006b3f;

            background: #fff;

            border-color: #cbd5e1;
        }

        .messages-pagination .current {
            color: #fff;

            background: #006b3f;

            border:
                1px solid #006b3f;

            box-shadow:
                0 6px 14px rgba(0, 107, 63, .15);
        }

        .messages-pagination .disabled {
            color: #cbd5e1;

            background: #f8fafc;

            border:
                1px solid #f1f5f9;
        }


        /* ==========================================================
           RESPONSIVE
        ========================================================== */

        @media (max-width: 1100px) {

            .petition-summary-grid {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }

        }


        @media (max-width: 900px) {

            .messages-filters {
                grid-template-columns:
                    1fr 1fr;
            }

            .message-filter-actions {
                grid-column: 1 / -1;
            }

            .message-row {
                align-items: flex-start;
            }

        }


        @media (max-width: 700px) {

            .petition-summary-grid {
                grid-template-columns: 1fr;
            }

            .messages-panel-header {
                padding: 20px;

                align-items: flex-start;

                flex-direction: column;
            }

            .messages-filter-panel {
                padding: 18px;
            }

            .messages-filters {
                grid-template-columns: 1fr;
            }

            .message-filter-actions {
                grid-column: auto;

                width: 100%;
            }

            .message-filter-btn,
            .message-clear-btn {
                flex: 1;
            }

            .messages-results-bar {
                padding: 15px 18px;
            }

            .messages-list {
                padding: 14px;
            }

            .message-row {
                flex-direction: column;

                align-items: stretch;
            }

            .message-actions {
                justify-content: flex-start;
            }

            .message-preview {
                white-space: normal;
            }

        }


        @media (max-width: 480px) {

            .petition-summary-card {
                padding: 18px;
            }

            .message-actions a,
            .message-actions button {
                flex: 1;
            }

            .message-action-form {
                flex: 1;
            }

            .message-actions {
                width: 100%;
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


        <!-- MAIN -->

        <div class="admin-menu-title">
            Main
        </div>


        <a href="admin_dashboard.php">

            <i class="fa-solid fa-gauge-high icon-dashboard"></i>

            <span>
                Dashboard
            </span>

        </a>


        <!-- MANAGEMENT -->

        <div class="admin-menu-title">
            Management
        </div>


        <a href="admin_petitions.php">

            <i class="fa-solid fa-file-signature icon-petitions"></i>

            <span>
                Petitions
            </span>

        </a>


        <a href="admin_users.php">

            <i class="fa-solid fa-users icon-users"></i>

            <span>
                Users
            </span>

        </a>


        <a
            href="admin_messages.php"
            class="active"
        >

            <i class="fa-solid fa-comments icon-messages"></i>

            <span>
                Messages
            </span>

            <?php if ($total_unread > 0): ?>

                <span class="admin-notification">
                    <?= number_format($total_unread) ?>
                </span>

            <?php endif; ?>

        </a>


        <a href="admin_signatures.php">

            <i class="fa-solid fa-pen-nib icon-signatures"></i>

            <span>
                Signatures
            </span>

        </a>


        <!-- ANALYTICS -->

        <div class="admin-menu-title">
            Analytics
        </div>


        <a href="admin_statistics.php">

            <i class="fa-solid fa-chart-line icon-statistics"></i>

            <span>
                Statistics
            </span>

        </a>


        <!-- SYSTEM -->

        <div class="admin-menu-title">
            System
        </div>


        <a href="admin_settings.php">

            <i class="fa-solid fa-gear icon-settings"></i>

            <span>
                Settings
            </span>

        </a>


        <a
            href="admin_logout.php"
            class="admin-logout"
        >

            <i class="fa-solid fa-right-from-bracket icon-logout"></i>

            <span>
                Logout
            </span>

        </a>


    </aside>


    <!-- ======================================================
         MAIN
    ======================================================= -->

    <main class="admin-main">


        <!-- TOP BAR -->

        <header class="admin-topbar">

            <div>

                <h2>

                    <i class="fa-solid fa-comments page-title-icon"></i>

                    Message Management

                </h2>

            </div>


            <div class="admin-user">

                <i class="fa-solid fa-user-shield"></i>

                Administrator

            </div>

        </header>


        <!-- CONTENT -->

        <div class="admin-content messages-page">


            <!-- ==================================================
                 PAGE HEADER
            ================================================== -->

            <div class="admin-page-header">

                <div>

                    <h1>

                        <i class="fa-solid fa-comments page-title-icon"></i>

                        Messages

                    </h1>

                    <p>
                        View, organize and manage messages
                        received from platform users.
                    </p>

                </div>

            </div>


            <!-- ==================================================
                 FLASH
            ================================================== -->

            <?php if (!empty($flash)): ?>

                <div
                    class="message-flash
                    <?= (
                        ($flash["type"] ?? "") === "success"
                    )
                        ? "message-flash-success"
                        : "message-flash-error"
                    ?>"
                >

                    <?php if (
                        ($flash["type"] ?? "") === "success"
                    ): ?>

                        <i class="fa-solid fa-circle-check"></i>

                    <?php else: ?>

                        <i class="fa-solid fa-triangle-exclamation"></i>

                    <?php endif; ?>


                    <span>
                        <?= e($flash["message"] ?? "") ?>
                    </span>

                </div>

            <?php endif; ?>


            <!-- ==================================================
                 SUMMARY
            ================================================== -->

            <div class="petition-summary-grid">


                <!-- TOTAL -->

                <a
                    href="admin_messages.php"
                    class="petition-summary-card"
                >

                    <div class="petition-summary-top">

                        <div
                            class="petition-summary-icon message-total"
                        >

                            <i class="fa-solid fa-comments"></i>

                        </div>

                        <span class="petition-summary-label">
                            Total Messages
                        </span>

                    </div>


                    <div class="petition-summary-value">
                        <?= number_format($total_messages) ?>
                    </div>


                    <div class="petition-summary-note">
                        All messages received
                    </div>

                </a>


                <!-- UNREAD -->

                <a
                    href="admin_messages.php?status=unread"
                    class="petition-summary-card"
                >

                    <div class="petition-summary-top">

                        <div
                            class="petition-summary-icon message-unread"
                        >

                            <i class="fa-solid fa-envelope"></i>

                        </div>

                        <span class="petition-summary-label">
                            Unread
                        </span>

                    </div>


                    <div class="petition-summary-value">
                        <?= number_format($total_unread) ?>
                    </div>


                    <div class="petition-summary-note">
                        Messages waiting for attention
                    </div>

                </a>


                <!-- READ -->

                <a
                    href="admin_messages.php?status=read"
                    class="petition-summary-card"
                >

                    <div class="petition-summary-top">

                        <div
                            class="petition-summary-icon message-read"
                        >

                            <i class="fa-solid fa-envelope-circle-check"></i>

                        </div>

                        <span class="petition-summary-label">
                            Read
                        </span>

                    </div>


                    <div class="petition-summary-value">
                        <?= number_format($total_read) ?>
                    </div>


                    <div class="petition-summary-note">
                        Messages already reviewed
                    </div>

                </a>


                <!-- TODAY -->

                <a
                    href="admin_messages.php"
                    class="petition-summary-card"
                >

                    <div class="petition-summary-top">

                        <div
                            class="petition-summary-icon message-today"
                        >

                            <i class="fa-solid fa-calendar-day"></i>

                        </div>

                        <span class="petition-summary-label">
                            Today
                        </span>

                    </div>


                    <div class="petition-summary-value">
                        <?= number_format($total_today) ?>
                    </div>


                    <div class="petition-summary-note">
                        Messages received today
                    </div>

                </a>


            </div>


            <!-- ==================================================
                 MAIN PANEL
            ================================================== -->

            <div class="admin-panel messages-panel">


                <!-- PANEL HEADER -->

                <div class="messages-panel-header">

                    <div class="messages-panel-title">

                        <h2>

                            <i class="fa-solid fa-inbox"></i>

                            User Messages

                        </h2>

                        <p>
                            Search, filter and manage incoming
                            messages.
                        </p>

                    </div>


                    <div class="messages-total-badge">

                        <i class="fa-solid fa-comments"></i>

                        <?= number_format($total_filtered) ?>

                        <?= $total_filtered === 1
                            ? "message"
                            : "messages"
                        ?>

                    </div>

                </div>


                <!-- ==================================================
                     FILTERS
                ================================================== -->

                <div class="messages-filter-panel">

                    <form
                        method="GET"
                        action="admin_messages.php"
                        class="messages-filters"
                    >


                        <!-- SEARCH -->

                        <div class="message-filter-group">

                            <label for="search">

                                <i class="fa-solid fa-magnifying-glass"></i>

                                Search messages

                            </label>


                            <div class="message-search-box">

                                <span class="message-search-icon">

                                    <i class="fa-solid fa-magnifying-glass"></i>

                                </span>


                                <input
                                    type="text"
                                    id="search"
                                    name="search"
                                    placeholder="Name, email, subject or message..."
                                    value="<?= e($search) ?>"
                                >

                            </div>

                        </div>


                        <!-- STATUS -->

                        <div class="message-filter-group">

                            <label for="status">

                                <i class="fa-solid fa-filter"></i>

                                Message status

                            </label>


                            <select
                                id="status"
                                name="status"
                            >

                                <option
                                    value=""
                                    <?= $status_filter === ""
                                        ? "selected"
                                        : ""
                                    ?>
                                >
                                    All Messages
                                </option>


                                <option
                                    value="unread"
                                    <?= $status_filter === "unread"
                                        ? "selected"
                                        : ""
                                    ?>
                                >
                                    Unread
                                </option>


                                <option
                                    value="read"
                                    <?= $status_filter === "read"
                                        ? "selected"
                                        : ""
                                    ?>
                                >
                                    Read
                                </option>

                            </select>

                        </div>


                        <!-- ACTIONS -->

                        <div class="message-filter-actions">

                            <button
                                type="submit"
                                class="message-filter-btn"
                            >

                                <i class="fa-solid fa-magnifying-glass"></i>

                                Search

                            </button>


                            <a
                                href="admin_messages.php"
                                class="message-clear-btn"
                            >

                                <i class="fa-solid fa-rotate-left"></i>

                                Clear

                            </a>

                        </div>


                    </form>

                </div>


                <!-- ==================================================
                     RESULTS BAR
                ================================================== -->

                <div class="messages-results-bar">

                    <div class="messages-results-count">

                        <i class="fa-solid fa-list"></i>

                        <span>
                            Showing
                        </span>

                        <strong>
                            <?= number_format(count($messages)) ?>
                        </strong>

                        <span>
                            of
                        </span>

                        <strong>
                            <?= number_format($total_filtered) ?>
                        </strong>

                        <span>
                            messages
                        </span>

                    </div>

                </div>


                <!-- ==================================================
                     MESSAGE LIST
                ================================================== -->

                <?php if (!empty($messages)): ?>

                    <div class="messages-list">


                        <?php foreach ($messages as $message): ?>

                            <?php

                            $fullname = trim(
                                $message["fullname"] ?? ""
                            );


                            $initial = strtoupper(
                                function_exists("mb_substr")
                                    ? mb_substr(
                                        $fullname ?: "U",
                                        0,
                                        1
                                    )
                                    : substr(
                                        $fullname ?: "U",
                                        0,
                                        1
                                    )
                            );


                            $is_unread =
                                ($message["status"] ?? "") === "unread";


                            $message_preview =
                                trim(
                                    $message["message"] ?? ""
                                );


                            if (
                                function_exists("mb_strlen") &&
                                mb_strlen($message_preview) > 160
                            ) {

                                $message_preview =
                                    mb_substr(
                                        $message_preview,
                                        0,
                                        160
                                    ) . "...";

                            } elseif (
                                !function_exists("mb_strlen") &&
                                strlen($message_preview) > 160
                            ) {

                                $message_preview =
                                    substr(
                                        $message_preview,
                                        0,
                                        160
                                    ) . "...";
                            }

                            ?>


                            <div
                                class="message-row
                                <?= $is_unread
                                    ? "unread"
                                    : ""
                                ?>"
                            >


                                <!-- MESSAGE INFORMATION -->

                                <div class="message-content-left">


                                    <div class="message-avatar">

                                        <?= e($initial) ?>

                                    </div>


                                    <div class="message-main">


                                        <div class="message-top">


                                            <span class="message-sender">

                                                <i class="fa-solid fa-user"></i>

                                                <?= e(
                                                    $fullname
                                                        ?: "Unknown User"
                                                ) ?>

                                            </span>


                                            <span class="message-email">

                                                <i class="fa-solid fa-envelope"></i>

                                                <?= e(
                                                    $message["email"] ?? ""
                                                ) ?>

                                            </span>


                                            <?php if ($is_unread): ?>

                                                <span
                                                    class="message-status message-status-unread"
                                                >

                                                    <i class="fa-solid fa-circle"></i>

                                                    Unread

                                                </span>

                                            <?php else: ?>

                                                <span
                                                    class="message-status message-status-read"
                                                >

                                                    <i class="fa-solid fa-circle-check"></i>

                                                    Read

                                                </span>

                                            <?php endif; ?>


                                        </div>


                                        <div class="message-subject">

                                            <i class="fa-solid fa-envelope-open-text"></i>

                                            <?= e(
                                                $message["subject"]
                                                    ?? "No subject"
                                            ) ?>

                                        </div>


                                        <div class="message-preview">

                                            <?= e(
                                                $message_preview
                                            ) ?>

                                        </div>


                                        <div class="message-meta">

                                            <i class="fa-solid fa-clock"></i>

                                            Received

                                            <?= e(
                                                format_date(
                                                    $message["created_at"]
                                                )
                                            ) ?>

                                        </div>


                                    </div>


                                </div>


                                <!-- ACTIONS -->

                                <div class="message-actions">


                                    <!-- VIEW -->

                                    <a
                                        href="admin_message_view.php?id=<?= (int) $message["id"] ?>"
                                        class="message-action-view"
                                    >

                                        <i class="fa-solid fa-eye"></i>

                                        View

                                    </a>


                                    <!-- READ / UNREAD -->

                                    <?php if ($is_unread): ?>

                                        <form
                                            method="POST"
                                            action="admin_messages.php"
                                            class="message-action-form"
                                        >

                                            <?= csrf_field() ?>


                                            <input
                                                type="hidden"
                                                name="action"
                                                value="mark_read"
                                            >


                                            <input
                                                type="hidden"
                                                name="message_id"
                                                value="<?= (int) $message["id"] ?>"
                                            >


                                            <button
                                                type="submit"
                                                class="message-action-status"
                                            >

                                                <i class="fa-solid fa-envelope-open"></i>

                                                Mark Read

                                            </button>

                                        </form>

                                    <?php else: ?>

                                        <form
                                            method="POST"
                                            action="admin_messages.php"
                                            class="message-action-form"
                                        >

                                            <?= csrf_field() ?>


                                            <input
                                                type="hidden"
                                                name="action"
                                                value="mark_unread"
                                            >


                                            <input
                                                type="hidden"
                                                name="message_id"
                                                value="<?= (int) $message["id"] ?>"
                                            >


                                            <button
                                                type="submit"
                                                class="message-action-status"
                                            >

                                                <i class="fa-solid fa-envelope"></i>

                                                Unread

                                            </button>

                                        </form>

                                    <?php endif; ?>


                                    <!-- DELETE -->

                                    <form
                                        method="POST"
                                        action="admin_messages.php"
                                        class="message-action-form"
                                        onsubmit="return confirm('Are you sure you want to delete this message? This action cannot be undone.');"
                                    >

                                        <?= csrf_field() ?>


                                        <input
                                            type="hidden"
                                            name="action"
                                            value="delete"
                                        >


                                        <input
                                            type="hidden"
                                            name="message_id"
                                            value="<?= (int) $message["id"] ?>"
                                        >


                                        <button
                                            type="submit"
                                            class="message-action-delete"
                                        >

                                            <i class="fa-solid fa-trash-can"></i>

                                            Delete

                                        </button>

                                    </form>


                                </div>


                            </div>


                        <?php endforeach; ?>


                    </div>


                <?php else: ?>


                    <!-- EMPTY -->

                    <div class="messages-empty">

                        <div class="messages-empty-icon">

                            <i class="fa-solid fa-inbox"></i>

                        </div>


                        <strong>
                            No messages found
                        </strong>


                        <p>
                            Try changing your search or
                            message status filter.
                        </p>

                    </div>


                <?php endif; ?>


                <!-- ==================================================
                     PAGINATION
                ================================================== -->

                <?php if ($total_pages > 1): ?>

                    <div class="messages-pagination">


                        <!-- PREVIOUS -->

                        <?php if ($page > 1): ?>

                            <a
                                href="<?= e(
                                    messages_page_url(
                                        $page - 1
                                    )
                                ) ?>"
                                aria-label="Previous page"
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


                        <!-- PAGE NUMBERS -->

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
                                    href="<?= e(
                                        messages_page_url($i)
                                    ) ?>"
                                >
                                    <?= $i ?>
                                </a>

                            <?php endif; ?>


                        <?php endfor; ?>


                        <!-- NEXT -->

                        <?php if ($page < $total_pages): ?>

                            <a
                                href="<?= e(
                                    messages_page_url(
                                        $page + 1
                                    )
                                ) ?>"
                                aria-label="Next page"
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


            </div>


        </div>


    </main>


</div>


</body>

</html>