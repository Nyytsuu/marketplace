<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "marketplace");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$error_message = "";
$signup_error = "";

if ($_SERVER["REQUEST_METHOD"] === 'POST') {
    if (isset($_POST['drivers'])) {
        $full_name = $_POST['full_name'] ?? '';
        $password = $_POST['password'] ?? '';

        // Check if it's a sign-in attempt (only name + password fields present)
        if (!isset($_POST['email']) && !isset($_POST['phone_number'])) {
            // Handle sign-in
            $query = "SELECT * FROM drivers WHERE full_name=? LIMIT 1";
            $stmt = mysqli_prepare($conn, $query);

            if (!$stmt) {
                die("Prepare failed: " . mysqli_error($conn));
            }

            mysqli_stmt_bind_param($stmt, "s", $full_name);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);

            $status = "FAILED";
            $driverID = null;

            // Plain text password check
            if ($row && $password === $row['password']) {
                $_SESSION['full_name'] = $row['full_name'];
                $driverID = $row['ID'];
                $status = "SUCCESS";
            }

            // Log sign-in attempt
            if ($driverID !== null) {
                $logQuery = "INSERT INTO driver_signin (DriverID, full_name, Status) VALUES (?, ?, ?)";
                $logStmt = mysqli_prepare($conn, $logQuery);
                if ($logStmt) {
                    mysqli_stmt_bind_param($logStmt, "iss", $driverID, $full_name, $status);
                    mysqli_stmt_execute($logStmt);
                    mysqli_stmt_close($logStmt);
                }
            }

            if ($status === "SUCCESS") {
                header('Location: ../user_driver_nav_dashboard/nav_dashboard.php');
                exit();
            } else {
                $error_message = "Invalid name or password.";
            }

            mysqli_stmt_close($stmt);
        } else {
            // Handle sign-up
            $email = $_POST['email'] ?? '';
            $phone_number = $_POST['phone_number'] ?? '';

            if (!$full_name || !$email || !$phone_number || !$password) {
                $signup_error = "All fields are required.";
                $_SESSION['form_mode'] = 'signup';
            } else {
                $checkQuery = "SELECT full_name FROM drivers WHERE full_name = ?";
                $checkStmt = mysqli_prepare($conn, $checkQuery);

                if (!$checkStmt) {
                    die("Prepare failed: " . mysqli_error($conn));
                }

                mysqli_stmt_bind_param($checkStmt, "s", $full_name);
                mysqli_stmt_execute($checkStmt);
                mysqli_stmt_store_result($checkStmt);

                if (mysqli_stmt_num_rows($checkStmt) > 0) {
                    $signup_error = "Name '{$full_name}' already exists. Please choose another.";
                    $_SESSION['form_mode'] = 'signup';
                } else {
                    // Insert plain text password (again, not secure for production)
                    $insertQuery = "INSERT INTO drivers (full_name, email, phone_number, password) VALUES (?, ?, ?, ?)";
                    $insertStmt = mysqli_prepare($conn, $insertQuery);

                    if (!$insertStmt) {
                        die("Prepare failed: " . mysqli_error($conn));
                    }

                    mysqli_stmt_bind_param($insertStmt, "ssss", $full_name, $email, $phone_number, $password);
                    mysqli_stmt_execute($insertStmt);
                    mysqli_stmt_close($insertStmt);

                    unset($_SESSION['form_mode']);
                    $_SESSION['full_name'] = $full_name;
                    header('Location: driver.php');
                    exit();
                }

                mysqli_stmt_close($checkStmt);
            }
        }
    }
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>CartIT Driver Sign Up</title>
  <link rel="stylesheet" href="dsignup.css"/>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    .error-message {
      color: red;
      font-size: 14px;
      margin-top: 5px;
    }
  </style>
</head>


