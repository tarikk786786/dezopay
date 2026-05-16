<?php
require_once '../config.php';
include('../smtp/PHPMailerAutoload.php');

// -----------------------------------------------------------------------
// SECURITY FIX: SMTP credentials are now loaded from the website_settings
// table (same pattern used in login.php) instead of being hardcoded.
// -----------------------------------------------------------------------
function getForgotSmtpSettings($conn) {
    $result = $conn->query("SELECT * FROM website_settings WHERE id = 1 LIMIT 1");
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    // Fallback: read from environment variables so nothing is hardcoded.
    return [
        'smtp_host'       => getenv('SMTP_HOST')       ?: '',
        'smtp_username'   => getenv('SMTP_USERNAME')   ?: '',
        'smtp_password'   => getenv('SMTP_PASSWORD')   ?: '',
        'smtp_port'       => getenv('SMTP_PORT')       ?: 587,
        'smtp_encryption' => getenv('SMTP_ENCRYPTION') ?: 'tls',
        'smtp_from_email' => getenv('SMTP_FROM_EMAIL') ?: '',
        'smtp_from_name'  => getenv('SMTP_FROM_NAME')  ?: '',
    ];
}

// ------------------------------------------------------------------
// Type: forgot  —  look up user and send OTP
// ------------------------------------------------------------------
if (isset($_POST['type']) && $_POST['type'] === 'forgot') {

    // SECURITY FIX: escape username before using it in queries.
    $username = mysqli_real_escape_string($conn, trim($_POST['mobile'] ?? ''));

    if ($username === '') {
        echo json_encode(['status' => 4, 'msg' => 'Mobile/Email is required.']);
        exit;
    }

    $query = "SELECT * FROM users WHERE mobile = '$username' OR email = '$username'";
    $run   = mysqli_query($conn, $query);
    $row   = mysqli_fetch_assoc($run);

    if (mysqli_num_rows($run) > 0) {

        $otp    = rand(100000, 999999);
        $userId = $row['id'];

        // BUG FIX: UPDATE now always uses the actual mobile number from the
        // DB row, not the raw $username — which could be an email address.
        // Previously "WHERE mobile = '$username'" would silently update 0 rows
        // when the user entered their email.
        $userMobile = mysqli_real_escape_string($conn, $row['mobile']);
        $sql        = "UPDATE users SET otp = '$otp' WHERE mobile = '$userMobile'";

        if (mysqli_query($conn, $sql)) {

            $toemail    = $row['email'];
            $strmobile  = substr((string)$userMobile, -4);
            $strmail    = substr((string)$toemail, -15);

            $mailmsg = '<html><head><title>Forgot OTP</title></head><body>'
                . '<p>Your One-Time Password (OTP): <strong>' . htmlspecialchars((string)$otp, ENT_QUOTES, 'UTF-8') . '</strong></p>'
                . '</body></html>';

            // Load SMTP settings from DB / env — no hardcoded credentials.
            $ws = getForgotSmtpSettings($conn);

            $mail = new PHPMailer();
            $mail->IsSMTP();
            $mail->SMTPAuth    = true;
            $mail->SMTPSecure  = $ws['smtp_encryption'] ?? 'tls';
            $mail->Host        = $ws['smtp_host']       ?? '';
            $mail->Port        = (int)($ws['smtp_port'] ?? 587);
            $mail->IsHTML(true);
            $mail->CharSet     = 'UTF-8';
            $mail->Username    = $ws['smtp_username']   ?? '';
            $mail->Password    = $ws['smtp_password']   ?? '';
            $mail->SetFrom($ws['smtp_from_email'] ?? '', $ws['smtp_from_name'] ?? '');
            $mail->Subject     = 'Forgot OTP Verification';
            $mail->Body        = $mailmsg;
            $mail->AddAddress($toemail);
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => false,
                ],
            ];

            if (!$mail->Send()) {
                echo $mail->ErrorInfo;
            } else {
                echo json_encode(['status' => 1, 'msg' => 'forgot success', 'userid' => $userId]);
                exit;
            }

        }

    } else {
        echo json_encode(['status' => 4, 'msg' => 'User Does not Exist!']);
        exit;
    }
}

// ------------------------------------------------------------------
// Type: otp  —  verify OTP entered by user
// ------------------------------------------------------------------
if (isset($_POST['type']) && $_POST['type'] === 'otp') {

    $id  = mysqli_real_escape_string($conn, $_POST['id']  ?? '');
    $otp = mysqli_real_escape_string($conn, $_POST['otp'] ?? '');

    $query = $conn->query("SELECT * FROM users WHERE id='$id' AND otp='$otp'");
    $num   = $query->num_rows;

    if ($num !== 0) {
        $conn->query("UPDATE users SET otp_attempts = '3', blocked_until = '' WHERE id = '$id'");
        echo '1';
    } else {
        echo '0';
    }
}

// ------------------------------------------------------------------
// Type: change  —  update password after OTP verified
// ------------------------------------------------------------------
if (isset($_POST['type']) && $_POST['type'] === 'change') {

    $id     = mysqli_real_escape_string($conn, $_POST['id']     ?? '');
    $npass  = $_POST['npass']  ?? '';
    $cnpass = $_POST['cnpass'] ?? '';

    if ($npass !== $cnpass) {
        echo json_encode(['status' => 2, 'msg' => 'Confirm Password Does Not Match!', 'Status' => false]);
        exit;
    }

    if (strlen($npass) < 8) {
        echo json_encode(['status' => 5, 'msg' => 'Password must be at least 8 characters.', 'Status' => false]);
        exit;
    }

    $pass = password_hash($npass, PASSWORD_BCRYPT);
    $run  = mysqli_query($conn, "UPDATE users SET password = '$pass' WHERE id = '$id'");

    if ($run) {
        echo json_encode(['status' => 1, 'msg' => 'Password Changed Successfully!', 'Status' => true]);
    } else {
        echo json_encode(['status' => 3, 'msg' => 'Failed to change password!', 'Status' => false]);
    }
    exit;
}
