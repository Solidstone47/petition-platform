<?php

require_once "../includes/db.php";
require_once "../includes/functions.php";
require_once "../includes/auth.php";

require_admin();


// ======================================================
// SETTINGS
// ======================================================

$per_page = 10;

$page = isset($_GET['page'])
    ? max(1, (int) $_GET['page'])
    : 1;

$search = trim($_GET['search'] ?? '');

$country_filter = trim($_GET['country'] ?? '');


// ======================================================
// HANDLE ACTIONS
// ======================================================

if (is_post()) {

    verify_csrf();

    $action = $_POST['action'] ?? '';

    $user_id = (int) ($_POST['user_id'] ?? 0);


    if ($user_id < 1) {

        set_flash(
            "error",
            "Invalid user."
        );

        redirect("admin_users.php");
    }


    // ==================================================
    // DELETE USER
    // ==================================================

    if ($action === "delete") {

        $stmt = $conn->prepare(
            "DELETE FROM users
             WHERE id = ?"
        );

        if (!$stmt) {

            set_flash(
                "error",
                "Unable to prepare delete request."
            );

            redirect("admin_users.php");
        }

        $stmt->bind_param(
            "i",
            $user_id
        );

        if ($stmt->execute()) {

            if ($stmt->affected_rows > 0) {

                set_flash(
                    "success",
                    "User deleted successfully."
                );

            } else {

                set_flash(
                    "error",
                    "User not found."
                );
            }

        } else {

            set_flash(
                "error",
                "Unable to delete user."
            );
        }

        $stmt->close();

        redirect("admin_users.php");
    }
}


// ======================================================
// GET COUNTRIES
// ======================================================

$countries = [];

$result = $conn->query(
    "SELECT DISTINCT country
     FROM users
     WHERE country IS NOT NULL
       AND country != ''
     ORDER BY country ASC"
);

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $countries[] = $row['country'];
    }
}


// ======================================================
// TOTAL USER COUNT
// ======================================================

$count_sql = "

    SELECT COUNT(*) AS total

    FROM users

    WHERE 1 = 1

";

$count_params = [];

$count_types = "";


if ($search !== '') {

    $count_sql .= "

        AND (

            fullname LIKE ?

            OR email LIKE ?

            OR phone LIKE ?

        )

    ";

    $search_value = "%" . $search . "%";

    $count_params[] = $search_value;

    $count_params[] = $search_value;

    $count_params[] = $search_value;

    $count_types .= "sss";
}


if ($country_filter !== '') {

    $count_sql .= "

        AND country = ?

    ";

    $count_params[] = $country_filter;

    $count_types .= "s";
}


$count_stmt = $conn->prepare($count_sql);

$total_users = 0;

if ($count_stmt) {

    if (!empty($count_params)) {

        $count_stmt->bind_param(
            $count_types,
            ...$count_params
        );
    }

    $count_stmt->execute();

    $count_result = $count_stmt->get_result();

    $count_row = $count_result->fetch_assoc();

    $total_users = (int) $count_row['total'];

    $count_stmt->close();
}


// ======================================================
// PAGINATION
// ======================================================

$total_pages = max(
    1,
    (int) ceil($total_users / $per_page)
);

if ($page > $total_pages) {

    $page = $total_pages;
}

$offset = ($page - 1) * $per_page;


// ======================================================
// GET USERS
// ======================================================

$sql = "

    SELECT

        id,

        fullname,

        email,

        phone,

        country,

        created_at

    FROM users

    WHERE 1 = 1

";

$params = [];

$types = "";


if ($search !== '') {

    $sql .= "

        AND (

            fullname LIKE ?

            OR email LIKE ?

            OR phone LIKE ?

        )

    ";

    $search_value = "%" . $search . "%";

    $params[] = $search_value;

    $params[] = $search_value;

    $params[] = $search_value;

    $types .= "sss";
}


if ($country_filter !== '') {

    $sql .= "

        AND country = ?

    ";

    $params[] = $country_filter;

    $types .= "s";
}


$sql .= "

    ORDER BY created_at DESC

    LIMIT ? OFFSET ?

";

$params[] = $per_page;

$params[] = $offset;

$types .= "ii";


$stmt = $conn->prepare($sql);

$users = [];

if ($stmt) {

    $stmt->bind_param(
        $types,
        ...$params
    );

    $stmt->execute();

    $user_result = $stmt->get_result();

    while ($row = $user_result->fetch_assoc()) {

        $users[] = $row;
    }

    $stmt->close();
}