<body>
<header class="top-bar">
  <div class="top-bar-container">
    <span>Follow us:</span>
    <div class="social-icon">
      <i class='bx bxl-facebook'></i>
      <i class='bx bxl-instagram'></i>
      <i class='bx bxl-twitter'></i>
    </div>
    <div class="divider">|</div>
    <a href="../User_Driver_SignIn/driver.php?mode=signup">Be a Rider</a>
    <div class="divider">|</div>
    <a href="../signup.php">Be a Seller</a>
    <div class="divider">|</div>
    <a href="../homepage.php">Start Shopping</a>
  </div>
</header>

<header class="main-header">
  <div class="main-header-container">
    <nav class="nav-left">
      <a href="homepage.php">Home</a>
      <a href="Header_About_Us/aboutus.php">About</a>
      <a href="#" class="logo">
        <img src="../5. pictures/driver.png" alt="CartIT Logo" />
      </a>
      <a href="../Header_Contact_Us/contactus.php">Contact</a>
      <a href="https://www.facebook.com/people/Bsit-2A/61556396265842/">Follow Us</a>
    </nav>
  </div>
</header>

<div class="main-content">
  <div class="container main-flex">
    <div class="left">
      <h4>CartIT Marketplace</h4>
      <h1>Be a CartIT Delivery Buddy!</h1>
      <p>Got a bike, motor, or car? Join CartIT as a delivery partner and earn while helping people get what they need—fast and easy!</p>
      <ul>
        <li>✅ Flexible hours</li>
        <li>✅ Real income</li>
      </ul>
      <em>Start your journey today and deliver with purpose!</em>
    </div>

    <div class="wrapper">
      <div class="form-box">
        <div class="toggle-buttons">
          <button id="toggle-signin" class="active">Sign In</button>
          <button id="toggle-signup">Sign Up</button>
        </div>

        <div class="form-container">
          <!-- Sign In Form -->
          <form id="signin-form" class="form active" method="POST" action="">
            <label>Full Name</label>
            <div class="input-box">
              <input type="text" name="full_name" placeholder="Enter your full name" required>
            </div>

            <label>Password</label>
            <div class="input-box" style="position: relative;">
              <input type="password" id="signin-password" name="password" placeholder="Enter your password" required />
              <button type="button" class="toggle-password" data-target="signin-password" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #555;">
                <i class="fa fa-eye"></i>
              </button>
            </div>

            <?php if (!empty($error_message)): ?>
              <p class="error-message"><?php echo $error_message; ?></p>
            <?php endif; ?>

            <button type="submit" name="drivers">Sign In</button>
          </form>

          <!-- Sign Up Form -->
          <form id="signup-form" class="form" method="POST" action="">
  <label>Full Name</label>
  <div class="input-box">
    <input type="text" name="full_name" placeholder="Enter your full name" required>
  </div>

  <?php if (!empty($signup_error)): ?>
    <p class="error-message"><?php echo $signup_error; ?></p>
  <?php endif; ?>

  <label>Email Address</label>
  <div class="input-box">
    <input type="email" name="email" placeholder="Enter your email address" required>
  </div>

  <label>Phone Number</label>
  <div class="input-box">
    <input type="text" name="phone_number" placeholder="Enter your phone number" required>
  </div>

  <label>Password</label>
  <div class="input-box" style="position: relative;">
    <input type="password" id="signup-password" name="Password" placeholder="Enter your password" required />
    <button type="button" class="toggle-password" data-target="signup-password" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #555;">
      <i class="fa fa-eye"></i>
    </button>
  </div>

  <label class="terms">
    <input type="checkbox" required>
    I agree to CartIT's <a href="#">Terms of Condition</a> & <a href="#">Privacy Policy</a>
  </label>

  <button type="submit" name="drivers">Sign Up</button>
</form>
        </div>
      </div>
    </div>
  </div>
</div>

