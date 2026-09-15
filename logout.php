<?php

require_once "includes/db.php";
require_once "includes/functions.php";
require_once "includes/auth.php";


// Log user out
logout_user();


// Show confirmation
set_flash(
    "success",
    "You have been logged out successfully."
);


// Redirect to login
redirect("login.php");