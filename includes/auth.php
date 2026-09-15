<?php

/**
 * Start the session safely.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/**
 * Check whether a user is logged in.
 */
function is_user_logged_in()
{
    return isset($_SESSION['user_id']);
}


/**
 * Check whether an admin is logged in.
 */
function is_admin_logged_in()
{
    return isset($_SESSION['admin_id']);
}


/**
 * Require a logged-in user.
 */
function require_user()
{
    if (!is_user_logged_in()) {
        set_flash('error', 'Please log in to continue.');
        redirect('login.php');
    }
}


/**
 * Require a logged-in administrator.
 */
function require_admin()
{
    if (!is_admin_logged_in()) {
        set_flash('error', 'Administrator login required.');
        redirect('admin/login.php');
    }
}


/**
 * Log a user in.
 */
function login_user($user_id)
{
    session_regenerate_id(true);

    $_SESSION['user_id'] = $user_id;
}


/**
 * Log an administrator in.
 */
function login_admin($admin_id)
{
    session_regenerate_id(true);

    $_SESSION['admin_id'] = $admin_id;
}


/**
 * Log the current user out.
 */
function logout_user()
{
    unset($_SESSION['user_id']);

    session_regenerate_id(true);
}


/**
 * Log the current administrator out.
 */
function logout_admin()
{
    unset($_SESSION['admin_id']);

    session_regenerate_id(true);
}