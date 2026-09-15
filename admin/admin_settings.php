<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

require_once "../includes/db.php";
require_once "../includes/functions.php";
require_once "../includes/auth.php";

require_admin();

$errors = [];


/*
|--------------------------------------------------------------------------
| DEFAULT SETTINGS
|--------------------------------------------------------------------------
*/

$settings = [
    'site_name' => 'Petition Platform',
    'site_description' => 'A platform for creating and supporting petitions.',
    'contact_email' => '',
    'default_signature_goal' => '1000',
    'allow_registration' => '1',
    'allow_petitions' => '1',
    'maintenance_mode' => '0'
];


/*
|--------------------------------------------------------------------------
| LOAD SETTINGS
|--------------------------------------------------------------------------
*/

$result = $conn->query("
    SELECT setting_key, setting_value
    FROM settings
");

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $key = $row['setting_key'];

        if (array_key_exists($key, $settings)) {

            $settings[$key] = $row['setting_value'];
        }
    }
}


/*
|--------------------------------------------------------------------------
| HANDLE FORM SUBMISSION
|--------------------------------------------------------------------------
*/

if (is_post()) {

    verify_csrf();


    $site_name = trim(
        $_POST['site_name'] ?? ''
    );

    $site_description = trim(
        $_POST['site_description'] ?? ''
    );

    $contact_email = trim(
        $_POST['contact_email'] ?? ''
    );

    $default_signature_goal = (int) (
        $_POST['default_signature_goal'] ?? 0
    );

    $allow_registration =
        isset($_POST['allow_registration'])
            ? '1'
            : '0';

    $allow_petitions =
        isset($_POST['allow_petitions'])
            ? '1'
            : '0';

    $maintenance_mode =
        isset($_POST['maintenance_mode'])
            ? '1'
            : '0';


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if ($site_name === '') {

        $errors[] =
            "Platform name is required.";

    } elseif (strlen($site_name) > 255) {

        $errors[] =
            "Platform name cannot exceed 255 characters.";
    }


    if ($site_description === '') {

        $errors[] =
            "Platform description is required.";
    }


    if (
        $contact_email !== '' &&
        !filter_var(
            $contact_email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $errors[] =
            "Please enter a valid contact email.";
    }


    if ($default_signature_goal < 1) {

        $errors[] =
            "Default signature goal must be at least 1.";

    } elseif ($default_signature_goal > 1000000000) {

        $errors[] =
            "Default signature goal is too large.";
    }


    /*
    |--------------------------------------------------------------------------
    | SAVE SETTINGS
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        $new_settings = [

            'site_name' =>
                $site_name,

            'site_description' =>
                $site_description,

            'contact_email' =>
                $contact_email,

            'default_signature_goal' =>
                (string) $default_signature_goal,

            'allow_registration' =>
                $allow_registration,

            'allow_petitions' =>
                $allow_petitions,

            'maintenance_mode' =>
                $maintenance_mode
        ];


        $stmt = $conn->prepare("
            INSERT INTO settings
                (setting_key, setting_value)
            VALUES
                (?, ?)
            ON DUPLICATE KEY UPDATE
                setting_value = VALUES(setting_value)
        ");


        if (!$stmt) {

            $errors[] =
                "Unable to prepare settings update.";

        } else {

            foreach ($new_settings as $key => $value) {

                $stmt->bind_param(
                    "ss",
                    $key,
                    $value
                );

                if (!$stmt->execute()) {

                    $errors[] =
                        "Unable to save setting: " .
                        $key;

                    break;
                }
            }

            $stmt->close();


            if (empty($errors)) {

                set_flash(
                    "success",
                    "Settings saved successfully."
                );

                redirect(
                    "admin_settings.php"
                );
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | KEEP FORM VALUES
    |--------------------------------------------------------------------------
    */

    $settings['site_name'] =
        $site_name;

    $settings['site_description'] =
        $site_description;

    $settings['contact_email'] =
        $contact_email;

    $settings['default_signature_goal'] =
        (string) $default_signature_goal;

    $settings['allow_registration'] =
        $allow_registration;

    $settings['allow_petitions'] =
        $allow_petitions;

    $settings['maintenance_mode'] =
        $maintenance_mode;
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

    <title>
        Settings - Admin Panel
    </title>

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    <link
        rel="stylesheet"
        href="css/admin.css"
    >


<style>

/* ======================================================
   SETTINGS PAGE
====================================================== */

.settings-page {
    max-width: 1150px;
    margin: 0 auto;
}


/* ======================================================
   HEADER
====================================================== */

.settings-header {
    margin-bottom: 28px;
}

.section-label {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 1.3px;
    color: #6b7280;
    margin-bottom: 7px;
}

.settings-header h1 {
    margin: 0;
    font-size: 30px;
    font-weight: 800;
    color: #111827;
}

.settings-header p {
    margin: 8px 0 0;
    color: #6b7280;
    font-size: 14px;
}


/* ======================================================
   ALERTS
====================================================== */

.settings-alert {
    padding: 14px 17px;
    border-radius: 12px;
    margin-bottom: 20px;
    font-size: 14px;
}

.settings-alert-success {
    background: #ecfdf5;
    color: #047857;
    border: 1px solid #a7f3d0;
}

.settings-alert-error {
    background: #fef2f2;
    color: #b91c1c;
    border: 1px solid #fecaca;
}

.settings-alert p {
    margin: 4px 0;
}


/* ======================================================
   GRID
====================================================== */

.settings-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 22px;
}


/* ======================================================
   CARDS
====================================================== */

.settings-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 18px;
    padding: 25px;
    box-shadow:
        0 6px 20px rgba(15, 23, 42, 0.04);
}

