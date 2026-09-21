<?php

/**
 * Database connection.
 *
* Credentials are read from environment variables with
* safe fallbacks for local development only.
 */

$host     = getenv('DB_HOST') ?: 'localhost';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$database = getenv('DB_NAME') ?: 'petition_platform';
$port     = (int) (getenv('DB_PORT') ?: 3306);

$conn = new mysqli($host, $username, $password, $database, $port);

if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    http_response_code(500);
    die("A server error occurred. Please try again later.");
}

$conn->set_charset("utf8mb4");
