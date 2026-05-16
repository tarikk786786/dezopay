<?php
include "header.php";

if (isset($_REQUEST['update'])) {

    // SECURITY FIX: validate CSRF token before processing the form.
    // The form already embeds $_SESSION['csrf_token'] but the original
    // handler never checked it.
    if (
        empty($_POST['csrf_token']) ||
        empty($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18"></script>';
        echo '<script>
            Swal.fire({ icon:"error", title:"Invalid request. Please try again.", showConfirmButton:true, confirmButtonText:"Ok" })
            .then(() => { window.location.href = "changepassword"; });
        </script>';
        exit;
    }

    // $mobile is set (and session-validated) in header.php.
    $sanitizedMobile = mysqli_real_escape_string($conn, $mobile);

    $current_password  = $_REQUEST['current_password']  ?? '';
    $new_password      = $_REQUEST['new_password']      ?? '';
    $confirm_password  = $_REQUEST['confirm_password']  ?? '';

    if (strlen($new_password) < 8) {
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18"></script>';
        echo '<script>
            Swal.fire({ icon:"error", title:"New password must be at least 8 characters.", showConfirmButton:true, confirmButtonText:"Try Again" })
            .then(() => { window.location.href = "changepassword"; });
        </script>';
        exit;
    }

    $query  = "SELECT password FROM users WHERE mobile = '$sanitizedMobile'";
    $result = mysqli_query($conn, $query);

    if ($result) {
        $row                 = mysqli_fetch_assoc($result);
        $hashedPasswordFromDB = $row['password'];

        if (password_verify($current_password, $hashedPasswordFromDB)) {

            if ($new_password === $confirm_password) {
                $newpass = password_hash($new_password, PASSWORD_DEFAULT);
                $passwor = "UPDATE users SET password = '$newpass' WHERE mobile = '$sanitizedMobile'";
                $up      = mysqli_query($conn, $passwor);

                if ($up) {
                    echo '<script src="js/jquery-3.2.1.min.js"></script>';
                    echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18"></script>';
                    echo '<script>
                        $("#loading_ajax").hide();
                        Swal.fire({
                            icon: "success",
                            title: "Password Changed Successfully",
                            text: "Your password has been updated.",
                            showConfirmButton: true,
                            confirmButtonText: "Ok",
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // BUG FIX: was "dashboard.php" — use extension-less URL to match routing.
                                window.location.href = "dashboard";
                            }
                        });
                    </script>';
                    exit;
                } else {
                    echo '<script src="js/jquery-3.2.1.min.js"></script>';
                    echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18"></script>';
                    echo '<script>
                        $("#loading_ajax").hide();
                        Swal.fire({
                            icon: "error",
                            title: "Password Update Failed",
                            text: "Please try again later.",
                            showConfirmButton: true,
                            confirmButtonText: "Try Again",
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // BUG FIX: was "changepassword.php".
                                window.location.href = "changepassword";
                            }
                        });
                    </script>';
                    exit;
                }

            } else {
                echo '<script src="js/jquery-3.2.1.min.js"></script>';
                echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18"></script>';
                echo '<script>
                    $("#loading_ajax").hide();
                    Swal.fire({
                        icon: "error",
                        title: "New Password and Confirm Password Do Not Match",
                        showConfirmButton: true,
                        confirmButtonText: "Try Again",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "changepassword";
                        }
                    });
                </script>';
                exit;
            }

        } else {
            echo '<script src="js/jquery-3.2.1.min.js"></script>';
            echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18"></script>';
            echo '<script>
                $("#loading_ajax").hide();
                Swal.fire({
                    icon: "error",
                    title: "Current Password Does Not Match",
                    showConfirmButton: true,
                    confirmButtonText: "Try Again",
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "changepassword";
                    }
                });
            </script>';
            exit;
        }

    } else {
        echo '<script src="js/jquery-3.2.1.min.js"></script>';
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18"></script>';
        echo '<script>
            $("#loading_ajax").hide();
            Swal.fire({
                icon: "error",
                title: "Please try again later.",
                showConfirmButton: true,
                confirmButtonText: "Try Again",
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "changepassword";
                }
            });
        </script>';
        exit;
    }
}
?>

<style>
.switch { position:relative; display:inline-block; width:65px; height:33px; }
.switch input { display:none; }
.slider { position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:#fff; transition:.4s; border:2px solid #25a6a1; border-radius:34px; }
.slider:before { position:absolute; content:""; height:22px; width:22px; left:5px; bottom:4px; background-color:#25a6a1; transition:.4s; border-radius:50%; }
input:checked + .slider { background-color:#25a6a1; }
input:checked + .slider:before { transform:translateX(30px); background-color:#fff; }
.switch-title { font-size:16px; font-weight:500; margin:0 10px 10px 0; }
</style>

<main class="app-content">
  <div class="app-title">
    <div><h1><i class="fa fa-key"></i> Change Password</h1></div>
    <ul class="app-breadcrumb breadcrumb">
      <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
      <li class="breadcrumb-item"><a href="dashboard">Dashboard</a></li>
    </ul>
  </div>
  </div>

  <div class="tile mb-4">
    <div class="page-header">
      <div class="row">
        <div class="col-lg-12">
          <div class="row row-card-no-pd">
            <div class="col-md-12">
              <div class="main-panel">
                <div class="content">
                  <div class="container-fluid">
                    <div class="row row-card-no-pd">
                      <div class="col-md-12">

                        <form class="row mb-4" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>">
                          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

                          <div class="col-md-4 mb-3">
                            <label>Current Password</label>
                            <input type="password" name="current_password" placeholder="Current Password" class="form-control" required>
                          </div>
                          <div class="col-md-4 mb-3">
                            <label>New Password</label>
                            <input type="password" name="new_password" placeholder="New Password (min 8 chars)" class="form-control" required minlength="8">
                          </div>
                          <div class="col-md-4 mb-3">
                            <label>Confirm Password</label>
                            <input type="password" name="confirm_password" placeholder="Confirm Password" class="form-control" required minlength="8">
                          </div>
                          <div class="col-md-4 mb-3">
                            <button type="submit" name="update" class="btn btn-primary btn-block">Change Password</button>
                          </div>
                        </form>

                        <div class="col-md-12 my-4">
                          <h3 class="mb-1 content-heading">Manage 2FA Security</h3>
                          <p class="text-muted mb-3">When 2FA is enabled we send a 6-digit OTP to your email on each login, keeping your account secure.</p>
                          <div class="d-flex justify-content-between align-items-center">
                            <div class="switch-container d-flex align-items-center">
                              <span class="switch-title">Two Factor Authentication</span>
                              <label class="switch">
                                <input type="checkbox" <?php if ($userdata['two_factor'] == 1) { echo 'checked'; } ?> class="update2fa_btn">
                                <span class="slider"></span>
                              </label>
                            </div>
                          </div>
                        </div>

                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

</div>
</body>
<script src="js/jquery-3.2.1.min.js"></script>
<script src="js/popper.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/main.js"></script>
<script src="js/mainscript.js"></script>
<script src="js/plugins/pace.min.js"></script>
<script type="text/javascript">
function utr_search(utr_number) {
    if (getCurentFileName() == "transactions") {
        if (utr_number.length == 12) {
            search_txn('2023-10-01', '2023-10-20', '', utr_number);
        } else {
            Swal.fire('Enter Valid UTR Number!');
        }
    } else {
        location.href = 'transactions';
    }
}
</script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css"/>
<script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function () {
    $('#dataTable').DataTable();
});
</script>
<script src="assets/js/bharatpe.js?1697765682"></script>
</html>