// ======================================================
// FLASH MESSAGE
// ======================================================

$flash = get_flash();


// ======================================================
// HELPER FOR PAGINATION URL
// ======================================================

function users_page_url($page_number)
{
    global $search, $country_filter;

    $query = [
        'page' => $page_number
    ];

    if ($search !== '') {

        $query['search'] = $search;
    }

    if ($country_filter !== '') {

        $query['country'] = $country_filter;
    }

    return 'admin_users.php?' . http_build_query($query);
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

    <title>Users | Administration</title>


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

/* ==========================================================
   ADMIN USERS
========================================================== */

.users-page {

    max-width: 1500px;

    margin: 0 auto;
}


/* ==========================================================
   PAGE HERO
========================================================== */

.users-hero {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 20px;

    margin-bottom: 25px;
}

.users-hero-left {

    display: flex;

    align-items: center;

    gap: 16px;
}

.users-hero-icon {

    width: 60px;

    height: 60px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 18px;

    background:
        linear-gradient(
            145deg,
            rgba(0, 107, 63, 0.13),
            rgba(30, 180, 212, 0.10)
        );

    border: 1px solid rgba(255, 255, 255, 0.75);

    box-shadow:
        0 10px 25px rgba(15, 60, 45, 0.08),
        inset 0 1px 0 rgba(255, 255, 255, 0.75);

    backdrop-filter: blur(14px);

    -webkit-backdrop-filter: blur(14px);

    color: #006b3f;

    font-size: 26px;
}

.users-hero h1 {

    margin: 0;

    color: #17211c;

    font-size: 30px;

    line-height: 1.15;

    letter-spacing: -0.6px;
}

.users-hero p {

    margin: 6px 0 0;

    color: #6f7d76;

    font-size: 14px;
}


/* ==========================================================
   SUMMARY
========================================================== */

.users-summary {

    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap: 18px;

    margin-bottom: 24px;
}

.user-summary-card {

    position: relative;

    display: flex;

    align-items: center;

    gap: 16px;

    padding: 21px;

    overflow: hidden;

    border-radius: 19px;

    background:
        linear-gradient(
            135deg,
            rgba(255, 255, 255, 0.76),
            rgba(255, 255, 255, 0.52)
        );

    border: 1px solid rgba(255, 255, 255, 0.78);

    box-shadow:
        0 12px 30px rgba(25, 65, 48, 0.07),
        inset 0 1px 0 rgba(255, 255, 255, 0.75);

    backdrop-filter: blur(18px);

    -webkit-backdrop-filter: blur(18px);

    text-decoration: none;

    transition:
        transform 0.22s ease,
        box-shadow 0.22s ease,
        border-color 0.22s ease;
}

.user-summary-card::after {

    content: "";

    position: absolute;

    width: 130px;

    height: 130px;

    right: -65px;

    top: -65px;

    border-radius: 50%;

    background: rgba(0, 107, 63, 0.06);

    pointer-events: none;
}

.user-summary-card:hover {

    transform: translateY(-3px);

    border-color: rgba(0, 107, 63, 0.18);

    box-shadow:
        0 18px 38px rgba(25, 65, 48, 0.11),
        inset 0 1px 0 rgba(255, 255, 255, 0.85);
}

.summary-icon {

    position: relative;

    z-index: 1;

    width: 52px;

    height: 52px;

    flex-shrink: 0;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 15px;

    background:
        rgba(255, 255, 255, 0.70);

    border: 1px solid rgba(255, 255, 255, 0.85);

    box-shadow:
        0 7px 18px rgba(20, 45, 35, 0.07);

    color: #006b3f;

    font-size: 23px;
}

.user-summary-card:nth-child(2) .summary-icon {

    color: #0891b2;

    background:
        rgba(8, 145, 178, 0.10);
}

.summary-content {

    position: relative;

    z-index: 1;

    min-width: 0;
}

.summary-title {

    margin-bottom: 5px;

    color: #718078;

    font-size: 12px;

    font-weight: 800;

    text-transform: uppercase;

    letter-spacing: 0.6px;
}

.summary-number {

    color: #17211c;

    font-size: 28px;

    line-height: 1;

    font-weight: 850;
}

.summary-link {

    position: relative;

    z-index: 1;

    margin-left: auto;

    color: #006b3f;

    font-size: 12px;

    font-weight: 800;

    white-space: nowrap;
}


/* ==========================================================
   MAIN CONTAINER
========================================================== */

.users-container {

    overflow: hidden;

    border-radius: 20px;

    background:
        linear-gradient(
            145deg,
            rgba(255, 255, 255, 0.78),
            rgba(255, 255, 255, 0.57)
        );

    border: 1px solid rgba(255, 255, 255, 0.82);

    box-shadow:
        0 14px 38px rgba(20, 55, 42, 0.08),
        inset 0 1px 0 rgba(255, 255, 255, 0.8);

    backdrop-filter: blur(20px);

    -webkit-backdrop-filter: blur(20px);
}


/* ==========================================================
   CONTAINER HEADER
========================================================== */

.users-container-header {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 20px;

    padding: 24px 26px 20px;

    border-bottom:
        1px solid rgba(15, 23, 42, 0.06);
}

.users-container-title h2 {

    margin: 0;

    color: #18231e;

    font-size: 20px;
}

.users-container-title h2 i {

    color: #006b3f;

    margin-right: 7px;
}

.users-container-title p {

    margin: 5px 0 0;

    color: #75827c;

    font-size: 13px;
}

.results-count {

    display: inline-flex;

    align-items: center;

    padding: 8px 12px;

    border-radius: 999px;

    background:
        rgba(0, 107, 63, 0.08);

    border:
        1px solid rgba(0, 107, 63, 0.12);

    color: #006b3f;

    font-size: 12px;

    font-weight: 800;

    white-space: nowrap;
}

.results-count i {

    margin-right: 5px;
}


/* ==========================================================
   SEARCH AREA
========================================================== */

.users-search-area {

    padding: 19px 26px;

    background:
        rgba(248, 250, 249, 0.52);

    border-bottom:
        1px solid rgba(15, 23, 42, 0.06);
}

.users-filter-form {

    display: grid;

    grid-template-columns:
        minmax(250px, 1fr)
        230px
        auto;

    gap: 11px;

    align-items: center;
}

.search-input-wrapper {

    position: relative;
}

.search-input-icon {

    position: absolute;

    left: 14px;

    top: 50%;

    transform: translateY(-50%);

    color: #006b3f;

    font-size: 15px;

    pointer-events: none;
}

.users-filter-form input,
.users-filter-form select {

    width: 100%;

    height: 45px;

    box-sizing: border-box;

    outline: none;

    border:
        1px solid rgba(148, 163, 184, 0.30);

    border-radius: 12px;

    background:
        rgba(255, 255, 255, 0.70);

    color: #26332c;

    font-family: inherit;

    font-size: 13px;

    box-shadow:
        inset 0 1px 1px rgba(15, 23, 42, 0.025);

    transition:
        border-color 0.2s ease,
        box-shadow 0.2s ease,
        background 0.2s ease;
}

.users-filter-form input {

    padding: 0 14px 0 40px;
}

.users-filter-form select {

    padding: 0 12px;
}

.users-filter-form input:focus,
.users-filter-form select:focus {

    background:
        rgba(255, 255, 255, 0.90);

    border-color:
        rgba(0, 107, 63, 0.38);

    box-shadow:
        0 0 0 4px rgba(0, 107, 63, 0.07);
}

.filter-buttons {

    display: flex;

    gap: 8px;
}

.search-button,
.clear-button {

    height: 45px;

    padding: 0 18px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    border-radius: 12px;

    font-family: inherit;

    font-size: 12px;

    font-weight: 800;

    text-decoration: none;

    cursor: pointer;

    transition:
        transform 0.18s ease,
        box-shadow 0.18s ease,
        background 0.18s ease;
}

.search-button {

    border: 1px solid rgba(0, 107, 63, 0.15);

    background:
        linear-gradient(
            135deg,
            #006b3f,
            #08784a
        );

    color: #ffffff;

    box-shadow:
        0 7px 16px rgba(0, 107, 63, 0.14);
}

.search-button i,
.clear-button i {

    margin-right: 6px;
}

.search-button:hover {

    transform: translateY(-1px);

    box-shadow:
        0 10px 20px rgba(0, 107, 63, 0.18);
}

.clear-button {

    border:
        1px solid rgba(148, 163, 184, 0.30);

    background:
        rgba(255, 255, 255, 0.68);

    color: #52615a;
}

.clear-button:hover {

    transform: translateY(-1px);

    background:
        rgba(255, 255, 255, 0.92);
}


/* ==========================================================
   TABLE
========================================================== */

.users-table-scroll {

    width: 100%;

    overflow-x: auto;

    scrollbar-width: thin;
}

.modern-users-table {

    width: 100%;

    min-width: 980px;

    border-collapse: collapse;
}

.modern-users-table th {

    padding: 14px 22px;

    background:
        rgba(246, 249, 247, 0.78);

    border-bottom:
        1px solid rgba(15, 23, 42, 0.07);

    text-align: left;

    color: #718078;

    font-size: 10px;

    font-weight: 850;

    text-transform: uppercase;

    letter-spacing: 0.75px;
}

.modern-users-table td {

    padding: 17px 22px;

    border-bottom:
        1px solid rgba(15, 23, 42, 0.055);

    vertical-align: middle;
}

.modern-users-table tbody tr {

    transition:
        background 0.18s ease;
}

.modern-users-table tbody tr:hover {

    background:
        rgba(0, 107, 63, 0.025);
}

.modern-users-table tbody tr:last-child td {

    border-bottom: none;
}

.table-header-icon {

    margin-right: 6px;
}

.table-header-icon.user {

    color: #006b3f;
}

.table-header-icon.contact {

    color: #2563eb;
}

.table-header-icon.country {

    color: #0891b2;
}

.table-header-icon.date {

    color: #64748b;
}

.table-header-icon.actions {

    color: #8b5cf6;
}


/* ==========================================================
   USER IDENTITY
========================================================== */

.modern-user {

    display: flex;

    align-items: center;

    gap: 13px;
}

.modern-avatar {

    width: 43px;

    height: 43px;

    flex-shrink: 0;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 13px;

    background:
        linear-gradient(
            145deg,
            rgba(0, 107, 63, 0.13),
            rgba(30, 180, 212, 0.10)
        );

    border:
        1px solid rgba(255, 255, 255, 0.80);

    box-shadow:
        0 6px 14px rgba(20, 45, 35, 0.07);

    color: #006b3f;

    font-size: 15px;

    font-weight: 850;
}

.modern-user-name {

    margin-bottom: 3px;

    color: #1b2821;

    font-size: 14px;

    font-weight: 800;

    line-height: 1.25;
}

.modern-user-id {

    color: #98a39e;

    font-size: 10px;

    font-weight: 700;
}

.modern-user-id i {

    color: #94a3b8;
}


/* ==========================================================
   CONTACT
========================================================== */

.modern-contact {

    display: flex;

    flex-direction: column;

    gap: 4px;
}

.modern-email {

    color: #35443c;

    font-size: 12px;

    font-weight: 700;
}

.modern-email i {

    color: #2563eb;

    margin-right: 5px;
}

.modern-phone {

    color: #8a9690;

    font-size: 11px;
}

.modern-phone i {

    color: #16a34a;

    margin-right: 5px;
}


/* ==========================================================
   COUNTRY
========================================================== */

.modern-country {

    display: inline-flex;

    align-items: center;

    gap: 6px;

    padding: 6px 10px;

    border-radius: 999px;

    background:
        rgba(255, 255, 255, 0.62);

    border:
        1px solid rgba(148, 163, 184, 0.20);

    color: #52615a;

    font-size: 11px;

    font-weight: 750;

    white-space: nowrap;
}

.modern-country i {

    color: #0891b2;
}

.modern-country-empty {

    color: #9aa49f;

    font-size: 11px;
}

.modern-country-empty i {

    color: #94a3b8;

    margin-right: 4px;
}


/* ==========================================================
   DATE
========================================================== */

.modern-date {

    color: #596760;

    font-size: 11px;

    font-weight: 700;

    white-space: nowrap;
}

.modern-date i {

    color: #64748b;

    margin-right: 5px;
}


/* ==========================================================
   ACTIONS
========================================================== */

.modern-actions {

    display: flex;

    align-items: center;

    gap: 6px;

    flex-wrap: wrap;
}

.modern-delete-form {

    margin: 0;
}

.modern-action {

    min-height: 32px;

    padding: 0 10px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    box-sizing: border-box;

    border-radius: 9px;

    font-family: inherit;

    font-size: 10px;

    font-weight: 800;

    text-decoration: none;

    cursor: pointer;

    transition:
        transform 0.16s ease,
        box-shadow 0.16s ease,
        background 0.16s ease;
}

.modern-action i {

    margin-right: 5px;
}

.modern-action:hover {

    transform: translateY(-1px);
}

.modern-action-view {

    color: #006b3f;

    background:
        rgba(0, 107, 63, 0.075);

    border:
        1px solid rgba(0, 107, 63, 0.12);
}

.modern-action-view i {

    color: #006b3f;
}

.modern-action-view:hover {

    background:
        rgba(0, 107, 63, 0.13);
}

.modern-action-reset {

    color: #5263a7;

    background:
        rgba(82, 99, 167, 0.075);

    border:
        1px solid rgba(82, 99, 167, 0.12);
}

.modern-action-reset i {

    color: #5263a7;
}

.modern-action-reset:hover {

    background:
        rgba(82, 99, 167, 0.13);
}

.modern-action-delete {

    color: #b84a4a;

    background:
        rgba(184, 74, 74, 0.07);

    border:
        1px solid rgba(184, 74, 74, 0.12);
}

.modern-action-delete i {

    color: #b84a4a;
}

.modern-action-delete:hover {

    background:
        rgba(184, 74, 74, 0.13);
}


/* ==========================================================
   EMPTY STATE
========================================================== */

.users-empty-state {

    padding: 70px 20px;

    text-align: center;
}

.empty-state-icon {

    width: 64px;

    height: 64px;

    margin: 0 auto 15px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 18px;

    background:
        rgba(0, 107, 63, 0.07);

    border:
        1px solid rgba(0, 107, 63, 0.09);

    color: #006b3f;

    font-size: 27px;
}

.users-empty-state h3 {

    margin: 0 0 6px;

    color: #39473f;

    font-size: 16px;
}

.users-empty-state p {

    margin: 0;

    color: #909b95;

    font-size: 12px;
}


/* ==========================================================
   PAGINATION
========================================================== */

.users-pagination {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;

    padding: 16px 22px;

    background:
        rgba(248, 250, 249, 0.58);

    border-top:
        1px solid rgba(15, 23, 42, 0.055);
}

.pagination-info {

    color: #77837d;

    font-size: 11px;

    font-weight: 650;
}

.pagination-info i {

    color: #64748b;

    margin-right: 5px;
}

.pagination-links {

    display: flex;

    align-items: center;

    gap: 5px;
}

.pagination-link {

    min-width: 34px;

    height: 34px;

    padding: 0 9px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    box-sizing: border-box;

    border-radius: 9px;

    border:
        1px solid rgba(148, 163, 184, 0.25);

    background:
        rgba(255, 255, 255, 0.68);

    color: #596760;

    font-size: 11px;

    font-weight: 800;

    text-decoration: none;

    transition:
        transform 0.16s ease,
        background 0.16s ease,
        border-color 0.16s ease;
}

.pagination-link:hover {

    transform: translateY(-1px);

    background:
        rgba(255, 255, 255, 0.95);

    border-color:
        rgba(0, 107, 63, 0.18);
}

.pagination-link.active {

    background:
        linear-gradient(
            135deg,
            #006b3f,
            #08784a
        );

    border-color: #006b3f;

    color: #ffffff;

    box-shadow:
        0 5px 12px rgba(0, 107, 63, 0.14);
}


/* ==========================================================
   FLASH MESSAGES
========================================================== */

.users-flash {

    margin-bottom: 18px;

    padding: 13px 16px;

    border-radius: 13px;

    font-size: 12px;

    font-weight: 750;

    backdrop-filter: blur(12px);

    -webkit-backdrop-filter: blur(12px);
}

.users-flash-success {

    background:
        rgba(22, 131, 78, 0.09);

    border:
        1px solid rgba(22, 131, 78, 0.15);

    color: #137043;
}

.users-flash-error {

    background:
        rgba(199, 74, 74, 0.09);

    border:
        1px solid rgba(199, 74, 74, 0.15);

    color: #a63f3f;
}


/* ==========================================================
   SIDEBAR ICONS
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


/* ==========================================================
   RESPONSIVE
========================================================== */

@media (max-width: 1100px) {

    .users-filter-form {

        grid-template-columns:
            minmax(220px, 1fr)
            200px
            auto;
    }

    .modern-users-table {

        min-width: 950px;
    }
}


@media (max-width: 900px) {

    .users-summary {

        grid-template-columns: 1fr;
    }

    .users-filter-form {

        grid-template-columns: 1fr;
    }

    .filter-buttons {

        width: 100%;
    }

    .search-button,
    .clear-button {

        flex: 1;
    }
}


@media (max-width: 650px) {

    .users-hero {

        align-items: flex-start;
    }

    .users-hero-left {

        align-items: flex-start;
    }

    .users-hero-icon {

        width: 50px;

        height: 50px;

        border-radius: 15px;

        font-size: 22px;
    }

    .users-hero h1 {

        font-size: 24px;
    }

    .users-hero p {

        font-size: 12px;
    }

    .users-container-header {

        align-items: flex-start;

        flex-direction: column;

        padding: 20px;
    }

    .users-search-area {

        padding: 16px;
    }

    .users-pagination {

        align-items: stretch;

        flex-direction: column;
    }

    .pagination-links {

        justify-content: center;
    }

    .user-summary-card {

        padding: 18px;
    }

    .summary-link {

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


        <a href="admin_dashboard.php">

            <i class="fa-solid fa-gauge-high icon-dashboard"></i>

            <span>
                Dashboard
            </span>

        </a>


        <div class="admin-menu-title">
            Management
        </div>


        <a href="admin_petitions.php">

            <i class="fa-solid fa-file-signature icon-petitions"></i>

            <span>
                Petitions
            </span>

        </a>


        <a
            href="admin_users.php"
            class="active"
        >

            <i class="fa-solid fa-users icon-users"></i>

            <span>
                Users
            </span>

        </a>


        <a href="admin_messages.php">

            <i class="fa-solid fa-comments icon-messages"></i>

            <span>
                Messages
            </span>

        </a>


        <a href="admin_signatures.php">

            <i class="fa-solid fa-pen-nib icon-signatures"></i>

            <span>
                Signatures
            </span>

        </a>


        <div class="admin-menu-title">
            Analytics
        </div>


        <a href="admin_statistics.php">

            <i class="fa-solid fa-chart-line icon-statistics"></i>

            <span>
                Statistics
            </span>

        </a>


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


        <!-- ==================================================
             TOP BAR
        =================================================== -->

        <header class="admin-topbar">

            <div>

                <h2>

                    <i
                        class="fa-solid fa-users"
                        style="color:#38bdf8;margin-right:7px;"
                    ></i>

                    User Management

                </h2>

            </div>


            <div class="admin-user">

                <i
                    class="fa-solid fa-user-shield"
                    style="color:#006b3f;margin-right:6px;"
                ></i>

                Administrator

            </div>

        </header>


        <!-- ==================================================
             CONTENT
        =================================================== -->

        <div class="admin-content users-page">


            <!-- ==================================================
                 FLASH MESSAGE
            =================================================== -->

            <?php if (!empty($flash)): ?>

                <div
                    class="users-flash
                    <?= $flash['type'] === 'success'
                        ? 'users-flash-success'
                        : 'users-flash-error'
                    ?>"
                >

                    <?php if ($flash['type'] === 'success'): ?>

                        <i
                            class="fa-solid fa-circle-check"
                            style="margin-right:7px;"
                        ></i>

                    <?php else: ?>

                        <i
                            class="fa-solid fa-circle-exclamation"
                            style="margin-right:7px;"
                        ></i>

                    <?php endif; ?>

                    <?= e($flash['message']) ?>

                </div>

            <?php endif; ?>


            <!-- ==================================================
                 PAGE HERO
            =================================================== -->

            <div class="users-hero">


                <div class="users-hero-left">


                    <div class="users-hero-icon">

                        <i class="fa-solid fa-users"></i>

                    </div>


                    <div>

                        <h1>
                            Registered Users
                        </h1>

                        <p>
                            Monitor, search and manage registered
                            Petition Platform accounts.
                        </p>

                    </div>


                </div>


            </div>


            <!-- ==================================================
                 SUMMARY CARDS
            =================================================== -->

            <div class="users-summary">


                <!-- USERS -->

                <a
                    href="admin_users.php"
                    class="user-summary-card"
                >

                    <div class="summary-icon">

                        <i class="fa-solid fa-users"></i>

                    </div>


                    <div class="summary-content">

                        <div class="summary-title">
                            Users Found
                        </div>

                        <div class="summary-number">

                            <?= number_format(
                                $total_users
                            ) ?>

                        </div>

                    </div>


                    <div class="summary-link">

                        Current Results

                        <i class="fa-solid fa-arrow-right"></i>

                    </div>


                </a>


                <!-- COUNTRIES -->

                <a
                    href="admin_country_statistics.php"
                    class="user-summary-card"
                >

                    <div class="summary-icon">

                        <i class="fa-solid fa-earth-africa"></i>

                    </div>


                    <div class="summary-content">

                        <div class="summary-title">
                            Countries
                        </div>

                        <div class="summary-number">

                            <?= number_format(
                                count($countries)
                            ) ?>

                        </div>

                    </div>


                    <div class="summary-link">

                        View Statistics

                        <i class="fa-solid fa-arrow-right"></i>

                    </div>


                </a>


            </div>


            <!-- ==================================================
                 USER DIRECTORY
            =================================================== -->

            <div class="users-container">


                <!-- ==================================================
                     HEADER
                =================================================== -->

                <div class="users-container-header">


                    <div class="users-container-title">

                        <h2>

                            <i class="fa-solid fa-address-book"></i>

                            User Directory

                        </h2>

                        <p>
                            Browse and manage all registered accounts.
                        </p>

                    </div>


                    <div class="results-count">

                        <i class="fa-solid fa-users"></i>

                        <?= number_format(
                            $total_users
                        ) ?>

                        <?= $total_users === 1
                            ? 'user'
                            : 'users'
                        ?>

                    </div>


                </div>


                <!-- ==================================================
                     SEARCH / FILTER
                =================================================== -->

                <div class="users-search-area">


                    <form
                        method="GET"
                        action="admin_users.php"
                        class="users-filter-form"
                    >


                        <!-- SEARCH -->

                        <div class="search-input-wrapper">

                            <span class="search-input-icon">

                                <i class="fa-solid fa-magnifying-glass"></i>

                            </span>


                            <input
                                type="text"
                                name="search"
                                placeholder="Search name, email or phone..."
                                value="<?= e($search) ?>"
                            >

                        </div>


                        <!-- COUNTRY -->

                        <select name="country">

                            <option value="">

                                <i class="fa-solid fa-earth-africa"></i>

                                All Countries

                            </option>


                            <?php foreach ($countries as $country): ?>

                                <option
                                    value="<?= e($country) ?>"
                                    <?= $country_filter === $country
                                        ? 'selected'
                                        : ''
                                    ?>
                                >

                                    <?= e($country) ?>

                                </option>

                            <?php endforeach; ?>


                        </select>


                        <!-- BUTTONS -->

                        <div class="filter-buttons">


                            <button
                                type="submit"
                                class="search-button"
                            >

                                <i class="fa-solid fa-magnifying-glass"></i>

                                Search

                            </button>


                            <a
                                href="admin_users.php"
                                class="clear-button"
                            >

                                <i class="fa-solid fa-rotate-left"></i>

                                Clear

                            </a>


                        </div>


                    </form>


                </div>


                <!-- ==================================================
                     USERS TABLE
                =================================================== -->

                <div class="users-table-scroll">


                    <?php if (!empty($users)): ?>


                        <table class="modern-users-table">


                            <thead>


                                <tr>


                                    <th>

                                        <i
                                            class="fa-solid fa-user table-header-icon user"
                                        ></i>

                                        User

                                    </th>


                                    <th>

                                        <i
                                            class="fa-solid fa-address-card table-header-icon contact"
                                        ></i>

                                        Contact

                                    </th>


                                    <th>

                                        <i
                                            class="fa-solid fa-earth-africa table-header-icon country"
                                        ></i>

                                        Country

                                    </th>


                                    <th>

                                        <i
                                            class="fa-solid fa-calendar-check table-header-icon date"
                                        ></i>

                                        Registered

                                    </th>


                                    <th>

                                        <i
                                            class="fa-solid fa-sliders table-header-icon actions"
                                        ></i>

                                        Actions

                                    </th>


                                </tr>


                            </thead>


                            <tbody>


                                <?php foreach ($users as $user): ?>


                                    <?php

                                    $fullname = trim(
                                        $user['fullname'] ?? ''
                                    );

                                    $initial = strtoupper(
                                        substr(
                                            $fullname ?: 'U',
                                            0,
                                            1
                                        )
                                    );

                                    ?>


                                    <tr>


                                        <!-- USER -->

                                        <td>


                                            <div class="modern-user">


                                                <div class="modern-avatar">

                                                    <?= e(
                                                        $initial
                                                    ) ?>

                                                </div>


                                                <div>


                                                    <div class="modern-user-name">

                                                        <?= e(
                                                            $fullname
                                                        ) ?>

                                                    </div>


                                                    <div class="modern-user-id">

                                                        <i class="fa-solid fa-hashtag"></i>

                                                        ID #

                                                        <?= (int) $user['id'] ?>

                                                    </div>


                                                </div>


                                            </div>


                                        </td>


                                        <!-- CONTACT -->

                                        <td>


                                            <div class="modern-contact">


                                                <span class="modern-email">

                                                    <i class="fa-solid fa-envelope"></i>

                                                    <?= e(
                                                        $user['email']
                                                    ) ?>

                                                </span>


                                                <?php if (
                                                    !empty($user['phone'])
                                                ): ?>

                                                    <span class="modern-phone">

                                                        <i class="fa-solid fa-phone"></i>

                                                        <?= e(
                                                            $user['phone']
                                                        ) ?>

                                                    </span>

                                                <?php else: ?>

                                                    <span class="modern-phone">

                                                        <i class="fa-solid fa-phone-slash"></i>

                                                        No phone number

                                                    </span>

                                                <?php endif; ?>


                                            </div>


                                        </td>


                                        <!-- COUNTRY -->

                                        <td>


                                            <?php if (
                                                !empty($user['country'])
                                            ): ?>

                                                <span class="modern-country">

                                                    <i class="fa-solid fa-earth-africa"></i>

                                                    <?= e(
                                                        $user['country']
                                                    ) ?>

                                                </span>

                                            <?php else: ?>

                                                <span class="modern-country-empty">

                                                    <i class="fa-solid fa-circle-question"></i>

                                                    Not specified

                                                </span>

                                            <?php endif; ?>


                                        </td>


                                        <!-- DATE -->

                                        <td>


                                            <span class="modern-date">

                                                <i class="fa-solid fa-calendar-days"></i>

                                                <?= e(
                                                    format_date(
                                                        $user['created_at']
                                                    )
                                                ) ?>

                                            </span>


                                        </td>


                                        <!-- ACTIONS -->

                                        <td>


                                            <div class="modern-actions">


                                                <!-- VIEW -->

                                                <a
                                                    href="admin_user_view.php?id=<?= (int) $user['id'] ?>"
                                                    class="modern-action modern-action-view"
                                                >

                                                    <i class="fa-solid fa-eye"></i>

                                                    View

                                                </a>


                                                <!-- RESET -->

                                                <a
                                                    href="admin_user_reset.php?id=<?= (int) $user['id'] ?>"
                                                    class="modern-action modern-action-reset"
                                                >

                                                    <i class="fa-solid fa-key"></i>

                                                    Reset

                                                </a>


                                                <!-- DELETE -->

                                                <form
                                                    method="POST"
                                                    action="admin_users.php"
                                                    class="modern-delete-form"
                                                    onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');"
                                                >


                                                    <?= csrf_field() ?>


                                                    <input
                                                        type="hidden"
                                                        name="action"
                                                        value="delete"
                                                    >


                                                    <input
                                                        type="hidden"
                                                        name="user_id"
                                                        value="<?= (int) $user['id'] ?>"
                                                    >


                                                    <button
                                                        type="submit"
                                                        class="modern-action modern-action-delete"
                                                    >

                                                        <i class="fa-solid fa-trash-can"></i>

                                                        Delete

                                                    </button>


                                                </form>


                                            </div>


                                        </td>


                                    </tr>


                                <?php endforeach; ?>


                            </tbody>


                        </table>


                    <?php else: ?>


                        <!-- ==================================================
                             EMPTY STATE
                        =================================================== -->

                        <div class="users-empty-state">


                            <div class="empty-state-icon">

                                <i class="fa-solid fa-user-slash"></i>

                            </div>


                            <h3>
                                No users found
                            </h3>


                            <p>
                                Try changing your search or country filter.
                            </p>


                        </div>


                    <?php endif; ?>


                </div>


                <!-- ==================================================
                     PAGINATION
                =================================================== -->

                <?php if ($total_pages > 1): ?>


                    <div class="users-pagination">


                        <div class="pagination-info">

                            <i class="fa-solid fa-file-lines"></i>

                            Page

                            <?= number_format($page) ?>

                            of

                            <?= number_format($total_pages) ?>

                        </div>


                        <div class="pagination-links">


                            <?php if ($page > 1): ?>


                                <a
                                    href="<?= e(
                                        users_page_url(
                                            $page - 1
                                        )
                                    ) ?>"
                                    class="pagination-link"
                                    aria-label="Previous page"
                                >

                                    <i class="fa-solid fa-chevron-left"></i>

                                </a>


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


                                <a
                                    href="<?= e(
                                        users_page_url($i)
                                    ) ?>"
                                    class="pagination-link
                                        <?= $i === $page
                                            ? 'active'
                                            : ''
                                        ?>"
                                >

                                    <?= $i ?>

                                </a>


                            <?php endfor; ?>


                            <?php if ($page < $total_pages): ?>


                                <a
                                    href="<?= e(
                                        users_page_url(
                                            $page + 1
                                        )
                                    ) ?>"
                                    class="pagination-link"
                                    aria-label="Next page"
                                >

                                    <i class="fa-solid fa-chevron-right"></i>

                                </a>


                            <?php endif; ?>


                        </div>


                    </div>


                <?php endif; ?>


            </div>


        </div>


    </main>


</div>


</body>

</html>