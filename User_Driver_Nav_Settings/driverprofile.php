  <?php
  session_start();
  $conn = mysqli_connect("localhost", "root", "", "marketplace");

  if (!$conn) {
      die("Connection failed: " . mysqli_connect_error());
  }

  // Check if the user is logged in
  if (!isset($_SESSION['Username'])) {
      header("Location: driver.php"); // or wherever your login page is
      exit();
  }

  $Username = $_SESSION['Username'];
  $query = "SELECT * FROM driver_signup WHERE Username = ?";
  $stmt = mysqli_prepare($conn, $query);

  if ($stmt) {
      mysqli_stmt_bind_param($stmt, "s", $Username);
      mysqli_stmt_execute($stmt);
      $result = mysqli_stmt_get_result($stmt);
      $driver = mysqli_fetch_assoc($result);
      mysqli_stmt_close($stmt);
  } else {
      die("Database error: " . mysqli_error($conn));
  }

  // Handle password change
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_pass'])) {
      $current = trim($_POST['current-password']);
      $new = trim($_POST['new-password']);

      // Get hashed password from DB
      $query = "SELECT Password FROM driver_signup WHERE Username = ?";
      $stmt = mysqli_prepare($conn, $query);
      mysqli_stmt_bind_param($stmt, "s", $Username);
      mysqli_stmt_execute($stmt);
      $result = mysqli_stmt_get_result($stmt);
      $user = mysqli_fetch_assoc($result);
      mysqli_stmt_close($stmt);

      if ($user && password_verify($current, $user['Password'])) {
          // Hash and update new password
          $newHashed = password_hash($new, PASSWORD_DEFAULT);
          $update = "UPDATE driver_signup SET Password = ? WHERE Username = ?";
          $stmt = mysqli_prepare($conn, $update);
          mysqli_stmt_bind_param($stmt, "ss", $newHashed, $Username);
          mysqli_stmt_execute($stmt);
          mysqli_stmt_close($stmt);

          header("Location: driverprofile.php?success=1");
          exit();
      } else {
          header("Location: driverprofile.php?error=wrongpass");
          exit();
      }
  }

  
  ?>

  <html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>Driver Profile</title>
    <link rel="stylesheet" href="driversProfile.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Poppins" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
  </head>
  <body>
    <aside>
      <div class="logo">
        <img src="../5. pictures/driver.png" alt="CartIT x Driver Logo" />
      </div>
      <hr class="logo-divider">
      <p class="menu-title">Main Menu</p>

      <nav>
        <ul class="sidebar-list">
    <li>
            <a href="../User_Driver_Nav_Dashboard/nav_dashboard.php"><i class='bx bx-layer'></i>Dashboard</a>
          </li>
          <li>
            <a href="../User_Driver_Nav_Deliviries/deliviries.php"><i class='bx bx-package'></i>Deliveries</a>
          </li>
          <li>
            <a href="../User_Driver_Nav_Withdrawal/withdraw.php"><i class='bx bx-money'></i>Cash Deposit</a>
          </li>
          <li class="active">
            <a href="../User_Driver_Nav_Settings/driverprofile.php"><i class='bx bx-user'></i>My Profile</a>
          </li>
        </ul>
      </nav>

<form action="../User_Driver_SignIn/driver.php" method="POST">
  <button type="submit" name="logout" class="logout">
    <i class='bx bx-log-out'></i> Log-out
  </button>
