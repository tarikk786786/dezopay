<?php
session_start();
require_once '../config.php';

if (isset($_POST['srno']) && $_POST['srno'] != '') {
    $userid = $_POST['srno'];
} else {
    $userid = $_SESSION['user_id'];
}

// ------------------------------------------------------------------
// loginuser  —  Admin-only: log in AS a merchant user.
// ------------------------------------------------------------------
if (isset($_POST['loginuser'])) {

    // SECURITY FIX 1: Verify the caller is an admin before allowing
    // impersonation.  Without this check ANY request to this endpoint
    // could hijack any merchant account.
    if (empty($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
        http_response_code(403);
        echo '<script>alert("Unauthorized"); window.location.href="../login";</script>';
        exit;
    }

    // SECURITY FIX 2: CSRF token check — the original handler had none,
    // making it trivially exploitable via CSRF.
    if (
        empty($_POST['csrf_token']) ||
        empty($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        http_response_code(403);
        echo '<script>alert("Invalid CSRF token.");</script>';
        exit;
    }

    $username = filter_var($_POST['mobileno'] ?? '', FILTER_SANITIZE_NUMBER_INT);
    $username = mysqli_real_escape_string($conn, $username);

    $query = "SELECT * FROM users WHERE mobile = '$username'";
    $run   = mysqli_query($conn, $query);
    $row   = mysqli_fetch_array($run);

    if (mysqli_num_rows($run) > 0) {

        $byteuserid = $row['id'];
        $pgmode     = $row['pg_mode'];

        // Regenerate CSRF token on impersonation for the new session.
        $csrf_token = bin2hex(random_bytes(32));

        $_SESSION['csrf_token'] = $csrf_token;
        $_SESSION['username']   = $username;
        $_SESSION['user_id']    = $byteuserid;
        $_SESSION['login_time'] = time();

        if ($pgmode == 2) {
            echo '<script>location.replace("../../imbpro/dashboard");</script>';
        } else {
            echo '<script>location.replace("../dashboard");</script>';
        }

    }
    exit;
}

// ------------------------------------------------------------------
// txnrdetails  —  get transaction summary between two dates.
// ------------------------------------------------------------------
if (isset($_POST['type']) && $_POST['type'] === 'txnrdetails') {

    // SECURITY FIX: escape date inputs before embedding in SQL.
    $fromdate = mysqli_real_escape_string($conn, $_POST['fromdate'] ?? '');
    $todate   = mysqli_real_escape_string($conn, $_POST['todate']   ?? '');

    // Validate that inputs are real dates to prevent injection past escaping.
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromdate) ||
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $todate)) {
        echo json_encode(['res_code' => 400, 'msg' => 'Invalid date format. Use YYYY-MM-DD.']);
        exit;
    }

    $uid = (int) $userid; // cast to int — $userid comes from session or POST

    $gettotaltxn = $conn->query(
        "SELECT SUM(amount) as amount, COUNT(id) as count FROM orders
         WHERE user_id = '$uid' AND (DATE(create_date) BETWEEN '$fromdate' AND '$todate')"
    )->fetch_assoc();

    $getsuccesstxn = $conn->query(
        "SELECT SUM(amount) as amount, COUNT(id) as count FROM orders
         WHERE user_id = '$uid' AND status = 'SUCCESS'
         AND (DATE(create_date) BETWEEN '$fromdate' AND '$todate')"
    )->fetch_assoc();

    $getfailedtxn = $conn->query(
        "SELECT SUM(amount) as amount, COUNT(id) as count FROM orders
         WHERE user_id = '$uid' AND status = 'FAILURE'
         AND (DATE(create_date) BETWEEN '$fromdate' AND '$todate')"
    )->fetch_assoc();

    $txndata = [
        'totaltxn'  => '₹ ' . number_format((float)($gettotaltxn['amount']  ?? 0), 2),
        'totaltxnc' => number_format((int)($gettotaltxn['count']             ?? 0)),
        'totalstxn' => '+₹ ' . number_format((float)($getsuccesstxn['amount'] ?? 0), 2),
        'totalstxnc'=> number_format((int)($getsuccesstxn['count']           ?? 0)),
        'totalftxn' => '₹ ' . number_format((float)($getfailedtxn['amount']  ?? 0), 2),
        'totalftxnc'=> number_format((int)($getfailedtxn['count']            ?? 0)),
    ];

    echo json_encode([
        'res_code' => 200,
        'msg'      => "Your Transactions Details from $fromdate to $todate.",
        'txndata'  => $txndata,
    ]);
    exit;
}

// ------------------------------------------------------------------
// two_factor_change  —  enable or disable 2FA for a user.
// ------------------------------------------------------------------
if (isset($_POST['type']) && $_POST['type'] === 'two_factor_change') {

    $enable_2fa = (int)($_POST['status'] ?? 0);
    $st         = ($enable_2fa === 1) ? 'Enabled' : 'Disabled';

    $uid     = (int) $userid;
    $success = $conn->query("UPDATE users SET two_factor = '$enable_2fa' WHERE id = '$uid'");

    if ($success) {
        echo json_encode(['status' => 1, 'msg' => "Two Factor Security is $st"]);
    } else {
        echo json_encode(['status' => 3, 'msg' => "Failed to $st Two Factor Security"]);
    }
    exit;
}

// ------------------------------------------------------------------
// get_api_token  —  regenerate the merchant's API token.
// ------------------------------------------------------------------
if (isset($_POST['get_api_token'])) {

    $bbbyteuserid = (int)($_SESSION['user_id'] ?? 0);

    $mobileResult  = $conn->query("SELECT mobile FROM users WHERE id = '$bbbyteuserid'")->fetch_assoc();
    $sanitizedMobile = mysqli_real_escape_string($conn, $mobileResult['mobile'] ?? '');

    $uniqueNumber = mt_rand(1000000000, 9999999999);
    $uniqueNumber = str_pad((string)$uniqueNumber, 10, '0', STR_PAD_LEFT);
    $key          = md5($uniqueNumber);

    $tables = [
        ['users',            "mobile = '$sanitizedMobile'"],
        ['orders',           "user_id = $bbbyteuserid"],
        ['reports',          "user_id = $bbbyteuserid"],
        ['hdfc',             "user_id = $bbbyteuserid"],
        ['bharatpe_tokens',  "user_id = '$bbbyteuserid'"],
        ['merchant',         "user_id = '$bbbyteuserid'"],
        ['mobikwik_token',   "user_id = '$bbbyteuserid'"],
        ['freecharge',       "user_id = '$bbbyteuserid'"],
        ['amazon_pay',       "user_id = '$bbbyteuserid'"],
        ['phonepe_tokens',   "user_id = '$bbbyteuserid'"],
        ['store_id',         "user_id = '$bbbyteuserid'"],
        ['paytm_tokens',     "user_id = '$bbbyteuserid'"],
    ];

    $allOk = true;
    foreach ($tables as [$table, $where]) {
        $r = mysqli_query($conn, "UPDATE `$table` SET user_token='$key' WHERE $where");
        if ($r === false) {
            $allOk = false;
        }
    }

    if ($allOk) {
        echo json_encode(['rescode' => 200, 'status' => true]);
    } else {
        echo json_encode(['rescode' => 403, 'status' => false, 'msg' => 'Failed to generate API Token!']);
    }
    exit;
}
