<?php include "header.php"; ?>
    <!-- Include the SweetAlert CDN link -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<?php
  include "config.php";

  if ($userdata["role"] != 'Admin') {
      echo '<script>window.location.href = "dashboard";</script>';
      exit;
  }

  if (isset($_POST['create'])) {

      // CSRF token validation
      if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
          echo '<script>
              Swal.fire({
                  title: "Security Error",
                  text: "Invalid request. Please try again.",
                  icon: "error",
                  confirmButtonText: "Ok"
              }).then(() => { window.location.href = "adduser"; });
          </script>';
          exit;
      }

      // Sanitize all inputs to prevent SQL injection
      $mobile   = mysqli_real_escape_string($conn, trim($_POST['mobile']));
      $email    = mysqli_real_escape_string($conn, trim($_POST['email']));
      $password = $_POST['password'];
      $name     = mysqli_real_escape_string($conn, trim($_POST['name']));
      $company  = mysqli_real_escape_string($conn, trim($_POST['company']));
      $pin      = mysqli_real_escape_string($conn, trim($_POST['pin']));
      $pan      = mysqli_real_escape_string($conn, strtoupper(trim($_POST['pan'])));
      $aadhaar  = mysqli_real_escape_string($conn, trim($_POST['aadhaar']));
      $location = mysqli_real_escape_string($conn, trim($_POST['location']));

      // Check if the mobile number already exists
      $checkMobileResult = mysqli_query($conn, "SELECT id FROM `users` WHERE `mobile` = '$mobile'");
      // Check if the email already exists
      $checkEmailResult  = mysqli_query($conn, "SELECT id FROM `users` WHERE `email` = '$email'");

      if (mysqli_num_rows($checkMobileResult) > 0) {
          echo '<script>
              Swal.fire({
                  title: "Mobile Already Exists!",
                  text: "This mobile number is already registered. Please use a different number.",
                  icon: "error",
                  confirmButtonText: "Ok"
              }).then(() => { window.location.href = "adduser"; });
          </script>';
          exit;
      } elseif (mysqli_num_rows($checkEmailResult) > 0) {
          echo '<script>
              Swal.fire({
                  title: "Email Already Exists!",
                  text: "This email address is already registered. Please use a different email.",
                  icon: "error",
                  confirmButtonText: "Ok"
              }).then(() => { window.location.href = "adduser"; });
          </script>';
          exit;
      } else {
          $key   = md5(rand(00000000, 99999999));
          $pass  = password_hash($password, PASSWORD_BCRYPT);
          // FIX: was (date("Y-m-d") + 3) which produced wrong arithmetic result
          $today = date("Y-m-d", strtotime("+3 days"));

          $register = "INSERT INTO `users`(`name`, `mobile`, `role`, `password`, `email`, `company`, `pin`, `pan`, `aadhaar`, `location`, `user_token`, `expiry`)
                       VALUES ('$name','$mobile','User','$pass','$email','$company','$pin','$pan','$aadhaar','$location','$key','$today')";
          $result = mysqli_query($conn, $register);

          if ($result) {
              echo '<script>
                  Swal.fire({
                      title: "User Added Successfully!",
                      text: "The new user account has been created.",
                      icon: "success",
                      confirmButtonText: "Ok"
                  }).then(() => { window.location.href = "dashboard"; });
              </script>';
              exit;
          } else {
              echo '<script>
                  Swal.fire({
                      title: "Something Went Wrong!",
                      text: "Could not create the user. Please try again.",
                      icon: "error",
                      confirmButtonText: "Ok"
                  }).then(() => { window.location.href = "adduser"; });
              </script>';
              exit;
          }
      }
  }
?>

<main class="app-content">
    <div class="app-title">
        <div>
            <h1><i class="fa fa-user-plus"></i> Add User</h1>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
            <li class="breadcrumb-item"><a href="dashboard">Dashboard</a></li>
        </ul>
    </div>

    <div class="tile mb-4">
        <div class="tile-title-w-btn">
            <h4 class="page-title">Add New User</h4>
        </div>

        <form class="row mb-4" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

            <div class="col-md-6 mb-2">
                <label>Mobile Number</label>
                <input type="text" name="mobile" placeholder="Enter Mobile Number" class="form-control"
                       oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);" required />
            </div>
            <div class="col-md-6 mb-2">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter Password" class="form-control" required />
            </div>
            <div class="col-md-6 mb-2">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="Enter Email Address" class="form-control" required />
            </div>
            <div class="col-md-6 mb-2">
                <label>Full Name</label>
                <input type="text" name="name" placeholder="Enter Name" class="form-control" required />
            </div>
            <div class="col-md-6 mb-2">
                <label>Company</label>
                <input type="text" name="company" placeholder="Enter Company" class="form-control" required />
            </div>
            <div class="col-md-6 mb-2">
                <label>Area PIN</label>
                <input type="text" name="pin" placeholder="Area Pin" class="form-control"
                       oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);" required />
            </div>
            <div class="col-md-6 mb-2">
                <label>PAN Number</label>
                <input type="text" name="pan" placeholder="Enter PAN Number (AAAAANNNNA)" class="form-control"
                       pattern="[A-Za-z]{5}[0-9]{4}[A-Za-z]{1}"
                       oninput="this.value = this.value.toUpperCase();" maxlength="10" required />
            </div>
            <div class="col-md-6 mb-2">
                <label>Aadhaar Number</label>
                <input type="text" name="aadhaar" placeholder="Enter Aadhaar Number" class="form-control"
                       oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 12);" required />
            </div>
            <div class="col-md-12 mb-2">
                <label>Location</label>
                <input type="text" name="location" placeholder="Enter Location" class="form-control" required />
            </div>
            <div class="col-md-12 mb-2 mt-2">
                <button type="submit" name="create" class="btn btn-primary btn-sm">Add Now</button>
            </div>
        </form>
    </div>
</main>

    <script src="js/jquery-3.2.1.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <script src="js/main.js"></script>
    <script src="js/mainscript.js"></script>
</body>
</html>