</form>


    </aside>

    <?php
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: ../User_Driver_SignIn/driver.php"); 
    exit();
}
  ?>

      <main>
        <header class="user-info-container">
          <div class="user-info-top">
            <div class="user-profile">
              <i class='bx bxs-user-circle'></i>
            </div>
            <div class="user-text">
  <p>@<?php echo htmlspecialchars($driver['Username']); ?></p>
  <p><?php echo htmlspecialchars($driver['EmailAdd']); ?></p>
    </div>
            </div>
          </div>
          <hr class="user-info-divider">
        </header>


  <section class="profile">
    <h2>Profile</h2>
    <hr class="profile-divider">
    
    <!-- Display fields -->
    <dl id="profile-display">
      <dt>Driver Name</dt>
      <dd id="display-username"><?php echo htmlspecialchars($driver['Username']); ?></dd>
      <dt>Email</dt>
      <dd id="display-email"><?php echo htmlspecialchars($driver['EmailAdd']); ?></dd>
      <dt>Phone number</dt>
      <dd id="display-phone"><?php echo htmlspecialchars($driver['PhoneNum']); ?></dd>
    </dl>
  </section>

  <!--Change Password-->
  <section class="change-password">
    <h2>Change Password</h2>
    <hr class="profile-divider" />
  <form id="password-form" method="POST" action="">
      <div class="form-group">
        <label for="current-password">Current password</label>
        <div class="input-wrapper">
          <input type="password" id="current-password" name="current-password" required>
          <span class="material-symbols-outlined" onclick="togglePassword('current-password', this)">visibility</span>
        </div>
      </div>

      <div class="form-group">
        <label for="new-password">New password</label>
        <div class="input-wrapper">
          <input type="password" id="new-password" name="new-password" required>
          <span class="material-symbols-outlined" onclick="togglePassword('new-password', this)">visibility</span>
        </div>
      </div>

      <div class="form-group">
        <label for="confirm-password">Confirm new password</label>
        <div class="input-wrapper">
          <input type="password" id="confirm-password" name="confirm-password" required>
          <span class="material-symbols-outlined" onclick="togglePassword('confirm-password', this)">visibility</span>
        </div>
      </div>

  <button type="submit" name="change_pass" id="change_pass" class="change_pass">Change Password</button>

    </form>
  </section>

  <!--Danger zone-->

      <section class="danger-zone">
        <h2>Danger Zone</h2>
        <p>Deleting your account is permanent and cannot be undone.</p>
        <hr class="profile-divider">
        <span style="color:blue"><strong>Action Will:</strong></span>
        <ul>
          <li>Remove your shop and all associated products</li>
          <li>Cancel any active listings</li>
          <li>Delete your order and payout history</li>
          <li>Prevent any future logins using this account</li>
        </ul>
        <button id="delete-btn" class="delete-account">Delete Account</button>
        <p>Feel free to contact <a href="mailto:cartit.support@gmail.com">cartit.support@gmail.com</a> with any questions.</p>
      </section>
    </main>

    <script>
    document.getElementById('delete-btn').addEventListener('click', function () {
      Swal.fire({
        title: 'Are you sure you want to delete your account?',
        text: "Deleting your account is permanent and cannot be undone.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes',
        cancelButtonText: 'No',
        reverseButtons: true,
        backdrop: true,
        showClass: {
          popup: 'animate__animated animate__fadeInDown'
        },
        hideClass: {
          popup: 'animate__animated animate__fadeOutUp'
        }
      }).then((result) => {
        if (result.isConfirmed) {
          fetch('deleteaccount.php', { method: 'POST' })
            .then(res => res.text())
            .then(data => {
              if (data.trim() === "success") {
                Swal.fire('Deleted!', 'Your account has been deleted.', 'success')
                  .then(() => window.location.href = 'driver.php');
              } else {
                Swal.fire('Error', data, 'error');
              }
            });
        }
      });
    });
  </script>
  </body>
  </html>
  <?php if (isset($_GET['success'])): ?>
  <script>
    Swal.fire({
      title: 'Password changed successfully!',
      icon: 'success',
      confirmButtonText: 'OK'
    });
  </script>
  <?php elseif (isset($_GET['error']) && $_GET['error'] === 'wrongpass'): ?>
  <script>
    Swal.fire({
      title: 'Incorrect current password!',
      icon: 'error',
      confirmButtonText: 'Retry'
    });
  </script>
  <?php endif; ?>


  <script>
    function togglePassword(id, icon) {
      const input = document.getElementById(id);
      const isPassword = input.type === "password";
      input.type = isPassword ? "text" : "password";
      icon.classList.toggle('bx-show');
      icon.classList.toggle('bx-hide');
    }

  document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('change_pass').addEventListener('click', function () {
      Swal.fire({
        title: 'Password Changed Successfully',
        icon: 'success',
        confirmButtonText: 'OK',
        customClass: {
          content: 'no-wrap-text'
        },
        showClass: {
          popup: 'animateanimated animatefadeInDown'
        },
        hideClass: {
          popup: 'animateanimated animatefadeOutUp'
        }
      });
    });
  });

    function togglePassword(id, icon) {
    const input = document.getElementById(id);
    const isPassword = input.type === "password";
    input.type = isPassword ? "text" : "password";
    icon.textContent = isPassword ? "visibility_off" : "visibility";
  }
  document.addEventListener('DOMContentLoaded', function () {
      const form = document.getElementById('password-form');

      form.addEventListener('submit', function (e) {
        e.preventDefault(); // Prevent the default form submission

        const newPass = document.getElementById('new-password').value;
        const confirmPass = document.getElementById('confirm-password').value;

        if (newPass !== confirmPass) {
          Swal.fire({
            title: 'Passwords do not match',
            icon: 'error',
            confirmButtonText: 'Try Again'
          });
          return;
        }

        // If validation passes, show success alert, then submit
        Swal.fire({
          title: 'Password has been Changed',
          icon: 'success',
          confirmButtonText: 'OK'
        }).then(() => {
          form.submit(); // Now submit the form to changepass.php
        });
      });
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <button id="change_pass" class="bg-blue-800 text-white px-4 py-2 rounded hover:bg-blue-900">
  </button>
  </section>

