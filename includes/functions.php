<?php

/**
 * Escape output safely for HTML.
 */
function e($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}


/**
 * Redirect to another page.
 */
function redirect($url)
{
    header("Location: " . $url);
    exit;
}


/**
 * Check whether the current request is POST.
 */
function is_post()
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}


/**
 * Generate a secure random token.
 */
function generate_token($length = 32)
{
    return bin2hex(random_bytes($length));
}


/**
 * Create a CSRF token.
 */
function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generate_token(32);
    }

    return $_SESSION['csrf_token'];
}


/**
 * Generate a hidden CSRF input.
 */
function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="' .
        e(csrf_token()) .
        '">';
}


/**
 * Verify a submitted CSRF token.
 */
function verify_csrf()
{
    if (
        !isset($_POST['csrf_token']) ||
        !isset($_SESSION['csrf_token']) ||
        !hash_equals(
            $_SESSION['csrf_token'],
            $_POST['csrf_token']
        )
    ) {
        http_response_code(403);
        die("Invalid security token.");
    }
}


/**
 * Set a flash message.
 */
function set_flash($type, $message)
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}


/**
 * Retrieve and remove the flash message.
 */
function get_flash()
{
    if (empty($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];

    unset($_SESSION['flash']);

    return $flash;
}


/**
 * Format a database timestamp.
 */
function format_date($date)
{
    if (empty($date)) {
        return '';
    }

    return date('F j, Y', strtotime($date));
}


/* ==========================================================
   GLOBAL PLATFORM SETTINGS
   ========================================================== */


/**
 * Get one setting from the settings table.
 *
 * Expected table structure:
 *
 * id
 * setting_key
 * setting_value
 * updated_at
 */
function get_setting($key, $default = '')
{
    global $conn;

    if (!$conn || empty($key)) {
        return $default;
    }

    static $settings_cache = [];

    /*
     * Return cached value if already loaded.
     */
    if (array_key_exists($key, $settings_cache)) {
        return $settings_cache[$key];
    }

    $stmt = $conn->prepare(
        "SELECT setting_value
         FROM settings
         WHERE setting_key = ?
         LIMIT 1"
    );

    if (!$stmt) {
        return $default;
    }

    $stmt->bind_param("s", $key);

    if (!$stmt->execute()) {
        $stmt->close();
        return $default;
    }

    $result = $stmt->get_result();

    if (!$result) {
        $stmt->close();
        return $default;
    }

    $row = $result->fetch_assoc();

    $stmt->close();

    if (!$row) {
        $settings_cache[$key] = $default;

        return $default;
    }

    $value = $row['setting_value'];

    $settings_cache[$key] = $value;

    return $value;
}


/**
 * Save or update a platform setting.
 */
function update_setting($key, $value)
{
    global $conn;

    if (!$conn || empty($key)) {
        return false;
    }

    /*
     * Check whether the setting already exists.
     */
    $check = $conn->prepare(
        "SELECT id
         FROM settings
         WHERE setting_key = ?
         LIMIT 1"
    );

    if (!$check) {
        return false;
    }

    $check->bind_param("s", $key);

    if (!$check->execute()) {
        $check->close();
        return false;
    }

    $result = $check->get_result();

    $existing = $result
        ? $result->fetch_assoc()
        : null;

    $check->close();


    /*
     * UPDATE existing setting.
     */
    if ($existing) {

        $stmt = $conn->prepare(
            "UPDATE settings
             SET setting_value = ?
             WHERE setting_key = ?"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "ss",
            $value,
            $key
        );

    }

    /*
     * INSERT new setting.
     */
    else {

        $stmt = $conn->prepare(
            "INSERT INTO settings
             (setting_key, setting_value)
             VALUES (?, ?)"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "ss",
            $key,
            $value
        );
    }


    $success = $stmt->execute();

    $stmt->close();


    /*
     * Clear cached version so the new
     * value is immediately available.
     */
    if ($success) {
        unset($GLOBALS['__settings_cache'][$key]);
    }

    return $success;
}


/**
 * Get all platform settings.
 */
function get_all_settings()
{
    global $conn;

    $settings = [];

    if (!$conn) {
        return $settings;
    }

    $result = $conn->query(
        "SELECT setting_key, setting_value
         FROM settings
         ORDER BY setting_key ASC"
    );

    if (!$result) {
        return $settings;
    }

    while ($row = $result->fetch_assoc()) {

        $settings[$row['setting_key']] =
            $row['setting_value'];
    }

    return $settings;
}


/**
 * Check whether maintenance mode is enabled.
 */
function maintenance_mode()
{
    $value = get_setting(
        'maintenance_mode',
        '0'
    );

    return (
        $value === '1' ||
        $value === 1 ||
        $value === true ||
        $value === 'true'
    );
}