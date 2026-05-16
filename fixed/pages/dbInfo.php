<?php
error_reporting(0);
date_default_timezone_set('Asia/Kolkata');

// -----------------------------------------------------------------------
// SECURITY FIX: Credentials are now read from environment variables.
// Set these in your server environment or a .env loader — NEVER hardcode
// real passwords in source code.
//
//   DB_HOST       (default: localhost)
//   DB_USERNAME   — your MySQL username
//   DB_PASSWORD   — your MySQL password
//   DB_NAME       — your MySQL database name
//   ADMIN_TOKEN   — a long random hex string (generate with: openssl rand -hex 32)
// -----------------------------------------------------------------------

function connect_database() {
    $dbHost  = getenv('DB_HOST')     ?: 'localhost';
    $dbLogin = getenv('DB_USERNAME') ?: '';
    $dbPwd   = getenv('DB_PASSWORD') ?: '';
    $dbName  = getenv('DB_NAME')     ?: '';

    if ($dbLogin === '' || $dbPwd === '' || $dbName === '') {
        die('Database credentials are not configured. Please set DB_USERNAME, DB_PASSWORD, and DB_NAME environment variables.');
    }

    $con = mysqli_connect($dbHost, $dbLogin, $dbPwd, $dbName);
    if (!$con) {
        die('Database connection failed: ' . mysqli_connect_errno());
    }
    return $con;
}

define('ADMIN_TOKEN', getenv('ADMIN_TOKEN') ?: '');
define('DB_HOST',     getenv('DB_HOST')     ?: 'localhost');
define('DB_USERNAME', getenv('DB_USERNAME') ?: '');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '');
define('DB_NAME',     getenv('DB_NAME')     ?: '');
