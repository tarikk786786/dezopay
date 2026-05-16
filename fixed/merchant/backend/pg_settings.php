<?php
session_start();
require_once '../config.php';

if (isset($_POST['srno']) && $_POST['srno'] != '') {
    $mobile = $_POST['srno'];
} else {
    $mobile = $_SESSION['username'];
}

if (isset($_POST['type']) && $_POST['type'] === 'updatepgservice') {

    $service = mysqli_real_escape_string($conn, $_POST['service'] ?? '');
    $status  = mysqli_real_escape_string($conn, $_POST['status']  ?? '');

    // BUG FIX: 'upiapps' and 'ads' were not handled and silently fell
    // through to the else branch, setting the wrong column (pg_upiidreq).
    // All valid service names now map to the correct column.
    $serviceMap = [
        'qrcode'     => ['pg_qrcode',   'QR Code'],
        'upiapps'    => ['pg_upiapps',  'UPI Apps'],      // was missing
        'ads'        => ['pg_ads',       'Ads Show'],      // was missing
        'intent1'    => ['pg_intent1',   'Google Pay Intent'],
        'intent2'    => ['pg_intent2',   'Paytm Intent'],
        'pby'        => ['pg_pby',       'Powered By'],
        'upirequest' => ['pg_upiidreq',  'UPI Request'],
    ];

    if (!array_key_exists($service, $serviceMap)) {
        echo json_encode(['status' => 3, 'msg' => 'Unknown service type.', 'Status' => false]);
        exit;
    }

    [$column, $service_name] = $serviceMap[$service];

    // Whitelist $status — only '0' or '1' are valid toggle values.
    if ($status !== '0' && $status !== '1') {
        echo json_encode(['status' => 3, 'msg' => 'Invalid status value.', 'Status' => false]);
        exit;
    }

    $safeMobile = mysqli_real_escape_string($conn, $mobile);
    $sql        = "UPDATE users SET `$column` = '$status' WHERE mobile = '$safeMobile'";
    $run        = mysqli_query($conn, $sql);

    if ($run) {
        echo json_encode(['status' => 1, 'msg' => "$service_name method updated successfully!", 'Status' => true]);
    } else {
        echo json_encode(['status' => 3, 'msg' => "Failed to update $service_name method!", 'Status' => false]);
    }
    exit;
}