.settings-card-full {
    grid-column: 1 / -1;
}


/* ======================================================
   CARD HEADER
====================================================== */

.settings-card-header {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 24px;
}

.settings-card-icon {
    width: 45px;
    height: 45px;
    border-radius: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f3f4f6;
    font-size: 20px;
    flex-shrink: 0;
}

.settings-card-icon.platform {
    color: #94a3b8;
    background: rgba(148, 163, 184, 0.10);
}

.settings-card-icon.petitions {
    color: #22c55e;
    background: rgba(34, 197, 94, 0.10);
}

.settings-card-icon.users {
    color: #38bdf8;
    background: rgba(56, 189, 248, 0.10);
}

.settings-card-icon.system {
    color: #f59e0b;
    background: rgba(245, 158, 11, 0.10);
}

.settings-card-header h2 {
    margin: 0;
    font-size: 18px;
    color: #111827;
}

.settings-card-header p {
    margin: 4px 0 0;
    color: #6b7280;
    font-size: 13px;
}


/* ======================================================
   FORM FIELDS
====================================================== */

.setting-group {
    margin-bottom: 20px;
}

.setting-group:last-child {
    margin-bottom: 0;
}

.setting-group label {
    display: block;
    margin-bottom: 8px;
    font-size: 13px;
    font-weight: 700;
    color: #374151;
}

.setting-group input,
.setting-group textarea {
    width: 100%;
    box-sizing: border-box;
    border: 1px solid #d1d5db;
    border-radius: 11px;
    padding: 12px 14px;
    background: #ffffff;
    color: #111827;
    font-family: inherit;
    font-size: 14px;
    outline: none;
    transition: 0.2s ease;
}

.setting-group input {
    height: 46px;
}

.setting-group textarea {
    min-height: 120px;
    resize: vertical;
}

.setting-group input:focus,
.setting-group textarea:focus {
    border-color: #2563eb;
    box-shadow:
        0 0 0 3px rgba(37, 99, 235, 0.10);
}

.setting-help {
    display: block;
    margin-top: 6px;
    color: #9ca3af;
    font-size: 12px;
}


/* ======================================================
   TOGGLE
====================================================== */

.setting-toggle {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    padding: 16px 0;
    border-bottom: 1px solid #f0f1f3;
}

.setting-toggle:first-child {
    padding-top: 0;
}

.setting-toggle:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.setting-toggle-info {
    flex: 1;
}

.setting-toggle-info strong {
    display: block;
    margin-bottom: 4px;
    color: #111827;
    font-size: 14px;
}

.setting-toggle-info span {
    display: block;
    color: #6b7280;
    font-size: 12px;
    line-height: 1.5;
}


/* ======================================================
   TOGGLE SWITCH
====================================================== */

.toggle-checkbox {
    position: relative;
    width: 48px;
    height: 26px;
    flex-shrink: 0;
}

.toggle-checkbox input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    inset: 0;
    cursor: pointer;
    background: #d1d5db;
    border-radius: 30px;
    transition: 0.2s ease;
}

.toggle-slider::before {
    content: "";
    position: absolute;
    width: 20px;
    height: 20px;
    left: 3px;
    top: 3px;
    background: #ffffff;
    border-radius: 50%;
    box-shadow:
        0 1px 4px rgba(0,0,0,0.15);
    transition: 0.2s ease;
}

.toggle-checkbox input:checked + .toggle-slider {
    background: #2563eb;
}

.toggle-checkbox input:checked + .toggle-slider::before {
    transform: translateX(22px);
}


/* ======================================================
   MAINTENANCE WARNING
====================================================== */

