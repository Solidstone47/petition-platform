<?php

require_once "includes/db.php";
require_once "includes/functions.php";
require_once "includes/auth.php";
require_once "google_config.php";

header("Location: " . google_auth_url());

exit;
