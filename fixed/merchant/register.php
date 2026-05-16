<?php
// Start session for CSRF protection on registration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Generate CSRF token if not set
if (empty($_SESSION['reg_csrf_token'])) {
    $_SESSION['reg_csrf_token'] = bin2hex(random_bytes(32));
}
include "config.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>imb Pay - Register</title>

    <!-- Favicons -->
    <link href="<?= htmlspecialchars($site_url) ?>/newassets/images/favicon.png" rel="icon">
    <link href="<?= htmlspecialchars($site_url) ?>/newassets/images/favicon.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --primary-color: #2c3eef;
            --secondary-color: #1a237e;
            --accent-color: #00c853;
            --light-bg: #f8f9fa;
            --dark-text: #2c3e50;
            --light-text: #7f8c8d;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: #f2f3f8 !important;
            color: var(--dark-text);
        }

        a {
            text-decoration: none !important;
            color: var(--primary-color);
        }

        a:hover {
            color: var(--secondary-color);
        }

        .card {
            border-radius: 10px !important;
            border: none;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .register-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .register-img {
            width: 100%;
            height: auto;
            max-height: 400px;
            object-fit: contain;
        }

        .register-heading {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 20px;
        }

        .form-control {
            height: 45px;
            border-radius: 5px !important;
            border: 1px solid #ddd;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(44, 62, 239, 0.25);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 10px;
            font-weight: 500;
            border-radius: 5px !important;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .footer-text {
            font-size: 12px;
            color: var(--light-text);
            text-align: center;
            margin-top: 20px;
        }

        .disclaimer-modal .modal-header {
            background-color: var(--primary-color);
            color: white;
        }

        .disclaimer-modal .modal-title {
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .register-img-container {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Audio Element -->
    <audio id="loginSound" src="<?= htmlspecialchars($site_url) ?>/Voice/Register.mp3" preload="auto"></audio>

    <script>
        window.onload = function() {
            if (!sessionStorage.getItem("soundPlayed")) {
                var sound = document.getElementById("loginSound");
                sound.play().catch(e => console.log("Audio play failed:", e));
                sessionStorage.setItem("soundPlayed", "true");
            }
        };
    </script>

    <!-- Disclaimer Modal -->
    <div class="modal fade disclaimer-modal" id="disclaimer" tabindex="-1" aria-labelledby="Disclaimer" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Disclaimer Notice</h5>
                </div>
                <div class="modal-body">
                    <p>This imb Pay does not provide any Payment Gateway services, UPI Accounts, or UPI Merchant Accounts.</p>
                    <p>We only provide an API to Generate a QR code for your UPI ID.</p>
                    <p>We are not involved in any kind of transaction. Please read our terms and conditions before using our service.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-dark" onclick="window.location.href='<?= htmlspecialchars($site_url) ?>'">Leave</button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">I Agree</button>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-5">
        <div class="register-container">
            <div class="card">
                <div class="row g-0">
                    <!-- Left Column (Image) -->
                    <div class="col-lg-6 register-img-container d-flex align-items-center">
                        <div class="p-4 text-center">
                            <img src="assets/imbpg_img/register.svg" class="register-img" alt="Registration Illustration">
                            <div class="mt-4">
                                <h4 class="text-primary">Fastest API For UPI Payments</h4>
                                <p class="text-muted">Accept payments from your customers through our easy-to-use QR code service, with 0% transaction fees.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column (Form) -->
                    <div class="col-lg-6">
                        <div class="card-body p-4 p-md-5">
                            <div class="text-center mb-4">
                                <h3 class="register-heading">Create Your Account</h3>
                                <p class="text-muted">Start with your free account today</p>
                            </div>

                            <?php
                            if (isset($_POST['create'])) {

                                // CSRF validation
                                if (empty($_POST['reg_csrf_token']) || $_POST['reg_csrf_token'] !== $_SESSION['reg_csrf_token']) {
                                    echo '<script>Swal.fire({ title: "Security Error", text: "Invalid request. Please refresh and try again.", icon: "error", confirmButtonText: "OK" });</script>';
                                } else {
                                    // Sanitize inputs to prevent SQL injection
                                    $mobile  = mysqli_real_escape_string($conn, trim($_POST['mobile']));
                                    $email   = mysqli_real_escape_string($conn, trim($_POST['email']));
                                    $pan     = mysqli_real_escape_string($conn, strtoupper(trim($_POST['pan'])));
                                    $aadhaar = mysqli_real_escape_string($conn, trim($_POST['aadhaar']));

                                    $checkMobileResult  = mysqli_query($conn, "SELECT id FROM `users` WHERE `mobile` = '$mobile'");
                                    $checkEmailResult   = mysqli_query($conn, "SELECT id FROM `users` WHERE `email` = '$email'");
                                    $checkpanResult     = mysqli_query($conn, "SELECT id FROM `users` WHERE `pan` = '$pan'");
                                    $checkAadharResult  = mysqli_query($conn, "SELECT id FROM `users` WHERE `aadhaar` = '$aadhaar'");

                                    if (mysqli_num_rows($checkMobileResult) > 0) {
                                        echo '<script>Swal.fire({ title: "Mobile Number Exists", text: "This mobile number is already registered. Please use a different number.", icon: "error", confirmButtonText: "OK" });</script>';
                                    } elseif (mysqli_num_rows($checkEmailResult) > 0) {
                                        echo '<script>Swal.fire({ title: "Email Exists", text: "This email address is already registered. Please use a different email.", icon: "error", confirmButtonText: "OK" });</script>';
                                    } elseif (mysqli_num_rows($checkpanResult) > 0) {
                                        echo '<script>Swal.fire({ title: "PAN Exists", text: "This PAN number is already registered. Please use a different PAN.", icon: "error", confirmButtonText: "OK" });</script>';
                                    } elseif (mysqli_num_rows($checkAadharResult) > 0) {
                                        echo '<script>Swal.fire({ title: "Aadhaar Exists", text: "This Aadhaar number is already registered. Please use a different Aadhaar.", icon: "error", confirmButtonText: "OK" });</script>';
                                    } else {
                                        $password    = $_POST['password'];
                                        $name        = mysqli_real_escape_string($conn, trim($_POST['name']));
                                        $company     = mysqli_real_escape_string($conn, trim($_POST['company']));
                                        $pin         = mysqli_real_escape_string($conn, trim($_POST['pin']));
                                        $location    = mysqli_real_escape_string($conn, trim($_POST['location']));
                                        $sponser_id  = mysqli_real_escape_string($conn, trim($_POST['sponser_id'] ?? ''));

                                        $key  = md5(rand(00000000, 99999999));
                                        $pass = password_hash($password, PASSWORD_BCRYPT);
                                        // FIX: use strtotime for correct date calculation
                                        $today = date("Y-m-d", strtotime("+3 days"));

                                        function generateRandomInstanceId($length = 16) {
                                            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                                            $randomString = 'I';
                                            for ($i = 1; $i < $length - 6; $i++) {
                                                $randomString .= $characters[rand(0, strlen($characters) - 1)];
                                            }
                                            $lastSixDigits = substr(strval(time()), -6);
                                            $randint = rand(100, 900);
                                            return $randomString . $randint . $lastSixDigits;
                                        }

                                        $instanceId = generateRandomInstanceId();

                                        $register = "INSERT INTO `users`(`name`, `mobile`, `role`, `password`, `email`, `company`, `pin`, `pan`, `aadhaar`, `location`, `user_token`, `expiry`, `sponser_by`, `instance_id`)
                                                     VALUES ('$name', '$mobile', 'User', '$pass', '$email', '$company', '$pin', '$pan', '$aadhaar', '$location', '$key', '$today', '$sponser_id', '$instanceId')";

                                        $result = mysqli_query($conn, $register);

                                        if ($result) {
                                            $usid = mysqli_insert_id($conn);
                                            $sponserid = "IMBRFL00$usid";
                                            $conn->query("UPDATE `users` SET `sponser_id` = '$sponserid' WHERE id = '$usid'");

                                            // Regenerate CSRF token after successful registration
                                            $_SESSION['reg_csrf_token'] = bin2hex(random_bytes(32));

                                            echo '<script>
                                                Swal.fire({
                                                    title: "Registration Successful!",
                                                    text: "Your account has been created successfully.",
                                                    icon: "success",
                                                    confirmButtonText: "Continue"
                                                }).then((result) => {
                                                    if (result.isConfirmed) { window.location.href = "index"; }
                                                });
                                            </script>';
                                        } else {
                                            echo '<script>
                                                Swal.fire({
                                                    title: "Registration Failed",
                                                    text: "There was an error creating your account. Please try again.",
                                                    icon: "error",
                                                    confirmButtonText: "OK"
                                                });
                                            </script>';
                                        }
                                    }
                                }
                            }
                            ?>

                            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                <input type="hidden" name="reg_csrf_token" value="<?php echo htmlspecialchars($_SESSION['reg_csrf_token']); ?>">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Mobile Number</label>
                                        <input type="text" name="mobile" placeholder="Enter Mobile Number" class="form-control"
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);" required />
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Password</label>
                                        <input type="password" name="password" placeholder="Enter Password" class="form-control" required />
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email Address</label>
                                        <input type="email" name="email" placeholder="Enter Email Address" class="form-control" required />
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" name="name" placeholder="Enter Your Name" class="form-control" required />
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Company Name</label>
                                        <input type="text" name="company" placeholder="Enter Company Name" class="form-control" required />
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">PIN Code</label>
                                        <input type="text" name="pin" placeholder="Enter Area PIN" class="form-control"
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);" required />
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">PAN Number</label>
                                        <input type="text" name="pan" placeholder="Enter PAN (AAAAANNNNA)" class="form-control"
                                            pattern="[A-Za-z]{5}[0-9]{4}[A-Za-z]{1}" title="Format: AAAAANNNNA"
                                            oninput="this.value = this.value.toUpperCase();" maxlength="10" required />
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Aadhaar Number</label>
                                        <input type="text" name="aadhaar" placeholder="Enter Aadhaar Number" class="form-control"
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 12);" required />
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Location</label>
                                        <input type="text" name="location" placeholder="Enter Your Location" class="form-control" required />
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Sponsor ID (Optional)</label>
                                        <input type="text" name="sponser_id" placeholder="Enter Referral ID" class="form-control"
                                            <?php if (!empty($_GET["sponserid"])) { ?>
                                            readonly value="<?= htmlspecialchars($_GET["sponserid"]) ?>"
                                            <?php } ?>
                                        />
                                    </div>
                                    <div class="col-12 mb-3">
                                        <button type="submit" name="create" class="btn btn-primary w-100 py-2">
                                            <i class="fas fa-user-plus me-2"></i> Register Now
                                        </button>
                                    </div>
                                    <div class="col-12 text-center">
                                        <p class="mb-0">Already have an account? <a href="index">Login here</a></p>
                                    </div>
                                </div>
                            </form>

                            <p class="footer-text mt-4">
                                *imb Pay Gateway provides Dynamic QR Generating service. IMB Payment Gateway does not offer payment gateway service, nor does it provide UPI ID and UPI Merchant account. Please read our policy and T&amp;C before using our services.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/register.js"></script>

    <script>
        $(document).ready(function() {
            $('#disclaimer').modal({ backdrop: 'static', keyboard: false });
        });
    </script>
</body>
</html>
