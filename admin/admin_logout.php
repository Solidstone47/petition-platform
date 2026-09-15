<?php

require_once "../includes/db.php";
require_once "../includes/functions.php";
require_once "../includes/auth.php";

if (is_admin_logged_in()) {
    logout_admin();
}

set_flash(
    "success",
    "You have been logged out."
);

redirect("admin_login.php");