.maintenance-warning {
    margin-top: 12px;
    padding: 11px 13px;
    border-radius: 10px;
    background: #fff7ed;
    border: 1px solid #fed7aa;
    color: #c2410c;
    font-size: 12px;
    line-height: 1.5;
}


/* ======================================================
   SAVE BAR
====================================================== */

.settings-save-bar {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 10px;
    margin-top: 22px;
}

.settings-save {
    height: 44px;
    padding: 0 22px;
    border: none;
    border-radius: 10px;
    background: #111827;
    color: #ffffff;
    font-family: inherit;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: 0.2s ease;
}

.settings-save:hover {
    background: #1f2937;
    transform: translateY(-1px);
}

.settings-cancel {
    height: 42px;
    padding: 0 18px;
    border: 1px solid #d1d5db;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #ffffff;
    color: #374151;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
}


/* ======================================================
   ADMIN SIDEBAR ICONS
====================================================== */

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


/* ======================================================
   TOPBAR ICONS
====================================================== */

.page-title-icon {
    color: #006b3f;
    margin-right: 8px;
}

.admin-user i {
    color: #006b3f;
    margin-right: 7px;
}


/* ======================================================
   MOBILE
====================================================== */

@media (max-width: 800px) {

    .settings-grid {
        grid-template-columns: 1fr;
    }

    .settings-card-full {
        grid-column: auto;
    }

    .settings-header h1 {
        font-size: 25px;
    }

    .settings-card {
        padding: 20px;
    }

    .settings-save-bar {
        flex-direction: column-reverse;
        align-items: stretch;
    }

    .settings-save,
    .settings-cancel {
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


        <div class="admin-menu-title">
            Main
        </div>


        <a
            href="admin_dashboard.php">
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


        <a
            href="admin_settings.php"
            class="active">
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


        <header class="admin-topbar">

            <div>
                <h2>
                    <i class="fa-solid fa-gear page-title-icon"></i>
                    System Settings
                </h2>
            </div>


            <div class="admin-user">
                <i class="fa-solid fa-user-shield"></i>
                Administrator
            </div>

        </header>


        <div class="admin-content">


            <div class="settings-page">


                <!-- ==================================================
                     PAGE HEADER
                ================================================== -->

                <div class="settings-header">

                    <div class="section-label">
                        SYSTEM CONFIGURATION
                    </div>

                    <h1>
                        Settings
                    </h1>

                    <p>
                        Configure the main settings of your
                        petition platform.
                    </p>

                </div>


                <!-- ==================================================
                     FLASH MESSAGE
                ================================================== -->

                <?php if (!empty($flash)): ?>

                    <div class="settings-alert settings-alert-success">

                        <?= e(
                            $flash['message'] ?? ''
                        ) ?>

                    </div>

                <?php endif; ?>


                <!-- ==================================================
                     ERRORS
                ================================================== -->

                <?php if (!empty($errors)): ?>

                    <div class="settings-alert settings-alert-error">

                        <?php foreach ($errors as $error): ?>

                            <p>
                                <?= e($error) ?>
                            </p>

                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>


                <form
                    method="POST"
                    action="admin_settings.php"
                >

                    <?= csrf_field() ?>


                    <div class="settings-grid">


                        <!-- ==================================================
                             PLATFORM INFORMATION
                        ================================================== -->

                        <div class="settings-card settings-card-full">

                            <div class="settings-card-header">

                                <div class="settings-card-icon platform">
                                    <i class="fa-solid fa-sliders"></i>
                                </div>

                                <div>

                                    <h2>
                                        Platform Information
                                    </h2>

                                    <p>
                                        Basic information for your
                                        petition platform.
                                    </p>

                                </div>

                            </div>


                            <div class="setting-group">

                                <label for="site_name">
                                    Platform Name
                                </label>

                                <input
                                    type="text"
                                    id="site_name"
                                    name="site_name"
                                    maxlength="255"
                                    value="<?= e(
                                        $settings['site_name']
                                    ) ?>"
                                    required
                                >

                            </div>


                            <div class="setting-group">

                                <label for="site_description">
                                    Platform Description
                                </label>

                                <textarea
                                    id="site_description"
                                    name="site_description"
                                    required
                                ><?= e(
                                    $settings['site_description']
                                ) ?></textarea>

                            </div>


                            <div class="setting-group">

                                <label for="contact_email">
                                    Contact Email
                                </label>

                                <input
                                    type="email"
                                    id="contact_email"
                                    name="contact_email"
                                    maxlength="150"
                                    placeholder="admin@example.com"
                                    value="<?= e(
                                        $settings['contact_email']
                                    ) ?>"
                                >

                                <span class="setting-help">
                                    Main contact address for the platform.
                                </span>

                            </div>

                        </div>


                        <!-- ==================================================
                             PETITION SETTINGS
                        ================================================== -->

                        <div class="settings-card">

                            <div class="settings-card-header">

                                <div class="settings-card-icon petitions">
                                    <i class="fa-solid fa-file-signature"></i>
                                </div>

                                <div>

                                    <h2>
                                        Petition Settings
                                    </h2>

                                    <p>
                                        Configure petition defaults.
                                    </p>

                                </div>

                            </div>


                            <div class="setting-group">

                                <label for="default_signature_goal">
                                    Default Signature Goal
                                </label>

                                <input
                                    type="number"
                                    id="default_signature_goal"
                                    name="default_signature_goal"
                                    min="1"
                                    max="1000000000"
                                    value="<?= e(
                                        $settings[
                                            'default_signature_goal'
                                        ]
                                    ) ?>"
                                    required
                                >

                                <span class="setting-help">
                                    Default goal for newly created petitions.
                                </span>

                            </div>


                            <div class="setting-toggle">

                                <div class="setting-toggle-info">

                                    <strong>
                                        Allow New Petitions
                                    </strong>

                                    <span>
                                        Enable administrators to create
                                        new petitions.
                                    </span>

                                </div>


                                <label class="toggle-checkbox">

                                    <input
                                        type="checkbox"
                                        name="allow_petitions"
                                        <?= (
                                            (string) $settings[
                                                'allow_petitions'
                                            ] === '1'
                                        )
                                            ? 'checked'
                                            : '' ?>
                                    >

                                    <span class="toggle-slider"></span>

                                </label>

                            </div>

                        </div>


                        <!-- ==================================================
                             USER SETTINGS
                        ================================================== -->

                        <div class="settings-card">

                            <div class="settings-card-header">

                                <div class="settings-card-icon users">
                                    <i class="fa-solid fa-users"></i>
                                </div>

                                <div>

                                    <h2>
                                        User Settings
                                    </h2>

                                    <p>
                                        Configure account registration.
                                    </p>

                                </div>

                            </div>


                            <div class="setting-toggle">

                                <div class="setting-toggle-info">

                                    <strong>
                                        Allow User Registration
                                    </strong>

                                    <span>
                                        Allow visitors to create
                                        new accounts.
                                    </span>

                                </div>


                                <label class="toggle-checkbox">

                                    <input
                                        type="checkbox"
                                        name="allow_registration"
                                        <?= (
                                            (string) $settings[
                                                'allow_registration'
                                            ] === '1'
                                        )
                                            ? 'checked'
                                            : '' ?>
                                    >

                                    <span class="toggle-slider"></span>

                                </label>

                            </div>

                        </div>


                        <!-- ==================================================
                             SYSTEM CONTROLS
                        ================================================== -->

                        <div class="settings-card settings-card-full">

                            <div class="settings-card-header">

                                <div class="settings-card-icon system">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </div>

                                <div>

                                    <h2>
                                        System Controls
                                    </h2>

                                    <p>
                                        Manage platform availability.
                                    </p>

                                </div>

                            </div>


                            <div class="setting-toggle">

                                <div class="setting-toggle-info">

                                    <strong>
                                        Maintenance Mode
                                    </strong>

                                    <span>
                                        Temporarily disable public access
                                        while maintenance is being performed.
                                    </span>


                                    <?php if (
                                        (string) $settings[
                                            'maintenance_mode'
                                        ] === '1'
                                    ): ?>

                                        <div class="maintenance-warning">

                                            <i class="fa-solid fa-triangle-exclamation"></i>
                                            Maintenance mode is currently
                                            enabled.

                                        </div>

                                    <?php endif; ?>

                                </div>


                                <label class="toggle-checkbox">

                                    <input
                                        type="checkbox"
                                        name="maintenance_mode"
                                        <?= (
                                            (string) $settings[
                                                'maintenance_mode'
                                            ] === '1'
                                        )
                                            ? 'checked'
                                            : '' ?>
                                    >

                                    <span class="toggle-slider"></span>

                                </label>

                            </div>

                        </div>


                    </div>


                    <!-- ==================================================
                         SAVE BUTTONS
                    ================================================== -->

                    <div class="settings-save-bar">

                        <a
                            href="admin_dashboard.php"
                            class="settings-cancel"
                        >
                            Cancel
                        </a>


                        <button
                            type="submit"
                            class="settings-save"
                        >
                            Save Settings
                        </button>

                    </div>


                </form>


            </div>


        </div>


    </main>


</div>


</body>

</html>