<?php include "header.php"; ?>
<?php
if ($userdata['aadhar_kyc'] == 1) {
    echo "<script>location.replace('dashboard?aadhar_kyc=0');</script>";
    exit;
}

if (isset($_POST['update'])) {

    // SECURITY FIX: escape $route with mysqli_real_escape_string before using
    // it in the SQL UPDATE.  The original code used $_POST['route'] directly,
    // which allowed SQL injection.
    $route = isset($_POST['route']) ? mysqli_real_escape_string($conn, $_POST['route']) : '';

    // Whitelist allowed values so an attacker cannot inject arbitrary data
    // even past the escape (defence-in-depth).
    if (!in_array($route, ['', '0', '1'], true)) {
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18"></script>';
        echo '<script>
            Swal.fire({ icon:"error", title:"Invalid route value.", showConfirmButton:true, confirmButtonText:"Try Again" })
            .then(() => { window.location.href = "profile"; });
        </script>';
        exit;
    }

    if ($route !== '') {

        // $mobile comes from header.php (session-validated).
        $safeMobile = mysqli_real_escape_string($conn, $mobile);
        $passwor    = "UPDATE users SET route = '$route' WHERE mobile = '$safeMobile'";
        $up         = mysqli_query($conn, $passwor);

        if ($up) {
            echo '<script src="js/jquery-3.2.1.min.js"></script>';
            echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18"></script>';
            echo '<script>
                $("#loading_ajax").hide();
                Swal.fire({
                    icon: "success",
                    title: "Route Changed Successfully",
                    text: "Your route has been updated.",
                    showConfirmButton: true,
                    confirmButtonText: "Ok",
                }).then((result) => {
                    if (result.isConfirmed) { window.location.href = "profile"; }
                });
            </script>';
        } else {
            echo '<script src="js/jquery-3.2.1.min.js"></script>';
            echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18"></script>';
            echo '<script>
                $("#loading_ajax").hide();
                Swal.fire({
                    icon: "error",
                    title: "Route Update Failed",
                    text: "Please try again later.",
                    showConfirmButton: true,
                    confirmButtonText: "Try Again",
                }).then((result) => {
                    if (result.isConfirmed) { window.location.href = "profile"; }
                });
            </script>';
        }
        exit;

    } else {
        echo '<script src="js/jquery-3.2.1.min.js"></script>';
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18"></script>';
        echo '<script>
            $("#loading_ajax").hide();
            Swal.fire({
                icon: "error",
                title: "Route Not Selected",
                showConfirmButton: true,
                confirmButtonText: "Try Again",
            }).then((result) => {
                if (result.isConfirmed) { window.location.href = "profile"; }
            });
        </script>';
        exit;
    }
}
?>