</main>

  <!--Footer-->

  <footer class="footer">
    <div class="footer-top">
      <div class="footer-logo">
        <img src="../5. pictures/white logo.png" alt="CartIT Logo">
        <p>CartIT – Where Smart Shopping Begins. Discover quality finds, hot deals, and fast delivery—all in one cart.</p>
        <div class="social-icons">
          <i class='bx bxl-facebook'></i>
          <i class='bx bxl-instagram'></i>
          <i class='bx bxl-twitter'></i>
        </div>
      </div>
      <div class="footer-links">
        <h3>My Account</h3>
        <a href="#">My Account</a>
        <a href="#">Order History</a>
        <a href="#">Shopping Cart</a>
      </div>
      <div class="footer-links">
        <h3>Helps</h3>
        <a href="#">Contact</a>
        <a href="#">Terms & Condition</a>
        <a href="#">Privacy Policy</a>
      </div>
      <div class="footer-links">
        <h3>Proxy</h3>
        <a href="#">About Us</a>
        <a href="#">Browse All Product</a>
      </div>
      <div class="footer-contact">
        <h3>Customer Supports:</h3>
        <p>(63+) 000 0000 000</p>
        <h3>Contact Us</h3>
        <p>info@cartit.com</p>
      </div>
    </div>
  
    <div class="footer-bottom">
      <p>&copy; 2025 CartIT eCommerce. All Rights Reserved</p>
      <div class="footer-payments">
        <div class="payment-icon">
          <i class='bx bx-lock'></i>
          <span>
            Secure<br>Payment
          </span>
        </div>
        <div class="payment-icons">
          <i class='bx bxl-visa'></i>
          <i class='bx bxl-mastercard'></i>
        </div>
      </div>
    </div>
  </footer> 
</body>
</html>

<script>

  document.querySelectorAll('.toggle-password').forEach(button => {
  button.addEventListener('click', () => {
    const targetId = button.getAttribute('data-target');
    const input = document.getElementById(targetId);

    if (input.type === 'password') {
      input.type = 'text';
      button.innerHTML = '<i class="fa fa-eye-slash"></i>';
    } else {
      input.type = 'password';
      button.innerHTML = '<i class="fa fa-eye"></i>';
    }
  });
});


<!-- Your animation script here -->
  const formContainer = document.querySelector('.form-container');

  document.getElementById('toggle-signin').addEventListener('click', () => {
    document.getElementById('signin-form').classList.add('active');
    document.getElementById('signup-form').classList.remove('active');
    document.getElementById('toggle-signin').classList.add('active');
    document.getElementById('toggle-signup').classList.remove('active');

    formContainer.classList.add('signin-active');
    formContainer.classList.remove('signup-active');

    document.querySelector('.left h1').textContent = 'Hey, Delivery Champ!';
    document.querySelector('.left p').textContent = 'Glad to have you here. Log in to see what’s waiting for you today—your next delivery, your earnings, and more. Let’s hit the road and deliver with heart!';
    document.querySelector('.left ul').style.display = 'none';
    document.querySelector('.left em').style.display = 'none';
  });

  document.getElementById('toggle-signup').addEventListener('click', () => {
    document.getElementById('signup-form').classList.add('active');
    document.getElementById('signin-form').classList.remove('active');
    document.getElementById('toggle-signup').classList.add('active');
    document.getElementById('toggle-signin').classList.remove('active');

    formContainer.classList.add('signup-active');
    formContainer.classList.remove('signin-active');

    document.querySelector('.left h1').textContent = 'Be a CartIT Delivery Buddy!';
    document.querySelector('.left p').textContent = 'Got a bike, motor, or car? Join CartIT as a delivery partner and earn while helping people get what they need—fast and easy!';
    document.querySelector('.left ul').style.display = 'block';
    document.querySelector('.left em').style.display = 'block';
  });
</script>

<!-- ✅ Correctly inject script AFTER the animation code -->
<?php if (isset($_SESSION['form_mode']) && $_SESSION['form_mode'] === 'signup'): ?>
  <script>
    window.addEventListener('DOMContentLoaded', () => {
      document.getElementById('toggle-signup').click();
    });
  </script>
  <?php unset($_SESSION['form_mode']); ?>
<?php endif; ?>
