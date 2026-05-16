<?php
session_start();
include("../config.php");

if (isset($_POST['id'])) {

    // SECURITY FIX: escape all user-supplied values before use in SQL.
    $id  = mysqli_real_escape_string($conn, $_POST['id']);
    $otp = mysqli_real_escape_string($conn, $_POST['otp']);

    $query = $conn->query("SELECT * FROM users WHERE id='$id' AND otp='$otp'");

    // BUG FIX: $row was set to $query->num_rows (an integer), then code tried
    // $row['pg_mode'] which is always null on an integer.
    // Use num_rows for the existence check, fetch_assoc() for data access.
    $numRows   = $query->num_rows;
    $fetchuser = ($numRows > 0) ? $query->fetch_assoc() : null;

    if ($numRows !== 0 && $fetchuser !== null) {

        $conn->query("UPDATE users SET otp_attempts = '3', blocked_until = '' WHERE id = '$id'");

        $_SESSION['username']   = $fetchuser['mobile'];
        $_SESSION['user_id']    = $fetchuser['id'];
        $_SESSION['login_time'] = time();

        // BUG FIX: was $row['pg_mode'] — now correctly uses $fetchuser['pg_mode'].
        if ($fetchuser['pg_mode'] == 2) {
            echo '2';
        } elseif ($fetchuser['pg_mode'] == 3) {
            echo '3';
        } else {
            echo '1';
        }

    } else {
        echo '0';
    }
}