<style>
.switch { position:relative; display:inline-block; width:65px; height:33px; }
.switch input { display:none; }
.slider { position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:#fff; transition:.4s; border:2px solid #14183f; border-radius:34px; }
.slider:before { position:absolute; content:""; height:22px; width:22px; left:5px; bottom:4px; background-color:#14183f; transition:.4s; border-radius:50%; }
input:checked + .slider { background-color:#14183f; }
input:checked + .slider:before { transform:translateX(30px); background-color:#fff; }
.switch-title { font-size:16px; font-weight:500; margin:0 10px 10px 0; }
.center-button { display:flex; justify-content:center; align-items:center; height:100px; }
.center-button button { padding:10px 20px; background-color:#14183f; color:#fff; border:none; border-radius:5px; font-size:16px; cursor:pointer; }
.center-button button:hover { background-color:#0a9ac2; }
</style>

<main class="app-content">
  <div class="app-title">
    <div><h1><i class="fa fa-user"></i> My Profile</h1></div>
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
                    <h4 class="page-title">My Profile</h4>
                    <div class="row row-card-no-pd">
                      <div class="col-md-12">

                        <div class="d-flex align-items-start align-items-sm-center gap-6 pb-4 my-2 border-bottom">
                          <img src="assets/img/k2.png" alt="user-avatar" class="d-block rounded mr-3" style="width:100px;height:100px;" id="uploadedAvatar">
                          <div class="button-wrapper">
                            <label for="upload" class="btn btn-primary me-3 mb-4" tabindex="0">
                              <span class="d-none d-sm-block">Upload new photo</span>
                              <i class="icon-base bx bx-upload d-block d-sm-none"></i>
                              <input type="file" id="upload" class="account-file-input" hidden accept="image/png, image/jpeg">
                            </label>
                            <button type="button" class="btn btn-outline-secondary account-image-reset mb-4">
                              <i class="icon-base bx bx-reset d-block d-sm-none"></i>
                              <span class="d-none d-sm-block">Reset</span>
                            </button>
                            <div>Allowed JPG, GIF or PNG. Max size of 800K</div>
                          </div>
                        </div>

                        <form class="row mb-4 border-bottom" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>">
                          <div class="col-md-4 mb-3">
                            <label>Name</label>
                            <input type="text" placeholder="Name" value="<?php echo htmlspecialchars($userdata['name'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" readonly>
                          </div>
                          <div class="col-md-4 mb-3">
                            <label>Company Name</label>
                            <input type="text" placeholder="Company Name" value="<?php echo htmlspecialchars($userdata['company'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" readonly>
                          </div>
                          <div class="col-md-4 mb-3">
                            <label>Mobile Number</label>
                            <input type="text" name="mobile" placeholder="Mobile Number" value="<?php echo htmlspecialchars($userdata['mobile'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control input-solid" required readonly>
                          </div>
                          <div class="col-md-4 mb-3">
                            <label>Email Address</label>
                            <input type="text" name="email" placeholder="Email Address" value="<?php echo htmlspecialchars($userdata['email'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control input-solid" required readonly>
                          </div>
                          <div class="col-md-4 mb-3">
                            <label>PAN Number</label>
                            <input type="text" placeholder="PAN Number" value="<?php echo htmlspecialchars($userdata['pan'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" readonly>
                          </div>
                          <div class="col-md-4 mb-3">
                            <label>Aadhaar Number</label>
                            <input type="text" placeholder="Aadhaar Number" value="<?php echo htmlspecialchars($userdata['aadhaar'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" readonly>
                          </div>
                          <div class="col-md-8 mb-3">
                            <label>Location</label>
                            <input type="text" placeholder="Location" value="<?php echo htmlspecialchars($userdata['location'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" readonly>
                          </div>
                          <?php if ($userdata['plan_id'] != 1) { ?>
                          <div class="col-md-4 mb-3">
                            <label>Route</label>
                            <select class="form-control" name="route">
                              <option value="">Select</option>
                              <option value="0" <?= ($userdata['route'] == 0) ? 'selected' : ''; ?>>Single Merchant Route</option>
                              <option value="1" <?= ($userdata['route'] == 1) ? 'selected' : ''; ?>>Multiple Merchant Route</option>
                            </select>
                          </div>
                          <?php } ?>
                          <div class="my-3">
                            <button type="submit" name="update" class="btn btn-primary me-3">Save changes</button>
                            <button type="reset" class="btn btn-outline-secondary">Cancel</button>
                          </div>
                        </form>

                        <div class="col-md-12 my-4">
                          <h3 class="mb-1 content-heading">Manage 2FA Security</h3>
                          <p class="text-muted mb-3">When 2FA is enabled we send a 6-digit OTP code to your email on each login, making your account more secure.</p>
                          <div class="d-flex justify-content-between align-items-center">
                            <div class="switch-container d-flex align-items-center">
                              <span class="switch-title">Two Factor Security</span>
                              <label class="switch">
                                <input type="checkbox" <?php if ($userdata['two_factor'] == 1) { echo 'checked'; } ?> class="update2fa_btn">
                                <span class="slider"></span>
                              </label>
                            </div>
                          </div>
                        </div>

                      </div>
                    </div>

                    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
                    <div class="center-button">
                      <button type="button" class="btn-primary" onclick="window.location.href='changepassword';">
                        <i class="fas fa-lock"></i> Change Password
                      </button>
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
<script src="assets/js/bharatpe.js?1697765827"></script>
</html>
